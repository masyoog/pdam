<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once 'include/ProcessingCode.class.php';
require_once 'Message_sender.php';

class Base_message
{  
    var $ci;
    
    function __construct() {
        $this->ci =& get_instance();
    }
    
    public function parse_message($format, $detail, $data){
        $idx = 0;
        foreach($format as $key=>$value){
            $format[$key] = _get_raw_item($data, $idx);
            if($key == "response_array"){
                $idx2 = 0;
                foreach($detail as $key2=>$value2){
//                    echo "[" . $key2 . "] " . $detail[$key2] . " => " . $data[$idx][$idx2] . "<br/>";
                    $detail[$key2] = _get_raw_item(_get_raw_item($data, $idx), $idx2);
                    $idx2++;
                }
            }
            $idx++;
        }
        return $format + $detail; 
    }
}

