<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Datagridcolumn
 *
 * @author Yoga Mahendra
 */
class Datagridcolumn {

    private $_VISIBLE;
    private $_STYLE;
    private $_FIELD_DB;
    private $_FIELD_DB_ALIAS;
    private $_FIELD_TYPE;
    private $_FORM_ID;
    private $_FORM_TYPE;
    private $_REQUIRED;
    private $_EDITABLE;
    private $_SIZE;
    private $_TEXT_TYPE = "TEXT";
    private $_NUM_TYPE = "NUMBER";
    private $_CHAR_TYPE = "CHAR";
    private $_ENUM_TYPE = "ENUM";
    private $_DATE_TYPE = "DATE";
    private $_MONTHYEAR_TYPE = "MONTHYEAR";
    private $_EMAIL_TYPE = "EMAIL";
    private $_DATE_RANGE_TYPE = "DATERANGE";
    private $_FILE_TYPE = "FILE";
    private $_PASSWORD_TYPE = "PASSWORD";
    private $_PASSWORD_CONFIRM_TYPE = "PASSWORD";
    private $_CHECKBOX_TYPE = "CHECKBOX";
    private $_ENUM_DEFAULT_VALUE;
    private $_VALUE;
    private $_CLASS;
    private $_VALIDATION;
    private $_WITH_NULL_OPTION = TRUE;

    
    public function get_search_operan($dataType=''){
        $dataType = $dataType == "" ? $this->_FIELD_TYPE : $dataType;
        $out = "";
        switch($dataType){
            case $this->_NUM_TYPE :
                $out = array("=", "!=", "<", ">");
            break;
            case $this->_DATE_TYPE :
            case $this->_MONTHYEAR_TYPE:
                $out = array("=", "!=", "<", ">");
            break;
            case $this->_ENUM_TYPE :
                $out = array("=", "!=");
            break;
            default:
                $out = array("=", "!=", "SEPERTI", "TIDAK SEPERTI");
            break;
        }
        return $out;
    }

    public function get_WITH_NULL_OPTION() {
        return $this->_WITH_NULL_OPTION;
    }
    
    public function set_WITH_NULL_OPTION($_WITH_NULL_OPTION) {
        $this->_WITH_NULL_OPTION = $_WITH_NULL_OPTION;
    }
    
    public function get_VALIDATION() {
        return $this->_VALIDATION;
    }
    
    public function set_VALIDATION($_VALIDATION) {
        $this->_VALIDATION = $_VALIDATION;
    }
    
    public function get_CLASS() {
        return $this->_CLASS;
    }
    
    public function set_CLASS($_CLASS) {
        $this->_CLASS = $_CLASS;
    }
    
    public function get_CHECKBOX_TYPE() {
        return $this->_CHECKBOX_TYPE;
    }
    
    public function set_CHECKBOX_TYPE($_CHECKBOX_TYPE) {
        $this->_CHECKBOX_TYPE = $_CHECKBOX_TYPE;
    }
    
    public function set_EMAIL_TYPE($_VALUE) {
        $this->_EMAIL_TYPE = $_VALUE;        
    }
    
    public function get_EMAIL_TYPE(){
        return $this->_EMAIL_TYPE;
    }
    
    public function set_MONTHYEAR_TYPE($_VALUE) {
        $this->_MONTHYEAR_TYPE = $_VALUE;        
    }
    
    public function get_MONTHYEAR_TYPE(){
        return $this->_MONTHYEAR_TYPE;
    }
    
    public function get_DATE_TYPE() {
        return $this->_DATE_TYPE;
    }
    
    public function get_FILE_TYPE() {
        return $this->_FILE_TYPE;
    }
    
    public function get_NUM_TYPE() {
        return $this->_NUM_TYPE;
    }
    
    public function set_NUM_TYPE($_NUM_TYPE) {
        $this->_NUM_TYPE = $_NUM_TYPE;
    }
    
    public function get_PASSWORD_TYPE() {
        return $this->_PASSWORD_TYPE;
    }
    
