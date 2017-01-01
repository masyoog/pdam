<?php

class ProcessingCode {

    public static function daftar() {
        return "000004";
    }
    public static function bonus(){
        return "000010";
    }
    public static function deposit(){
        return "000023";
    }
    

    
    public static function getCodeDesc($code){
        $ret=null;
        if($code==daftar()){
            $ret="Pendaftaran member";
        }else if($code==bonus()){
            $ret="Pencairan bonus ke saldo";
        }else if($code==deposit()){
            $ret="Deposit";
        }

        return ret;
    }
}
?>