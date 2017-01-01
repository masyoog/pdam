<?php 
class Message_sender
{
    var $_target_ip = "";
    var $_target_port = 80;
    var $_target_path = "";
    var $_time_out = 60;
    var $_connect_time_out = 10000;
    var $_ci = "";
    var $_latest_ip_used = "";
    var $_latest_port_used = "";
    
    function __construct() {
        $this->_ci =& get_instance();
        
        $this->_target_ip = $this->_ci->config->item("MY_SERVER_IP");
        $this->_target_port = $this->_ci->config->item("MY_SERVER_PORT");
        $this->_target_path = $this->_ci->config->item("MY_SERVER_PATH");
        
        $this->_timeout = $this->_ci->config->item("MY_DEFAULT_REQUEST_TIMEOUT");
        $this->_connect_time_out = $this->_ci->config->item("MY_DEFAULT_CONNECT_TIMEOUT");
        
    }   
        
//    function send($msg, $is_post=true)
//    {
//        $message = '';
//        $result = '';
//        $data = '';
//        $errno = 0;
//        $error = '';
//
//        $url = "http://". $this->_target_ip. $this->_target_path;
//
//        $ch = curl_init();
////        echo strlen($msg) . "<br>" ;
//        $message = $this->get_message_length($msg);
////        die($message);
//        //set the url, number of POST vars, POST data
//        if($is_post){
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
//        } else {
//            curl_setopt($ch, CURLOPT_URL, $url."?". $message);
//        }
//        
//        curl_setopt($ch, CURLOPT_PORT, $this->_target_port); 
//        curl_setopt($ch, CURLOPT_POST, $is_post);        
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connect_time_out);
//
//        //execute post
//        $data = curl_exec($ch);
//        $errno = curl_errno($ch);
//        $error = curl_error($ch);
//
//        if ($errno > 0)
//            $result = 'null';
//        else
//            $result = $data;
//        //close connection
//        curl_close($ch);
//
//        return $result;  
//    }
    
    function send($msg){
        $reply = '';
//        $msg = '26002030004080811004000004000000003604201401072225566003070000000ps001           225081234567890   nama daftar                                       jl indah tak berujung                                                                               kota                     propinsi            61232 100      202CB962AC59075B964B07152D234B70248Telepon Outlet Anda      ps001     NAMA OUTLET ANDA                                  000000000000ALAMAT OUTLET ANDA                                                                                                                                     ';
        $message = $this->get_message_length($msg);
//        die($message);
        $fp = fsockopen($this->_target_ip, $this->_target_port); 
            
        if(!$fp){
                $res = "Error: $errno $errdesc\n";
        }else{
            fputs($fp, $message);
            while(!feof($fp)){
                $reply .= fgets( $fp, 12000);
            }
            fclose($fp);
            $res = $reply; 
        }
        return $res;
    }
    
    function get_message_length($message){
        $length = 0;
        $dec = strlen($message);
        $hex = dechex($dec);
        $hexa = str_pad($hex, '4','0', STR_PAD_LEFT);
        
        for($i=0;$i<strlen($hexa);$i+=2){
            $ascii = substr($hexa,$i,2);

            $length .= chr($ascii);
        }
        $result = $length . $message;
        return $result;
    }
}
?>
