<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Datagrid
 *
 * @author Yoga Mahendra
 */
class Datagrid {

    private $CI;
    private $_CFG;
    private $_PAGE_TITLE = "";
    private $_RIGHT_HEADER = "";
//    private $_SEARCH_FORM;
//    private $_BTN_COMMAND;
//    private $_BTN_GRID_COMMAND;
    private $_ADDITIONAL_SCRIPT = "";
    private $_START_ITEM;
    private $_END_ITEM;
    private $_QUERY_STRING;
    private $_LIST_ACCESS;

    function __construct() {
        $this->CI = & get_instance();
        $this->_QUERY_STRING = $this->get_query_string();
    }

    function authorize($type = "baca") {

        return _get_raw_object($this->_LIST_ACCESS, $type) == "1" ? TRUE : FALSE;
    }

    public function get_ADDITIONAL_SCRIPT() {
        return $this->_ADDITIONAL_SCRIPT;
    }

    public function set_ADDITIONAL_SCRIPT($_ADDITIONAL_SCRIPT, $append = TRUE) {
        if (TRUE == $append) {
            $this->_ADDITIONAL_SCRIPT .= $_ADDITIONAL_SCRIPT;
        } else {
            $this->_ADDITIONAL_SCRIPT = $_ADDITIONAL_SCRIPT;
        }
    }

    function set_config($cfg) {
        $this->_CFG = & $cfg;
        $this->_LIST_ACCESS = $this->_list_access();
    }

    private function get_order_form_query_string() {
        $out = "";

        $sort = $this->get_query_string("sort");
        $order = $this->get_query_string("order");
        if ($sort != "") {
            $colObj = _get_raw_item($this->_CFG->get_column(), $sort);
            $i = 1;
            $col = is_object($colObj) ? $colObj->get_FIELD_DB() : "";

            if ("" != $col) {
                $out = array($col . " " . $order);
            }
        }
        return $out;
    }

    private function _list_access() {

        $id_menu = $this->_current_url();
        $accessTemp = $this->CI->session->userdata(USER_AUTH . "child_menu");
        $access = json_decode(json_encode($accessTemp), TRUE);

        $access = $access == "" ? array() : $access;

        $key = array_search($id_menu, array_column($access, 'uri'));

        if ($key === FALSE) {
            $accessTemp = $this->CI->session->userdata(USER_AUTH . "main_menu");
            $access = json_decode(json_encode($accessTemp), TRUE);
            $access = $access == "" ? array() : $access;
            $key = array_search($id_menu, array_column($access, 'uri'));

            if ($key === FALSE) {
                $id_menu = _replace_after($id_menu, "/");
                $key = array_search($id_menu, array_column($access, 'uri'));
            }
        }

        if ($key !== FALSE) {
            $access = _get_raw_item($accessTemp, $key);
        }
        return $access;
    }

    private function get_where_form_search() {
        $params = $this->get_query_string();
        $fields = _get_raw_item($params, 'srcKolom');
        $values = _get_raw_item($params, 'srcCari');
        $operans = _get_raw_item($params, 'srcOperan');

        $out = array();

        if ("" != $this->_CFG->get_WHR_TBL()) {
            array_push($out, $this->_CFG->get_WHR_TBL());
        }

        $column = $this->_CFG->get_column();

        $i = 0;

        if ($fields != "") {
            $cfg = "";
            $outTemp = "";
            foreach ($fields as $field) {
                if ($field == "") {
                    continue;
                }
                $cfg = _get_raw_item($column, $field);
                $value = trim(_get_raw_item($values, $i));
                $operan = trim(_get_raw_item($operans, $i));

                if ($field == "" || $value == "" || $operan == "") {
                    continue;
                }

                $fieldDB = $cfg->get_FIELD_DB();
                $fieldType = $cfg->get_FIELD_TYPE();
                $class = $cfg->get_CLASS();
                $value = str_replace("'", "''", $value);

                if ($value != "") {
                    if ($fieldType == 'DATE') {
                        if ($class == "elm-date-range") {
                            $value = explode("-", $value);
                            $value1 = str_replace("/", "-", _get_raw_item($value, 0));
                            $value1 = date("Y-m-d", strtotime($value1));
                            $value2 = str_replace("/", "-", _get_raw_item($value, 1));
                            $value2 = date("Y-m-d", strtotime($value2));
                            $outTemp = array($fieldDB . " >= " => $value1, $fieldDB . " <= " => $value2);
                        } else {
                            $value = str_replace("/", "-", $value);
                            $value = date("Y-m-d", strtotime($value));
                            $outTemp = array($fieldDB . " " . $operan => $value);
                        }
                    } else if ($fieldType == 'NUMBER' || $fieldType == 'ENUM') {
                        $outTemp = array($fieldDB . " " . $operan => intval($value));
                    } else {
                        if ($operan == 'SEPERTI' OR $operan == 'TIDAK SEPERTI') {
                            $operan = $operan == 'SEPERTI' ? "~*" : "!~*";
                            $outTemp = array($fieldDB . " " . $operan => '.*' . strtolower($value) . '.*');
                        } else {
                            $outTemp = array($fieldDB . " " . $operan => '' . strtolower($value) . '');
                        }
                    }

                    array_push($out, $outTemp);
                }
                $i++;
            }
        }

//        if ($column != "") {
//            $outTemp = "";
//            if ($field != "") {
//                foreach ($column as $cols => $property) {
//                    $fieldDB = $property->get_FIELD_DB();
//                    $fieldType = $property->get_FIELD_TYPE();
//                    $class = $property->get_CLASS();
//                    $value = str_replace("'", "''", $value);
//
//                    if (ucfirst(str_replace(" ", "", $cols)) == $field) {
//                        if ($fieldType == 'DATE') {
//                            if ($class == "elm-date-range") {
//                                $value = explode("-", $value);
//                                $value1 = str_replace("/", "-", _get_raw_item($value, 0));
//                                $value1 = date("Y-m-d", strtotime($value1));
//                                $value2 = str_replace("/", "-", _get_raw_item($value, 1));
//                                $value2 = date("Y-m-d", strtotime($value2));
//                                $outTemp = array($fieldDB . " >= " => $value1, $fieldDB . " <= " => $value2);
//                            } else {
//                                $value = str_replace("/", "-", $value);
//                                $value = date("Y-m-d", strtotime($value));
//                                $outTemp = array($fieldDB => $value);
//                            }
//                        } else if ($fieldType == 'NUMBER' || $fieldType == 'ENUM') {
//                            $outTemp = array($fieldDB => intval($value));
//                        } else {
//                            $outTemp = array($fieldDB . ' ilike ' => '%' . strtolower($value) . '%');
//                        }
//
//                        array_push($out, $outTemp);
//                    }
//                }
//            }
//        }
//        _debug_var($out);
        return $out;
    }

    private function get_query_string($param = "") {
        $out = "";
        if (is_array($_GET)) {
            if ("" == $param) {
                foreach ($_GET as $param => $value) {
                    $out[$param] = $this->CI->input->get_post($param);
                }
            } else {
                $out = $this->CI->input->get_post($param);
            }
        }
        return $out;
    }

    private function add_query_string($key, $value) {

        $this->_QUERY_STRING[$key] = $this->CI->security->xss_clean($value);
    }

    private function remove_query_string($key) {
        if ($this->get_query_string($key) != "")
            unset($this->_QUERY_STRING[$key]);
    }

    private function build_query_string($array) {
        $out = "";
        if (is_array($array))
            $out = http_build_query($array);
        return $out;
    }

