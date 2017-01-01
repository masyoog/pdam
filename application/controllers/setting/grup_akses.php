<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Grup_akses extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "sys_grup_akses a";
    private $_TBL_PRIMARY_PK = "a.id";    
    private $_ORDER = array( "b.id_induk ASC", "a.id_menu ASC","a.id_grup_user ASC");
    private $_ITEM_PER_PAGE = "";
    private $_TBL_JOIN = array(
                            "sys_menu b"=>"a.id_menu=b.id",
                            "sys_grup_user c"=>"a.id_grup_user=c.id",
                            "sys_menu d"=>"b.id_induk=d.id"
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
        
        $modul = new Datagridcolumn();
        $modul->set_FIELD_DB("d.menu");
        $modul->set_FIELD_DB_ALIAS("modul");
        $modul->set_SIZE(128);
        $modul->set_FORM_ID("id_modul");
        $modul->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Modul", $modul);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("b.menu");
        $menu->set_FIELD_DB_ALIAS("menu");
        $menu->set_SIZE(128);
        $menu->set_FORM_ID("id_menu");
        $menu->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Menu", $menu);
        
        $grup = new Datagridcolumn();
        $grup->set_FIELD_DB("c.nama");
        $grup->set_FIELD_DB_ALIAS("grup");
        $grup->set_SIZE(128);
        $grup->set_FORM_ID("id_grup_user");
        $grup->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Grup User", $grup);
        
        $baca = new Datagridcolumn();
        $baca->set_FIELD_DB("a.baca");
        $baca->set_FIELD_DB_ALIAS("baca");
        $baca->set_SIZE(1);
        $baca->set_FORM_ID("baca");
        $baca->set_FIELD_TYPE($baca->get_CHECKBOX_TYPE());
        $baca->set_ENUM_DEFAULT_VALUE(
                array("1" => "checked",
                    "0" => ""
        ));
        $baca->set_STYLE(
                array("1" => '<i class="fa fa-check text-success"></i>',
                    "0" => '<i class="fa fa-times text-danger"></i>'
        ));
        $this->_CFG->add_column("Lihat", $baca);
        
        $tambah = new Datagridcolumn();
        $tambah->set_FIELD_DB("a.tambah");
        $tambah->set_FIELD_DB_ALIAS("tambah");
        $tambah->set_SIZE(128);
        $tambah->set_FORM_ID("tambah"); 
        $tambah->set_FIELD_TYPE($baca->get_CHECKBOX_TYPE());
        $tambah->set_STYLE(
                array("1" => '<i class="fa fa-check text-success"></i>',
                    "0" => '<i class="fa fa-times text-danger"></i>'
        ));
        $this->_CFG->add_column("Tambah Data", $tambah);
        
        $ubah = new Datagridcolumn();
        $ubah->set_FIELD_DB("a.ubah");
        $ubah->set_FIELD_DB_ALIAS("ubah");
        $ubah->set_SIZE(128);
        $ubah->set_FORM_ID("ubah");   
        $ubah->set_FIELD_TYPE($baca->get_CHECKBOX_TYPE());        
        $ubah->set_STYLE(
                array("1" => '<i class="fa fa-check text-success"></i>',
                    "0" => '<i class="fa fa-times text-danger"></i>'
        ));
        $this->_CFG->add_column("Ubah Data", $ubah);
        
        $hapus = new Datagridcolumn();
        $hapus->set_FIELD_DB("a.hapus");
        $hapus->set_FIELD_DB_ALIAS("hapus");
        $hapus->set_SIZE(128);
        $hapus->set_FORM_ID("hapus");
        $hapus->set_FIELD_TYPE($baca->get_CHECKBOX_TYPE());
        $hapus->set_STYLE(
                array("1" => '<i class="fa fa-check text-success"></i>',
                    "0" => '<i class="fa fa-times text-danger"></i>'
        ));
        $this->_CFG->add_column("Hapus Data", $hapus);
        
        $cetak = new Datagridcolumn();
        $cetak->set_FIELD_DB("a.cetak");
        $cetak->set_FIELD_DB_ALIAS("cetak");
        $cetak->set_SIZE(128);
        $cetak->set_FORM_ID("cetak");  
        $cetak->set_FIELD_TYPE($baca->get_CHECKBOX_TYPE());
        $cetak->set_STYLE(
                array("1" => '<i class="fa fa-check text-success"></i>',
                    "0" => '<i class="fa fa-times text-danger"></i>'
        ));
        $this->_CFG->add_column("Cetak", $cetak);
        
        $this->_CFG->add_grid_button(
            "UBAH", array(
            "method" => base_url( $this->_INDEX_PAGE )."/form/edit",
            "style" => "fa-pencil",
            "action" => "openBox('URL', 80)",
            "overideUri"=>TRUE        
        ));

        $this->_CFG->add_grid_button(
            "HAPUS", array());
        
        $this->_CFG->add_COMMAND_BUTTON("TAMBAH", array());        
    } 
    
    function index($id_menu="", $id_grup = ""){
        $data = array();
        $whr = array ();
        
        if ( "" != $id_menu  && "null" != $id_menu) {
            $whr = $whr + array("a.id_menu"=> intval($id_menu));            
        }
        
        if ( "" != $id_grup ) {
            $whr = $whr + array("a.id_grup_user"=> intval($id_grup));
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

        $dg = new Datagrid();
        
        $menu = new Datagridcolumn();
        $this->_CFG->add_column("Menu", $menu);
        
        $modul = new Datagridcolumn();
        $this->_CFG->add_column("Modul", $modul);
        
        $grup = new Datagridcolumn();
        $this->_CFG->add_column("Grup User", $grup);
        
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
                    $data["additional_script2"] = "closeBox(true);";
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
?>
