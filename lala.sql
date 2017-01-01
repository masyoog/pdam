--
-- PostgreSQL database dump
--

-- Dumped from database version 9.4.10
-- Dumped by pg_dump version 9.4.10
-- Started on 2017-01-01 15:37:18 WIB

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 13 (class 2615 OID 17170)
-- Name: dashboard; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA dashboard;


SET search_path = dashboard, pg_catalog;

--
-- TOC entry 356 (class 1255 OID 17171)
-- Name: fn_bayar(character varying, character varying); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_bayar(_no_reff1 character varying, _no_reff2 character varying) RETURNS jsonb
    LANGUAGE plpgsql
    AS $$DECLARE 
	_out JSONB;
	_data JSONB;
	_lembar integer;
	_tagihan numeric(14,2);
	_out_temp pembayaran%ROWTYPE;
	_id_pelanggan pelanggan.id%type;
BEGIN

	SELECT INTO _id_pelanggan id FROM pelanggan WHERE no_reff1=_no_reff1 OR no_reff2=_no_reff2;
	
	IF _id_pelanggan IS NULL THEN
		_out := '{"error":1, "desc": "Nomor Referensi Pelanggan tidak dikenali"}'::jsonb;
		RETURN _out;
	END IF;

	--PERFORM fn_generate_denda(_id_pelanggan);
		
	SELECT 
		INTO _data, _lembar, _tagihan json_agg(v_tagihan), COUNT(id_pelanggan), SUM(total) 
	FROM v_tagihan 
	WHERE id_pelanggan = _id_pelanggan 
		AND periode<=fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval);
	 
	
	IF _tagihan IS NOT NULL THEN 
		INSERT INTO pembayaran (id_pelanggan, jumlah, jumlah_tagihan, item_tagihan)
			VALUES (_id_pelanggan, _tagihan, _lembar, _data) RETURNING * INTO _out_temp;

		INSERT INTO item_pembayaran (id_pembayaran, periode,id_item_tarif, dasar_hitung,tarif,nilai,id_pelanggan,deskripsi)
		SELECT _out_temp.id, periode,id_item_tarif, dasar_hitung,tarif,nilai,id_pelanggan,deskripsi FROM item_tagihan
			WHERE id_pelanggan = _id_pelanggan AND periode<=fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval)
		UNION
		SELECT _out_temp.id, periode,id_item_tarif, dasar_hitung,tarif,nilai,id_pelanggan,deskripsi FROM item_tagihan_non_rutin
			WHERE id_pelanggan = _id_pelanggan AND periode<=fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval);

		IF NOT FOUND THEN
			_out := '{"error":1, "desc": "Gagal Mengupdate Data Pembayaran"}'::jsonb;
		ELSE
			DELETE FROM item_tagihan WHERE id_pelanggan = _id_pelanggan AND periode<=fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval);
			DELETE FROM item_tagihan_non_rutin WHERE id_pelanggan = _id_pelanggan AND periode<=fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval);
			_out := row_to_json(_out_temp);
		END IF;			
		
	ELSE
		_out := '{"error":1, "desc": "Tidak ada tagihan"}'::jsonb;
	END IF;
	RETURN _out;
END$$;


--
-- TOC entry 357 (class 1255 OID 17172)
-- Name: fn_generate_denda(integer); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_generate_denda(_id_pelanggan integer DEFAULT 0) RETURNS void
    LANGUAGE plpgsql
    AS $$DECLARE
	_rs_tarif RECORD;
	_rs_item_tarif RECORD;
	_rs_pelanggan pelanggan%rowtype;
	_periode character varying(7);
	_rs_tagihan RECORD;
	_due_date text;
BEGIN
	--GET SETTING JATUH TEMPO;
	SELECT INTO _due_date CASE WHEN length(nilai) < 2 THEN '0'||nilai ELSE nilai END FROM sys_config WHERE tipe='TANGGAL' AND nama='JATUH TEMPO';
	
	SELECT INTO _rs_pelanggan * FROM pelanggan WHERE id=_id_pelanggan;
	IF FOUND THEN
		raise notice 'found pelanggan %', _id_pelanggan;
		FOR _rs_tagihan IN
			SELECT DISTINCT periode FROM item_tagihan WHERE id_pelanggan = _id_pelanggan
			AND periode<=fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval)  ORDER BY periode
		LOOP
			raise notice 'check periode %', _rs_tagihan.periode;

			DELETE FROM item_tagihan_non_rutin WHERE id_pelanggan = _rs_pelanggan.id AND periode = _rs_tagihan.periode ;
			DELETE FROM rpt_telat WHERE id_pelanggan = _rs_pelanggan.id AND periode = _rs_tagihan.periode ;
			raise notice 'check if overdue % duedate %', now()::date, _due_date::text|| '-' || _rs_tagihan.periode::text;
			IF now() > to_timestamp(_due_date::text|| '-' || _rs_tagihan.periode, 'DD-MM-YYYY') THEN
				raise notice 'payment date overdue, duedate %', to_timestamp( _due_date::text|| '-' || _rs_tagihan.periode, 'DD-MM-YYYY')::date;	
				--DENDA
				FOR _rs_item_tarif IN
					SELECT * FROM mt_item_tarif 
					WHERE status=1 AND tipe_tarif = 'DENDA'
					ORDER BY urutan ASC
				LOOP
					_rs_tarif := NULL;
					FOR _rs_tarif IN 
					SELECT * FROM mt_tarif 
					WHERE 
						id_tipe_pelanggan = _rs_pelanggan.id_tipe_pelanggan
						AND id_item_tarif = _rs_item_tarif.id ORDER BY batas DESC
					LOOP
						raise notice 'INSERT denda %', _rs_tarif.id_item_tarif;  			
						INSERT INTO item_tagihan_non_rutin 
							(id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai)
						VALUES 
							(_rs_pelanggan.id, _rs_tagihan.periode, _rs_tarif.id_item_tarif, 1, _rs_tarif.tarif, (_rs_tarif.tarif));
					END LOOP;
				END LOOP;
				
				INSERT INTO rpt_telat(id_pelanggan, periode, duedate, paymentdate) 
				VALUES(
					_rs_pelanggan.id, 
					_rs_tagihan.periode, 
					to_timestamp( _due_date::text|| '-' || _rs_tagihan.periode, 'DD-MM-YYYY')::date,
					now()::date
				);
			END IF;			
		END LOOP;
	END IF;
END$$;


--
-- TOC entry 358 (class 1255 OID 17173)
-- Name: fn_get_periode(character varying, interval); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_get_periode(_periode character varying, _interval interval) RETURNS character varying
    LANGUAGE plpgsql
    AS $$DECLARE 
	_out character varying;
	_outtemp timestamp;
BEGIN
	
	--IF _bulan < 0 THEN   
	--	_outtemp := to_date(_periode, 'MM-YYYY') - interval _interval;
	--ELSE
		_outtemp := to_date(_periode, 'MM-YYYY') + _interval;
	--END IF;

	_out := to_char( _outtemp , 'MM-YYYY');
	
	return _out;
END$$;


--
-- TOC entry 359 (class 1255 OID 17174)
-- Name: fn_get_tagihan(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_get_tagihan() RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$DECLARE 
	_qry text;
	_qry_cat text;
	_qry_cat_column text;
	_rs RECORD;
	_rs_item_tarif RECORD;
BEGIN
		
	_qry_cat := '';
	_qry_cat_column := '';
	FOR _rs_item_tarif IN
		SELECT nama FROM mt_item_tarif 
		WHERE status=1
	LOOP
		IF _qry_cat <> '' THEN
			_qry_cat := _qry_cat || ',';	
			_qry_cat_column := _qry_cat_column || ',';
		END IF;		
		_qry_cat := _qry_cat || '"'|| _rs_item_tarif.nama ||'" numeric(10,2)';	
		_qry_cat_column := _qry_cat_column || '"'|| _rs_item_tarif.nama ||'"';	
	END LOOP;
	
	_qry := 'SELECT id_pelanggan, periode, __replace_column__  FROM (
		SELECT * FROM crosstab
		(
		  ''SELECT id_pelanggan, periode, mt_item_tarif.nama, nilai FROM mt_item_tarif left join item_tagihan ON item_tagihan.id_item_tarif = mt_item_tarif.id'',
		  ''SELECT DISTINCT nama FROM mt_item_tarif''
		)
		AS
		(
			id_pelanggan integer,	
			periode text,       
			__replace__
		)
		) as a
		where a.id_pelanggan IS NOT NULL';
		
	IF _qry_cat <> '' THEN
		_qry := replace(_qry, '__replace_column__', _qry_cat_column);
		_qry := replace(_qry, '__replace__', _qry_cat);
		RAISE NOTICE '%', _qry;	
	END IF;
	RETURN QUERY EXECUTE _qry;
END
$$;


