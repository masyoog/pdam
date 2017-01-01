<?php

class MY_Controller extends CI_Controller {
    
    var $auth;
    
    function __construct() {
        
        parent::__construct();
        
        $this->auth = new Userauth();

        if ( ! $this->auth->is_logged() ) {
            redirect ( 'authorization' );
        }
        
        if ( $this->router->fetch_method() != 'error_page'){
            if ( $this->auth->view_page_authorize() !== TRUE)
                redirect ( $this->auth->view_page_authorize().'/error_page' );
        }

       
    }
    
    function _get_query_string($param = "") {
        $out = "";
        if (is_array($_GET)) {
            if ("" == $param) {
                foreach ($_GET as $param => $value) {
                    $out[$param] = $this->input->get_post($param);
                }
            } else {
                $out = $this->input->get_post($param);
            }
        }
        return $out;
    }
    
    function error_page(){
        $data = '';
        $data['paging'] = '';
        $data['grid'] = '';
        $this->template->load('template/template_error', 'error.php', $data);
    }

    function select_required($str){
        $str = trim ($str);
        $this->form_validation->set_message('select_required', 'field %s harus dipilih');
        return $str == '0' || strlen($str) < 1 ? FALSE : TRUE;
    }
}

?>
