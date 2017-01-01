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
class Menu extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "sys_menu a";
    private $_TBL_PRIMARY_PK = "a.id";    
    private $_ORDER = array("a.id_induk ASC", "a.urutan ASC");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array("sys_menu b"=>"b.id=a.id_induk");
    private $_INDEX_PAGE;
//    private $_MENU_ID = 6;
    
    function __construct() {
        parent::__construct();
        
        
        $this->_INDEX_PAGE = $this->uri->segment(1)."/".$this->uri->segment(2);
        
        $this->_CFG = new Datagridconfig();
        $this->_CFG->set_KEYS($this->_TBL_PRIMARY_PK);
        $this->_CFG->set_PRIMARY_TBL($this->_TBL_PRIMARY);
        $this->_CFG->set_JOIN_TBL($this->_TBL_JOIN);
        $this->_CFG->set_ORDER_TBL($this->_ORDER);
        $this->_CFG->set_ITEM_PER_PAGE($this->_ITEM_PER_PAGE);
//        $this->_CFG->set_MENU_ID($this->_MENU_ID);
        
        $menuInduk = new Datagridcolumn();
        $menuInduk->set_FIELD_DB("b.menu");
        $menuInduk->set_FIELD_DB_ALIAS("menu_induk");
        $menuInduk->set_SIZE(4);
        $menuInduk->set_FORM_ID("id_induk");
        $menuInduk->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Menu Induk", $menuInduk);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.menu");
        $menu->set_SIZE(128);
        $menu->set_FORM_ID("menu");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Menu", $menu);
        
        $urutan = new Datagridcolumn();
        $urutan->set_FIELD_DB("a.urutan");
        $urutan->set_SIZE(2);
        $urutan->set_FORM_ID("urutan");
        $urutan->set_FIELD_TYPE($urutan->get_NUM_TYPE());
        $this->_CFG->add_column("Urutan", $urutan);
        
        $uri = new Datagridcolumn();
        $uri->set_FIELD_DB("a.uri");
        $uri->set_SIZE(256);
        $uri->set_FORM_ID("uri");                
        $this->_CFG->add_column("URL", $uri);
        
        $icon = new Datagridcolumn();
        $icon->set_FIELD_DB("a.icon");
        $icon->set_SIZE(256);
        $icon->set_FORM_ID("icon");                
        $this->_CFG->add_column("Icon", $icon);
        
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
        
        $this->_CFG->add_grid_button(
                "HAK AKSES" , array(
                    "method" => base_url("setting/grup_akses/index"),
                    "style" => "fa-unlock",
                    "action" => "openBox('URL', '80')",
                    "overideUri" => TRUE
                    )
                );
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
    
    function form($mode="add", $key = "") {
        $data = array();

        $dg = new Datagrid();
      
        $rsMenuInduk = $this->base_model->list_data("id as kunci, menu as nilai", "sys_menu", "", array('status'=>1, 'id_induk'=>0), array("menu asc"));       
        
        $menuInduk = new Datagridcolumn();
        $menuInduk->set_FIELD_DB("id_induk");
        $menuInduk->set_FIELD_DB_ALIAS("menu_induk");
        $menuInduk->set_FIELD_TYPE($menuInduk->get_ENUM_TYPE());
        $menuInduk->set_ENUM_DEFAULT_VALUE( $rsMenuInduk );
        $menuInduk->set_SIZE(4);
        $menuInduk->set_CLASS("-selectize");
        $menuInduk->set_FORM_ID("id_induk");
        $this->_CFG->add_column("Menu Induk", $menuInduk);
        
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
