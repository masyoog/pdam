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
class Tagihan extends MY_Controller {

    private $_CFG;
    private $_TBL_PRIMARY = "v_tagihan a";
    private $_TBL_PRIMARY_PK = "";
    private $_ORDER = array("periode, id_pelanggan");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array("pelanggan b"=>"b.id=a.id_pelanggan");
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
        $menu->set_FIELD_DB("periode");
        $menu->set_FIELD_DB_alias("periode");
        $menu->set_FIELD_TYPE($menu->get_MONTHYEAR_TYPE());
        $menu->set_FORM_ID("periode");
        $this->_CFG->add_column("Periode", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.id");
        $menu->set_FIELD_DB_alias("id_pelanggan");
        $menu->set_FORM_ID("id_pelanggan");
        $menu->set_VISIBLE(FALSE);
        $this->_CFG->add_column("ID Pelanggan", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.no_reff1");
        $menu->set_FIELD_DB_alias("no_pelanggan");
        $menu->set_FORM_ID("no_pelanggan");
        $this->_CFG->add_column("No.Pelanggan", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.nama");
        $menu->set_FIELD_DB_alias("nama_pelanggan");
        $menu->set_FORM_ID("nama_pelanggan");
        $this->_CFG->add_column("Nama", $menu);

        $rs = $this->base_model->list_data("id, kode, nama", "mt_item_tarif", "", array("status"=>1), array("is_rutin DESC", "id ASC"));
        if ( $rs != "") {
            foreach( $rs as $row){
                $menu = new Datagridcolumn();
                $menu->set_FIELD_DB("a.".$row->id);
                $menu->set_FIELD_DB_ALIAS("a".$row->id);
                $menu->set_FORM_ID($row->kode);
                $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
                $this->_CFG->add_column($row->nama, $menu);
            }
        }

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.total");
        $menu->set_FIELD_DB_alias("total");
        $menu->set_FORM_ID("total");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $this->_CFG->add_column("Total", $menu);

        $this->_CFG->add_grid_button(
                "DETIL", array(
                "method" => base_url("transaksi/info_tagihan/index"),
                "style" => "fa-info",
                "keys" => array("periode", "id_pelanggan"),
                "action" => "openBox('URL', '80', true)",
                "overideUri" => TRUE    ));

        $this->_CFG->add_grid_button(
                "UBAH", array());
        $this->_CFG->add_grid_button(
                "HAPUS", array());

        $this->_CFG->add_COMMAND_BUTTON("TAMBAH", array());

    }

    function index(){
        $data = array();

        //initiate datagrid
        $dg = new Datagrid();

        $dg->set_config($this->_CFG);
        $data["pages"] = $dg->render();
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();

        //echo _replace_after($this->session->userdata("lastQuery"), "LIMIT");
        //pasing to template lib

        $this->template->load($data);
    }

    function detil($periode="", $id_pelanggan=""){
       
    }
    
    function form($mode="add", $key = "") {
        $data = array();

        $dg = new Datagrid();
        $menu = new Datagridcolumn();
        $this->_CFG->add_column("No.Pelanggan", $menu);

        $rsTipePelanggan = $this->base_model->list_data("id as kunci, nama as nilai", "pelanggan", "", array('status'=>1), array("nama asc"));

        $tipePelanggan = new Datagridcolumn();
        $tipePelanggan->set_FIELD_DB("id_pelanggan");
        $tipePelanggan->set_FIELD_TYPE($tipePelanggan->get_ENUM_TYPE());
        $tipePelanggan->set_ENUM_DEFAULT_VALUE( $rsTipePelanggan );
        $tipePelanggan->set_SIZE(4);
        $tipePelanggan->set_CLASS("-selectize");
        $tipePelanggan->set_FORM_ID("id_pelanggan");
        $this->_CFG->add_column("Pelanggan", $tipePelanggan);

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
                    redirect(base_url($this->_INDEX_PAGE) . _build_query_string($this->_get_query_string()));
                }
            }


        }

        //initiate datagrid
        $data["pages"] = $dg->render_form($mode, $key, $errorMsg);
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

        $this->_TBL_PRIMARY =  _replace_after($this->_TBL_PRIMARY, " ");
        $this->_TBL_PRIMARY_PK = _replace_before($this->_TBL_PRIMARY_PK, ".");

        if ( "" != $key ) {
            $whr = array ($this->_TBL_PRIMARY_PK => $key);
            $this->base_model->delete_data($this->_TBL_PRIMARY, $whr);
        }

        redirect(base_url($this->_INDEX_PAGE) . _build_query_string($this->_get_query_string()));
    }
}
