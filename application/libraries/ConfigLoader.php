<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigLoader
 *
 * @author yoog
 */
class ConfigLoader {
    
    private $CI;
    
    function __construct() {
        $this->CI = & get_instance();
    }
    
    function get_config($tipe=""){
        $out = array();
        
        if ( $tipe == "" ){
            return $out;
        }
        
        $rs = $this->CI->base_model->list_data("*", "sys_config", "", array("status"=>1));
        if ( is_array( $rs)) {
            foreach ( $rs as $row ){
                $out[ $row->nama ] = $row->nilai;
            }
        }
        
        return $out;
    }
    
    function get_param($tipe="", $nama=""){
        $out = array();
                
        $out = $this->CI->base_model->list_single_data("*", "sys_config", "", array("tipe"=>$tipe, "status"=>1));
        
        $out = _get_raw_object($out, $nama);
        return $out;
    }
}
