<?php

class Datagrid2 {

    private $CI;
    private $_rs;
    private $_cfg;
    private $_is_error;
    private $_error_msg;
    private $_current_url;
    private $_pattern_text_area = '[a-zA-Z0-9\s]+';
    private $_pattern_an = '[a-zA-Z0-9\s\-,=:]+';
    private $_pattern_date = '[0-9\s\/]+';
    private $_pattern_num = '[0-9\-]+';
    private $_operan_num = array(
        'sama' => '=',
        'tidaksama' => '!=',
        'kurangdari' => '<',
        'lebihdari' => '>',
        'diantara' => 'diantara'
    );
    private $_operan_date = array(
        'sama' => '=',
        'tidaksama' => '!=',
        'kurangdari' => '<',
        'lebihdari' => '>',
        'diantara' => 'diantara'
    );
    private $_operan_an = array(
        'sama' => 'Sama dengan',
        'tidaksama' => 'Tidak sama dengan',
        'diawali' => 'Diawali dengan',
        'diakhiri' => 'Diakhiri dengan',
        'seperti' => 'Seperti',
        'diantara' => 'Diantara'
    );
    private $_operans;
    private $_additional_script;

    /*
     * @rs result set
     * @cfg config for grid property
     */

    function __construct($rs = '', $cfg = '') {
        $this->CI = & get_instance();

        if ($rs != '')
            $this->_rs = $rs;

        if ($cfg != '')
            $this->_cfg = $cfg;

        $this->_is_error = FALSE;

        $this->_operans = array('AN' => $this->_operan_an, 'NUM' => $this->_operan_num, 'DATE' => $this->_operan_date, 'N' => $this->_operan_num);

//        echo $this->CI->uri->segment(3);
//        echo $this->_current_url = site_url(array(,$this->CI->uri->segment(4)));
    }

    function add_additional_script($script = '') {
        $this->_additional_script .= $script;
    }

    function get_where_from_search() {

        $out = array();
        $column = _get_raw_item($this->_cfg, 'column');
        $posts = $this->CI->input->post();

        $i = 1;
        if ($column != '' && is_array($column)) {
            foreach ($column as $cfg) {
                $field = _get_raw_item($posts, 'srcLabel' . $i);
                $operan = _get_raw_item($posts, 'srcOperan' . $i);
                $value = _get_raw_item($posts, 'srcValue1' . $i);
                $value2 = _get_raw_item($posts, 'srcValue2' . $i);

                $data_type = _get_raw_item($cfg, 'fieldType');
                if ($value != "") {
                    if ($operan == 'sama') {
                        if ($data_type == 'DATE')
                            $out += array('date(' . $field . ')' => $value);
                        else
                            $out += array($field => $value);
                    } else if ($operan == 'tidaksama') {

                        if ($data_type == 'DATE')
                            $out += array('date(' . $field . ') !=' => $value);
                        else
                            $out += array($field . ' !=' => $value);
                    } else if ($operan == 'kurangdari') {
                        if ($data_type == 'DATE')
                            $out += array('date(' . $field . ') <' => $value);
                        else
                            $out += array($field . ' < ' => $value);
                    } else if ($operan == 'lebihdari') {
                        if ($data_type == 'DATE')
                            $out += array('date(' . $field . ') <' => $value);
                        else
                            $out += array($field . ' > ' => $value);
                    } else if ($operan == 'diakhiri') {
                        $out += array($field . ' like' => '%' . $value);
                    } else if ($operan == 'diawali') {
                        $out += array($field . ' like' => $value . '%');
                    } else if ($operan == 'seperti') {
                        $out += array($field . ' like' => '%' . $value . '%');
                    } else if ($operan == 'diantara') {
                        if ($data_type == 'DATE') {
                            $out += array('date(' . $field . ') >=' => $value);
                            $out += array('date(' . $field . ') <=' => $value2);
                        } else {
                            $out += array($field . ' >=' => $value);
                            $out += array($field . ' <=' => $value2);
                        }
                    } else {
                        
                    }
                }

                $i++;
            }
        }
        return $out;
    }