    private function _generate_QRY($is_count_query = FALSE) {
        $SQL = "SELECT ";
        $fld = $this->_CFG->get_KEYS();
        if (!$is_count_query) {
            $columns = $this->_CFG->get_column();
            if (count($columns) > 0) {

                foreach ($columns as $colsName => $colsProperty) {
                    if ("" != $colsProperty->get_FIELD_DB()) {
                        $fld .= $fld == "" ? "" : ", ";
                        $fld .= $colsProperty->get_FIELD_DB();
                    }
                }
                $SQL .= $fld;
            }
        } else {
            $SQL .= " COUNT (" . $this->_CFG->get_KEYS() . ") AS JUMLAH ";
        }

        $SQL .= " FROM " . $this->_CFG->get_PRIMARY_TBL();

        return $SQL;
    }

    function get_resultset_only() {
        $out = "";
        $whr = $this->get_where_form_search();
        $page = $this->_get_current_page();
        $itemPerPage = null != $this->_CFG->get_ITEM_PER_PAGE() ? intval($this->_CFG->get_ITEM_PER_PAGE()) : $this->CI->config->item("ITEM_PER_PAGE");
        $startItem = $page * $itemPerPage - $itemPerPage + 1;
        $endItem = $startItem + $itemPerPage - 1;

        $this->_START_ITEM = $startItem;
        $this->_END_ITEM = $endItem;

        $columns = $this->_CFG->get_column();
        if (count($columns) > 0) {

            foreach ($columns as $colsName => $colsProperty) {
                $fld .= $fld == "" ? "" : ", ";
                $fld .= $colsProperty->get_FIELD_DB();
            }
            $SQL .= $fld;
        }
//        $SQL = $this->_generate_QRY();
//
//        $SQL = $this->_limit($SQL, $whr, $this->_CFG->get_ORDER_TBL(), $startItem, $endItem);

        $rs = $this->CI->base_model->list_data($columns, $this->_CFG->get_PRIMARY_TBL(), "", $whr, $order = "", $limit = "", $offset = "", $distinct = "", $escapes = TRUE);
        if ("" != $rs) {
            $out = $rs;
        }

        return $out;
    }

    /**
     * fn render
     * @param type $rs
     * @return string
     */
    function render($isPopUp = FALSE) {
        $out = $this->get_header();
        $out .= '<!-- Main content -->';
        $out .= '<section class="content">';
        if (!$this->authorize()) {

            $out .= '<section class="content" >';
            $out .= '<div class="row">';
            $out .= '<div class="col-xs-12">';
            $out .= '<p>';
            $out .= $this->get_validation_error("Anda tidak punya hak akses untuk melihat halaman ini.");
            $out .= '</p>';
            $out .= '</div>';
            $out .= '</div>';
            $out .= '</section>';
            $out .= '</section>';
            return $out;
        }

        $order = $this->get_order_form_query_string();
        $whr = $this->get_where_form_search();
//        _debug_var($whr);
        $page = $this->_get_current_page();
        $itemPerPage = null != $this->_CFG->get_ITEM_PER_PAGE() ? intval($this->_CFG->get_ITEM_PER_PAGE()) : $this->CI->config->item("ITEM_PER_PAGE");
        $offset = ($page * $itemPerPage) - $itemPerPage;

        $columns = $this->_CFG->get_column();
        $fld = $this->_CFG->get_KEYS();
        if (count($columns) > 0) {
            foreach ($columns as $colsName => $colsProperty) {
                
                if ("" != $colsProperty->get_FIELD_DB()) {
                    $fld .= $fld == "" ? "" : ", ";
                    $fld .= $colsProperty->get_FIELD_DB() . " " . $colsProperty->get_FIELD_DB_ALIAS();
                }
            }
        }


        if (is_array($order)) {
            $this->_CFG->set_ORDER_TBL($order);
        }

        $rs = $this->CI->base_model->list_data($fld, $this->_CFG->get_PRIMARY_TBL(), $this->_CFG->get_JOIN_TBL(), $whr, $this->_CFG->get_ORDER_TBL(), $itemPerPage, $offset);
        $this->CI->session->set_userdata(array("lastQuery" => $this->CI->base_model->db->last_query()));
//        echo $this->CI->base_model->db->last_query();

        $out .= $this->get_filter_form();
        $out .= $this->get_data_grid($rs);

        $out .= '</section>';

        return $out;
    }

    function render_form($mode, $key = "", $errorMsg = "", $isPopUp = FALSE, $addContent="") {

        $out = "";
        $title = $mode == "add" ? "Tambah Data" : "Ubah Data";

        $key = _sanitaze_input($key);
        $SQL = $this->_generate_QRY();

        $SQL .= " WHERE " . $this->_CFG->get_KEYS() . "='" . $this->CI->base_model->db->escape_str($key) . "'";

        $rs = "";

//        if ($mode == "edit" && $key != "" ) {
        if ($key != "") {
            $rs = $this->CI->base_model->execute($SQL);
            $rs = _get_raw_item($rs, '0');
        }
//        _debug_var($this->CI->base_model->db->last_query());
//        if (!$isPopUp) {
        $out .= $this->get_header(FALSE);
//        }
        $out .= '<!-- Main content -->';
        $out .= '<section class="content">';

        if (!$this->authorize('tambah') && !$this->authorize('ubah')) {

            $out .= '<section class="content" >';
            $out .= '<div class="row">';
            $out .= '<div class="col-xs-12">';
            $out .= '<p>';
            $out .= $this->get_validation_error("Anda tidak punya hak akses untuk melihat halaman ini.");
            $out .= '</p>';
            $out .= '</div>';
            $out .= '</div>';
            $out .= '</section>';
            $out .= '</section>';
            return $out;
        }

        $out .= '<div class="row">';
        $out .= '<div class="col-xs-12">';
        $out .= '<div class="box box-primary">';
        $out .= '<div class="box-header">';
        $out .= '<h4 class="box-title text-orange">';
        $out .= $title;
        $out .= '</h4>';
        $out .= '</div><!-- /.box-header -->';
        $out .= '<div class="box-body">';
        $out .= $errorMsg;
        $attributes = array('role' => 'form', 'id' => 'formBBLK', 'class' => 'formEvent');

        $url = base_url($this->CI->router->fetch_directory() . $this->CI->router->fetch_class() . "/save/" . $key);

        $out .= form_open_multipart(current_url() . _build_query_string($this->get_query_string()), $attributes);

        $out .= $this->_render_form_element($rs);
        $out .= $addContent;
        $out .= '</div><!-- /.box-body -->';
        $out .= '<div class="box-footer">';
//        $out .= '<button type="submit" class="btn btn-success" name="save" value="simpan">Simpan</button>&nbsp;';
//        $out .= '<button type="button" class="btn btn-warning" name="cancel" onClick="window.history.back(-1)">Batal</button>';
        $out.= $this->_command_form_button();
        $out .= '</div>';
        $out .= '</form>';
        $out .= '</div>';
        $out .= '</div>';
        $out .= '</section>';

        return $out;
    }

    function get_validation_error($msg = "") {
        if (trim($msg) != "") {
            $msg = str_replace("contain a number greater than 0", "selected", $msg);
            $msg = '<div id="divErrorMsg" class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <b>Alert!</b> ' . $msg . '
                        </div>';
        }
        return $msg;
    }

