<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of home
 *
 * @author Yoga Mahendra
 */
class Home extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        $out = "";
        $data = array();
        $dg = new Datagrid();
        $dg->set_config(null);
        if (!$dg->authorize()) {
            
            $out .= $dg->get_header();
            $out .= '<section class="content" >';
            $out .= '<div class="row">';
            $out .= '<div class="col-xs-12">';
            $out .= '<p>';
            $out .= $dg->get_validation_error("Anda tidak punya hak akses untuk melihat halaman ini.");
            $out .= '</p>';
            $out .= '</div>';
            $out .= '</div>';
            $out .= '</section>';
            $out .= '</section>';
            
            
            $data["pages"] = $out;
            $this->template->load($data);
        } else {
            $this->template->load($data, 'home');
        }
    }

}