    function set_current_url($url) {
        $this->_current_url = $url;
    }

    function get_current_url() {

        if ($this->_current_url == '') {
            $dir = str_replace('/', '', $this->CI->router->fetch_directory());
            $path = str_replace('/', '', $this->CI->router->fetch_class());

            $this->_current_url = site_url(array($dir, $path));
        }

        return $this->_current_url;
    }

    function set_error($msg) {
        $msg = str_replace('#', '-', $msg);
        $this->_error_msg .= $this->_error_msg == '' ? $msg : '#' . $msg;
    }

    function get_error() {
        return $this->_error_msg;
    }

    private function _generate_element($nilai) {
        $out = '';
        $pattern = '';
        $isReadOnly = '';

        if ($nilai != '' && is_array($nilai)) {
            $class = _get_raw_item($nilai, 'style');

            $field = _get_raw_item($nilai, 'fieldDB');
            $form_type = _get_raw_item($nilai, 'formType');
            $data_type = _get_raw_item($nilai, 'fieldType');
            $max_length = _get_raw_item($nilai, 'maxLength');


            $elm_value = _get_raw_object($this->_rs, $field);
            $required = _get_raw_item($nilai, 'required') == TRUE ? 'required' : '';
            $selectedValue = _get_raw_item($nilai, 'selectedValue');
            $isEditable = _get_raw_item($nilai, 'isEditable');

            if (_get_raw_item($nilai, 'updateToText')) {
                $elm_value = $elm_value == "0" ? "pending" : ($elm_value == "1" ? "sukses" : "gagal");
            }

            $readonly = '';
            if ($isEditable === FALSE) {
                $readonly = 'readonly="readonly"';
                $class = $class != '' ? $class . ' hanyabaca' : 'hanyabaca';
            }

            $class = $class != '' ? 'class="' . $class . '"' : '';

            if ($data_type == 'AN') {
                $pattern = $this->_pattern_an;
            } else if ($data_type == 'NUM') {
                $pattern = $this->_pattern_num;
            } else if ($data_type == 'N') {
                $pattern = $this->_pattern_num;
            }

            $pattern = $data_type == 'AN' ? $this->_pattern_an : ($data_type == 'NUM' ? $this->_pattern_num : '');

            if ($form_type == 'text') {
//                $pattern = $data_type == 'AN' ? $this->_pattern_an : ($data_type == 'NUM' ? $this->_pattern_num : '');
//                $elm_value = is_null($elm_value) || $elm_value == NULL || $elm_value = "" ? "" : 'value="'. $elm_value .'"' ;
                $out .= '<input ' . $class . ' id="' . $field . '" name="' . $field . '" type="text" size="' . $max_length . '" maxlength="' . $max_length . '" ' . $readonly . ' value="' . $elm_value . '" ' . $required . ' pattern="' . $pattern . '"/>';

                if ($data_type == 'AN') {
                    $out .= '<span class="form_hint">Isian yang diperbolehkan hanya angka dan huruf(a-z dan 0-9)"</span>';
                } else if ($data_type == 'NUM') {
                    $out .= '<span class="form_hint">Isian yang diperbolehkan hanya angka (0-9)"</span>';
                }
            } else if ($form_type == 'password') {
//                $pattern = $data_type == 'AN' ? $this->_pattern_an : ($data_type == 'NUM' ? $this->_pattern_num : '');
//                $elm_value = is_null($elm_value) || $elm_value == NULL || $elm_value = "" ? "" : 'value="'. $elm_value .'"' ;
                $out .= '<input ' . $class . ' id="' . $field . '" name="' . $field . '" type="password" size="' . $max_length . '" maxlength="' . $max_length . '" ' . $readonly . ' value="' . $elm_value . '" ' . $required . ' pattern="' . $pattern . '"/>';

                if ($data_type == 'AN') {
                    $out .= '<span class="form_hint">Isian yang diperbolehkan hanya angka dan huruf(a-z dan 0-9)"</span>';
                } else if ($data_type == 'NUM') {
                    $out .= '<span class="form_hint">Isian yang diperbolehkan hanya angka (0-9)"</span>';
                }
            } else if ($form_type == 'checkbox') {
                $out .= '<input ' . $class . ' id="' . $field . '" name="' . $field . '" type="checkbox" value="' . $selectedValue . '" ' . _set_checked($elm_value, $selectedValue) . ' />';
            } else if ($form_type == 'date') {
                $out .= '<input ' . $class . ' id="' . $field . '" name="' . $field . '" type="text" size="15" maxlength="' . $max_length . '" value="' . $elm_value . '" />';
                $this->_additional_script .= '$( "#' . $field . '" ).datepicker({ inline: true, dateFormat: \'yy-mm-dd\'});';
            } else if ($form_type == 'select' OR $form_type == 'options') {
                $out .= $this->_create_combo($field, $selectedValue, $elm_value, '', _get_raw_item($nilai, 'required'));
            } else if ($form_type == 'textarea') {

                if ($isEditable === FALSE) {
                    $isReadOnly = 'readonly';
                }

                $out .= '<textarea ' . $isReadOnly . ' name="' . $field . '" id="' . $field . '" ' . $required . ' pattern="' . $this->_pattern_text_area . '" maxlength="' . $max_length . '" rows="3" cols="40">' . $elm_value . '</textarea>';
                if (intval($max_length) > 0) {
                    $out .= '<span class="charsRemaining"></span>';
                }
            }
        } else {
            $this->_is_error = TRUE;
            $this->_error_msg = 'Uncomplete config column property (FRM CFG COLUMN)';
        }

        return $out;
    }

