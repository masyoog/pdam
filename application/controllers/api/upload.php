<?php 

class Upload extends CI_Controller {
    private $lib;
    
    public function __construct($config = 'rest'){
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->lib = new ApiLib();
        
//        {
//	"user":"user",
//	"pass":"pass",
//	"data": {
//		"idpel":"",
//		"idpel2":"",
//		"periode": "",
//		"meter_awal":"",
//		"meter_akhir":"",
//		"tgl_catat":"",
//		"status_meteran":"",
//		"ket_status":"",
//		"lat":"",
//		"lng":"",
//		"foto_meter":"",
//		"foto_ruman":"",
//	}
}
    }
    
    function index(){
        $data = file_get_contents("php://input"); 
//        $user = $this->input->get_post("user");
//        $pass = $this->input->get_post("pass");
//        $data = $this->input->get_post("data");
//        $out = $this->upload($user, $pass, $data);
//        echo json_encode($out);
    }
}