    public function set_PASSWORD_TYPE($_PASSWORD_TYPE) {
        $this->_PASSWORD_TYPE = $_PASSWORD_TYPE;
    }
    
    public function get_PASSWORD_CONFIRM_TYPE() {
        return $this->_PASSWORD_CONFIRM_TYPE;
    }
    
    public function set_PASSWORD_CONFIRM_TYPE($_PASSWORD_CONFIRM_TYPE) {
        $this->_PASSWORD_CONFIRM_TYPE = $_PASSWORD_CONFIRM_TYPE;
    }
    

    public function set_DATE_TYPE($_DATE_TYPE) {
        $this->_DATE_TYPE = $_DATE_TYPE;
    }

    public function get_FORM_ID() {
        return $this->_FORM_ID;
    }

    public function set_FORM_ID($_FORM_ID) {
        $this->_FORM_ID = $_FORM_ID;
    }

    public function get_ENUM_TYPE() {
        return $this->_ENUM_TYPE;
    }

    public function get_ENUM_DEFAULT_VALUE() {
        return $this->_ENUM_DEFAULT_VALUE;
    }

    public function set_ENUM_TYPE($_ENUM_TYPE) {
        $this->_ENUM_TYPE = $_ENUM_TYPE;
    }

    public function set_ENUM_DEFAULT_VALUE($_ENUM_DEFAULT_VALUE) {
        $this->_ENUM_DEFAULT_VALUE = $_ENUM_DEFAULT_VALUE;
    }

    function __construct() {
        $this->_VISIBLE = TRUE;
        $this->_STYLE = "";
        $this->_FIELD_DB = "";
        $this->_EDITABLE = TRUE;
        $this->_REQUIRED = FALSE;
        $this->_FIELD_TYPE = $this->_CHAR_TYPE;
        $this->_FORM_TYPE = "text";
//        $this->_SIZE = 1;
    }

    public function get_TEXT_TYPE() {
        return $this->_TEXT_TYPE;
    }

    public function get_CHAR_TYPE() {
        return $this->_CHAR_TYPE;
    }

    public function get_VISIBLE() {
        return $this->_VISIBLE;
    }

    public function get_STYLE() {
        return $this->_STYLE;
    }

    public function get_FIELD_DB_ALIAS() {
        return $this->_FIELD_DB_ALIAS;
    }
    
    public function get_FIELD_DB() {
        return $this->_FIELD_DB;
    }

    public function get_FIELD_TYPE() {
        return $this->_FIELD_TYPE;
    }

    public function get_REQUIRED() {
        return $this->_REQUIRED;
    }

    public function get_EDITABLE() {
        return $this->_EDITABLE;
    }

    public function get_SIZE() {
        return $this->_SIZE;
    }

    public function set_VISIBLE($_VISIBLE) {
        $this->_VISIBLE = $_VISIBLE;
    }

    public function set_STYLE($_STYLE) {
        $this->_STYLE = $_STYLE;
    }

    public function set_FIELD_DB($_FIELD_DB) {
        $this->_FIELD_DB = $_FIELD_DB;
    }
    
    public function set_FIELD_DB_ALIAS($_FIELD_DB_ALIAS) {
        $this->_FIELD_DB_ALIAS = $_FIELD_DB_ALIAS;
    }

    public function set_FIELD_TYPE($_FIELD_TYPE) {
        $this->_FIELD_TYPE = $_FIELD_TYPE;
    }

    public function set_REQUIRED($_REQUIRED) {
        $this->_REQUIRED = $_REQUIRED;
    }

    public function set_EDITABLE($_EDITABLE) {
        $this->_EDITABLE = $_EDITABLE;
    }

    public function set_SIZE($_SIZE) {
        $this->_SIZE = $_SIZE;
    }
    
    public function set_VALUE($_VALUE) {
        $this->_VALUE = $_VALUE;        
    }
    
    public function get_VALUE(){
        return $this->_VALUE;
    }
    

}