    private function _create_combo($elmName, $optVal, $defaultVal, $opt = '', $is_required = FALSE) {
        $required = $is_required ? 'required' : '';
        $selected = '';
        $out = '';
        $out .= '<select class="select2It" ' . $required . '  name="' . $elmName . '" id="' . $elmName . '" ' . $opt . '>';
        $out .= '<option value="" style="display:none">-- Pilih --</option>';
        if (count($optVal) > 0 && is_array($optVal)) {
            foreach ($optVal as $key => $value) {
//                echo "|".$key."| == |". $value."|<br />";
                $selected = $key == $defaultVal ? 'selected' : '';
                $out .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
            }
        }
        $out .= '</select>';
        
        return $out;
    }

    function render_search_form($isAktifasi = FALSE) {
        $out = '';

        $label_opt_vals = '';

        $column = _get_raw_item($this->_cfg, 'column');

        if ($column != '' && is_array($column)) {
            foreach ($column as $kunci => $nilai) {
                $label_opt_vals[_get_raw_item($nilai, 'fieldDB')] = $kunci;
            }


            $i = 1;
            reset($column);
            foreach ($column as $kunci => $nilai) {
                $fldDB = _get_raw_item($nilai, 'fieldDB');
                $form_type = _get_raw_item($nilai, 'formType');
                $elmNameLabel = 'srcLabel' . $i;
                $elmNameOperan = 'srcOperan' . $i;
                $elmNameValue1 = 'srcValue1' . $i;
                $elmNameValue2 = 'srcValue2' . $i;
                $selectedValue = _get_raw_item($nilai, 'selectedValue');
                $data_type = _get_raw_item($nilai, 'fieldType');

                $pattern = $data_type == 'AN' ? $this->_pattern_an : ($data_type == 'NUM' ? $this->_pattern_num : '');
                $out .= '<li>';
                $out .= '<label for="' . $elmNameOperan . '">' . $kunci . '</label>';
                $out .= '<input type="hidden" name="' . $elmNameLabel . '" id="' . $elmNameLabel . '" value="' . $fldDB . '" />';

                $out .= $this->_create_combo($elmNameOperan, _get_raw_item($this->_operans, _get_raw_item($nilai, 'fieldType')), $elmNameOperan, ' class="src-operan"');

                if ($form_type == 'checkbox') {
                    $out .= "&nbsp;&nbsp;" . $this->_create_combo($elmNameValue1, array('1' => 'Aktif', '0' => 'Non Aktif'), '');
                } else {
                    if ($data_type == 'AN' OR $data_type == 'NUM') {
                        $out .= '&nbsp;<input style="width:120px;" id="' . $elmNameValue1 . '" name="' . $elmNameValue1 . '" type="text" maxlength="50" value="" pattern="' . $pattern . '" />';
                        $out .= '&nbsp;<input class="src-value2" style="width:120px;" id="' . $elmNameValue2 . '" name="' . $elmNameValue2 . '" type="text" maxlength="50" value="" pattern="' . $pattern . '" />';
                    } else if ($data_type == 'DATE') {
                        $out .= '&nbsp;<input style="width:120px;" id="' . $elmNameValue1 . '" name="' . $elmNameValue1 . '" type="text" maxlength="50" value="" />';
                        $out .= '&nbsp;<input class="src-value2" style="width:120px;" id="' . $elmNameValue2 . '" name="' . $elmNameValue2 . '" type="text" maxlength="50" value=""  />';
                    }

                    if ($data_type == 'AN') {
                        $out .= '<span class="form_hint">Isian yang diperbolehkan hanya angka dan huruf(a-z dan 0-9)"</span>';
                    } else if ($data_type == 'NUM') {
                        $out .= '<span class="form_hint">Isian yang diperbolehkan hanya angka (0-9)"</span>';
                    } else if ($data_type == 'DATE') {
                        $this->_additional_script .= '$( "#' . $elmNameValue1 . '" ).datepicker({ inline: true, dateFormat: \'yy-mm-dd\'});';
                        $this->_additional_script .= '$( "#' . $elmNameValue2 . '" ).datepicker({ inline: true, dateFormat: \'yy-mm-dd\'});';
                    }
                }

                $out .='</li>';
                $i++;
            }

            $out .='<li>';
            $out .='<button class="btn-batal" type="button" onclick="javaScript:window.location.href=\'' . $this->get_current_url() . '/\'" >Batal</button>';
            if ($isAktifasi) {
//                $out .='<button class="tombol" type="submit" name="submit" id="submit" value="Cari">Cari</button>';
                $out .= ' <button class="tombol" type="submit" name="submit" value="Cari" onclick="javascript:openModal(\'' . $this->get_current_url() . '/aktifasi_massal/)" >Tampilkan</button>';
            } else {
                $out .='<button class="tombol" type="submit" name="submit" id="submit" value="Cari">Cari</button>';
            }
            $out .='</li>';
        } else {
            $this->_is_error = TRUE;
            $this->_error_msg = 'Undefined column property (FRM SRC COLUMN)';
        }

        $out .= '<script>';
        $out .= '$(document).ready(function(){';
        $out .= '$(function() {';
        $out .= $this->_additional_script;
        $out .= '});';
        $out .= '});';
        $out .= '</script>';

        return $out;
    }

