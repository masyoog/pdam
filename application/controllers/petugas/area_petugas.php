<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Area_petugas extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "area_petugas a";
    private $_TBL_PRIMARY_PK = "a.id";    
    private $_ORDER = array( "b.nama ASC", "c.nama ASC");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array(
                            "mt_area b"=>"a.id_area=b.id",
                            "petugas c"=>"a.id_petugas=c.id"
                        );
    private $_WHR_TBL = array();
    private $_INDEX_PAGE;
//    private $_MENU_ID = 19;
    
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
        
     
        
        $grup = new Datagridcolumn();
        $grup->set_FIELD_DB("c.nama");
        $grup->set_FIELD_DB_ALIAS("petugas");
        $grup->set_SIZE(4);
        $grup->set_FORM_ID("petugas");
        $grup->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Petugas", $grup);

        $grup = new Datagridcolumn();
        $grup->set_FIELD_DB("b.nama");
        $grup->set_FIELD_DB_ALIAS("area");
        $grup->set_SIZE(4);
        $grup->set_FORM_ID("area");
        $grup->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Area", $grup);
        
        
        
        $this->_CFG->add_grid_button(
            "UBAH", array(
        ));

        
            
    } 
    
    function index($id_petugas="", $id_area=""){
        $data = array();
        $whr = array ();
        
        if ( "" != $id_petugas  && "null" != $id_petugas) {
            $whr = $whr + array("a.id_petugas"=> intval($id_petugas));
            $this->session->set_userdata(array(__FILE__."id_petugas" => intval($id_petugas) ));
        }
        
        if ( "" != $id_area ) {
            $whr = $whr + array("a.id_area"=> intval($id_area));
            $this->session->set_userdata(array(__FILE__."id_area" => intval($id_area) ));
        }
        
        $this->_WHR_TBL = $whr;
        $this->_CFG->set_WHR_TBL($this->_WHR_TBL);
        
        //initiate datagrid
        $dg = new Datagrid();
        
        $dg->set_config($this->_CFG);
        $data["pages"] = $dg->render(TRUE);
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();
        $data["isWindowPopUp"] = TRUE;
        
//        echo $this->session->userdata("lastQuery");
        
        $this->template->load($data);        
    }
    
    function form($mode, $key = "") {
        $data = array();
        $id_petugas = $this->session->userdata(__FILE__."id_petugas");
        $dg = new Datagrid();

        $grup = new Datagridcolumn();
        $this->_CFG->add_column("Petugas", $grup);

        $rsMenuInduk = $this->base_model->list_data("id as kunci, nama as nilai", "mt_area", "", array('status'=>1), array("nama asc"));

        $menuInduk = new Datagridcolumn();
        $menuInduk->set_FIELD_DB("id_area");
        $menuInduk->set_FIELD_TYPE($menuInduk->get_ENUM_TYPE());
        $menuInduk->set_ENUM_DEFAULT_VALUE( $rsMenuInduk );
        $menuInduk->set_SIZE(4);
        $menuInduk->set_CLASS("-selectize");
        $menuInduk->set_FORM_ID("id_area");
        $this->_CFG->add_column("Area", $menuInduk);
        
        
        $this->_CFG->add_COMMAND_BUTTON(
            "BATAL", array(
            "name"=>'cancel',
            "type"=>'button',    
            "class" => "btn-danger",
            "action" => "javascript:closeBox(true);"             
        ));
        
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
                    redirect(base_url($this->_INDEX_PAGE."/index/".$id_petugas) . _build_query_string($this->_get_query_string()));
                }
            }
            
            
        }

        //initiate datagrid
        $data["pages"] = $dg->render_form($mode, $key, $errorMsg, TRUE);
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();
        $data["isWindowPopUp"] = TRUE;
        //pasing to template lib
        $this->template->load($data);
    }
    
    private function _tambah() {
        $ID = "";
        $columns = $this->_CFG->get_column();
        
        $this->_TBL_PRIMARY =  _replace_after($this->_TBL_PRIMARY, " ");
        $id_petugas = $this->session->userdata(__FILE__."id_petugas");
        
        $datas = array("id_petugas"=>$id_petugas);
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
        $id_petugas = $this->session->userdata(__FILE__."id_petugas");


        if ("" != $key && "" != $this->_CFG->get_KEYS()) {
            $columns = $this->_CFG->get_column();
            $whr = array ($this->_TBL_PRIMARY_PK => $key);
            $datas = array("id_petugas"=>$id_petugas);
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
        $id_petugas = $this->session->userdata(__FILE__."id_petugas");
        if ( "" != $key ) {
            $whr = array ($this->_TBL_PRIMARY_PK => $key); 
            $this->base_model->delete_data($this->_TBL_PRIMARY, $whr);
        }
        
        redirect(base_url($this->_INDEX_PAGE."/index/".$id_petugas) . _build_query_string($this->_get_query_string()));
    }
    
}
?>
