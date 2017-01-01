<?php 

class Message_sender
{
    var $_target_ip = "";
    var $_target_port = 80;
    var $_target_path = "";
    var $_time_out = 60;
    var $_connect_time_out = 10;
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
    
    function send($method, $params){
        
        $result = '';
        $data = '';
        $errno = 0;
        $error = '';
        
        // parse to XML-RPC
        $message = xmlrpc_encode_request($method, $params);
//        _debug_var($message);
        
        $url = "http://". $this->_target_ip. $this->_target_path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_PORT, $this->_target_port); 
        curl_setopt($ch, CURLOPT_POST, true);        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_connect_time_out);

        //execute post
        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        if ($errno > 0)
            $result = 'null';
        else
            $result = $data;
        //close connection
        curl_close($ch);
        
        return $result;
    }
    
}
?>
