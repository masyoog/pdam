<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Datagrid_config
 *
 * @author Yoga Mahendra
 */
class Datagridconfig {

    private $_COLS; // columns object of rs
    private $_KEYS; // primary keys of tables / rs
    private $_PRIMARY_TBL; // primary tables / rs
    private $_JOIN_TBL; // join tables / rs
    private $_ORDER_TBL;
    private $_WHR_TBL;
    private $_QRY; // SQL Query
    private $_ITEM_PER_PAGE = 10;
    private $_GRID_BUTTON;
    private $_GRID_GROUP_BUTTON = "";
    private $_COMMAND_BUTTON;
    private $_MENU_ID;
    
    function __construct() {
        $this->_COLS = array();
        $this->_GRID_BUTTON = array();
    }
    
    function set_MENU_ID($_MENU_ID){
        $this->_MENU_ID = $_MENU_ID;
    }
    
    function get_MENU_ID(){
        return $this->_MENU_ID;
    }

    function add_COMMAND_BUTTON($name, $content) {
        $this->_COMMAND_BUTTON[$name] = $content;
    }
    
    function get_COMMAND_BUTTON($name=""){
        if ( "" != $name){
            return _get_raw_item($this->_COMMAND_BUTTON, $name);
        }
        return $this->_COMMAND_BUTTON;
    }

    function set_grid_group_button($_GRID_GROUP_BUTTON){
        $this->_GRID_GROUP_BUTTON = $_GRID_GROUP_BUTTON;
    }

    function get_grid_group_button(){
        return $this->_GRID_GROUP_BUTTON;
    }

    function clear_grid_button(){
        $this->_GRID_BUTTON = "";
    }

    function add_grid_button($name, $content) {
        $this->_GRID_BUTTON[$name] = $content;
    }
    
    function get_grid_button($name=""){
        if ( "" != $name){
            return _get_raw_item($this->_GRID_BUTTON, $name);
        }
        return $this->_GRID_BUTTON;
    }   

    public function get_ORDER_TBL() {
        return $this->_ORDER_TBL;
    }

    public function get_WHR_TBL() {
        return $this->_WHR_TBL;
    }

    public function set_ORDER_TBL($_ORDER_TBL) {
        $this->_ORDER_TBL = $_ORDER_TBL;
    }

    public function set_WHR_TBL($_WHR_TBL) {
        $this->_WHR_TBL = $_WHR_TBL;
    }

    public function get_ITEM_PER_PAGE() {
        return $this->_ITEM_PER_PAGE;
    }

    public function set_ITEM_PER_PAGE($_ITEM_PER_PAGE) {
        $this->_ITEM_PER_PAGE = $_ITEM_PER_PAGE;
    }

    public function get_KEYS() {
        return $this->_KEYS;
    }

    public function get_QRY() {
        return $this->_QRY;
    }

    public function set_QRY($_QRY) {
        $this->_QRY = $_QRY;
    }

    public function get_PRIMARY_TBL() {
        return $this->_PRIMARY_TBL;
    }

    public function get_JOIN_TBL() {
        return $this->_JOIN_TBL;
    }

    public function set_PRIMARY_TBL($_PRIMARY_TBL) {
        $this->_PRIMARY_TBL = $_PRIMARY_TBL;
    }

    public function set_JOIN_TBL($_JOIN_TBL) {
        $this->_JOIN_TBL = $_JOIN_TBL;
    }

    /**
     * 
     * @param type $name
     * @param type $property
     */
    function add_column($name, $property) {
        $this->_COLS[$name] = $property;
    }

    /**
     * fn get_column get column object by name, if name not defined return all column object
     * @param type $name
     */
    function get_column($name = "") {
        if ("" != $name) {
            return _get_raw_item($this->_COLS, $name);
        }
        return $this->_COLS;
    }

    /**
     * 
     * @param type $fieldKeys primarykey of table / rs
     */
    function set_KEYS($fieldKeys) {
        $this->_KEYS = $fieldKeys;
    }

    /**
     * fn add_btn_cmd to add control button in grid list
     * @param type $name
     * @param type $property
     */
    function add_btn_cmd($name, $property = "") {
        
    }

}
