<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of menu
 *
 * @author Yoga Mahendra
 */
class Info_tagihan extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "item_tagihan a";
    private $_TBL_PRIMARY_PK = "a.id";    
    private $_ORDER = array("a.periode, a.id_item_tarif ASC");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array(
        "pelanggan b"=>"b.id=a.id_pelanggan",
        "mt_item_tarif c"=>"c.id=a.id_item_tarif");
    private $_INDEX_PAGE;
    
    function __construct() {
        parent::__construct();
        
        
        $this->_INDEX_PAGE = $this->uri->segment(1)."/".$this->uri->segment(2);
        
        $this->_CFG = new Datagridconfig();
        $this->_CFG->set_KEYS($this->_TBL_PRIMARY_PK);
        $this->_CFG->set_PRIMARY_TBL($this->_TBL_PRIMARY);
        $this->_CFG->set_JOIN_TBL($this->_TBL_JOIN);
        $this->_CFG->set_ORDER_TBL($this->_ORDER);
        $this->_CFG->set_ITEM_PER_PAGE($this->_ITEM_PER_PAGE);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.periode");
        $menu->set_FIELD_TYPE($menu->get_MONTHYEAR_TYPE());
        $menu->set_CLASS("elm-date-month");
        $menu->set_SIZE(10);
        $menu->set_FORM_ID("periode");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Periode", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.no_reff1");
        $menu->set_FIELD_DB_alias("no_pelanggan");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("no_pelanggan");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("No.Pelanggan", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.nama");
        $menu->set_FIELD_DB_alias("pelanggan");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("pelanggan");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Pelanggan", $menu);
        
        
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("c.nama");
        $menu->set_FIELD_DB_ALIAS("nama_tarif");
        $menu->set_FORM_ID("id_item_tarif");
        $menu->set_SIZE(12);
        $this->_CFG->add_column("Item Pembayaran", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.dasar_hitung");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $menu->set_FORM_ID("dasar_hitung");
        $menu->set_SIZE(12);
        $this->_CFG->add_column("Dasar Hitung", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.tarif");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $menu->set_FORM_ID("tarif");
        $menu->set_SIZE(12);
        $this->_CFG->add_column("Tarif", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.nilai");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $menu->set_FORM_ID("nilai");
        $menu->set_SIZE(12);
        $this->_CFG->add_column("Nilai", $menu);
    }
    
    function index($periode="", $id_pelanggan=""){
        $data = array();
        $whr = array ();

        if ( "" != $periode ) {
            $whr = $whr + array("a.periode"=> $periode);
            $this->session->set_userdata(array(__FILE__."periode"=> $periode));
            
        }

        if ( "" != $id_pelanggan ) {
            $whr = $whr + array("a.id_pelanggan"=> intval($id_pelanggan));
            $this->session->set_userdata(array(__FILE__."id_pelanggan"=> $id_pelanggan));
        }

        $this->_WHR_TBL = $whr;
        $this->_CFG->set_WHR_TBL($this->_WHR_TBL);

        //initiate datagrid
        $dg = new Datagrid();

        $dg->set_config($this->_CFG);
        $data["pages"] = $dg->render(TRUE);
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();
        $data["isWindowPopUp"] = TRUE;

       // echo $this->session->userdata("lastQuery");

        $this->template->load($data);
    }
    
    function form($mode="add", $key = "") {
        $data = array();

        $id_pelanggan = $this->session->userdata(__FILE__."id_pelanggan");
        $periode = $this->session->userdata(__FILE__."periode");
        $dg = new Datagrid();
        $menu = new Datagridcolumn();
        $this->_CFG->add_column("No.Pelanggan", $menu);

        $whr = array("status"=>1);
        if ( intval($id_pelanggan) > 0 ) {
            $whr =  $whr + array("id"=> intval($id_pelanggan));
        }
        $rsTipePelanggan = $this->base_model->list_data("id as kunci, nama as nilai", "pelanggan", "", $whr, array("nama asc"));
        
        $tipePelanggan = new Datagridcolumn();
        $tipePelanggan->set_FIELD_DB("id_pelanggan");        
        $tipePelanggan->set_FIELD_TYPE($tipePelanggan->get_ENUM_TYPE());
        $tipePelanggan->set_ENUM_DEFAULT_VALUE( $rsTipePelanggan );
        $tipePelanggan->set_SIZE(4);
        $tipePelanggan->set_CLASS("-selectize");
        $tipePelanggan->set_FORM_ID("id_pelanggan");
        $this->_CFG->add_column("Pelanggan", $tipePelanggan);

        $rs_item_tarif = $this->base_model->list_data("id as kunci, (kode|| ' - ' || nama) as nilai", "mt_item_tarif", "", array("status"=>1), array("nama asc"));

        $itemTarif = new Datagridcolumn();
        $itemTarif->set_FIELD_DB("id_item_tarif");
        $itemTarif->set_FIELD_TYPE($tipePelanggan->get_ENUM_TYPE());
        $itemTarif->set_ENUM_DEFAULT_VALUE( $rs_item_tarif );
        $itemTarif->set_SIZE(4);
        $itemTarif->set_CLASS("-selectize");
        $itemTarif->set_FORM_ID("id_item_tarif");
        $this->_CFG->add_column("Item Pembayaran", $itemTarif);

        $menu = new Datagridcolumn();
        $this->_CFG->add_column("Meter Awal", $menu);
        
        $jumlahMeter = new Datagridcolumn();
        $this->_CFG->add_column("Jumlah Meter", $jumlahMeter);
        
        $status = new Datagridcolumn();
        $this->_CFG->add_column("Status", $status);
        
        $dg->set_config($this->_CFG);

        // validation
        $this->form_validation->set_rules($dg->get_validation_rules());
        $this->form_validation->set_error_delimiters('<br />', '');
        $errorMsg = "";
        if ($this->input->post('save') != "") {
            
            if ($this->form_validation->run() == FALSE) {
                $errorMsg = $dg->get_validation_error($this->form_validation->error_string());
            } else {
                $errorMsg = "";
            }

            if ($errorMsg == "") {
                if ($mode == "add") {
                    $this->_tambah();
                } else if ($mode == "edit") {
                    $this->_ubah($key);
                }
                
                if ( "" != $this->base_model->db->_error_message()){
                    $errorMsg = $this->base_model->db->_error_message();
                } else {

                    redirect(base_url($this->_INDEX_PAGE). $periode."/". $id_pelanggan . _build_query_string($this->_get_query_string()));
                }
            }
            
            
        }

        //initiate datagrid
        $data["pages"] = $dg->render_form($mode, $key, $errorMsg, TRUE);
        $data["isWindowPopUp"] = TRUE;
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();

        //pasing to template lib
        $this->template->load($data);
    }

    private function _tambah() {
        $ID = "";
        $columns = $this->_CFG->get_column();
        
        $this->_TBL_PRIMARY =  _replace_after($this->_TBL_PRIMARY, " ");
        
        
        $datas = array();
        if (is_array($columns)) {
            foreach ($columns as $column => $property) {
                $value = $this->input->post($property->get_FORM_ID());
                $value = $property->get_FIELD_TYPE() == $property->get_DATE_TYPE() ? _date($value, "Y-m-d") : $value;
                $field = _replace_before($property->get_FIELD_DB(), ".");
                if ( "" != $property->get_FIELD_DB() )
                    $datas = $datas + array($field => $value);
            }

            $ID = $this->base_model->insert_data($this->_TBL_PRIMARY, $datas);
        }

        return $ID;
    }

    private function _ubah($key = "") {
        
        $this->_TBL_PRIMARY =  _replace_after($this->_TBL_PRIMARY, " ");
        $this->_TBL_PRIMARY_PK = _replace_before($this->_TBL_PRIMARY_PK, ".");
        
        if ("" != $key && "" != $this->_CFG->get_KEYS()) {
            $columns = $this->_CFG->get_column();
            $whr = array ($this->_TBL_PRIMARY_PK => $key);
            $datas = array();
            if (is_array($columns)) {
                foreach ($columns as $column => $property) {
                    $value = $this->input->post($property->get_FORM_ID());
                    $value = $property->get_FIELD_TYPE() == $property->get_DATE_TYPE() ? _date($value, "Y-m-d") : $value;
                    $field = _replace_before($property->get_FIELD_DB(), ".");
                    if ( "" != $property->get_FIELD_DB() )
                        $datas = $datas + array($field => $value);
                }

                $ID = $this->base_model->update_data($this->_TBL_PRIMARY, $datas, $whr);                
            }
        }
    }
    
    function remove($key=""){
        $id_pelanggan = $this->session->userdata(__FILE__."id_pelanggan");
        $periode = $this->session->userdata(__FILE__."periode");
        $this->_TBL_PRIMARY =  _replace_after($this->_TBL_PRIMARY, " ");
        $this->_TBL_PRIMARY_PK = _replace_before($this->_TBL_PRIMARY_PK, ".");
        
        if ( "" != $key ) {
            $whr = array ($this->_TBL_PRIMARY_PK => $key); 
            $this->base_model->delete_data($this->_TBL_PRIMARY, $whr);
        }
        
        redirect(base_url($this->_INDEX_PAGE."/index/".$periode."/".$id_pelanggan) . _build_query_string($this->_get_query_string()));
    }
}
