<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once 'Base_message.php';
require_once 'Message_sender.php';

class Deposit_message extends Base_message{
    
//    var $_user_input = "";
//    var $_nominal = "";
//    var $_id_tiket = "";
//    var $_id_bank = "";
//    var $_noresi = "";
//    var $_id_member = "";
//    var $_nama = "";
//    var $_saldo = "";
//    var $_alamat = "";
//    var $_kota = "";
//    var $_propinsi = "";
//    var $_kode_pos = "";
//    var $_id_upline = "";
//    var $_tgl_registrasi = "";
    
    var $_format_request = array(
        "10" => "right",
        "12" => "left",
        "12" => "left",
        "2" => "left"
    );
    
    var $_format_response = array(
        "10" => "right",
        "12" => "left",
        "12" => "left",
        "2" => "left",
        "12" => "left",
        "10" => "right",
        "50" => "right",
        "12" => "left",
        "100" => "right",
        "25" => "right",
        "20" => "right",
        "6" => "right",
        "10" => "right",
        "8" => ""
    );
    
    public function send_to_core_system($additional_private_data, $id_member, $pin) {
        
        $this->set_additional_private_data($this->_format_request, $additional_private_data);
        
        $sender = new Message_sender();
        return $sender->send($this->set_iso_message_request(ProcessingCode::deposit(), $id_member, $pin));
        
    }
    public function get_iso_message($response) {

        $response_keterangan = array(
            "response_code" => $this->get_iso_message_response($response, Field::getResponseCode()),
            "keterangan" => $this->get_iso_message_response($response, Field::getAdditionalData60())
        );
        $additional_private_data = $this->get_iso_message_response($response, Field::getAdditionalPrivateData48());
        return $response_keterangan + $this->get_additional_private_data($this->_format_response, $additional_private_data);
    }
}

?>
