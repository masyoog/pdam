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
class Tarif extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "mt_tarif a";
    private $_TBL_PRIMARY_PK = "a.id";
    private $_ORDER = array("a.id_tipe_pelanggan, a.id_item_tarif, a.batas ASC");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array(
        "mt_tipe_pelanggan b"=>"b.id=a.id_tipe_pelanggan",
        "mt_item_tarif c"=>"c.id=a.id_item_tarif"
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
        $menu->set_FIELD_DB("c.nama");
        $menu->set_FIELD_DB_ALIAS("item_tarif");
        $menu->set_FORM_ID("id_item_tarif");
        $menu->set_SIZE(12);
        $this->_CFG->add_column("Item Tarif", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.nama");
        $menu->set_FORM_ID("id_tipe_pelanggan");
        $menu->set_SIZE(12);
        $this->_CFG->add_column("Tipe Pelanggan", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.batas");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $menu->set_FORM_ID("batas");
        $menu->set_SIZE(11);
        $this->_CFG->add_column("Batas", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.satuan");
        $menu->set_FORM_ID("satuan");
        $menu->set_SIZE(32);
        $this->_CFG->add_column("Satuan Batas", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.tarif");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $menu->set_FORM_ID("Tarif");
        $menu->set_SIZE(11);
        $this->_CFG->add_column("Tarif", $menu);
    }
    
    function index(){
        $data = array();
        $whr = array ();
        
        //initiate datagrid
        $dg = new Datagrid();
        
        $dg->set_config($this->_CFG);
        $data["pages"] = $dg->render();
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();
        
        $this->template->load($data);        
    }
    
    function form($mode="add", $key = "") {
        $data = array();

        $dg = new Datagrid();
        
        $rsTipePelanggan = $this->base_model->list_data("id as kunci, (kode ||' - '|| nama) as nilai", "mt_tipe_pelanggan", "", array('status' => 1), array("kode asc"));

        $tipe_pelanggan = new Datagridcolumn();
        $tipe_pelanggan->set_FIELD_DB("a.id_tipe_pelanggan");
        $tipe_pelanggan->set_FIELD_TYPE($tipe_pelanggan->get_ENUM_TYPE());
        $tipe_pelanggan->set_ENUM_DEFAULT_VALUE($rsTipePelanggan);
        $tipe_pelanggan->set_REQUIRED(TRUE);
        $tipe_pelanggan->set_FORM_ID("id_tipe_pelanggan");
        $this->_CFG->add_column("Tipe Pelanggan", $tipe_pelanggan);

        $rsItemTarif = $this->base_model->list_data("id as kunci, (kode ||' - '|| nama) as nilai", "mt_item_tarif", "", array('status' => 1), array("kode asc"));

        $tipe_pelanggan = new Datagridcolumn();
        $tipe_pelanggan->set_FIELD_DB("a.id_item_tarif");
        $tipe_pelanggan->set_FIELD_TYPE($tipe_pelanggan->get_ENUM_TYPE());
        $tipe_pelanggan->set_ENUM_DEFAULT_VALUE($rsItemTarif);
        $tipe_pelanggan->set_REQUIRED(TRUE);
        $tipe_pelanggan->set_FORM_ID("id_item_tarif");
        $this->_CFG->add_column("Item Tarif", $tipe_pelanggan);

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
                    redirect(base_url($this->_INDEX_PAGE."/index/".$this->session->userdata("id_tipe_pelanggan")) . _build_query_string($this->_get_query_string()));
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
        
        redirect(base_url($this->_INDEX_PAGE."/index/".$this->session->userdata("id_tipe_pelanggan")) . _build_query_string($this->_get_query_string()));
    }
}
