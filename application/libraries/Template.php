<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Template {

    var $template_data = array();
    private $CI;
    private $template_dir = "";
    private $template_file = "";

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('base_model');

        $this->template_dir = "template";
        $this->template_file = $this->template_dir . "/template";
//        $main_menu = $this->CI->base_model->list_data('*', 'sys_menu', '', array('id_induk' => '0', 'status'=>1), array("urutan ASC"));
        $main_menu = $this->CI->session->userdata(USER_AUTH . "main_menu");

        $this->set('main_menu', $main_menu);

        $rs_child_menu = $this->CI->session->userdata(USER_AUTH . "child_menu");
        $child_menu = array();
        if (is_array($rs_child_menu)) {
            foreach ($rs_child_menu as $row) {
                $child_menu[$row->id_induk][] = $row;
            }
        }

        $this->set('child_menu', $child_menu);
    }

    function parse_child_menu($menus, $child_menus, $layer = 1) {
        if (count($menus) > 0 && is_array($menus)) {
            echo '<ul class="treeview-menu">';
            foreach ($menus as $row) {
                if ($row->status != 1) {
                    continue;
                }
                $active_menu = strpos($row->uri, str_replace(base_url(), "", site_url($this->CI->router->fetch_directory() . $this->CI->router->fetch_class())));
                $active_menu = $active_menu === 0 ? "active" : "";
                if (is_array(_get_raw_item($child_menus, $row->id))) {

                    $style = $layer == "1" ? "fa-angle-double-right" : "fa-th-large";
                    $style2 = $layer == "1" ? "text-blue" : "text-orange";
//                    <i class="fa fa-angle-double-right"></i>
                    echo '<li class="treeview ' . $active_menu . '"><a href="#" class="' . $style2 . '"><i class="fa ' . $style . '"></i>' . $row->menu . '</a>';
                    $this->parse_child_menu(_get_raw_item($child_menus, $row->id), $child_menus, 2);
                    echo '</li>';
                } else {
                    echo '<li class="' . $active_menu . '"><a href="' . base_url($row->uri) . '"><i class="fa fa-angle-double-right"></i>' . $row->menu . '</a></li>';
                }
            }
            echo '</ul>';
        }
    }

    function set($name, $value) {
        $this->template_data[$name] = $value;
    }

    function load($view_data = array(), $view = '', $template_file = '', $return = FALSE) {
        if ("" == $view) {
            $dir = str_replace('/', '', $this->CI->router->fetch_directory());
            $path = str_replace('/', '', $this->CI->router->fetch_class());
            $file = str_replace('/', '', $this->CI->router->fetch_method());
            $view_file = $dir . "/" . $path . "/" . $file;
            $view = "" == $view ? $view_file : "/" . $view;
        }

        $server_dir = str_replace("index.php", "", $this->CI->input->server("SCRIPT_FILENAME"));

        if (file_exists($server_dir . APPPATH . "views/" . $view . ".php")) {
            $this->set('pages', $this->CI->load->view($view, $view_data, TRUE));
        } else {
            $this->template_data = $this->template_data + $view_data;
        }


        if ('' != $template_file && file_exists(APPPATH . "views/" . $this->template_dir . "/" . $template_file . ".php")) {
            return $this->CI->load->view($this->template_dir . "/" . $template_file, $this->template_data, $return);
        } else {
            return $this->CI->load->view($this->template_file, $this->template_data, $return);
        }
    }

}

/* End of file Template.php */
/* Location: ./system/application/libraries/Template.php */