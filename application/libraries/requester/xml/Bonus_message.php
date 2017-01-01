<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once 'Base_message.php';

class Bonus_message extends Base_message{
    
    var $_format_request = array(
        "id_member" => "",
        "pin" => ""
    );
    
    var $_format_response = array(
        "id_pendaftar" => "",
        "pin" => "",
        "tanggal" => "",
        "response_code" => "",
        "response_desc" => "",
        "unknown" => "",
        "response" => "",
        "response_array" => ""
    );
    
    var $_format_response_detail = array(
        "id_member"=>"",
        "nama"=>"",
        "saldo"=>"",
        ""=>"",
        "kota"=>"",
        ""=>"",
        ""=>"",
        "id_upline"=>"",
        "tanggal_input"=>"",
        "bonus"=>""
    );
    
    public function send_to_core_system($additional_private_data) {
        $sender = new Message_sender();
        $response = $sender->send(ProcessingCode::bonus(), $additional_private_data);
        $result = $this->get_xml_message($response);
        return $result;
    }
    
    public function get_xml_message($response){
        return $this->parse_message($this->_format_response, $this->_format_response_detail, xmlrpc_decode($response));
    }
}

?>
