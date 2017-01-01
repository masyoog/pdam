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
class Pelanggan extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "pelanggan a";
    private $_TBL_PRIMARY_PK = "a.id";    
    private $_ORDER = array("a.id ASC");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array(
        "mt_tipe_pelanggan b"=>"b.id=a.id_tipe_pelanggan",
        "mt_area c"=>"c.id=a.id_area",
        );
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
        $menu->set_FIELD_DB("b.nama");
        $menu->set_FIELD_DB_ALIAS("tipe_pelanggan");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("id_tipe_pelanggan");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Tipe Pelanggan", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("c.kode");
        $menu->set_FIELD_DB_ALIAS("kodearea");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("kodearea");
        $this->_CFG->add_column("Kode Area", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("c.nama");
        $menu->set_FIELD_DB_ALIAS("nama_area");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("nama_area");
        $this->_CFG->add_column("Area", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.no_reff1");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("no_reff1");
        $menu->set_VALIDATION("is_unique[pelanggan.no_reff1]");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("No Pelanggan", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.no_reff2");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("no_reff2");
        $menu->set_REQUIRED(TRUE);
        $menu->set_VALIDATION("is_unique[pelanggan.no_reff2]");
        $this->_CFG->add_column("No Meteran", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.nama");
        $menu->set_SIZE(256);
        $menu->set_FORM_ID("nama");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Nama Pelanggan", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.alamat");
        $menu->set_SIZE(256);
        $menu->set_FORM_ID("alamat");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Alamat", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.no_hp");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("no_hp");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("No HP", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.email");
        $menu->set_FIELD_TYPE($menu->get_EMAIL_TYPE());
        $menu->set_SIZE(256);
        $menu->set_FORM_ID("email");
        $this->_CFG->add_column("Email", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.tanggal_registrasi");
        $menu->set_FIELD_TYPE($menu->get_DATE_TYPE());
        $menu->set_SIZE(10);
        $menu->set_FORM_ID("tanggal_registrasi");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Tanggal Registrasi", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.tanggal_terpasang");
        $menu->set_FIELD_TYPE($menu->get_DATE_TYPE());
        $menu->set_SIZE(10);
        $menu->set_FORM_ID("tanggal_terpasang");
        $this->_CFG->add_column("Tanggal Terpasang", $menu);
        
        $status = new Datagridcolumn();
        $status->set_FIELD_DB("a.status");
        $status->set_FIELD_TYPE($status->get_ENUM_TYPE());
        $status->set_FORM_ID("status");
        $status->set_ENUM_DEFAULT_VALUE(
                array("1" => "Aktif",
                    "0" => "Pasif"
        ));
        $status->set_STYLE(
                array("1" => '<span class="label label-success">Aktif</span>',
                    "0" => '<span class="label label-warning">Pasif</span>'
        ));
        $status->set_SIZE(1);
        $this->_CFG->add_column("Status", $status);
        
    }
    
    function index(){
        $data = array();

        //initiate datagrid
        $dg = new Datagrid();
        $dg->set_config($this->_CFG);
        $data["pages"] = $dg->render();
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();

        //echo _replace_after($this>session->userdata("lastQuery"), "LIMIT");
        //pasing to template lib
        
        $this->template->load($data);        
    }
    
    function form($mode="add", $key = "") {
        $data = array();

        $dg = new Datagrid();
        
        $rsTipePelanggan = $this->base_model->list_data("id as kunci, nama as nilai", "mt_tipe_pelanggan", "", array('status'=>1), array("nama asc"));       
        
        $tipePelanggan = new Datagridcolumn();
        $tipePelanggan->set_FIELD_DB("id_tipe_pelanggan");
        $tipePelanggan->set_FIELD_TYPE($tipePelanggan->get_ENUM_TYPE());
        $tipePelanggan->set_ENUM_DEFAULT_VALUE( $rsTipePelanggan );
        $tipePelanggan->set_VALIDATION("greater_than[0]");
        $tipePelanggan->set_REQUIRED(TRUE);
        $tipePelanggan->set_SIZE(4);
        $tipePelanggan->set_CLASS("-selectize");
        $tipePelanggan->set_FORM_ID("id_tipe_pelanggan");
        $this->_CFG->add_column("Tipe Pelanggan", $tipePelanggan);

        $menu = new Datagridcolumn();
        $this->_CFG->add_column("Kode Area", $menu);

        $rsArea = $this->base_model->list_data("id as kunci, (kode ||' - '|| nama) as nilai", "mt_area", "", array('status'=>1), array("nama asc"));

        $tipePelanggan = new Datagridcolumn();
        $tipePelanggan->set_FIELD_DB("id_area");
        $tipePelanggan->set_FIELD_TYPE($tipePelanggan->get_ENUM_TYPE());
        $tipePelanggan->set_REQUIRED(TRUE);
        $tipePelanggan->set_VALIDATION("greater_than[0]");
        $tipePelanggan->set_ENUM_DEFAULT_VALUE( $rsArea );
        $tipePelanggan->set_SIZE(4);
        $tipePelanggan->set_CLASS("-selectize");
        $tipePelanggan->set_FORM_ID("id_area");
        $this->_CFG->add_column("Area", $tipePelanggan);
        
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
