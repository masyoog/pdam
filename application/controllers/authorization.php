<?php

class Authorization extends CI_Controller {
    
    function __construct() {
        parent::__construct();
    }
    
    function index()
    {
        $data = '';
        $data['error_msg'] = $this->session->userdata('error_msg');
        $this->load->view('login', $data);
    }
    
    function authorized()
    {
        $username = $this->input->post('login');
        $password = $this->input->post('password');
        
        $auth = new Userauth();
        $auth->authorize($username, $password);
        
        if ( $auth->authorize($username, $password) ){
//            
            $this->session->set_userdata(array('error_msg'=> ''));
            redirect(base_url()."home");
        } else {
            $this->session->set_userdata(array('error_msg'=> $auth->get_error_string()));
            redirect('authorization');
        }
    }
    
    function logout()
    {
        $this->session->sess_destroy();
        redirect('/','refresh');
    }
}
