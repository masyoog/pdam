<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of error
 *
 * @author yoog
 */
class error extends MY_Controller{
    function index(){
        $data = array();
        $this->template->load($data);
    }
}
