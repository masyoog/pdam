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
class Pembayaran extends MY_Controller {
    
    private $_CFG;
    private $_TBL_PRIMARY = "pembayaran a";
    private $_TBL_PRIMARY_PK = "a.id";    
    private $_ORDER = array("a.id ASC");
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
        $this->_CFG->add_column("Nama Pelanggan", $menu);
        
        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.tanggal_bayar");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("tanggal_bayar");
        $menu->set_FIELD_TYPE($menu->get_DATE_TYPE());
        $this->_CFG->add_column("Tanggal", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("a.jumlah");
        $menu->set_SIZE(32);
        $menu->set_FORM_ID("jumlah");
        $menu->set_FIELD_TYPE($menu->get_NUM_TYPE());
        $menu->set_EDITABLE(FALSE);
        $this->_CFG->add_column("Jumlah", $menu);
        
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
        $menu = new Datagridcolumn();
        $this->_CFG->add_column("No.Pelanggan", $menu);
        
        $rsTipePelanggan = $this->base_model->list_data("id as kunci, (no_reff1 || ' - ' || no_reff2 || ' - ' ||nama) as nilai", "pelanggan", "", array('status'=>1), array("nama asc"));
        
        $tipePelanggan = new Datagridcolumn();
        $tipePelanggan->set_FIELD_DB("id_pelanggan");        
        $tipePelanggan->set_FIELD_TYPE($tipePelanggan->get_ENUM_TYPE());
        $tipePelanggan->set_ENUM_DEFAULT_VALUE( $rsTipePelanggan );
        $tipePelanggan->set_SIZE(4);
        $tipePelanggan->set_CLASS("-selectize");
        $tipePelanggan->set_FORM_ID("id_pelanggan");
        $tipePelanggan->set_REQUIRED(TRUE);
        $this->_CFG->add_column("Nama Pelanggan", $tipePelanggan);

        $menu = new Datagridcolumn();
        $this->_CFG->add_column("Tanggal", $menu);

        $menu = new Datagridcolumn();
        $menu->set_FIELD_DB("''");
        $menu->set_FIELD_DB_alias("detil_tagihan");
        $menu->set_SIZE(32);
        $menu->set_EDITABLE(FALSE);
        $menu->set_FORM_ID("detil_tagihan");
        $this->_CFG->add_column("Detil Tagihan", $menu);

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
                    $id_pelanggan = $this->input->post("id_pelanggan");
                    $pelanggan = $this->base_model->list_single_data("*", "pelanggan", "", array("id"=>intval($id_pelanggan)));
                    $resp = $this->base_model->execute("SELECT fn_bayar('". $pelanggan->no_reff1."','')");
                    $resp = _get_raw_object(_get_raw_item($resp, 0), "fn_bayar");
                    $errorMsg = json_decode($resp);
                    
                    $errorMsg = _get_raw_object($errorMsg, "desc");
                    
                    if ( $errorMsg == "NULL" ){
                        $errorMsg = "";
                    }else {
                        $errorMsg = $dg->get_validation_error($errorMsg);
                    }
                } else if ($mode == "edit") {
                    $this->_ubah($key);
                }
                
                if ( "" == $errorMsg){

                    redirect(base_url($this->_INDEX_PAGE) . _build_query_string($this->_get_query_string()));
                }
            }
            
            
        }

        $addContent = '<div id="billWrapper" class="row"></div>';
        //initiate datagrid
        $pages = $dg->render_form($mode, $key, $errorMsg, FALSE,$addContent);
        
        $data["pages"] = $pages;
        $addScript = '
            function renderBill(response){
                $("#detil_tagihan").hide();

                var data = $.parseJSON(response);
                var fields = data.fields;
                var rows = data.data;
                var trHTML = "";
                var total = 0;
                $.each(rows, function (i, item) {
                    trHTML += "<div class=\"col-md-4\"><ul class=\"billContent\">";
                    $.each(item, function (o, nilai){
                        trHTML += "<li>";
                        trHTML += "<span class=\"billItemTitle\">"+ o +"</span>";
                        trHTML += "<span class=\"billItemValue\">"+ nilai +"</span>";
                        trHTML += "</li>";
                        if( o == "Total"){
                            console.log(nilai);
                            total = parseFloat(total) + parseFloat(nilai);
                        }
                    });
                    trHTML += "</ul>";
                    trHTML += "</div>";
                });
                
                $("#billWrapper").html(trHTML);
                $("#jumlah").val(total.toFixed(2));
                $("#jumlah").priceFormat(moneyFormat);
            };
            
            $("#id_pelanggan")[0].selectize.on("change", function(){

                  var idPel = parseInt(this.getValue());
                  
                  if ( idPel > 0 ){
                    $.ajax({
                      type: "GET",
                      url: "'. base_url("transaksi/pembayaran/detil") .'/" + idPel,
                      data: {},
                      success:function(resp){
                        renderBill(resp);
                      },
                      error:function(){}
                    });
                    }
            });
        ';
        
        $dg->set_ADDITIONAL_SCRIPT($addScript);
        $data["additional_script"] = $dg->get_ADDITIONAL_SCRIPT();

        //pasing to template lib
        $this->template->load($data);
    }

    function detil($id_pelanggan){
        $out = array();
        $field= '"periode" as "Periode"';
        $id_pelanggan = intval($id_pelanggan);

        $rs = $this->base_model->list_data("id, kode, nama", "mt_item_tarif", "", array("status"=>1), array("is_rutin DESC", "id ASC"));
        if ( $rs != "") {
            foreach( $rs as $row){
               $field .= $field == "" ? "" : ", ";
               $field .= '"'.$row->id .'" as "' . ucwords($row->nama) .'"';
            }
        }
        
        $field .= ', "total" as "Total"';
        $out["fields"] = array();
        
        foreach(explode(",", $field) as $names){
            array_push($out["fields"], str_replace('"', '', trim(_replace_before($names, "as"))));
        }
        $rs = $this->base_model->list_data($field, "v_tagihan", "", array('id_pelanggan'=>$id_pelanggan), array("periode asc"));
        $out["data"] = $rs;
        echo json_encode($out);
        
    }

    private function _tambah() {
        $ID = "";
        $id_pelanggan = $this->input->post("id_pelanggan");
//        $this->base_model
//        return $ID;
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