--
-- TOC entry 360 (class 1255 OID 17175)
-- Name: fn_qry_tagihan(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_qry_tagihan() RETURNS text
    LANGUAGE plpgsql
    AS $$DECLARE 
	_qry text;
	_qry_cat text;
	_qry_cat_column text;
	_qry_total text;
	_rs RECORD;
	_rs_item_tarif RECORD;
BEGIN
		
	_qry_cat := '';
	_qry_cat_column := '';
	_qry_total := '';
	FOR _rs_item_tarif IN
		SELECT * FROM mt_item_tarif 
		WHERE status=1 AND is_rutin=1 ORDER BY id ASC
	LOOP
		IF _qry_cat <> '' THEN
			_qry_cat := _qry_cat || ',';	
			_qry_cat_column := _qry_cat_column || ',';
			_qry_total := _qry_total || '+';
		END IF;		
		_qry_cat := _qry_cat || '"'|| _rs_item_tarif.id ||'" numeric(10,2)';	
		_qry_cat_column := _qry_cat_column || 'coalesce(cttagihan."'|| _rs_item_tarif.id ||'",0.00) as "'|| _rs_item_tarif.id ||'"';
		_qry_total := _qry_total || '"'|| _rs_item_tarif.id ||'"';	
	END LOOP;
	
	_qry := 'SELECT *, (__replace_total__) as total FROM (
		SELECT cttagihan.row_name[1]::integer as id_pelanggan , cttagihan.row_name[2] as periode, __replace_column__  FROM crosstab
		(
		  ''SELECT ARRAY[id_pelanggan::text, periode::text] as row_name, id_item_tarif, SUM(nilai) AS nilai 
			FROM item_tagihan			
			GROUP BY id_pelanggan, periode, id_item_tarif	
			ORDER BY id_pelanggan, periode ASC'',
		  ''SELECT DISTINCT id FROM mt_item_tarif WHERE status=1 AND is_rutin=1 ORDER BY id ASC''
		)
		AS cttagihan
		(
			row_name text[],       
			__replace__
		)
		) as a
		where a.id_pelanggan IS NOT NULL';
		
	IF _qry_cat <> '' THEN
		_qry := replace(_qry, '__replace_column__', _qry_cat_column);
		_qry := replace(_qry, '__replace__', _qry_cat);
		_qry := replace(_qry, '__replace_total__', _qry_total);
		--RAISE NOTICE '%', _qry;	
	END IF;
	RETURN _qry;
END
$$;