    function render_form() {
        $out = '';

        $column = _get_raw_item($this->_cfg, 'column');

        if ($column != '' && is_array($column)) {

            foreach ($column as $kunci => $nilai) {
                $label = $kunci;
                $field = _get_raw_item($nilai, 'fieldDB');
                $out .='<li>';
                $out .='<label for="' . $field . '">' . $label . '</label>';
                $out .= $this->_generate_element($nilai);
                $out .='</li>';
            }

            $out .='<li>';
            $out .='<button class="btn-batal" type="button" onclick="javaScript:window.location.href=\'' . $this->get_current_url() . '/\'" >Batal</button>';
            $out .='<button class="tombol" type="submit" name="submit" id="submit" value="Simpan">Simpan</button>';
            $out .='</li>';
        } else {
            $this->_is_error = TRUE;
            $this->_error_msg = 'Undefined column property (FRM COLUMN)';
        }

        $out .= '<script>';
        $out .= '$(document).ready(function(){';
        $out .= '$(function() {';
        $out .= $this->_additional_script;
        $out .= '});';
        $out .= '});';
        $out .= '</script>';

        return $out;
    }

    function set_additional_script($script) {
        $this->_additional_script = $script;
    }

    function get_additional_script() {
        return $this->_additional_script;
    }

    function render($isTambahBaru = TRUE, $isGenerateBaru = FALSE, $isKirimSms = FALSE, $isValidasiDeposit = FALSE, $isPesanHistory = FALSE) {

        $out = '';
        if ($isTambahBaru) {
            $out = '<input class="tombol margin-dev" type="button" name="btn_add" id="btn_add" 
                        value="Tambah"  onclick="javascript:openModal(\'' . $this->get_current_url() . '/edit/\')" />';
        }

        if ($isKirimSms) {
            $out = '<input class="tombol margin-dev" type="button" name="btn_add" id="btn_add" 
                        value="Kirim Pesan"  onclick="javascript:openModal(\'' . $this->get_current_url() . '/edit/\')" />';
        }

        if ($isGenerateBaru) {
            $out = '<input class="tombol margin-dev" type="button" name="btn_generate" id="btn_generate" 
                        value="Generate"  onclick="javascript:openModal(\'' . $this->get_current_url() . '/generate/\')" />';
            $out .='<input class="tombol margin-dev" type="button" name="btn_aktifasi_massal" id="btn_aktifasi_massal" 
                        value="Aktifasi Massal"  onclick="javascript:openModal(\'' . $this->get_current_url() . '/search/\')" />';
        }

        $out .='<input class="btn-search margin-dev" type="button" name="btn_search" id="btn_search" 
                    value="Pencarian"  onclick="javascript:openModal(\'' . $this->get_current_url() . '/search/\')" />';

        $out .='<input class="btn-orange margin-dev" type="button" name="btn_export" id="btn_export" value="Export"/> ';
        $out .='<input class="tombol margin-dev" type="button" name="btn_export_all" id="btn_export_all" value="Export All"/>';

        $column = _get_raw_item($this->_cfg, 'column');
        $grid_command = _get_raw_item($this->_cfg, 'gridCommand');


//        _debug_var($grid_command, true);
        if ($column != '' && is_array($column)) {
            $out .= '<table class="grid_list">';
            $out .= '<thead>';
            $out .= '<tr class="title_bar">';
            $out .= '<th>No.</th>';

            foreach ($column as $kunci => $nilai) {
                $label = $kunci;
                $class = _get_raw_item($nilai, 'style');

                $out .= '<th class="' . $class . '">' . $label . '</th>';
            }
            $out .= '<th class="tengah">&nbsp;</th>';

            $out .= '</tr>';
            $out .= '</thead>';
            $out .= '<tbody>';



            if ($this->CI->uri->segment(3) == 'index') {
                $no = $this->CI->uri->segment(4);
            } else if (is_int($this->CI->uri->segment(3))) {
                $no = $this->CI->uri->segment(3);
            } else {

                $no = '0';
            }


            $no = intval($no);
            if (count($this->_rs) > 0) {
                $no++;
                foreach ($this->_rs as $row) {

//                    $field_param = _get_raw_object($row, _get_raw_item($this->_cfg, 'fieldKey'));
                    $out .= '<tr>';
                    $out .= '<td>' . $no . '</td>';

                    $param_where = _get_raw_item($this->_cfg, 'fieldKey');

                    foreach ($column as $kunci => $nilai) {
                        $field = _get_raw_item($nilai, 'fieldDB');
                        $kolom = $field == '' ? '' : _get_raw_object($row, $field);
                        $kolom_type = _get_raw_item($nilai, 'fieldType');
                        $class = 'class="' . _get_raw_item($nilai, 'style') . '"';
//                        $kolom = _get_raw_item($nilai, 'fieldType') == 'NUM' ? _number($kolom) : $kolom;
                        $form_type = _get_raw_item($nilai, 'formType');
                        $selectedValue = _get_raw_item($nilai, 'selectedValue');

                        $info = _get_raw_item($nilai, 'info');
                        if ($info != '') {
                            $info = ' tooltip=""';
                        }

                        if ($isValidasiDeposit) {
                            if ($row->status == '0') {
                                $row->status = 'pending';
                            } else if ($row->status == '1') {
                                $row->status = 'sukses';
                            } else if ($row->status == '2') {
                                $row->status = 'gagal';
                            }
                        }


                        if ($isPesanHistory && $field == "arah") {
                            if ($kolom == '1') {
                                $kolom = 'Masuk';
                            } else if ($kolom == '2') {
                                $kolom = 'Keluar';
                            }
                        }

                        if ($form_type == 'checkbox') {
                            $out .= '<td ' . $class . '><input id="' . $field . $no . '" name="' . $field . $no . '" type="checkbox" value="' . $selectedValue . '" ' . _set_checked($kolom, $selectedValue) . ' onclick="javaScript:' . _get_raw_item($nilai, 'callback') . '(this, \'' . $this->_generate_param_where($param_where, $row) . '\', \'' . $field . '\')" /></td>';
                        } else if ($form_type == 'options') {
                            $out .= '<td ' . $class . '>' . $this->_create_combo($field . $no, $selectedValue, $kolom, 'onchange="javaScript:' . _get_raw_item($nilai, 'callback') . '(this, \'' . $this->_generate_param_where($param_where, $row) . '\', \'' . $field . '\')"') . '</td>';
                        } else {
                            if ($kolom_type == 'NUM')
                                $kolom = _number($kolom);
                            else if ($kolom_type == 'DATE')
                                $kolom = _date($kolom);
                            else if ($kolom_type == 'DATETIME')
                                $kolom = _date($kolom, "Y-m-d H:i:s");
                            else
                                $kolom = $kolom;

                            $out .= '<td ' . $class . '>' . html_escape(substr($kolom, 0, 50)) . '</td>';
                        }
                    }

                    $out .= '<td class="tengah">';

                    /*                     * * default grid command ** */

                    if ($grid_command == '') {
                        $grid_command = array(
                                    'Ubah' => array(
                                        'style' => 'icon-pencil',
                                        'id' => '',
                                        'name' => 'btn-edit',
                                        'attribut' => '',
                                        'action' => 'openModal(\'' . $this->get_current_url() . '/edit/ubah/__WHR_PRM\')',
                                    ),
                                    'Hapus' => array(
                                        'style' => 'icon-trash',
                                        'id' => '',
                                        'name' => 'btn-del',
                                        'attribut' => '',
                                        'action' => 'if(confirm(\'Anda yakin akan menghapus data ini ?\')){window.location.href=\'' . $this->get_current_url() . '/hapus/__WHR_PRM\'}',
                                    )
                        );
                    }

                    if ($grid_command != '') {
                        if (is_array($grid_command)) {
                            foreach ($grid_command as $btnname => $btnattr) {
//                                _debug_var($row);
//                                _debug_var($this->_generate_param_where($param_where, $row));
                                $action = _get_raw_item($btnattr, 'action');
                                $action = str_replace('__WHR_PRM', $this->_generate_param_where($param_where, $row), $action);
                                $action = $action == '' ? '' : 'onclick="javascript:' . $action . '"';

                                //                            $out.= '<input class="'. _get_raw_item($btnattr, 'style') .'" type="button" name="'. _get_raw_item($btnattr, 'name') .'" value="'. $btnname .'" '. $action .'/>';
                                $out .= '<a href="#" class="grid-btn" title="' . $btnname . '" ' . $action . '><i class="' . _get_raw_item($btnattr, 'style') . '"></i></a>';
                                //                            echo '<pre>' . htmlspecialchars('<a href="#" class="grid-btn" title="'. $btnname .'" '. $action .'><i class="'. _get_raw_item($btnattr, 'style') .'"></i></a>', ENT_QUOTES) . '</pre>';
                                //                            echo '<pre>' . htmlspecialchars($action, ENT_QUOTES) . '</pre>';
                            }
                        }
                    }

                    $out .= '</td>';
                    $out .= '</tr>';

                    $no++;
                }
            } else {
                $out .= '<tr>';
                $out .= '<td colspan="' . (count(_get_raw_item($this->_cfg, 'column')) + 2) . '" class="tengah">Tidak ada data untuk ditampilkan.</td>';
                $out .= '</tr>';
            }

            $out .= '</tbody>';
            $out .= '</table>';
        } else {
            $this->_is_error = TRUE;
            $this->_error_msg = 'Undefined column property (DG COLUMN)';
        }

        return $out;
    }