    function get_validation_rules() {
        $out = array();
        $column = $this->_CFG->get_column();
        if (count($column) > 0) {
            foreach ($column as $fieldName => $property) {
                $tempRules = array("trim", "xss_clean");

                if (intval($property->get_SIZE()) > 0) {
                    array_push($tempRules, "max_length[" . intval($property->get_SIZE()) . "]");
                }

                if ($property->get_REQUIRED()) {
                    if ( $property->get_FIELD_TYPE() == $property->get_ENUM_TYPE() ){
                        array_push($tempRules, "greater_than[0]");
                    }
                    array_push($tempRules, "required");
                }

                if ($property->get_VALIDATION() != "") {
                    array_push($tempRules, $property->get_VALIDATION());
                }

                if ($property->get_FIELD_TYPE() == $property->get_NUM_TYPE()) {
                    array_push($tempRules, "regex_match ^\d+(?:\.\d{3})*$");
                }

                if ($property->get_FIELD_TYPE() == $property->get_PASSWORD_CONFIRM_TYPE()) {
                    array_push($tempRules, "matches[" . str_replace("confirm", "", $property->get_FORM_ID()) . "]");
                }

                $rules = array(
                    'field' => $property->get_FORM_ID(),
                    'label' => $fieldName,
                    'rules' => implode("|", $tempRules)
                );
                array_push($out, $rules);
            }
        }

        return $out;
    }

    private function _render_form_element($rs = "") {
        $isFormError = $this->CI->form_validation->error_string() != "" ? TRUE : FALSE;
        $out = "";
        $column = $this->_CFG->get_column();
        if (count($column) > 0) {
            foreach ($column as $fieldName => $property) {
                $required = $property->get_REQUIRED() ? "required" : "";
                $editable = $property->get_EDITABLE() ? '' : 'readonly="readonly"';
                if ("" != $property->get_FIELD_DB()) {

                    $class = $property->get_CLASS();

                    $requiredMark = $property->get_REQUIRED() ? '<span class="fa fa-asterisk text-info"></span>' : "";
                    $maxLength = intval($property->get_SIZE()) < 1 ? '' : 'maxlength="' . intval($property->get_SIZE()) . '"';
                    $valueDB = $isFormError ? $this->CI->input->post($property->get_FORM_ID()) : _get_raw_object($rs, _replace_before($property->get_FIELD_DB(), "."));
                    if ($property->get_FIELD_TYPE() == $property->get_ENUM_TYPE()) {

                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '<select class="form-control elm-select' . $class . '" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '"  ' . $required . '>';
                        if ($property->get_WITH_NULL_OPTION())
                            $out .= '<option value="0">&nbsp;Pilih&nbsp;</option>';
//                        $countLala = 1;
                        if (is_array($property->get_ENUM_DEFAULT_VALUE())) {
                            foreach ($property->get_ENUM_DEFAULT_VALUE() as $value => $text) {
                                if (is_object($text)) {
                                    $valueDB = $valueDB == "" ? "0" : $valueDB;
                                    $this->set_ADDITIONAL_SCRIPT('var $' . strtoupper($property->get_FORM_ID()) . ' = $("#' . $property->get_FORM_ID() . '").selectize();$' . strtoupper($property->get_FORM_ID()) . '[0].selectize.setValue("' . $valueDB . '");');

                                    if (_get_raw_object($text, "kunci") == $valueDB) {
                                        $out .= '<option value="' . _get_raw_object($text, "kunci") . '" data-field-type="CHAR" selected>' . _get_raw_object($text, "nilai") . '</option>';
                                    } else {
                                        $out .= '<option value="' . _get_raw_object($text, "kunci") . '" data-field-type="CHAR" >' . _get_raw_object($text, "nilai") . '</option>';
                                    }
                                } else {
                                    if ($value == $valueDB) {
                                        $out .= '<option value="' . $value . '" data-field-type="CHAR" selected>' . $text . '</option>';
                                    } else {
                                        $out .= '<option value="' . $value . '" data-field-type="CHAR" >' . $text . '</option>';
                                    }
                                }
//                                $countLala++;
                            }
                        }

                        $out .= '</select>';
                        $out .= '</div>';
                    } elseif ($property->get_FIELD_TYPE() == $property->get_CHECKBOX_TYPE()) {
                        $checked = $valueDB == 1 ? "checked" : "";
//                        $out .= '<div class="form-group form-group-sm">';
//                        $out .= '<div class="checkbox">';
//                        $out .= '<label class="control-label" style="font-weight:bold;">';
//                        $out .= '<div aria-disabled="false" style="position: relative;" class="icheckbox_minimal">';
//                        $out .= '<input name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" style="position: absolute; opacity: 0;" type="checkbox" value="1" ' . $checked . '>';
//                        $out .= '<ins style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: none repeat scroll 0% 0% rgb(255, 255, 255); border: 0px none; opacity: 0;" class="iCheck-helper"></ins>';
//                        $out .= '</div>';
//                        $out .= '&nbsp;&nbsp;&nbsp;' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
//                        $out .= '</div>';
//                        $out .= '</div>';
                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<div class="checkbox">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">';
                        $out .= '<input name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '"  type="checkbox" value="1" ' . $checked . '>';
                        $out .= '&nbsp;&nbsp;&nbsp;&nbsp;' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '</div>';
                        $out .= '</div>';
                    } else if ($property->get_FIELD_TYPE() == $property->get_DATE_TYPE()) {
                        $valueDB = $valueDB == "" ? date("Y-m-d") : $valueDB;

                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '<div class="input-group">';
                        $out .= '<div class="input-group-addon">';
                        $out .= '<i class="fa fa-calendar"></i>';
                        $out .= '</div>';
                        $out .= '<input class="form-control pull-right elm-date" value="' . _date($valueDB, "d/m/Y") . '" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" placeholder="' . date("d/m/Y") . '" type="text">';
                        $out .= '</div>';
                        $out .= '</div>';
                    } else if ($property->get_FIELD_TYPE() == $property->get_MONTHYEAR_TYPE()) {
                        $valueDB = $valueDB == "" ? date("m-Y") : $valueDB;

                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '<div class="input-group">';
                        $out .= '<div class="input-group-addon">';
                        $out .= '<i class="fa fa-calendar"></i>';
                        $out .= '</div>';
                        $out .= '<input class="form-control pull-right elm-date-month" value="' . $valueDB . '" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" placeholder="' . date("m-Y") . '" type="text">';
                        $out .= '</div>';
                        $out .= '</div>';
                    } else if ($property->get_FIELD_TYPE() == $property->get_PASSWORD_TYPE() ||
                            $property->get_FIELD_TYPE() == $property->get_PASSWORD_CONFIRM_TYPE()) {

                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '<input class="form-control" value="" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" placeholder="' . $fieldName . '" type="password" ' . $required . ' pattern="[a-zA-Z0-9\s\-,=:]+" ' . $maxLength . '>';
                        $out .= '</div>';
                    } else if ($property->get_FIELD_TYPE() == $property->get_NUM_TYPE()) {
//                        echo $valueDB;
                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '<input class="form-control elm-num" value="' . _number($valueDB) . '" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" placeholder="' . $fieldName . '" type="text" ' . $editable . ' ' . $required . ' pattern="^\d+(?:.\d{3})*$" ' . $maxLength . '>';
                        $out .= '</div>';
                    } else if ($property->get_FIELD_TYPE() == $property->get_FILE_TYPE()) {
//                        _debug_var($valueDB, TRUE);
                        $src = $valueDB == "" ? "assets/img/no-pic.png" : str_replace(FCPATH, "", $valueDB);
                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label for="' . $property->get_FORM_ID() . '">' . $fieldName . '</label>';
                        $out .= '<input type="file" id="' . $property->get_FORM_ID() . '" name="' . $property->get_FORM_ID() . '" ' . $required . ' >';
                        $out .= '<div class="row">';
                        $out .= '<div class="col-xs-3">';
                        $out .= '<a class="thumbnail" href="#">';
                        $out .= '<img class="img-responsive" src="' . base_url() . $src . '" alt="">';
                        $out .= '</a>';
                        $out .= '</div>';
                        $out .= '</div>';
                        $out .= '</div>';
                    } else if ($property->get_FIELD_TYPE() == $property->get_EMAIL_TYPE()) {

                        $out .= '<div class="form-group form-group-sm">';
                        $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                        $out .= '<input class="form-control" value="' . $valueDB . '" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" placeholder="' . $fieldName . '" type="text" ' . $editable . ' ' . $required . '  ' . $maxLength . '>'; //pattern="[a-zA-Z0-9\s\(\)\+\-\.,\*\<\>\/=:\@]+"
                        $out .= '</div>';
                    } else {
                        if ($property->get_SIZE() <= 160 OR $property->get_SIZE() < 1) {
                            $out .= '<div class="form-group form-group-sm">';
                            $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                            $out .= '<input class="form-control" value="' . $valueDB . '" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '" placeholder="' . $fieldName . '" type="text" ' . $editable . ' ' . $required . ' ' . $maxLength . '>'; //pattern="[a-zA-Z0-9\s\(\)\+\-\.,\*\<\>\/=:]+"
                            $out .= '</div>';
                        } else {
                            $out .= '<div class="form-group form-group-sm">';
                            $out .= '<label class="control-label" for="' . $property->get_FORM_ID() . '">' . $fieldName . '&nbsp;&nbsp;&nbsp;&nbsp;' . $requiredMark . '&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa"></i></label>';
                            $out .= '<textarea class="form-control" name="' . $property->get_FORM_ID() . '" id="' . $property->get_FORM_ID() . '"  rows="3" placeholder="' . $fieldName . '" ' . $maxLength . '>' . $valueDB . '</textarea>';
                            $out .= '<span class="charsRemaining text-green">You have ' . $property->get_SIZE() . ' characters remaining</span>';
                            $out .= '</div>';
                        }
                    }
                }
            }
        }
        return $out;
    }