--
-- TOC entry 361 (class 1255 OID 17176)
-- Name: fn_trg_grup_user_set_grup_akses(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_trg_grup_user_set_grup_akses() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	INSERT INTO sys_grup_akses (id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak)
		SELECT NEW.ID, id, 1, 1, 1, 1, 1 FROM sys_menu WHERE status=1;	
	RETURN NEW;
END;
$$;


--
-- TOC entry 362 (class 1255 OID 17177)
-- Name: fn_trg_hitung_pemakaian(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_trg_hitung_pemakaian() RETURNS trigger
    LANGUAGE plpgsql
    AS $$/*
DECLARE
	rs_tarif RECORD;
	rs_item_tarif RECORD;
	_dasar_hitung numeric(10,2);
	temp_jumlah_meter numeric(8,2);
	temp_pengurang numeric(8,2);
	_id_item_tarif_pemakaian integer;
	_id_tipe_pelanggan integer;
*/	
BEGIN
	--temp_jumlah_meter := 0;
	--temp_pengurang := 0;
	
	
	SELECT INTO NEW.meter_awal meter_akhir FROM pemakaian
	WHERE 
		id_pelanggan = NEW.id_pelanggan 
		AND periode < NEW.periode 
	ORDER by periode DESC LIMIT 1;
	
	IF NEW.meter_awal IS NULL OR NEW.meter_awal < 1 THEN
		NEW.meter_awal := 0;
	END IF;
	
	NEW.jumlah_meter := NEW.meter_akhir - NEW.meter_awal;
	/*
	temp_jumlah_meter := NEW.jumlah_meter;
	
	SELECT INTO _id_item_tarif_pemakaian id FROM  mt_item_tarif WHERE tipe_tarif='PEMAKAIAN';
	
	SELECT INTO _id_tipe_pelanggan id_tipe_pelanggan FROM  pelanggan WHERE id=NEW.id_pelanggan;
	
	--reset tagihan
	DELETE FROM item_tagihan 
		WHERE id_pelanggan = NEW.id_pelanggan 
			AND periode = NEW.periode;
			
	--TAGIHAN AIR		
	FOR rs_tarif IN 
		SELECT * FROM mt_tarif 
		WHERE 
			id_tipe_pelanggan = _id_tipe_pelanggan
			AND id_item_tarif = _id_item_tarif_pemakaian ORDER BY batas DESC
	LOOP
        	IF temp_jumlah_meter > rs_tarif.batas THEN
		
			temp_pengurang := temp_jumlah_meter - rs_tarif.batas;
			
			INSERT INTO item_tagihan 
				(id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai)
			VALUES 
				(NEW.id_pelanggan, NEW.periode, rs_tarif.id_item_tarif, temp_pengurang, rs_tarif.tarif, (temp_pengurang * rs_tarif.tarif));	
			
			temp_jumlah_meter := temp_jumlah_meter - temp_pengurang;		
		
		END IF;		
	END LOOP;
	
	--ADMINISTRASI
	FOR rs_item_tarif IN
		SELECT * FROM mt_item_tarif 
		WHERE status=1 AND is_rutin = 1 AND tipe_tarif = 'ADMINISTRASI'
		ORDER BY urutan ASC
	LOOP
		rs_tarif := NULL;
		FOR rs_tarif IN 
		SELECT * FROM mt_tarif 
		WHERE 
			id_tipe_pelanggan = _id_tipe_pelanggan
			AND id_item_tarif = rs_item_tarif.id ORDER BY batas DESC
		LOOP
						
			INSERT INTO item_tagihan 
				(id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai)
			VALUES 
				(NEW.id_pelanggan, NEW.periode, rs_tarif.id_item_tarif, 1, rs_tarif.tarif, (rs_tarif.tarif));	
				
		END LOOP;
	END LOOP;	
	
	--PAJAK
	rs_item_tarif := NULL;
	FOR rs_item_tarif IN
		SELECT 
			a.id,
			b.id_tipe_pelanggan,
			b.tarif,
			c.referensi 
		FROM mt_item_tarif a
		LEFT JOIN mt_tarif b ON
			b.id_item_tarif = a.id
		LEFT JOIN mt_item_tarif_referensi c ON
			c.id_item_tarif	= a.id
		WHERE 
			a.status = 1 AND
			a.tipe_tarif = 'PAJAK' AND
			a.is_rutin = 1 AND
			b.id_tipe_pelanggan = _id_tipe_pelanggan AND
			c.tipe_referensi = 'ITEM TARIF'
	LOOP
		SELECT INTO _dasar_hitung SUM(nilai) FROM item_tagihan
			WHERE periode=NEW.periode AND id_pelanggan=NEW.id_pelanggan AND id_item_tarif
			IN (SELECT id FROM mt_item_tarif WHERE kode=rs_item_tarif.referensi);
		IF _dasar_hitung IS NOT NULL THEN 	
			INSERT INTO item_tagihan 
				(id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai)
			VALUES 
				(NEW.id_pelanggan, NEW.periode, rs_item_tarif.id, _dasar_hitung, rs_item_tarif.tarif, (rs_item_tarif.tarif * _dasar_hitung / 100));
		END IF;		
	END LOOP;
	*/
	RETURN NEW;
END$$;


--
-- TOC entry 363 (class 1255 OID 17178)
-- Name: fn_trg_menu_set_grup_akses(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_trg_menu_set_grup_akses() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	INSERT INTO sys_grup_akses (id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak)
		SELECT id, NEW.id, 1, 1, 1, 1, 1 FROM sys_grup_user WHERE status=1;	
	RETURN NEW;
END;
$$;


--
-- TOC entry 364 (class 1255 OID 17179)
-- Name: fn_trg_petugas_set_password(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_trg_petugas_set_password() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
	IF NEW.userpassword IS NOT NULL OR NEW.userpassword <> '' THEN
		NEW.userpassword := crypt(NEW.userpassword::text, gen_salt('md5'));
	END IF;

	RETURN NEW;	
END;$$;


--
-- TOC entry 365 (class 1255 OID 17180)
-- Name: fn_trg_user_set_password(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_trg_user_set_password() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF NEW.userpassword IS NOT NULL OR NEW.userpassword <> '' THEN
		NEW.userpassword := crypt(NEW.userpassword::text, gen_salt('md5'));
	END IF;
	RETURN NEW;	
END;
$$;


--
-- TOC entry 366 (class 1255 OID 17181)
-- Name: fn_trg_validate_insert_antrian(); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION fn_trg_validate_insert_antrian() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE 
	_rs_pelanggan pelanggan%rowtype;
	_age INTERVAL;
BEGIN
	SELECT INTO _rs_pelanggan * FROM pelanggan WHERE id = NEW.id_pelanggan AND status=1;
	
	IF NOT FOUND OR _rs_pelanggan.tanggal_terpasang IS NULL THEN
		RETURN NULL;
	ELSE
		_age := AGE(now()::date, _rs_pelanggan.tanggal_terpasang);
		IF EXTRACT(YEAR FROM _age) >= 1 OR EXTRACT(MONTH FROM _age) >= 1 THEN
			RETURN NEW;		
		ELSE 
			RETURN NULL;
		END IF;	
	END IF;	
END;$$;


--
-- TOC entry 2657 (class 0 OID 0)
-- Dependencies: 366
-- Name: FUNCTION fn_trg_validate_insert_antrian(); Type: COMMENT; Schema: dashboard; Owner: -
--

COMMENT ON FUNCTION fn_trg_validate_insert_antrian() IS 'untuk mengecek apakah umur pelanggan lebih dari satu bulan berdasarkan tanggal terpasang';


--
-- TOC entry 367 (class 1255 OID 17182)
-- Name: sch_fn_insert_sch_generate_denda(integer); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION sch_fn_insert_sch_generate_denda(_max_row integer DEFAULT 100) RETURNS void
    LANGUAGE plpgsql
    AS $$DECLARE 
	_max_id_pelanggan integer;
	_periode character varying(7);
	_tgl smallint;
BEGIN
	
	SELECT INTO _tgl nilai::smallint FROM sys_config WHERE tipe='TANGGAL' AND nama='PREPARE DENDA';
	--raise notice 'tgl %', _tgl;
	--raise notice 'tgl %', to_char(now(), 'dd')::int; 
	IF _tgl = to_char(now(), 'DD')::int THEN
	  
		_periode := fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval);
		SELECT INTO _max_id_pelanggan COALESCE(max(id_pelanggan), 0) FROM sch_generate_denda WHERE status=0 AND periode=_periode;
		--raise notice 'max %', _max_id_pelanggan;	
		INSERT INTO sch_generate_denda (id_pelanggan, periode) 
			SELECT id, _periode FROM pelanggan WHERE id > _max_id_pelanggan AND status=1 LIMIT _max_row;  	
	END IF;		
END$$;


--
-- TOC entry 368 (class 1255 OID 17183)
-- Name: sch_fn_insert_sch_generate_tagihan(integer); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION sch_fn_insert_sch_generate_tagihan(_max_row integer DEFAULT 5000) RETURNS void
    LANGUAGE plpgsql
    AS $$DECLARE 
	_max_id_pelanggan integer;
	_periode character varying(7);
	_tgl smallint;
BEGIN
	
	SELECT INTO _tgl nilai::smallint FROM sys_config WHERE tipe='TANGGAL' AND nama='PREPARE TAGIHAN';
	--raise notice 'tgl %', _tgl;
	--raise notice 'tgl %', to_char(now(), 'dd')::int; 
	IF _tgl = to_char(now(), 'DD')::int THEN
	  
		_periode := fn_get_periode(to_char(now(), 'MM-YYYY'), '-1 month'::interval);
		SELECT INTO _max_id_pelanggan COALESCE(max(id_pelanggan), 0) 
		FROM sch_generate_tagihan WHERE status=0 AND periode=_periode;
		--raise notice 'max %', _max_id_pelanggan;	
		INSERT INTO sch_generate_tagihan (id_pelanggan, periode) 
			SELECT id, _periode FROM pelanggan 
			WHERE 
				id > _max_id_pelanggan AND 
				status=1 AND 
				tanggal_terpasang < now()::date
			LIMIT _max_row ;  	
	END IF;		
END$$;


--
-- TOC entry 369 (class 1255 OID 17184)
-- Name: sch_fn_process_sch_generate_tagihan(smallint); Type: FUNCTION; Schema: dashboard; Owner: -
--

CREATE FUNCTION sch_fn_process_sch_generate_tagihan(_max_row smallint DEFAULT 100) RETURNS void
    LANGUAGE plpgsql
    AS $$DECLARE 
	_rs RECORD;
	_rs_tarif RECORD;
	_rs_item_tarif RECORD;
	_rs_pelanggan pelanggan%rowtype;
	_rs_pemakaian pemakaian%rowtype;
	_temp_jumlah_meter numeric(8,2);
	_temp_pengurang numeric(8,2);
	_dasar_hitung numeric(10,2);
	_tgl smallint;
BEGIN
	--SELECT INTO _tgl nilai::smallint FROM sys_config WHERE tipe='TANGGAL' AND nama='TAGIHAN';
	--IF _tgl <> to_char(now(), 'DD')::int THEN
	--	RETURN;
	--END IF;
	
	FOR _rs IN SELECT * FROM sch_generate_tagihan WHERE status=0 ORDER BY id LIMIT _max_row 
	LOOP
		SELECT INTO _rs_pelanggan * FROM pelanggan WHERE id = _rs.id_pelanggan;
		
		--reset tagihan
		DELETE FROM item_tagihan WHERE id_pelanggan = _rs.id_pelanggan AND periode = _rs.periode;

		--get pemakaian
		SELECT INTO _rs_pemakaian * FROM pemakaian WHERE id_pelanggan = _rs.id_pelanggan AND periode=_rs.periode;
		_temp_jumlah_meter := _rs_pemakaian.jumlah_meter;

		--TAGIHAN AIR	
		_rs_tarif := null;	
		FOR _rs_tarif IN 
			SELECT * FROM mt_tarif 
			WHERE 
				id_tipe_pelanggan = _rs_pelanggan.id_tipe_pelanggan 
				AND id_item_tarif IN (SELECT id FROM  mt_item_tarif WHERE tipe_tarif='PEMAKAIAN') 
			ORDER BY batas DESC
		LOOP
			IF _temp_jumlah_meter > _rs_tarif.batas THEN
			
				_temp_pengurang := _temp_jumlah_meter - _rs_tarif.batas;
				
				INSERT INTO item_tagihan 
				(
					id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai
				)
				VALUES 
				(
					_rs.id_pelanggan, _rs.periode, _rs_tarif.id_item_tarif, _temp_pengurang, _rs_tarif.tarif, 
					(_temp_pengurang * _rs_tarif.tarif)
				);	
				
				_temp_jumlah_meter := _temp_jumlah_meter - _temp_pengurang;		
			
			END IF;		
		END LOOP;

		--ADMINISTRASI
		FOR _rs_item_tarif IN
			SELECT * FROM mt_item_tarif 
			WHERE status=1 AND is_rutin = 1 AND tipe_tarif = 'ADMINISTRASI'
			ORDER BY urutan ASC
		LOOP
			_rs_tarif := NULL;
			FOR _rs_tarif IN 
			SELECT * FROM mt_tarif 
			WHERE 
				id_tipe_pelanggan = _rs_pelanggan.id_tipe_pelanggan
				AND id_item_tarif = _rs_item_tarif.id ORDER BY batas DESC
			LOOP
							
				INSERT INTO item_tagihan 
					(id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai)
				VALUES 
					(_rs.id_pelanggan, _rs.periode, _rs_tarif.id_item_tarif, 1, _rs_tarif.tarif, (_rs_tarif.tarif));	
					
			END LOOP;
		END LOOP;

		--PAJAK
		_rs_item_tarif := NULL;
		FOR _rs_item_tarif IN
			SELECT 
				a.id,
				b.id_tipe_pelanggan,
				b.tarif,
				c.referensi 
			FROM mt_item_tarif a
			LEFT JOIN mt_tarif b ON
				b.id_item_tarif = a.id
			LEFT JOIN mt_item_tarif_referensi c ON
				c.id_item_tarif	= a.id
			WHERE 
				a.status = 1 AND
				a.tipe_tarif = 'PAJAK' AND
				a.is_rutin = 1 AND
				b.id_tipe_pelanggan = _rs_pelanggan.id_tipe_pelanggan AND
				c.tipe_referensi = 'ITEM TARIF'
		LOOP
			SELECT INTO _dasar_hitung SUM(nilai) FROM item_tagihan
				WHERE periode=_rs.periode 
					AND id_pelanggan=_rs.id_pelanggan 
					AND id_item_tarif IN (SELECT id FROM mt_item_tarif WHERE kode=_rs_item_tarif.referensi);
			IF _dasar_hitung IS NOT NULL THEN 	
				INSERT INTO item_tagihan 
					(id_pelanggan, periode, id_item_tarif, dasar_hitung, tarif, nilai)
				VALUES 
				(
					_rs.id_pelanggan, _rs.periode, _rs_item_tarif.id, _dasar_hitung, _rs_item_tarif.tarif, 
					(_rs_item_tarif.tarif * _dasar_hitung / 100)
				);
			END IF;		
		END LOOP;		
		
		UPDATE sch_generate_tagihan SET status=1 WHERE id=_rs.id;
	END LOOP;
END$$;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 198 (class 1259 OID 17185)
-- Name: area_petugas; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE area_petugas (
    id integer NOT NULL,
    id_petugas integer DEFAULT 0,
    id_area integer DEFAULT 0
);


--
-- TOC entry 199 (class 1259 OID 17190)
-- Name: area_petugas_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE area_petugas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2658 (class 0 OID 0)
-- Dependencies: 199
-- Name: area_petugas_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE area_petugas_id_seq OWNED BY area_petugas.id;


--
-- TOC entry 200 (class 1259 OID 17192)
-- Name: item_pembayaran; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE item_pembayaran (
    id integer NOT NULL,
    id_pembayaran integer DEFAULT 0,
    periode character varying(7),
    id_item_tarif integer DEFAULT 0,
    dasar_hitung numeric(8,2),
    tarif numeric(8,2),
    nilai numeric(12,2),
    id_pelanggan integer DEFAULT 0,
    deskripsi character varying(256)
);


--
-- TOC entry 201 (class 1259 OID 17198)
-- Name: item_pembayaran_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE item_pembayaran_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2659 (class 0 OID 0)
-- Dependencies: 201
-- Name: item_pembayaran_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE item_pembayaran_id_seq OWNED BY item_pembayaran.id;


--
-- TOC entry 202 (class 1259 OID 17200)
-- Name: item_tagihan; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE item_tagihan (
    id integer NOT NULL,
    periode character varying(7),
    id_item_tarif integer DEFAULT 0,
    dasar_hitung numeric(8,2),
    tarif numeric(8,2),
    nilai numeric(12,2),
    id_pelanggan integer DEFAULT 0,
    deskripsi character varying(256)
);


--
-- TOC entry 203 (class 1259 OID 17205)
-- Name: item_tagihan_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE item_tagihan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2660 (class 0 OID 0)
-- Dependencies: 203
-- Name: item_tagihan_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE item_tagihan_id_seq OWNED BY item_tagihan.id;


--
-- TOC entry 204 (class 1259 OID 17207)
-- Name: item_tagihan_non_rutin; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE item_tagihan_non_rutin (
    id integer NOT NULL,
    periode character varying(7),
    id_item_tarif integer DEFAULT 0,
    dasar_hitung numeric(8,2),
    tarif numeric(8,2),
    nilai numeric(12,2),
    id_pelanggan integer DEFAULT 0,
    deskripsi character varying(256)
);


--
-- TOC entry 205 (class 1259 OID 17212)
-- Name: item_tagihan_non_rutin_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE item_tagihan_non_rutin_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2661 (class 0 OID 0)
-- Dependencies: 205
-- Name: item_tagihan_non_rutin_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE item_tagihan_non_rutin_id_seq OWNED BY item_tagihan_non_rutin.id;


--
-- TOC entry 206 (class 1259 OID 17214)
-- Name: mt_area; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE mt_area (
    id integer NOT NULL,
    kode character varying(8),
    nama character varying(256),
    status smallint DEFAULT 1
);


--
-- TOC entry 207 (class 1259 OID 17218)
-- Name: mt_area_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE mt_area_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2662 (class 0 OID 0)
-- Dependencies: 207
-- Name: mt_area_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE mt_area_id_seq OWNED BY mt_area.id;


--
-- TOC entry 208 (class 1259 OID 17220)
-- Name: mt_item_tarif; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE mt_item_tarif (
    id integer NOT NULL,
    kode character varying(4),
    nama character varying(128),
    status smallint,
    is_rutin smallint DEFAULT 0,
    urutan smallint DEFAULT 0,
    tipe_tarif character varying(32)
);


--
-- TOC entry 209 (class 1259 OID 17225)
-- Name: mt_item_tarif_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE mt_item_tarif_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2663 (class 0 OID 0)
-- Dependencies: 209
-- Name: mt_item_tarif_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE mt_item_tarif_id_seq OWNED BY mt_item_tarif.id;


--
-- TOC entry 210 (class 1259 OID 17227)
-- Name: mt_item_tarif_referensi; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE mt_item_tarif_referensi (
    id integer NOT NULL,
    referensi character varying(32),
    tipe_referensi character varying(32),
    id_item_tarif integer DEFAULT 0
);


--
-- TOC entry 211 (class 1259 OID 17231)
-- Name: mt_item_tarif_referensi_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE mt_item_tarif_referensi_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2664 (class 0 OID 0)
-- Dependencies: 211
-- Name: mt_item_tarif_referensi_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE mt_item_tarif_referensi_id_seq OWNED BY mt_item_tarif_referensi.id;


--
-- TOC entry 212 (class 1259 OID 17233)
-- Name: mt_tarif; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE mt_tarif (
    id integer NOT NULL,
    id_tipe_pelanggan integer NOT NULL,
    batas integer DEFAULT 0,
    tarif numeric(12,2) DEFAULT 0,
    id_item_tarif integer DEFAULT 0,
    satuan character varying(32)
);


--
-- TOC entry 213 (class 1259 OID 17239)
-- Name: mt_tarif_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE mt_tarif_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2665 (class 0 OID 0)
-- Dependencies: 213
-- Name: mt_tarif_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE mt_tarif_id_seq OWNED BY mt_tarif.id;


--
-- TOC entry 214 (class 1259 OID 17241)
-- Name: mt_tipe_pelanggan; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE mt_tipe_pelanggan (
    id integer NOT NULL,
    nama character varying(128),
    status smallint DEFAULT 1,
    kode character varying(8),
    deskripsi character varying(256)
);


--
-- TOC entry 215 (class 1259 OID 17245)
-- Name: mt_tipe_pelanggan_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE mt_tipe_pelanggan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2666 (class 0 OID 0)
-- Dependencies: 215
-- Name: mt_tipe_pelanggan_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE mt_tipe_pelanggan_id_seq OWNED BY mt_tipe_pelanggan.id;


--
-- TOC entry 216 (class 1259 OID 17247)
-- Name: pelanggan; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE pelanggan (
    id integer NOT NULL,
    no_reff1 character varying(32) NOT NULL,
    no_reff2 character varying(32),
    nama character varying(256),
    alamat character varying(256),
    no_hp character varying(32),
    email character varying(256),
    tanggal_registrasi date DEFAULT (now())::date,
    status smallint DEFAULT 0,
    id_tipe_pelanggan bigint DEFAULT 0,
    tanggal_terpasang date,
    id_area integer
);


--
-- TOC entry 217 (class 1259 OID 17256)
-- Name: pelanggan_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE pelanggan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2667 (class 0 OID 0)
-- Dependencies: 217
-- Name: pelanggan_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE pelanggan_id_seq OWNED BY pelanggan.id;


--
-- TOC entry 218 (class 1259 OID 17258)
-- Name: pemakaian; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE pemakaian (
    id integer NOT NULL,
    id_pelanggan bigint DEFAULT 0,
    periode character varying(8),
    meter_awal numeric(12,2) DEFAULT 0,
    meter_akhir numeric(12,2) DEFAULT 0,
    jumlah_meter numeric(12,2) DEFAULT 0,
    status smallint DEFAULT 0
);


--
-- TOC entry 219 (class 1259 OID 17266)
-- Name: pemakaian_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE pemakaian_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2668 (class 0 OID 0)
-- Dependencies: 219
-- Name: pemakaian_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE pemakaian_id_seq OWNED BY pemakaian.id;


--
-- TOC entry 220 (class 1259 OID 17268)
-- Name: pembayaran; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE pembayaran (
    id integer NOT NULL,
    id_pelanggan integer,
    jumlah numeric(14,2),
    jumlah_tagihan smallint DEFAULT 0,
    item_tagihan jsonb,
    tanggal_bayar timestamp without time zone DEFAULT now()
);


--
-- TOC entry 221 (class 1259 OID 17276)
-- Name: pembayaran_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE pembayaran_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2669 (class 0 OID 0)
-- Dependencies: 221
-- Name: pembayaran_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE pembayaran_id_seq OWNED BY pembayaran.id;


--
-- TOC entry 222 (class 1259 OID 17278)
-- Name: petugas; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE petugas (
    id integer NOT NULL,
    kode character varying(32),
    nama character varying(300),
    username character varying(64),
    userpassword character varying(256),
    is_forcelogin smallint DEFAULT 0,
    status smallint DEFAULT 0,
    nohp character varying(20)
);


--
-- TOC entry 223 (class 1259 OID 17286)
-- Name: petugas_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE petugas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2670 (class 0 OID 0)
-- Dependencies: 223
-- Name: petugas_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE petugas_id_seq OWNED BY petugas.id;


--
-- TOC entry 224 (class 1259 OID 17288)
-- Name: rpt_telat; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE rpt_telat (
    id integer NOT NULL,
    id_pelanggan integer DEFAULT 0,
    periode character varying(7),
    duedate date,
    paymentdate date
);


--
-- TOC entry 225 (class 1259 OID 17292)
-- Name: rpt_telat_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE rpt_telat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2671 (class 0 OID 0)
-- Dependencies: 225
-- Name: rpt_telat_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE rpt_telat_id_seq OWNED BY rpt_telat.id;


--
-- TOC entry 226 (class 1259 OID 17294)
-- Name: sch_generate_denda; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sch_generate_denda (
    id integer NOT NULL,
    id_pelanggan integer DEFAULT 0,
    periode character varying(7),
    status smallint DEFAULT 0
);


--
-- TOC entry 227 (class 1259 OID 17299)
-- Name: sch_generate_denda_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sch_generate_denda_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2672 (class 0 OID 0)
-- Dependencies: 227
-- Name: sch_generate_denda_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sch_generate_denda_id_seq OWNED BY sch_generate_denda.id;


--
-- TOC entry 228 (class 1259 OID 17301)
-- Name: sch_generate_tagihan; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sch_generate_tagihan (
    id integer NOT NULL,
    id_pelanggan integer DEFAULT 0,
    periode character varying(7),
    status smallint DEFAULT 0
);


--
-- TOC entry 229 (class 1259 OID 17306)
-- Name: sch_generate_tagihan_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sch_generate_tagihan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2673 (class 0 OID 0)
-- Dependencies: 229
-- Name: sch_generate_tagihan_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sch_generate_tagihan_id_seq OWNED BY sch_generate_tagihan.id;


--
-- TOC entry 246 (class 1259 OID 17526)
-- Name: sync_pemakaian; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sync_pemakaian (
    id integer NOT NULL,
    id_petugas integer DEFAULT 0,
    date_uploaded timestamp without time zone DEFAULT now(),
    status smallint DEFAULT 0,
    uploaded_data text
);


--
-- TOC entry 245 (class 1259 OID 17524)
-- Name: sync_pemakaian_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sync_pemakaian_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2674 (class 0 OID 0)
-- Dependencies: 245
-- Name: sync_pemakaian_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sync_pemakaian_id_seq OWNED BY sync_pemakaian.id;


--
-- TOC entry 230 (class 1259 OID 17308)
-- Name: sys_config; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sys_config (
    id integer NOT NULL,
    tipe character varying(32),
    nama character varying(32),
    nilai character varying(32),
    status smallint DEFAULT 1
);


--
-- TOC entry 231 (class 1259 OID 17312)
-- Name: sys_config_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sys_config_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2675 (class 0 OID 0)
-- Dependencies: 231
-- Name: sys_config_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sys_config_id_seq OWNED BY sys_config.id;


--
-- TOC entry 232 (class 1259 OID 17314)
-- Name: sys_grup_akses; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sys_grup_akses (
    id integer NOT NULL,
    id_grup_user integer DEFAULT 0 NOT NULL,
    id_menu integer DEFAULT 0 NOT NULL,
    baca smallint DEFAULT 0,
    tambah smallint DEFAULT 0,
    ubah smallint DEFAULT 0,
    hapus smallint DEFAULT 0,
    cetak smallint DEFAULT 0
);


--
-- TOC entry 233 (class 1259 OID 17324)
-- Name: sys_grup_akses_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sys_grup_akses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2676 (class 0 OID 0)
-- Dependencies: 233
-- Name: sys_grup_akses_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sys_grup_akses_id_seq OWNED BY sys_grup_akses.id;


--
-- TOC entry 234 (class 1259 OID 17326)
-- Name: sys_grup_user; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sys_grup_user (
    id integer NOT NULL,
    nama character varying(128),
    keterangan character varying(128),
    status smallint DEFAULT 0
);


--
-- TOC entry 235 (class 1259 OID 17330)
-- Name: sys_grup_user_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sys_grup_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2677 (class 0 OID 0)
-- Dependencies: 235
-- Name: sys_grup_user_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sys_grup_user_id_seq OWNED BY sys_grup_user.id;


--
-- TOC entry 236 (class 1259 OID 17332)
-- Name: sys_log; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sys_log (
    id integer NOT NULL,
    log_actor character varying(128),
    log_event character varying(128),
    log_object character varying(128),
    log_ref_key character varying(128),
    log_date date DEFAULT (now())::date,
    log_time time without time zone DEFAULT (now())::time without time zone
);


--
-- TOC entry 237 (class 1259 OID 17340)
-- Name: sys_log_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sys_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2678 (class 0 OID 0)
-- Dependencies: 237
-- Name: sys_log_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sys_log_id_seq OWNED BY sys_log.id;


--
-- TOC entry 238 (class 1259 OID 17342)
-- Name: sys_menu; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sys_menu (
    id integer NOT NULL,
    id_induk integer DEFAULT 0,
    menu character varying(128),
    uri character varying(256),
    urutan integer DEFAULT 0,
    status smallint DEFAULT 1,
    icon character varying(32)
);


--
-- TOC entry 239 (class 1259 OID 17348)
-- Name: sys_menu_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sys_menu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2679 (class 0 OID 0)
-- Dependencies: 239
-- Name: sys_menu_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sys_menu_id_seq OWNED BY sys_menu.id;


--
-- TOC entry 240 (class 1259 OID 17350)
-- Name: sys_user; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE sys_user (
    id integer NOT NULL,
    id_grup_user integer DEFAULT 0 NOT NULL,
    username character varying(128),
    userpassword character varying(128),
    nama character varying(256),
    status smallint DEFAULT 0,
    poto character varying(256),
    tanda_tangan character varying(256)
);


--
-- TOC entry 241 (class 1259 OID 17358)
-- Name: sys_user_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE sys_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2680 (class 0 OID 0)
-- Dependencies: 241
-- Name: sys_user_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE sys_user_id_seq OWNED BY sys_user.id;


--
-- TOC entry 242 (class 1259 OID 17360)
-- Name: tes; Type: TABLE; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE TABLE tes (
    id integer NOT NULL,
    data jsonb
);


--
-- TOC entry 243 (class 1259 OID 17366)
-- Name: tes_id_seq; Type: SEQUENCE; Schema: dashboard; Owner: -
--

CREATE SEQUENCE tes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2681 (class 0 OID 0)
-- Dependencies: 243
-- Name: tes_id_seq; Type: SEQUENCE OWNED BY; Schema: dashboard; Owner: -
--

ALTER SEQUENCE tes_id_seq OWNED BY tes.id;


--
-- TOC entry 244 (class 1259 OID 17368)
-- Name: v_tagihan; Type: VIEW; Schema: dashboard; Owner: -
--

CREATE VIEW v_tagihan AS
 SELECT a.id_pelanggan,
    a.periode,
    a."1",
    a."2",
    a."3",
    a."4",
    a."5",
    a."6",
    a."7",
    a."8",
    (((((((a."1" + a."2") + a."3") + a."4") + a."5") + a."6") + a."7") + a."8") AS total
   FROM ( SELECT (cttagihan.row_name[1])::integer AS id_pelanggan,
            cttagihan.row_name[2] AS periode,
            COALESCE(cttagihan."1", 0.00) AS "1",
            COALESCE(cttagihan."2", 0.00) AS "2",
            COALESCE(cttagihan."3", 0.00) AS "3",
            COALESCE(cttagihan."4", 0.00) AS "4",
            COALESCE(cttagihan."5", 0.00) AS "5",
            COALESCE(cttagihan."6", 0.00) AS "6",
            COALESCE(cttagihan."7", 0.00) AS "7",
            COALESCE(cttagihan."8", 0.00) AS "8"
           FROM public.crosstab('SELECT ARRAY[id_pelanggan::text, periode::text] as row_name, id_item_tarif, SUM(nilai) AS nilai 
			FROM item_tagihan			
			GROUP BY id_pelanggan, periode, id_item_tarif	
			UNION 
			SELECT ARRAY[id_pelanggan::text, periode::text] as row_name, id_item_tarif, SUM(nilai) AS nilai 
			FROM item_tagihan_non_rutin			
			GROUP BY id_pelanggan, periode, id_item_tarif	
			ORDER BY row_name ASC'::text, 'SELECT DISTINCT id FROM mt_item_tarif ORDER BY id ASC'::text) cttagihan(row_name text[], "1" numeric(10,2), "2" numeric(10,2), "3" numeric(10,2), "4" numeric(10,2), "5" numeric(10,2), "6" numeric(10,2), "7" numeric(10,2), "8" numeric(10,2))) a
  WHERE (a.id_pelanggan IS NOT NULL);


--
-- TOC entry 2345 (class 2604 OID 17373)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY area_petugas ALTER COLUMN id SET DEFAULT nextval('area_petugas_id_seq'::regclass);


--
-- TOC entry 2349 (class 2604 OID 17374)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY item_pembayaran ALTER COLUMN id SET DEFAULT nextval('item_pembayaran_id_seq'::regclass);


--
-- TOC entry 2352 (class 2604 OID 17375)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY item_tagihan ALTER COLUMN id SET DEFAULT nextval('item_tagihan_id_seq'::regclass);


--
-- TOC entry 2355 (class 2604 OID 17376)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY item_tagihan_non_rutin ALTER COLUMN id SET DEFAULT nextval('item_tagihan_non_rutin_id_seq'::regclass);


--
-- TOC entry 2357 (class 2604 OID 17377)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY mt_area ALTER COLUMN id SET DEFAULT nextval('mt_area_id_seq'::regclass);


--
-- TOC entry 2360 (class 2604 OID 17378)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY mt_item_tarif ALTER COLUMN id SET DEFAULT nextval('mt_item_tarif_id_seq'::regclass);


--
-- TOC entry 2362 (class 2604 OID 17379)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY mt_item_tarif_referensi ALTER COLUMN id SET DEFAULT nextval('mt_item_tarif_referensi_id_seq'::regclass);


--
-- TOC entry 2366 (class 2604 OID 17380)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY mt_tarif ALTER COLUMN id SET DEFAULT nextval('mt_tarif_id_seq'::regclass);


--
-- TOC entry 2368 (class 2604 OID 17381)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY mt_tipe_pelanggan ALTER COLUMN id SET DEFAULT nextval('mt_tipe_pelanggan_id_seq'::regclass);


--
-- TOC entry 2372 (class 2604 OID 17382)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY pelanggan ALTER COLUMN id SET DEFAULT nextval('pelanggan_id_seq'::regclass);


--
-- TOC entry 2378 (class 2604 OID 17383)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY pemakaian ALTER COLUMN id SET DEFAULT nextval('pemakaian_id_seq'::regclass);


--
-- TOC entry 2381 (class 2604 OID 17384)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY pembayaran ALTER COLUMN id SET DEFAULT nextval('pembayaran_id_seq'::regclass);


--
-- TOC entry 2384 (class 2604 OID 17385)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY petugas ALTER COLUMN id SET DEFAULT nextval('petugas_id_seq'::regclass);


--
-- TOC entry 2386 (class 2604 OID 17386)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY rpt_telat ALTER COLUMN id SET DEFAULT nextval('rpt_telat_id_seq'::regclass);


--
-- TOC entry 2389 (class 2604 OID 17387)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sch_generate_denda ALTER COLUMN id SET DEFAULT nextval('sch_generate_denda_id_seq'::regclass);


--
-- TOC entry 2392 (class 2604 OID 17388)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sch_generate_tagihan ALTER COLUMN id SET DEFAULT nextval('sch_generate_tagihan_id_seq'::regclass);


--
-- TOC entry 2417 (class 2604 OID 17529)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sync_pemakaian ALTER COLUMN id SET DEFAULT nextval('sync_pemakaian_id_seq'::regclass);


--
-- TOC entry 2394 (class 2604 OID 17389)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_config ALTER COLUMN id SET DEFAULT nextval('sys_config_id_seq'::regclass);


--
-- TOC entry 2402 (class 2604 OID 17390)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_grup_akses ALTER COLUMN id SET DEFAULT nextval('sys_grup_akses_id_seq'::regclass);


--
-- TOC entry 2404 (class 2604 OID 17391)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_grup_user ALTER COLUMN id SET DEFAULT nextval('sys_grup_user_id_seq'::regclass);


--
-- TOC entry 2407 (class 2604 OID 17392)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_log ALTER COLUMN id SET DEFAULT nextval('sys_log_id_seq'::regclass);


--
-- TOC entry 2411 (class 2604 OID 17393)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_menu ALTER COLUMN id SET DEFAULT nextval('sys_menu_id_seq'::regclass);


--
-- TOC entry 2414 (class 2604 OID 17394)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_user ALTER COLUMN id SET DEFAULT nextval('sys_user_id_seq'::regclass);


--
-- TOC entry 2415 (class 2604 OID 17395)
-- Name: id; Type: DEFAULT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY tes ALTER COLUMN id SET DEFAULT nextval('tes_id_seq'::regclass);


--
-- TOC entry 2605 (class 0 OID 17185)
-- Dependencies: 198
-- Data for Name: area_petugas; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO area_petugas (id, id_petugas, id_area) VALUES (3, 1, 1);


--
-- TOC entry 2682 (class 0 OID 0)
-- Dependencies: 199
-- Name: area_petugas_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('area_petugas_id_seq', 3, true);


--
-- TOC entry 2607 (class 0 OID 17192)
-- Dependencies: 200
-- Data for Name: item_pembayaran; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2683 (class 0 OID 0)
-- Dependencies: 201
-- Name: item_pembayaran_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('item_pembayaran_id_seq', 42, true);


--
-- TOC entry 2609 (class 0 OID 17200)
-- Dependencies: 202
-- Data for Name: item_tagihan; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2684 (class 0 OID 0)
-- Dependencies: 203
-- Name: item_tagihan_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('item_tagihan_id_seq', 417, true);


--
-- TOC entry 2611 (class 0 OID 17207)
-- Dependencies: 204
-- Data for Name: item_tagihan_non_rutin; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2685 (class 0 OID 0)
-- Dependencies: 205
-- Name: item_tagihan_non_rutin_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('item_tagihan_non_rutin_id_seq', 6, true);


--
-- TOC entry 2613 (class 0 OID 17214)
-- Dependencies: 206
-- Data for Name: mt_area; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO mt_area (id, kode, nama, status) VALUES (1, 'ARE', 'Asih Raya', 1);


--
-- TOC entry 2686 (class 0 OID 0)
-- Dependencies: 207
-- Name: mt_area_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('mt_area_id_seq', 1, true);


--
-- TOC entry 2615 (class 0 OID 17220)
-- Dependencies: 208
-- Data for Name: mt_item_tarif; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (1, 'PPIR', 'Pemakaian Air', 1, 1, 1, 'PEMAKAIAN');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (3, 'BBEB', 'Biaya Beban', 1, 1, 2, 'ADMINISTRASI');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (5, 'BADM', 'Biaya Administrasi', 1, 1, 3, 'ADMINISTRASI');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (7, 'PPPN', 'PPN', 1, 1, 4, 'PAJAK');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (2, 'BPSB', 'Biaya Pasang Baru', 1, 0, 0, 'REGISTRASI');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (4, 'DDKT', 'Denda Keterlambatan', 1, 0, 0, 'DENDA');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (6, 'OBLK', 'Biaya Open Blokir', 1, 0, 0, 'REAKTIVASI');
INSERT INTO mt_item_tarif (id, kode, nama, status, is_rutin, urutan, tipe_tarif) VALUES (8, 'MTR', 'Materai', 1, 1, 4, 'ADMINISTRASI');


--
-- TOC entry 2687 (class 0 OID 0)
-- Dependencies: 209
-- Name: mt_item_tarif_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('mt_item_tarif_id_seq', 8, true);


--
-- TOC entry 2617 (class 0 OID 17227)
-- Dependencies: 210
-- Data for Name: mt_item_tarif_referensi; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO mt_item_tarif_referensi (id, referensi, tipe_referensi, id_item_tarif) VALUES (8, 'PPIR', 'ITEM TARIF', 7);


--
-- TOC entry 2688 (class 0 OID 0)
-- Dependencies: 211
-- Name: mt_item_tarif_referensi_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('mt_item_tarif_referensi_id_seq', 8, true);


--
-- TOC entry 2619 (class 0 OID 17233)
-- Dependencies: 212
-- Data for Name: mt_tarif; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (16, 4, 0, 2500.00, 5, '');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (15, 4, 0, 100000.00, 2, '');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (17, 4, 0, 250.00, 1, 'meter kubik');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (18, 4, 10, 300.00, 1, 'meter kubik');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (19, 4, 20, 350.00, 1, 'meter kubik');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (20, 4, 30, 500.00, 1, 'meter kubik');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (21, 4, 0, 100000.00, 6, '');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (22, 4, 0, 10000.00, 3, '');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (23, 4, 0, 10000.00, 4, '');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (25, 4, 0, 6000.00, 8, '');
INSERT INTO mt_tarif (id, id_tipe_pelanggan, batas, tarif, id_item_tarif, satuan) VALUES (24, 4, 0, 10.00, 7, '');


--
-- TOC entry 2689 (class 0 OID 0)
-- Dependencies: 213
-- Name: mt_tarif_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('mt_tarif_id_seq', 26, true);


--
-- TOC entry 2621 (class 0 OID 17241)
-- Dependencies: 214
-- Data for Name: mt_tipe_pelanggan; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (3, 'Sosial Umum', 1, 'A2', 'Kran Umum, MCK Umum, Tempat Ibadah');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (5, 'Rumah Tangga Golongan 1', 1, 'B1', 'Rumah Susun Perumnas');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (7, 'Rumah Tangga Golongan 3', 1, 'B3', 'Rumah yang terletak di jalan besar bukan protokol dengan lebar jalan tidak kurang dari 2 meter dan tidak lebih dari 4 meter.');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (6, 'Rumah Tangga Golongan 2', 1, 'B2', 'Rumah yang terletak di jalan kecil/gang dengan lebar jalan kurang dari 2 meter');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (8, 'Instansi Pemerintah/TNI/POLRI', 1, 'B4', 'Sarana Instansi Pemerintah / TNI / POLRI baik pusat maupun daerah, sekolah milik pemerintah');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (9, 'Niaga Kecil', 1, 'C1', 'Warung/Kios/Jongko, Bengkel Kecil/pencucian motor, Penjahit, Perusahaan Dagang/Jasa kecil');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (10, 'Niaga Menengah / Besar', 1, 'C2', '');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (11, 'Industri Kecil', 1, 'D1', '');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (12, 'Industri Menengah / Besar', 1, 'D2', '');
INSERT INTO mt_tipe_pelanggan (id, nama, status, kode, deskripsi) VALUES (4, 'Sosial Khusus', 1, 'A1', 'Puskesmas, Klinik Pemerintah, Rumah Yatim Piatu, Rumah Jompo, Badan sosial lainnya');


--
-- TOC entry 2690 (class 0 OID 0)
-- Dependencies: 215
-- Name: mt_tipe_pelanggan_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('mt_tipe_pelanggan_id_seq', 12, true);


--
-- TOC entry 2623 (class 0 OID 17247)
-- Dependencies: 216
-- Data for Name: pelanggan; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO pelanggan (id, no_reff1, no_reff2, nama, alamat, no_hp, email, tanggal_registrasi, status, id_tipe_pelanggan, tanggal_terpasang, id_area) VALUES (6, '0001010102', '0001010102', 'Pelanggan Khusus 2', 'Alamat palsu', '098889898', '', '2016-06-11', 1, 4, '2016-06-20', 1);
INSERT INTO pelanggan (id, no_reff1, no_reff2, nama, alamat, no_hp, email, tanggal_registrasi, status, id_tipe_pelanggan, tanggal_terpasang, id_area) VALUES (5, '0001010101', '2901010101', 'Pelanggan Sosial Khusus', 'Asih Raya Mamamama', '0812828282', 'dsdsdsd', '2016-10-18', 1, 4, '2016-09-01', 1);


--
-- TOC entry 2691 (class 0 OID 0)
-- Dependencies: 217
-- Name: pelanggan_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('pelanggan_id_seq', 7, true);


--
-- TOC entry 2625 (class 0 OID 17258)
-- Dependencies: 218
-- Data for Name: pemakaian; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO pemakaian (id, id_pelanggan, periode, meter_awal, meter_akhir, jumlah_meter, status) VALUES (33, 6, '10-2016', 0.00, 12.00, 12.00, 0);


--
-- TOC entry 2692 (class 0 OID 0)
-- Dependencies: 219
-- Name: pemakaian_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('pemakaian_id_seq', 33, true);


--
-- TOC entry 2627 (class 0 OID 17268)
-- Dependencies: 220
-- Data for Name: pembayaran; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2693 (class 0 OID 0)
-- Dependencies: 221
-- Name: pembayaran_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('pembayaran_id_seq', 24, true);


--
-- TOC entry 2629 (class 0 OID 17278)
-- Dependencies: 222
-- Data for Name: petugas; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO petugas (id, kode, nama, username, userpassword, is_forcelogin, status, nohp) VALUES (1, NULL, 'Petugas Catat 1', 'catat1', '$1$EI7OU8oi$5MKoJTqR.8gRnxbs1ejGJ/', 0, 1, '081616166');


--
-- TOC entry 2694 (class 0 OID 0)
-- Dependencies: 223
-- Name: petugas_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('petugas_id_seq', 2, true);


--
-- TOC entry 2631 (class 0 OID 17288)
-- Dependencies: 224
-- Data for Name: rpt_telat; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2695 (class 0 OID 0)
-- Dependencies: 225
-- Name: rpt_telat_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('rpt_telat_id_seq', 6, true);


--
-- TOC entry 2633 (class 0 OID 17294)
-- Dependencies: 226
-- Data for Name: sch_generate_denda; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2696 (class 0 OID 0)
-- Dependencies: 227
-- Name: sch_generate_denda_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sch_generate_denda_id_seq', 2, true);


--
-- TOC entry 2635 (class 0 OID 17301)
-- Dependencies: 228
-- Data for Name: sch_generate_tagihan; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO sch_generate_tagihan (id, id_pelanggan, periode, status) VALUES (26, 6, '07-2016', 1);


--
-- TOC entry 2697 (class 0 OID 0)
-- Dependencies: 229
-- Name: sch_generate_tagihan_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sch_generate_tagihan_id_seq', 26, true);


--
-- TOC entry 2652 (class 0 OID 17526)
-- Dependencies: 246
-- Data for Name: sync_pemakaian; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2698 (class 0 OID 0)
-- Dependencies: 245
-- Name: sync_pemakaian_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sync_pemakaian_id_seq', 1, false);


--
-- TOC entry 2637 (class 0 OID 17308)
-- Dependencies: 230
-- Data for Name: sys_config; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (3, 'UPL_USR', 'max_size', '200', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (4, 'UPL_USR', 'overwrite', 'TRUE', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (5, 'UPL_USR', 'max_height', '0', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (6, 'UPL_USR', 'max_width', '0', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (1, 'UPL_USR', 'upload_path', './uploads/user/', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (2, 'UPL_USR', 'allowed_types', 'png|jpg|gif|pdf|jpeg', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (11, 'TANGGAL', 'TAGIHAN', '5', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (12, 'TANGGAL', 'PREPARE TAGIHAN', '3', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (13, 'TANGGAL', 'PREPARE DENDA', '21', 1);
INSERT INTO sys_config (id, tipe, nama, nilai, status) VALUES (10, 'TANGGAL', 'JATUH TEMPO', '20', 1);


--
-- TOC entry 2699 (class 0 OID 0)
-- Dependencies: 231
-- Name: sys_config_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sys_config_id_seq', 13, true);


--
-- TOC entry 2639 (class 0 OID 17314)
-- Dependencies: 232
-- Data for Name: sys_grup_akses; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (232, 4, 43, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (259, 8, 56, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (258, 4, 56, 1, 0, 0, 0, 0);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (260, 4, 57, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (261, 8, 57, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (262, 4, 58, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (263, 8, 58, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (266, 4, 60, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (267, 8, 60, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (268, 4, 61, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (269, 8, 61, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (233, 8, 43, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (234, 4, 44, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (235, 8, 44, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (236, 4, 45, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (237, 8, 45, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (238, 4, 46, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (239, 8, 46, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (240, 4, 47, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (241, 8, 47, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (242, 4, 48, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (243, 8, 48, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (244, 4, 49, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (245, 8, 49, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (246, 4, 50, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (247, 8, 50, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (248, 4, 51, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (249, 8, 51, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (250, 4, 52, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (251, 8, 52, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (252, 4, 53, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (253, 8, 53, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (254, 4, 54, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (255, 8, 54, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (256, 4, 55, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (257, 8, 55, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (264, 4, 59, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (265, 8, 59, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (270, 4, 62, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (271, 8, 62, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (289, 4, 63, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (290, 8, 63, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (291, 4, 64, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (292, 8, 64, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (293, 4, 65, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (294, 8, 65, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (295, 4, 66, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (296, 8, 66, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (297, 4, 67, 1, 1, 1, 1, 1);
INSERT INTO sys_grup_akses (id, id_grup_user, id_menu, baca, tambah, ubah, hapus, cetak) VALUES (298, 8, 67, 1, 1, 1, 1, 1);


--
-- TOC entry 2700 (class 0 OID 0)
-- Dependencies: 233
-- Name: sys_grup_akses_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sys_grup_akses_id_seq', 298, true);


--
-- TOC entry 2641 (class 0 OID 17326)
-- Dependencies: 234
-- Data for Name: sys_grup_user; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO sys_grup_user (id, nama, keterangan, status) VALUES (4, 'Administrator', '-', 1);
INSERT INTO sys_grup_user (id, nama, keterangan, status) VALUES (8, 'Super Administrator', '-', 1);


--
-- TOC entry 2701 (class 0 OID 0)
-- Dependencies: 235
-- Name: sys_grup_user_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sys_grup_user_id_seq', 10, true);


--
-- TOC entry 2643 (class 0 OID 17332)
-- Dependencies: 236
-- Data for Name: sys_log; Type: TABLE DATA; Schema: dashboard; Owner: -
--



--
-- TOC entry 2702 (class 0 OID 0)
-- Dependencies: 237
-- Name: sys_log_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sys_log_id_seq', 1, false);


--
-- TOC entry 2645 (class 0 OID 17342)
-- Dependencies: 238
-- Data for Name: sys_menu; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (43, 0, 'Dashboard', 'home', 1, 1, 'desktop');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (44, 0, 'Setting', 'setting/menu', 2, 1, 'cogs');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (45, 44, 'Menu', 'setting/menu', 1, 1, NULL);
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (46, 44, 'User', 'setting/user', 2, 1, NULL);
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (47, 44, 'Grup User', 'setting/grup_user', 3, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (50, 49, 'Tipe Pelanggan', 'datamaster/tipe_pelanggan', 1, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (53, 0, 'Pelanggan', 'pelanggan/pelanggan', 4, 1, 'users');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (54, 0, 'Transaksi', 'transaksi', 5, 1, 'money');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (55, 54, 'Pemakaian', 'transaksi/pemakaian', 1, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (48, 44, 'Grup Akses', 'setting/grup_akses', 0, 0, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (56, 44, 'Konfigurasi', 'setting/konfigurasi', 5, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (58, 53, 'Data Pelanggan', 'pelanggan/pelanggan', 1, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (59, 54, 'Tagihan', 'transaksi/tagihan', 2, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (51, 49, 'Item Tarif', 'datamaster/item_tarif', 2, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (52, 49, 'Tarif', 'datamaster/tarif', 3, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (57, 53, 'Pasang Baru', 'pelanggan/pasang_baru', 2, 0, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (60, 49, 'Referensi Tarif Pajak', 'datamaster/item_tarif_referensi', 3, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (61, 54, 'Pembayaran', 'transaksi/pembayaran', 3, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (62, 54, 'Info Tagihan', 'transaksi/info_tagihan', 0, 0, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (49, 0, 'Data Master', 'datamaster', 3, 1, 'bars');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (64, 0, 'Petugas', 'petugas/petugas', 3, 1, 'user');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (65, 64, 'Area Petugas', 'petugas/area_petugas', 1, 0, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (63, 49, 'Area / Blok', 'datamaster/area', 6, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (66, 64, 'Petugas Catat Meter', 'petugas/petugas', 1, 1, '');
INSERT INTO sys_menu (id, id_induk, menu, uri, urutan, status, icon) VALUES (67, 0, 'Report', 'report', 7, 1, 'dashboard');


--
-- TOC entry 2703 (class 0 OID 0)
-- Dependencies: 239
-- Name: sys_menu_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sys_menu_id_seq', 67, true);


--
-- TOC entry 2647 (class 0 OID 17350)
-- Dependencies: 240
-- Data for Name: sys_user; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO sys_user (id, id_grup_user, username, userpassword, nama, status, poto, tanda_tangan) VALUES (45, 8, 'yoog', '$1$aA3mEmw0$VPbRBTyF8vIWOJ9.qELPR0', 'Yoga Mahendra', 1, NULL, NULL);


--
-- TOC entry 2704 (class 0 OID 0)
-- Dependencies: 241
-- Name: sys_user_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('sys_user_id_seq', 45, true);


--
-- TOC entry 2649 (class 0 OID 17360)
-- Dependencies: 242
-- Data for Name: tes; Type: TABLE DATA; Schema: dashboard; Owner: -
--

INSERT INTO tes (id, data) VALUES (1, '{"guid": "9c36adc1-7fb5-4d5b-83b4-90356a46061a", "name": "Angela Barton", "tags": ["enim", "aliquip", "qui"], "address": "178 Howard Place, Gulf, Washington, 702", "company": "Magnafone", "latitude": 19.793713, "is_active": true, "longitude": 86.513373, "registered": "2009-11-07T08:53:22 +08:00"}');
INSERT INTO tes (id, data) VALUES (2, '[1, 2, 3]');
INSERT INTO tes (id, data) VALUES (3, '{"tes": "tes"}');
INSERT INTO tes (id, data) VALUES (4, '{"tes": "tes"}');
INSERT INTO tes (id, data) VALUES (5, '{"tes": "tes"}');


--
-- TOC entry 2705 (class 0 OID 0)
-- Dependencies: 243
-- Name: tes_id_seq; Type: SEQUENCE SET; Schema: dashboard; Owner: -
--

SELECT pg_catalog.setval('tes_id_seq', 5, true);


--
-- TOC entry 2421 (class 2606 OID 17397)
-- Name: area_petugas_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY area_petugas
    ADD CONSTRAINT area_petugas_id_pk PRIMARY KEY (id);


--
-- TOC entry 2424 (class 2606 OID 17399)
-- Name: item_pembayaran_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY item_pembayaran
    ADD CONSTRAINT item_pembayaran_pk PRIMARY KEY (id);


--
-- TOC entry 2428 (class 2606 OID 17401)
-- Name: item_tagihan_non_rutin_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY item_tagihan_non_rutin
    ADD CONSTRAINT item_tagihan_non_rutin_pk PRIMARY KEY (id);


--
-- TOC entry 2426 (class 2606 OID 17403)
-- Name: item_tagihan_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY item_tagihan
    ADD CONSTRAINT item_tagihan_pk PRIMARY KEY (id);


--
-- TOC entry 2432 (class 2606 OID 17405)
-- Name: mt_item_tarif_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY mt_item_tarif
    ADD CONSTRAINT mt_item_tarif_pk PRIMARY KEY (id);


--
-- TOC entry 2434 (class 2606 OID 17407)
-- Name: mt_item_tarif_referensi_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY mt_item_tarif_referensi
    ADD CONSTRAINT mt_item_tarif_referensi_pk PRIMARY KEY (id);


--
-- TOC entry 2436 (class 2606 OID 17409)
-- Name: mt_tarif_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY mt_tarif
    ADD CONSTRAINT mt_tarif_id_pk PRIMARY KEY (id);


--
-- TOC entry 2438 (class 2606 OID 17411)
-- Name: mt_tipe_pelanggan_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY mt_tipe_pelanggan
    ADD CONSTRAINT mt_tipe_pelanggan_id_pk PRIMARY KEY (id);


--
-- TOC entry 2430 (class 2606 OID 17413)
-- Name: mtareaidpk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY mt_area
    ADD CONSTRAINT mtareaidpk PRIMARY KEY (id);


--
-- TOC entry 2441 (class 2606 OID 17415)
-- Name: pelanggan_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pelanggan
    ADD CONSTRAINT pelanggan_id_pk PRIMARY KEY (id);


--
-- TOC entry 2443 (class 2606 OID 17417)
-- Name: pelanggan_no_reff1_unique; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pelanggan
    ADD CONSTRAINT pelanggan_no_reff1_unique UNIQUE (no_reff1);


--
-- TOC entry 2445 (class 2606 OID 17419)
-- Name: pelanggan_no_reff2_unique; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pelanggan
    ADD CONSTRAINT pelanggan_no_reff2_unique UNIQUE (no_reff2);


--
-- TOC entry 2448 (class 2606 OID 17421)
-- Name: pemakaian_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pemakaian
    ADD CONSTRAINT pemakaian_id_pk PRIMARY KEY (id);


--
-- TOC entry 2450 (class 2606 OID 17423)
-- Name: pembayaran_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pembayaran
    ADD CONSTRAINT pembayaran_id_pk PRIMARY KEY (id);


--
-- TOC entry 2453 (class 2606 OID 17425)
-- Name: petugas_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY petugas
    ADD CONSTRAINT petugas_id_pk PRIMARY KEY (id);


--
-- TOC entry 2463 (class 2606 OID 17427)
-- Name: pk_sys_grup_akses; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sys_grup_akses
    ADD CONSTRAINT pk_sys_grup_akses PRIMARY KEY (id);


--
-- TOC entry 2465 (class 2606 OID 17429)
-- Name: pk_sys_grup_user; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sys_grup_user
    ADD CONSTRAINT pk_sys_grup_user PRIMARY KEY (id);


--
-- TOC entry 2467 (class 2606 OID 17431)
-- Name: pk_sys_log; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sys_log
    ADD CONSTRAINT pk_sys_log PRIMARY KEY (id);


--
-- TOC entry 2471 (class 2606 OID 17433)
-- Name: pk_sys_user; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sys_user
    ADD CONSTRAINT pk_sys_user PRIMARY KEY (id);


--
-- TOC entry 2455 (class 2606 OID 17435)
-- Name: rpt_telat_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY rpt_telat
    ADD CONSTRAINT rpt_telat_id_pk PRIMARY KEY (id);


--
-- TOC entry 2457 (class 2606 OID 17437)
-- Name: sch_generate_denda_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sch_generate_denda
    ADD CONSTRAINT sch_generate_denda_id_pk PRIMARY KEY (id);


--
-- TOC entry 2459 (class 2606 OID 17439)
-- Name: sch_generate_tagihan_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sch_generate_tagihan
    ADD CONSTRAINT sch_generate_tagihan_id_pk PRIMARY KEY (id);


--
-- TOC entry 2476 (class 2606 OID 17537)
-- Name: sync_pemakaian_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sync_pemakaian
    ADD CONSTRAINT sync_pemakaian_id_pk PRIMARY KEY (id);


--
-- TOC entry 2461 (class 2606 OID 17441)
-- Name: sys_config_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sys_config
    ADD CONSTRAINT sys_config_pk PRIMARY KEY (id);


--
-- TOC entry 2469 (class 2606 OID 17443)
-- Name: sys_menu_id_pk; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY sys_menu
    ADD CONSTRAINT sys_menu_id_pk PRIMARY KEY (id);


--
-- TOC entry 2474 (class 2606 OID 17445)
-- Name: tes_pkey; Type: CONSTRAINT; Schema: dashboard; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tes
    ADD CONSTRAINT tes_pkey PRIMARY KEY (id);


--
-- TOC entry 2422 (class 1259 OID 17446)
-- Name: fki_item_pembayaran_pembayaran_id_pembayaran_fk; Type: INDEX; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE INDEX fki_item_pembayaran_pembayaran_id_pembayaran_fk ON item_pembayaran USING btree (id_pembayaran);


--
-- TOC entry 2439 (class 1259 OID 17447)
-- Name: fki_pelanggan_mt_area_id_area_fk; Type: INDEX; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE INDEX fki_pelanggan_mt_area_id_area_fk ON pelanggan USING btree (id_area);


--
-- TOC entry 2472 (class 1259 OID 17448)
-- Name: lala_idx; Type: INDEX; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE INDEX lala_idx ON tes USING gin (data);


--
-- TOC entry 2446 (class 1259 OID 17449)
-- Name: pelanggan_noreff_idx; Type: INDEX; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE INDEX pelanggan_noreff_idx ON pelanggan USING btree (no_reff1, no_reff2);


--
-- TOC entry 2451 (class 1259 OID 17450)
-- Name: pembayaran_item_tagihan_idx; Type: INDEX; Schema: dashboard; Owner: -; Tablespace: 
--

CREATE INDEX pembayaran_item_tagihan_idx ON pembayaran USING gin (item_tagihan jsonb_path_ops);


--
-- TOC entry 2492 (class 2620 OID 17451)
-- Name: trg_grup_user_set_grup_akses; Type: TRIGGER; Schema: dashboard; Owner: -
--

CREATE TRIGGER trg_grup_user_set_grup_akses AFTER INSERT ON sys_grup_user FOR EACH ROW EXECUTE PROCEDURE fn_trg_grup_user_set_grup_akses();


--
-- TOC entry 2493 (class 2620 OID 17452)
-- Name: trg_menu_set_grup_akses; Type: TRIGGER; Schema: dashboard; Owner: -
--

CREATE TRIGGER trg_menu_set_grup_akses AFTER INSERT ON sys_menu FOR EACH ROW EXECUTE PROCEDURE fn_trg_menu_set_grup_akses();


--
-- TOC entry 2489 (class 2620 OID 17453)
-- Name: trg_pemakaian_hitung_pemakaian; Type: TRIGGER; Schema: dashboard; Owner: -
--

CREATE TRIGGER trg_pemakaian_hitung_pemakaian BEFORE INSERT OR UPDATE OF meter_akhir ON pemakaian FOR EACH ROW EXECUTE PROCEDURE fn_trg_hitung_pemakaian();


--
-- TOC entry 2490 (class 2620 OID 17454)
-- Name: trg_petugas_set_password; Type: TRIGGER; Schema: dashboard; Owner: -
--

CREATE TRIGGER trg_petugas_set_password BEFORE INSERT OR UPDATE OF userpassword ON petugas FOR EACH ROW EXECUTE PROCEDURE fn_trg_petugas_set_password();


--
-- TOC entry 2494 (class 2620 OID 17455)
-- Name: trg_user_set_password_before_insert_update; Type: TRIGGER; Schema: dashboard; Owner: -
--

CREATE TRIGGER trg_user_set_password_before_insert_update BEFORE INSERT OR UPDATE ON sys_user FOR EACH ROW EXECUTE PROCEDURE fn_trg_user_set_password();


--
-- TOC entry 2491 (class 2620 OID 17456)
-- Name: validate_before_insert; Type: TRIGGER; Schema: dashboard; Owner: -
--

CREATE TRIGGER validate_before_insert BEFORE INSERT ON sch_generate_tagihan FOR EACH ROW EXECUTE PROCEDURE fn_trg_validate_insert_antrian();


--
-- TOC entry 2477 (class 2606 OID 17457)
-- Name: areapetugasareaidareafk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY area_petugas
    ADD CONSTRAINT areapetugasareaidareafk FOREIGN KEY (id_area) REFERENCES mt_area(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2478 (class 2606 OID 17462)
-- Name: areapetugaspetugasidpetugasfk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY area_petugas
    ADD CONSTRAINT areapetugaspetugasidpetugasfk FOREIGN KEY (id_petugas) REFERENCES petugas(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2487 (class 2606 OID 17467)
-- Name: fk_sys_user_reference_sys_grup; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_user
    ADD CONSTRAINT fk_sys_user_reference_sys_grup FOREIGN KEY (id_grup_user) REFERENCES sys_grup_user(id) ON UPDATE SET DEFAULT ON DELETE SET DEFAULT;


--
-- TOC entry 2479 (class 2606 OID 17472)
-- Name: item_pembayaran_pembayaran_id_pembayaran_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY item_pembayaran
    ADD CONSTRAINT item_pembayaran_pembayaran_id_pembayaran_fk FOREIGN KEY (id_pembayaran) REFERENCES pembayaran(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2480 (class 2606 OID 17477)
-- Name: mt_tarif_mt_tipe_pelanggan_id_tipe_pelanggan_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY mt_tarif
    ADD CONSTRAINT mt_tarif_mt_tipe_pelanggan_id_tipe_pelanggan_fk FOREIGN KEY (id_tipe_pelanggan) REFERENCES mt_tipe_pelanggan(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2481 (class 2606 OID 17482)
-- Name: pelanggan_mt_area_id_area_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY pelanggan
    ADD CONSTRAINT pelanggan_mt_area_id_area_fk FOREIGN KEY (id_area) REFERENCES mt_area(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2482 (class 2606 OID 17487)
-- Name: pelanggan_mt_tipe_pelanggan_id_tipe_pelanggan_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY pelanggan
    ADD CONSTRAINT pelanggan_mt_tipe_pelanggan_id_tipe_pelanggan_fk FOREIGN KEY (id_tipe_pelanggan) REFERENCES mt_tipe_pelanggan(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2483 (class 2606 OID 17492)
-- Name: sch_generate_denda_pelanggan_id_pelanggan_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sch_generate_denda
    ADD CONSTRAINT sch_generate_denda_pelanggan_id_pelanggan_fk FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2484 (class 2606 OID 17497)
-- Name: sch_generate_tagihan_pelanggan_id_pelanggan_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sch_generate_tagihan
    ADD CONSTRAINT sch_generate_tagihan_pelanggan_id_pelanggan_fk FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2488 (class 2606 OID 17538)
-- Name: sync_pemakaian_petugas_id_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sync_pemakaian
    ADD CONSTRAINT sync_pemakaian_petugas_id_fk FOREIGN KEY (id_petugas) REFERENCES petugas(id) ON DELETE CASCADE;


--
-- TOC entry 2485 (class 2606 OID 17502)
-- Name: sys_grup_akses_sys_grup_user_id_grup_user_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_grup_akses
    ADD CONSTRAINT sys_grup_akses_sys_grup_user_id_grup_user_fk FOREIGN KEY (id_grup_user) REFERENCES sys_grup_user(id) ON UPDATE RESTRICT ON DELETE CASCADE;


--
-- TOC entry 2486 (class 2606 OID 17507)
-- Name: sys_grup_akses_sys_menu_id_menu_fk; Type: FK CONSTRAINT; Schema: dashboard; Owner: -
--

ALTER TABLE ONLY sys_grup_akses
    ADD CONSTRAINT sys_grup_akses_sys_menu_id_menu_fk FOREIGN KEY (id_menu) REFERENCES sys_menu(id) ON UPDATE RESTRICT ON DELETE CASCADE;


-- Completed on 2017-01-01 15:37:19 WIB

--
-- PostgreSQL database dump complete
--

