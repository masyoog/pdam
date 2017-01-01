<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once 'Base_message.php';

class Daftar_message extends Base_message{
    
    var $_format_request = array(
        "id_upline" => "",
        "pin" => "",
        "nomor_hp" => "",
        "nama" => "",
        "alamat" => "",
        "kota" => "",
        "propinsi" => "",
        "kode_pos" => "",
        "up_harga" => "",
        "prefix_id" => ""
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
                      "nomor_hp" => "",
                      "nama" => "",
                      "alamat" => "",
                      "kota" => "",
                      "propinsi" => "",
                      "kode_pos" => "",
                      "up_harga" => "",
                      "prefix_id" => "",
                      "unknown1" => "",
                      "tanggal_register" => "",
                      "id_upline" => "",
                      "nama_upline" => "",
                      "unknown2" => "",
                      "unknown3" => "",
                      "kota" => "",
                      "unknown4" => "",
                      "unknown5" => "",
                      "id_upline2" => "",
                      "tanggal_input" => "");
    
    public function send_to_core_system($additional_private_data) {
        $sender = new Message_sender();
        $response = $sender->send(ProcessingCode::daftar(), $additional_private_data);
        $result = $this->get_xml_message($response);
        return $result;
    }
    
    public function get_xml_message($response){
        return $this->parse_message($this->_format_response, $this->_format_response_detail, xmlrpc_decode($response));
    }
}

?>
