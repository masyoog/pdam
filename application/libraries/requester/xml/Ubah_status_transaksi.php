<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once 'Base_message.php';

class Ubah_status_transaksi extends Base_message{
    
    var $_format_request = array(
        "user_update" => "",
        "id_transaksi" => "",
        "status" => "",
        "keterangan" => ""
    );
    
    var $_format_response = array(
        "user_update" => "",
        "tanggal" => "",
        "response_code" => "",
        "response_desc" => "",
        "response_array" => ""
    );
    
    var $_format_response_detail = array(
                      "nomor_resi" => "",
                      "status" => "",
                      "user_update" => "",
                      "keterangan" => "",
                      "id_member" => "",
                      "kode_produk" => "",
                      "tujuan" => "",
                      "nominal" => "");
    
    public function send_to_core_system($additional_private_data) {
        $sender = new Message_sender();
        $response = $sender->send(ProcessingCode::ubah_status_transaksi(), $additional_private_data);
        $result = $this->get_xml_message($response);
        return $result;
    }
    
    public function get_xml_message($response){
        return $this->parse_message($this->_format_response, $this->_format_response_detail, xmlrpc_decode($response));
    }
}

?>
