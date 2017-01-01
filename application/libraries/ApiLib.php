<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiLib
 *
 * @author Yoga Mahendra <masyoog@yahoo.com>
 */
class ApiLib {

    private $ci;

    function __construct() {
        $this->ci = & get_instance();
    }

    function authenticate($user, $pass) {
        $is_authenticated = false;
        $is_userexist = false;
        $area = array();

        $msg = "";
        $nama = "";
        $userName = $user;
        $date = date("Y-m-d H:i:s");

        if ($user && $pass) {
            $username = $this->ci->base_model->db->escape_str($user);
            $password = $this->ci->base_model->db->escape_str($pass);

            $result = $this->ci->base_model->list_single_data("*, crypt('" . $pass . "', userpassword) as passdb ", "petugas", "", array('username' => $userName), FALSE);

            if ($result != '') {

                $is_userexist = true;
                $passdb = trim($result->passdb);

                if ($result->userpassword == $passdb) {
                    if ($result->status == "1") {
                        $is_authenticated = true;
                        $nama = $result->nama;
                        
                        $area = $this->ci->base_model->list_data("id_area", "area_petugas", "", array("id_petugas"=>$result->id));
                        $area = array_column($area, 'id_area');
                    } else {
                        $msg = "User ID anda saat ini tidak aktif !";
                    }
                } else {
                    $msg = "User ID atau password anda tidak cocok !";
                }
            }

            if (!$is_userexist) {
                $msg = "User ID Belum Terdaftar!";
            }
        } else {
            $msg = "User ID atau password anda tidak cocok !";
        }
        
        return array(
            'status' => $is_authenticated,
            'msg' => $msg,
            'user' => $userName,
            'nama' => $nama,
            'tgl' => $date,
            'area' => $area
        );
    }

}
