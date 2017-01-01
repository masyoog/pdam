<?php

class Login extends CI_Controller {
    private $lib;
    
    public function __construct(){
        header('Access-Control-Allow-Origin: *');
        parent::__construct();
        $this->lib = new ApiLib();        
    }
    
    private function log_in($user, $pass){
        $auth = $this->lib->authenticate($user, $pass);
        return $auth;
    }

    function index(){
        $user = $this->input->get_post("user");
        $pass = $this->input->get_post("pass");
        
        $out = $this->log_in($user, $pass);
        echo json_encode($out);
    }
}