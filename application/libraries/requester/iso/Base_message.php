<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

require_once "include/ISOMsg.class.php";
require_once "include/MTI.class.php";
require_once "include/Field.class.php";
require_once "include/MerchantCode.class.php";
require_once "include/ProcessingCode.class.php";

class Base_message
{  
    var $_base_request_model 
        = array(
                "1" => "",
                "3" => "",
                "12" => "",
                "26" => "",
                "33" => "",
                "41" => "",
                "48" => array(),
                "52" => "",
                "62" => array()
        );
    
    var $_base_response_model 
        = array(
                "1" => "",
                "3" => "",
                "12" => "",
                "26" => "",
                "33" => "",
                "39" => "",
                "41" => "",
                "48" => array(),
                "52" => "",
                "60" => array(),
                "61" => array(),
                "62" => array()
        );
    
    var $_additional_private_data_message = "";
    
    var $_additional_private_data_bonus 
        = array(
                "id_member",
                "nama",
                "saldo",
                "alamat",
                "kota",
                "propinsi",
                "kode_pos",
                "id_upline",
                "tgl_registrasi",
                "bonus"
        );
    var $_additional_private_data_daftar 
        = array(
                "nomor_hp",
                "nama",
                "alamat",
                "kota",
                "propinsi",
                "kode_pos",
                "up_harga",
                "prefix_id",
                "id_member",
                "tgl_registrasi",
                "id_upline",
                "nama_upline",
                "saldo_upline",
                "alamat_upline",
                "kota_upline",
                "propinsi_upline",
                "kode_pos_upline",
                "id_upline",
                "tgl_registrasi_upline"
        );
    var $_additional_private_data_deposit 
        = array(
                "user_input",
                "nominal",
                "id_tiket",
                "id_bank",
                "noresi",
                "id_member",
                "nama",
                "saldo",
                "alamat",
                "kota",
                "propinsi",
                "kode_pos",
                "id_upline",
                "tgl_registrasi"
        );
    var $ci;
    function __construct() {
        $this->ci =& get_instance();
    }
    
    function set_iso_item($format, $key, $value="")
    {
        if($key == Field::getAdditionalPrivateData48()){
            $this->set_additional_private_data($format, $value);
        }else{
            $this->_base_request_model[$key] = $value;
        }
    }
    
    function set_additional_private_data($format, $arr_data){
        $data = "";
        $idx = 0;
        foreach($format as $key => $value){
            $data .= $this->pad($arr_data[$idx], $key, $value);
            $idx++;
        }
        $this->_additional_private_data_message = $data;
    }
    
    function set_iso_message_request($processing_code, $id_member, $pin)
    {
        $iso = new ISOMsg();
	$iso->addMTI(MTI::getMTITransReq());
        $iso->addData(Field::getProcessingCode(), $processing_code);
        $iso->addData(Field::getTimeLocalTrans(), date('YmdHis'));
        $iso->addData(Field::getMerchantCategoryCode(), MerchantCode::pms());
        $iso->addData(Field::getPartnerCentralId(), $this->ci->config->item("MY_CENTRAL_ID"));
        $iso->addData(Field::getTerminalId(), $id_member);
        $iso->addData(Field::getAdditionalPrivateData48(), $this->_additional_private_data_message);
        $iso->addData(Field::getPinData(), md5($pin));
//        $iso->addData(Field::getAdditionalData62(), "");
        
        return $iso->getISO();
    }
    
    function get_iso_message_response($response, $key)
    {
        $response = "";
        $iso = new ISOMsg();
        $iso->addISO($response);
        if (!$iso->validateISO()) {
            $response = "FORMAT RESPONSE SALAH";
        }else{
            $data = $iso->getData();
            $response = _get_raw_item($data, $key);
        }
        return $response;
    }
    
    function get_additional_private_data($format, $additional_private_data){
        $idx = array();
	$data = array();
        
	foreach($format as $key => $value){
            $idx[] = $key;
	}
        
	for($i=0;$i<count($idx);$i++){
            $posisi=$this->getPosisi($idx, $i);
            $data[$this->_additional_private_data_bonus[$i]]=substr($additional_private_data,$posisi,$idx[$i]);
	}
        
        return $data;
    }
    
    function getPosisi($format, $index){
        $pos=0;
        for($i=0;$i<$index;$i++){
            $pos=$pos+$format[$i];
        }
        return $pos;
    }
    
    function pad($str, $length, $position){
            $ret=$str;
            $char = '';
            $justify = "";
            if($position=="right"){
                $char = ' ';
                $justify = "-";
            }else if($position=="left"){
                $char = '0';
                $justify = "";
            }
            $ret=sprintf("%".$justify.$char.$length."s",$str);

            return $ret;
    }

}

