<?php 

class Download extends CI_Controller {
    private $lib;
    
    public function __construct($config = 'rest'){
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->lib = new ApiLib(); 
    }
    
    private function dl($user, $pass){
        $auth = $this->lib->authenticate($user, $pass);
        $area = _get_raw_item($auth, "area");
        
        $out = $auth;
        
        if (!_get_raw_item($auth, "status")){
            
            return $out;            
        } 
        
        $select = "a.id as id";
        $select .= ", a.no_reff1 as idpel";
        $select .= ", a.no_reff2 as idpel2";
        $select .= ", a.nama";
        $select .= ", a.alamat";
        $select .= ", c.nama as blok";
        $select .= ", b.nama as golongan";
        $select .= ", d.meter_akhir as meter_awal ";
        $select .= ", e.meter_akhir ";
        
        $table = "pelanggan a";
        $join = array(
            "mt_tipe_pelanggan b"=>"b.id=a.id_tipe_pelanggan",
            "mt_area c"=>"c.id=a.id_area",
            "pemakaian d"=>"d.id_pelanggan=a.id AND d.periode='".date("m-Y", strtotime("-1 month"))."'",
            "pemakaian e"=>"d.id_pelanggan=a.id AND d.periode='".date("m-Y")."'"
        );
        
        $whr = array(
            "a.status"=>1, 
            "a.id_area IN (". implode(",", $area).")"=>NULL            
        );
        
        $rs = $this->base_model->list_data(
            $select,
            $table,
            $join,
            $whr    
        );
        
        $out = $out + array("data"=>$rs);
        return $out;
    }

    function index(){
        $user = $this->input->get_post("user");
        $pass = $this->input->get_post("pass");
        $out = $this->dl($user, $pass);
        echo json_encode($out);
    }
}