    function render_hak_akses($arrChecked, $id_group_user) {
        $out = '';

        $column = _get_raw_item($this->_cfg, 'column');
        if ($column != '' && is_array($column)) {
            $out .= '<table class="grid_list">';
            $out .= '<thead>';
            $out .= '<tr class="title_bar">';
            $out .= '<th>No.</th>';

            foreach ($column as $kunci => $nilai) {
                $label = $kunci;
                $class = _get_raw_item($nilai, 'style');

                $out .= '<th class="' . $class . '">' . $label . '</th>';
            }
            $out .= '<th class="tengah">&nbsp;</th>';

            $out .= '</tr>';
            $out .= '</thead>';
            $out .= '<tbody>';

            if ($this->CI->uri->segment(3) == 'menu') {
                $no = $this->CI->uri->segment(5);
            } else if (is_int($this->CI->uri->segment(3))) {
                $no = $this->CI->uri->segment(3);
            } else {

                $no = '0';
            }

            $no = intval($no);
            if (count($this->_rs) > 0) {
                $no++;
                foreach ($this->_rs as $row) {

//                    $field_param = _get_raw_object($row, _get_raw_item($this->_cfg, 'fieldKey'));
                    $out .= '<tr>';
                    $out .= '<td>' . $no . '</td>';

                    $param_where = _get_raw_item($this->_cfg, 'fieldKey');

                    foreach ($column as $kunci => $nilai) {
                        $field = _get_raw_item($nilai, 'fieldDB');
                        $kolom = $field == '' ? '' : _get_raw_object($row, $field);
                        $class = 'class="' . _get_raw_item($nilai, 'style') . '"';
                        $kolom = _get_raw_item($nilai, 'fieldType') == 'NUM' ? _number($kolom) : $kolom;
                        $form_type = _get_raw_item($nilai, 'formType');
                        $selectedValue = _get_raw_item($nilai, 'selectedValue');
                        $checked = _get_raw_item($arrChecked, ($no - 1)) == '1' ? 'checked' : '';
                        $id_menu = $row->id;

                        if ($form_type == 'checkbox') {
//                            echo $kolom.'='.$selectedValue.' ('.$checked.') => ' . $id_menu . '<br/>';
                            $out .= '<td ' . $class . '><input id="' . $field . $no . '" name="' . $field . $no . '" type="checkbox" value="' . $selectedValue . '" ' . $checked . ' onclick="javaScript:' . _get_raw_item($nilai, 'callback') . '(this, \'' . $id_group_user . '\', \'' . $id_menu . '\')" /></td>';
                        } else {
                            $out .= '<td ' . $class . '>' . html_escape(substr($kolom, 0, 50)) . '</td>';
                        }
                    }

                    $out .= '</tr>';

                    $no++;
                }
            } else {
                $out .= '<tr>';
                $out .= '<td colspan="' . (count(_get_raw_item($this->_cfg, 'column')) + 2) . '" class="tengah">Tidak ada data untuk ditampilkan.</td>';
                $out .= '</tr>';
            }

            $out .= '</tbody>';
            $out .= '</table>';
        } else {
            $this->_is_error = TRUE;
            $this->_error_msg = 'Undefined column property (DG COLUMN)';
        }

        return $out;
    }

    private function _generate_param_where($field_where = '', $row_data = '') {
//        _debug_var($row_data);
        $out = '';

        if ($field_where != '' && is_array($field_where)) {
            foreach ($field_where as $value) {
                $field = $value;
                $param = _get_raw_object($row_data, $field);
                $out .= $out == '' ? $param : '/' . $param;
            }
        } else {
            $this->_is_error = TRUE;
            $this->_error_msg = 'Undefined param where for form property (DG PARAM WHERE)';
        }

        return $out;
    }

}

?>
