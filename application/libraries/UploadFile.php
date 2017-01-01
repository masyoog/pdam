<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UploadFile
 *
 * @author yoog
 */
class UploadFile {

    private $CI;
    private $err_msg;

    function __construct() {
        $this->CI = & get_instance();
    }

    function get_error_msg(){
        return $this->err_msg;
    }
    
    function do_upload($fieldName = "", $cnf = "", $fileNameAfterUpload="") {
        $out = FALSE;

        if ("" == $fieldName OR "" == $cnf) {
            return $out;
        }
        
        
        $this->CI->load->library('upload', $cnf);

        if (!$this->CI->upload->do_upload($fieldName)) {
            $this->err_msg = $this->CI->upload->display_errors();
            
        } else {
            $out = $this->CI->upload->data();
            $this->err_msg = "";
        }
        return $out;
    }

}