    function get_paging() {

        $itemsPerPage = null != $this->_CFG->get_ITEM_PER_PAGE() ? intval($this->_CFG->get_ITEM_PER_PAGE()) : $this->CI->config->item("ITEM_PER_PAGE");

        $whr = $this->get_where_form_search();

        $totalItems = $this->CI->base_model->count_all_data("1", $this->_CFG->get_PRIMARY_TBL(), $this->_CFG->get_JOIN_TBL(), $whr);

        $page = $this->_get_current_page();

        $jml = ceil($totalItems / $itemsPerPage);

        $groupPage = ceil($page / $this->CI->config->item("MAX_NUMBER_PAGING"));

        $outTemp = "";
        if ($groupPage == 1) {
            $outTemp .= '<li class="prev disabled"><a href="#"> ← </a></li>';
        } else {
            $this->add_query_string("page", ($groupPage * $this->CI->config->item("MAX_NUMBER_PAGING") - $this->CI->config->item("MAX_NUMBER_PAGING")));
            $link = $this->build_query_string($this->_QUERY_STRING);
            $outTemp .= '<li class="prev"><a href="?' . $link . '"> ← </a></li>';
        }

        for ($i = 1; $i <= $this->CI->config->item("MAX_NUMBER_PAGING"); $i++) {
            $count = ($groupPage * $this->CI->config->item("MAX_NUMBER_PAGING")) - $this->CI->config->item("MAX_NUMBER_PAGING") + $i;
            if ($count <= $jml) {
                $this->add_query_string("page", $page);
                if ($page == $count) {
                    $outTemp .= '<li class="active"><a href="#">' . $count . '</a></li>';
                } else {
                    $this->add_query_string("page", $count);
                    $link = $this->build_query_string($this->_QUERY_STRING);
                    $outTemp .= '<li><a href="?' . $link . '">' . $count . '</a></li>';
                }
            }
        }

//        echo ceil($jml / $this->CI->config->item("MAX_NUMBER_PAGING")) ." <= ".$groupPage;
        if (ceil($jml / $this->CI->config->item("MAX_NUMBER_PAGING")) <= $groupPage) {
            $outTemp .= '<li class="next disabled"><a href="#"> → </a></li>';
        } else {
            $this->add_query_string("page", ($count + 1));
            $link = $this->build_query_string($this->_QUERY_STRING);
            $outTemp .= '<li class="next"><a href="?' . $link . '"> → </a></li>';
        }


//        $out = "jumlah data " . $totalItems . " max row per page " . $itemsPerPage . " start index " . $itemStartIndex . " jumlah halaman " . $jml;

        $out = "";
        $out .= '<div class="row">';
        $out .= '<div class="col-xs-6">';
        if ($this->_END_ITEM > 0)
            $out .= '<div id="example1_info" class="dataTables_info">Menampilkan ' . ($this->_START_ITEM) . ' sampai ' . $this->_END_ITEM . ' dari ' . _number($totalItems) . ' baris</div>';
        $out .= '</div>';
        $out .= '<div class="col-xs-6">';
        $out .= '<div class="dataTables_paginate paging_bootstrap">';
        $out .= '<ul class="pagination">';
        $out .= $outTemp;
        $out .= '</ul>';
        $out .= '</div>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

    private function _limit($sql, $whr, $order, $offset, $limit) {
        $OrderBy = "";
        $field = $this->get_query_string('srcKolom');
        $value = $this->get_query_string('srcCari');

        if (count($order) > 0) {
            $OrderBy = "ORDER BY ";
            $OrderByTemp = "";
            foreach ($order as $ord => $fld) {
                $OrderByTemp .= $OrderByTemp == "" ? "" : ", ";
                $OrderByTemp .= $fld . " " . $ord;
            }

            $OrderBy .= $OrderByTemp;
        }

        $where = "";
        if (count($whr) > 0) {
            $where = $where == "" ? " WHERE " : "";
            $whrTemp = "";
            foreach ($whr as $cond) {
                $whrTemp .= $whrTemp == "" ? "" : ($field == "" && $value != "" ? " OR " : " AND ");
                $whrTemp .= $cond;
            }
            $where .= $whrTemp;
        }

        $sql = preg_replace('/(\\' . $OrderBy . '\n?)/i', '', $sql);
        $sql = preg_replace('/(^\SELECT (DISTINCT)?)/i', '\\1 row_number() OVER (' . $OrderBy . ') AS rownum, ', $sql);
        $sql .= $where;

        $NewSQL = "SELECT * \nFROM (\n" . $sql . ") AS A \nWHERE A.rownum BETWEEN (" . ($offset) . ") AND (" . ($limit) . ")";

        return $NewSQL;
    }

    private function _get_current_page() {
//        $this->security->xss_clean(filter_input(INPUT_REQUEST, "request_param"))
        $currPage = $this->CI->input->get_post("page");
        $currPage = intval($currPage) == 0 ? 1 : intval($currPage);
        return $currPage;
    }

    /**
     * return data grid 
     */
    function get_data_grid($rs) {
        $out = "";
        $itemsPerPage = null != $this->_CFG->get_ITEM_PER_PAGE() ? intval($this->_CFG->get_ITEM_PER_PAGE()) : $this->CI->config->item("ITEM_PER_PAGE");
        $out .= '<div class="row" style="margin-top: 10px;">';
        $out .= '<div class="col-xs-12">';
        $out .= '<div class="box box-primary">';
        $out .= '<div class="box-body no-padding table-responsive" >';
        $out .= '<table id="tbl-grid" class="table table-hover nowrap">';
        $out .= '<thead>';
        $out .= '<tr>';
        $out .= '<th >No.&nbsp;</th>';

        $column = $this->_CFG->get_column();
        if (count($column) > 0) {
            foreach ($column as $fieldName => $property) {
                $sortTemp = $this->get_query_string("sort");
                $orderTemp = $this->get_query_string("order");
                $orderTemp = $orderTemp == "" ? "" : ($orderTemp == "desc" ? "desc" : "asc");
                $style = $sortTemp == $fieldName && $orderTemp != "" ? ($orderTemp == "asc" ? "sort_asc.png" : "sort_desc.png") : "sort_both.png";
                $link = $this->build_query_string($this->_QUERY_STRING);
                $align = $property->get_FIELD_TYPE() == $property->get_NUM_TYPE() ? 'float:right;' : '';
                if ( $property->get_VISIBLE() ){
                $out .= '<th style="white-space: nowrap;"><a class="sortTable" data-sort="' . $fieldName . '" data-order="' . $orderTemp . '" data-uri="' . $link . '" href="#" style="' . $align . 'color: #000; cursor:pointer;padding-right: 20px;background: url(' . base_url("assets/img/" . $style) . ') no-repeat center right;">' . $fieldName . '</a></th>';
                }
            }
        }

        $out .= '<th>&nbsp;</th>';
        $out .= '</tr>';
        $out .= '</thead>';
        $out .= '<tbody>';
        $no = (($this->_get_current_page() - 1) * $itemsPerPage) + 1;
        $this->_START_ITEM = $no;
        if ("" != $rs) {
            foreach ($rs as $row) {

                $out .= '<tr>';
                $out .= '<td>' . $no . '</td>';

                $out .= $this->_render_grid_rows($column, $row);

                $out .= '<td>';

                $out .= $this->_render_grid_button($row);
//                        $out .= '<a href="#" role="button" data-toggle="tooltip" data-placement="top" title="Ubah"><i class="fa fa-pencil btn-grid"></i></a>&nbsp;';
//                $out .= '<a href="#" role="button" data-toggle="tooltip" data-placement="top" title="Hapus"><i class="fa fa-trash-o btn-grid"></i></a>&nbsp;';
                $out .= '</td>';
                $out .= '</tr>';
                $no++;
            }
        }

        if ($this->_START_ITEM == $no && $no > 1) {
            $this->_START_ITEM = 0;
            $this->add_query_string("page", "1");

            redirect(current_url() . "?" . $this->build_query_string($this->_QUERY_STRING));
        }


        $this->_END_ITEM = $no - 1;

        $out .= '</tbody>';
        $out .= '<!--<tfoot>';
        $out .= '<tr>';
        $out .= '<th>No.</th>';
        $out .= '<th>Rendering engine</th>';
        $out .= '<th>Browser</th>';
        $out .= '<th>Platform(s)</th>';
        $out .= '<th>Engine version</th>';
        $out .= '<th>CSS grade</th>';
        $out .= '</tr>';
        $out .= '</tfoot>-->';
        $out .= '</table>';

        $out .= '</div><!-- /.box-body -->';

        $out .= '</div><!-- /.box -->';
        $out .= $this->get_paging();
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

    private function _render_grid_button($row) {
        $out = "";

        if ($this->authorize("ubah")) {
            if ("" == $this->_CFG->get_grid_button("UBAH")) {
                $this->_CFG->add_grid_button(
                        "UBAH", array(
                    "method" => "form/edit",
                    "style" => "fa-pencil"
                        //"action" => "openBox('URL', 1000, 600)"
                ));
            }
        }

        if ($this->authorize("hapus")) {
            if ("" == $this->_CFG->get_grid_button("HAPUS")) {
                $this->_CFG->add_grid_button(
                        "HAPUS", array(
                    "method" => "remove",
                    "style" => "fa-trash-o",
                    "option" => 'data-bb="prompt" data-msg="Anda akan menghapus data ini ?" data-confirm="true"'
                ));
            }
        }

        $buttons = $this->_CFG->get_grid_button();
        $keys = _get_raw_object($row, $this->_get_field_name_without_alias($this->_CFG->get_KEYS()));
        $url = site_url($this->CI->router->fetch_directory() . $this->CI->router->fetch_class());


        foreach ($buttons as $btn => $property) {
            
            if (!$this->authorize("ubah") OR !$this->authorize("tambah")) {
                if ($btn != "UBAH" OR $btn != "HAPUS")
                    continue;
            }
//            _debug_var($property);
            $action = _get_raw_item($property, "action");
            $overideUri = _get_raw_item($property, "overideUri");
            $option = _get_raw_item($property, "option");
            $visible = _get_raw_item($property, "visible");
            $customKeys = _get_raw_item($property, "keys");

            
            if( $customKeys != ""){
                $keys = "";
                foreach($customKeys as $keyField){
                    $keys .= $keys == "" ? "" : "/";
                    $keys .= _get_raw_object($row, $keyField);
                }
            }

            if ($visible != "") {
                $visible = call_user_func_array(array($this->CI, $visible), array($row));
                if (!$visible)
                    continue;
            }


            if ($action != "") {
                if (strpos($action, "openBox") !== FALSE) {
                    if ($overideUri) {
                        $action = str_replace("URL", _get_raw_item($property, "method") . '/' . $keys, $action);
                    } else {
                        $action = str_replace("URL", $url . '/' . _get_raw_item($property, "method") . '/' . $keys, $action);
                    }
                }
                $out .= '<a href="#" ' . $option . ' role="button" data-toggle="tooltip" data-placement="top" title="' . $btn . '" onclick="javascript:' . $action . '"><i class="fa ' . _get_raw_item($property, "style") . ' btn-grid"></i></a>&nbsp;';
            } else {
                if ("" != _get_raw_item($property, "method")) {
                    if ($overideUri) {
                        $action = _get_raw_item($property, "method") . '/' . $keys;
                    } else {
                        $action = $url . '/' . _get_raw_item($property, "method") . '/' . $keys;
                    }
                    $out .= '<a ' . $option . ' href="' . $action . _build_query_string($this->get_query_string()) . '" role="button" data-toggle="tooltip" data-placement="top" title="' . $btn . '"><i class="fa ' . _get_raw_item($property, "style") . ' btn-grid"></i></a>&nbsp;';
                }
            }
        }


        $buttonGroups = $this->_CFG->get_grid_group_button();
        $tempOutBtGrp = "";
        if ($buttonGroups != "") {

            foreach ($buttonGroups as $btn => $property) {

                if (!$this->authorize("ubah") OR !$this->authorize("tambah")) {
                    if ($btn != "UBAH" OR $btn != "HAPUS")
                        continue;
                }

                $action = _get_raw_item($property, "action");
                $overideUri = _get_raw_item($property, "overideUri") == "" ? true : _get_raw_item($property, "overideUri") == "";
                $option = _get_raw_item($property, "option");
                $visible = _get_raw_item($property, "visible");

                if ($visible != "") {
                    $visible = call_user_func_array(array($this->CI, $visible), array($row));
                    if (!$visible)
                        continue;
                }


                if ($action != "") {
                    if (strpos($action, "openBox") !== FALSE) {
                        if ($overideUri) {
                            $action = str_replace("URL", _get_raw_item($property, "method") . '/' . $keys, $action);
                        } else {
                            $action = str_replace("URL", $url . '/' . _get_raw_item($property, "method") . '/' . $keys, $action);
                        }
                    }
                    $tempOutBtGrp .= '<li><a href="#" ' . $option . ' title="' . $btn . '" onclick="javascript:' . $action . '">' . $btn . '</a></li>';
                } else {
                    if ("" != _get_raw_item($property, "method")) {
                        if ($overideUri) {
                            $action = _get_raw_item($property, "method") . '/' . $keys;
                        } else {
                            $action = $url . '/' . _get_raw_item($property, "method") . '/' . $keys;
                        }
                        $tempOutBtGrp .= '<li><a ' . $option . ' href="' . $action . _build_query_string($this->get_query_string()) . '"  title="' . $btn . '">' . $btn . '</a></li>';
                    }
                }
            }
            $out .= '<div class="btn-group" style="margin-left: 8px;">
                  <button type="button" class="btn btn-default dropdown-toggle" aria-haspopup="true" aria-expanded="true" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu" style="right: 0 !important; left: auto;">
                    ' . $tempOutBtGrp . '
                  </ul>
                </div>';
        }

        return $out;
    }

    private function _render_grid_rows($column, $row) {
        $out = "";
        foreach ($column as $fieldName => $property) {
            if ($property->get_VISIBLE()) {
                if ($property->get_CHECKBOX_TYPE() == $property->get_FIELD_TYPE() OR $property->get_ENUM_TYPE() == $property->get_FIELD_TYPE()) {
                    $out .= $this->_render_column_enum($row, $property);
                    //            } else if ( $property->get_ENUM_TYPE() == $property->get_FIELD_NUM())
                    //                $out .= $this->_render_column_enum($row, $property);
                } else {
                    $out .= $this->_render_column($row, $property);
                }
            }
        }

        return $out;
    }

    private function _get_field_name_without_alias($fieldName) {
        $out = "";
        $pos = strpos($fieldName, ".");
        if ($pos !== FALSE) {
            $out = substr($fieldName, $pos + 1);
        } else {
            $out = $fieldName;
        }
        return $out;
    }

    private function _render_column($row, $property) {

        $out = "";
        $field = $property->get_FIELD_DB_ALIAS() == "" ? $property->get_FIELD_DB() : $property->get_FIELD_DB_ALIAS();
        $field = $this->_get_field_name_without_alias($field);

        $styles = $property->get_STYLE();

        if ($property->get_NUM_TYPE() == $property->get_FIELD_TYPE()) {
            $out .= '<td style="white-space: nowrap;padding-right:20px;text-align:right;">';
            $out .= _number(_get_raw_object($row, $field));
            $out .= '</td>';
        } else {
            $out .= '<td style="white-space: nowrap;">';
            $out .= _get_raw_object($row, $field);
            $out .= '</td>';
        }

        return $out;
    }

    private function _render_column_enum($row, $property) {
        $out = "";
        $field = $property->get_FIELD_DB_ALIAS() == "" ? $property->get_FIELD_DB() : $property->get_FIELD_DB_ALIAS();
        $field = $this->_get_field_name_without_alias($field);
        $value = _get_raw_object($row, $field);
        $styles = $property->get_STYLE();
        $out .= '<td>';
        if (is_array($styles)) {
            $out .= _get_raw_item($styles, $value);
        } else {
            $out .= _get_raw_item($property->get_ENUM_DEFAULT_VALUE(), $value);
        }
        $out .= '</td>';
        return $out;
    }

    function get_filter_form_elm($nama="", $nilai="", $operan="") {

        $out = '';
        $out .= '<div class="row" style="margin-top: 7px;">';
        $out .= '<div class="form-group srcKolomWrapper">';
        $out .= '<label for="srcKolom">Cari dikolom : </label>';
        $out .= '<select class="form-control input-sm srcKolom" name="srcKolom[]">';
        $out .= '<option value="">- Pilih -</option>';
        $columns = $this->_CFG->get_column();
        $column = _get_raw_item($columns, $nama);


        $data_field_type = $column == "" ? "" : $column->get_FIELD_TYPE();

        if (count($columns) > 0) {
            foreach ($columns as $fieldName => $property) {
                $selected = $fieldName == $nama ? "selected" : "";

                if ("DATE" == $property->get_FIELD_TYPE()) {
                    $field_type = $property->get_CLASS() == "" ? "DATE" : $property->get_CLASS();
                } else {
                    $field_type = $property->get_FIELD_TYPE();
                }
                $out .= '<option value="' . $fieldName . '" data-field-type="' . $field_type . '" ' . $selected . '>' . $fieldName . '</option>';
            }
        }

        $out .= '</select>';
        $out .= '</div>';


        $srcOperan = $column == "" ? "" : $column->get_search_operan();
        $srcOperan = $srcOperan == "" ? array("=", "!=", "LIKE", "NOT LIKE") : $srcOperan;
        $out .= '<div class="form-group srcOperanWrapper">';
        $out .= '<select class="form-control input-sm srcOperan" name="srcOperan[]">';
        $out .= '<option value="">- Pilih -</option>';
        foreach ($srcOperan as $operator) {
            $selected = $operator == $operan ? "selected" : "";
            $out .= '<option value="' . $operator . '" ' . $selected . '>' . $operator . '</option>';
        }
        $out .= '</select>';
        $out .= '</div>';

        if ("ENUM" == $data_field_type) {
            $srcCariElmTemp = '<select class="form-control input-sm srcCari" name="srcCari[]">';
            if (count($column->get_ENUM_DEFAULT_VALUE()) > 0) {
                foreach ($column->get_ENUM_DEFAULT_VALUE() as $value => $text) {
                    $selected = $value == $nilai ? "selected" : "";
                    $srcCariElmTemp .= '<option value="' . $value . '" ' . $selected . '>' . $text . '</option>';
                }
            }
            $srcCariElmTemp .= '</select>';
        } else if ("DATE" == $data_field_type ||
                "MONTHYEAR" == $data_field_type
        ) {

            $class = $column->get_CLASS() == "" ? "elm-date" : $column->get_CLASS();
            $width = $column->get_CLASS() == "elm-date-range" ? "200" : "120";
            $srcCariElmTemp = '<div class="input-group input-group-sm" style="width: ' . $width . 'px;">';
            $srcCariElmTemp .= '<span class="input-group-addon"><i class="fa fa-calendar"></i></span>';
            $srcCariElmTemp .= '<input type="text" class="form-control ' . $class . ' srcCari" name="srcCari[]" placeholder="Tanggal" value="' . $nilai . '"/>';
            $srcCariElmTemp .= '</div>';
        } else {
            $srcCariElmTemp = '   <input type="text" class="form-control input-sm srcCari" name="srcCari[]" placeholder="Kata Kunci" value="' . $nilai . '">';
        }

        $out .= '<div class="form-group srcCariWrapper">';
        if ($srcCariElmTemp == "") {
            $out .= '   <input type="text" class="form-control input-sm srcCari" name="srcCari[]" placeholder="Kata Kunci" value="">';
        } else {
            $out .= $srcCariElmTemp;
        }

        $out .= '</div>';

        $out .= '</div>';
        return $out;
    }

    /*
     * fn get_filter_form return filter form for datagrid
     */

    function get_filter_form() {
        $params = $this->get_query_string("srcKolom");
        $values = $this->get_query_string("srcCari");
        $operan = $this->get_query_string("srcOperan");
        $columns = $this->_CFG->get_column();
        $addscript = "";
        $out = "";
        $out .= '<section class="content">';
        $out .= '<div class="row">';
        $out .= '<div class="col-xs-12 pull-left">';
        $out .= '<form id="frmSearch" class="form-inline" role="form" method=get action="?">';

        if ($params != "") {
            foreach ($params as $idx => $field) {
                $elm = $this->get_filter_form_elm($field, _get_raw_item($values, $idx), _get_raw_item($operan, $idx));
                $out .= $elm;
            }
        } else {
            $elm = $this->get_filter_form_elm("", "");
            $out .= $elm;
        }

        if (count($columns) > 0) {
            $srcCariElm = array();
            $srcOperanElm = array();
            foreach ($columns as $fieldName => $property) {
                $data_field_type = "";
                if ("DATE" == $property->get_FIELD_TYPE()) {
                    $data_field_type = $property->get_CLASS() == "" ? "DATE" : $property->get_CLASS();
                } else {
                    $data_field_type = $property->get_FIELD_TYPE();
                }

                if ("ENUM" == $property->get_FIELD_TYPE()) {
                    $srcCariElmTemp = '<select class="form-control input-sm srcCari" name="srcCari[]">';
                    if (count($property->get_ENUM_DEFAULT_VALUE()) > 0) {
                        foreach ($property->get_ENUM_DEFAULT_VALUE() as $value => $text) {
                            $srcCariElmTemp .= '<option value="' . $value . '">' . $text . '</option>';
                        }
                    }
                    $srcCariElmTemp .= '</select>';
                } else if ("DATE" == $property->get_FIELD_TYPE() || $property->get_MONTHYEAR_TYPE() == $property->get_FIELD_TYPE()) {
                    $class = $property->get_CLASS() == "" ? "elm-date" : $property->get_CLASS();
                    $width = "165";
                    $srcCariElmTemp = '<div class="input-group input-group-sm" style="width: ' . $width . 'px;">';
                    $srcCariElmTemp .= '<span class="input-group-addon"><i class="fa fa-calendar"></i></span>';
                    $srcCariElmTemp .= '<input type="text" class="form-control ' . $class . ' srcCari" name="srcCari[]" placeholder="Tanggal" value=""/>';
                    $srcCariElmTemp .= '</div>';
                } else {
                    $srcCariElmTemp = '<input type="text" class="form-control input-sm srcCari" name="srcCari[]" placeholder="Kata Kunci" value="">';
                }

                $srcCariElm[$fieldName] = $srcCariElmTemp;
                $srcOperanElmTemp = '<select class="form-control input-sm srcOperan" name="srcOperan[]">';
                if (count($property->get_search_operan()) > 0) {
                    foreach ($property->get_search_operan() as $operan) {
                        $srcOperanElmTemp .= '<option value="' . $operan . '">' . $operan . '</option>';
                    }
                } else {
                    $srcOperanElmTemp .= '<option value="=">=</option>';
                }
                $srcOperanElmTemp .= '</select>';
                $srcOperanElm[$fieldName] = $srcOperanElmTemp;
            }

            $addscript = '
            $(".srcKolom").each( function (){
                $(this).change(function(){
                    
                    var fieldType = $("option:selected", this).attr("data-field-type");
                    var val = $("option:selected", this).val();
                    var elmSrcCari = ' . json_encode($srcCariElm) . ';
                    var elmSrcOperan = ' . json_encode($srcOperanElm) . ';
                    var elmOperanWrapper = $(this).parent().next(".srcOperanWrapper");
                    var elmWrapper = $(this).parent().next().next(".srcCariWrapper");

                    if (elmSrcCari[val] !== undefined ){
                        elmWrapper.html(elmSrcCari[val]);
                    } else{
                        elmWrapper.html("<input type=\"text\" class=\"form-control input-sm\" name=\"srcCari[]\" placeholder=\"Kata Kunci\" value=\"\">");
                    }

                    if (elmSrcOperan[val] !== undefined ){
                        elmOperanWrapper.html(elmSrcOperan[val]);
                    } else{
                        elmOperanWrapper.html("");
                    }

                    
                    var elmCari = elmWrapper.find(".form-control");
                    //console.log(fieldType);
                    if ( fieldType == "DATE" ) {
                        elmCari.datepicker({format: "dd/mm/yyyy",autoclose: true});
                    } else if (fieldType == "MONTHYEAR"){
                        elmCari.datepicker(
                        {format: "mm-yyyy",
                    viewMode: "months",
                    minViewMode: "months",
                    autoclose: true}
                        );
                    }
                });
            })';

            $this->set_ADDITIONAL_SCRIPT($addscript);
        }
        $out .= '<div class="row" style="margin-top: 7px;">';
        $out .= '<div class="form-group btnSrc">';
        $out .= '   <button class="btn btn-primary btn-sm"><span class="fa fa-search"></span>Cari</button>';
        $out .= '   <button id="btnAddSearch" class="btn btn-success btn-sm"><span class="fa fa-search-plus"></span>Tambah Kriteria</button>';
        $out .= '</div>';
        $out .= '</div>';
        $out .= '</form>';
        $out .= '</div>';

        $out .= '</div>';
        $out .= '<div class="row" style="margin-top: 7px;">';
        $out .= $this->_command_button();
        $out .= '</div>';
        $out .= '</section>';

        return $out;
    }

    private function _current_url() {
        $out = $this->CI->router->fetch_directory() . $this->CI->router->fetch_class();
        return $out;
    }

    private function _command_button() {

        $out = "";
        $out .= '<div class="col-xs-12" >
                <form class="form-inline" role="form">';

        if ("" == $this->_CFG->get_COMMAND_BUTTON("TAMBAH")) {
            $this->_CFG->add_COMMAND_BUTTON(
                    "TAMBAH", array(
                "method" => "/form/add",
                "style" => "fa-plus",
                "class" => "btn-primary"
            ));
        }

        if ("" == $this->_CFG->get_COMMAND_BUTTON("EXPORT")) {
            $this->_CFG->add_COMMAND_BUTTON(
                    "EXPORT", array(
                "method" => "/export",
                "style" => "fa-download",
                "class" => "btn-warning"
            ));
        }

        if (!$this->authorize("tambah")) {
            $this->_CFG->add_COMMAND_BUTTON(
                    "TAMBAH", array());
        }

        if (!$this->authorize("cetak")) {
            $this->_CFG->add_COMMAND_BUTTON(
                    "EXPORT", array());
        }

        $buttons = $this->_CFG->get_COMMAND_BUTTON();


//        $url = _replace_after(current_url(), "/index");
        $url = _replace_after(site_url($this->CI->router->fetch_directory() . $this->CI->router->fetch_class()), "/index");
//        $url = $this->CI->uri->fetch_class();

        foreach ($buttons as $btn => $property) {
            $action = _get_raw_item($property, "action");
            $method = _get_raw_item($property, "method");
            $overide = _get_raw_item($property, "overideUri");
            $keys = "";
            if ("" != $method) {
                if ($action != "") {
                    if (strpos($action, "openBox") !== FALSE) {
                        if ($overide) {
                            $action = str_replace("URL", $method . '/' . $keys . _build_query_string($this->get_query_string()), $action);
                        } else {
                            $action = str_replace("URL", $url . '/' . $method . '/' . $keys . _build_query_string($this->get_query_string()), $action);
                        }
                    }
//                    echo $action;
                    /*
                      $out .= '<div class="form-group pull-right">'
                      . '<button class="btn ' . _get_raw_item($property, "class") . ' btn-sm pull-right"><a href="#" role="button" title="' . $btn . '" onclick="javascript:' . $action . '" style="color:#FFFFFF">'
                      . '<span class="fa ' . _get_raw_item($property, "style") . '"></span>&nbsp;' . $btn . '</a>'
                      . '</button>'
                      . '</div>';
                     */
                    $out .= '<div class="form-group pull-right">'
                            . '<a class="btn ' . _get_raw_item($property, "class") . ' btn-sm pull-right text-white" href="#" onclick="javascript:' . $action . '" role="button"><span class="fa ' . _get_raw_item($property, "style") . '"></span>&nbsp;' . $btn . '</a>'
                            . '</div>';
                } else {
                    if ($overide) {
                        $action = str_replace("URL", $method . '/' . $keys . _build_query_string($this->get_query_string()), $action);
                    } else {
                        $action = str_replace("URL", $url . '/' . $method . '/' . $keys . _build_query_string($this->get_query_string()), $action);
                    }

//                    $out .= '<div class="form-group pull-right">
//                    <button class="btn ' . _get_raw_item($property, "class") . ' btn-sm pull-right"><a href="' . current_url() . _get_raw_item($property, "method") . _build_query_string($this->get_query_string()) . '" style="color:#FFFFFF"><span class="fa ' . _get_raw_item($property, "style") . '"></span>&nbsp;' . $btn . '</a></button>
//                </div>';
                    $out .= '<div class="form-group pull-right">'
                            . '<a class="btn ' . _get_raw_item($property, "class") . ' btn-sm pull-right text-white" href="' . $url . _get_raw_item($property, "method") . _build_query_string($this->get_query_string()) . '" role="button"><span class="fa ' . _get_raw_item($property, "style") . '"></span>&nbsp;' . $btn . '</a>'
                            . '</div>';
                }
            }
        }

        $out .= '</form>    
                </div>';
        return $out;
    }

    private function _command_form_button() {

        $out = "";
        //        $out .= '<button type="submit" class="btn btn-success" name="save" value="simpan">Simpan</button>&nbsp;';
//        $out .= '<button type="button" class="btn btn-warning" name="cancel" onClick="window.history.back(-1)">Batal</button>';


        if ("" == $this->_CFG->get_COMMAND_BUTTON("SIMPAN")) {
            $this->_CFG->add_COMMAND_BUTTON(
                    "SIMPAN", array(
                "type" => "submit",
                "name" => "save",
                "style" => "",
                "class" => "btn-primary",
                "action" => ""
            ));
        }

        if ("" == $this->_CFG->get_COMMAND_BUTTON("BATAL")) {
            $this->_CFG->add_COMMAND_BUTTON(
                    "BATAL", array(
                "type" => "button",
                "name" => "cancel",
                "style" => "",
                "class" => "btn-danger",
                "action" => "window.history.back(-1)"
            ));
        }


        $buttons = $this->_CFG->get_COMMAND_BUTTON();


        foreach ($buttons as $btn => $property) {
            $action = _get_raw_item($property, "action");
            $action = $action == "" ? "" : 'onclick="' . $action . '"';
            $name = _get_raw_item($property, "name");
            $overide = _get_raw_item($property, "overideUri");
            $type = _get_raw_item($property, "type");
            $class = _get_raw_item($property, "class");
            $keys = "";
            if ("" != $type) {
                $out .= '<button type="' . $type . '" class="btn ' . $class . '" name="' . $name . '" value="' . $btn . '" ' . $action . '>' . $btn . '</button>&nbsp;';
            }
        }


        return $out;
    }

    /*
     * generate header content
     * @with_right_header boolean if true enable right header content
     */

    function get_header($with_right_header = TRUE) {
        $out = "";
        $out .= '<!-- Content Header (Page header) -->';
        $out .= '<section class="content-header" style="background: #FFFFFF;">';
        $out .= '<div class="row" >';
//left header
        $out .= $this->_get_page_title();
//right header
        if (TRUE == $with_right_header) {
            $out .= $this->_get_right_header();
        }
        $out .= '</div>';
        $out .= '</section>';
        return $out;
    }

    /*
     * fn set_page_title, set page title $this->PAGE_TITLE
     * @page_title String page title  
     * @as_is boolean overide page title default content
     */

    function set_page_title($page_title, $as_is = FALSE) {

        if (TRUE == $as_is) {
            $this->_PAGE_TITLE = $page_title;
        } else {
            $this->_PAGE_TITLE = '<div class="col-xs-6 pull-left"><h4 class="text-primary">' . $page_title . '</h4></div>';
        }
    }

    /*
     * fn set_right_header, set right header $this->_RIGHT_HEADER
     * @right_header String right title 
     * @as_is boolean overide page title default content
     */

    function set_right_header($right_header, $as_is = FALSE) {

        if (TRUE == $as_is) {
            $this->_RIGHT_HEADER = $right_header;
        } else {
            $this->_RIGHT_HEADER = '<div class="col-xs-6">' . $right_header . '</div>';
        }
    }

    private function _get_page_title() {
        $out = _get_raw_object($this->_list_access(), "menu");
//        _debug_var($this->_LIST_ACCESS);
        $out = $out == "" ? $this->CI->router->fetch_class() : $out;
        if ("" == $this->_PAGE_TITLE) {
            $this->_PAGE_TITLE = '<div class="col-xs-6 pull-left " ><h4 class="text-primary">' . _string_human($out) . '</h4></div>';
        }
        return $this->_PAGE_TITLE;
    }

    private function _get_right_header() {
        if ("" == $this->_RIGHT_HEADER) {
//            $this->_RIGHT_HEADER = $this->_get_cmb_unit_bisnis();
            $this->_RIGHT_HEADER = "";
        }

        return $this->_RIGHT_HEADER;
    }

    private function _get_cmb_unit_bisnis() {
        $out = "";
        $rs = $this->CI->base_model->list_data("*", "TB_ADM_PERUSAHAAN", "", array("TMPR_STATUS" => "A"));
        $out .= '<div class="col-xs-6">';

        if ("" != $rs) {
            $out .= '<form class="form" role="form" style="margin-top: 15px;">';
            $out .= '<div class="form-group form-group-sm" style="margin-bottom: 0;">';
//            $out .= '<label for="srcBisnisUnit">Bisnis Unit : </label>';
            $out .= '<select class="demo-default form-control elm-select-auto" id="srcBisnisUnit" name="srcBisnisUnit" data-placeholder="Pilih Bisnis Unit">';
            $out .= '<option value="">&nbsp;&nbsp;</option>';

            foreach ($rs as $row) {
                if ($row->TMPR_KODE == $this->get_query_string("srcBisnisUnit")) {
                    $out .= '<option value="' . $row->TMPR_KODE . '" >' . $row->TMPR_NAMA . '</option>';
                } else {
                    $out .= '<option value="' . $row->TMPR_KODE . '">' . $row->TMPR_NAMA . '</option>';
                }
            }

            $out .= '</select>';
            $out .= '</div>';
            $out .= '</form>';
        }
        $out .= '</div>';
        return $out;
    }

}
