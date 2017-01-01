<?php

class Change_password extends CI_Controller {
    private $lib;
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->lib = new ApiLib(); 
    }

    private function pass($user, $pass, $pass_baru) {
        $status = false;
        $auth = $this->lib->authenticate($user, $pass);
        $msg = "";
        $out = $auth;
        
        if (!_get_raw_item($auth, "status")){            
            return $out;            
        } 
        
        if (!$pass_baru){
            $out["msg"] = "Password Baru tidak valid";            
            return $out;            
        }        
        
        $whr = array('username' => $user);
        $this->base_model->update_data('petugas', array('userpassword' => $pass_baru), $whr);
        $msg = 'Success';
        $out["msg"] = $msg;

        return $out;        
    }

  
    function index() {
        $user = $this->input->get_post("user");
        $pass = $this->input->get_post("pass");
        $pass_baru = $this->input->get_post('pass_baru');

        $out = $this->pass($user, $pass, $pass_baru);
        echo json_encode($out);
    }

}