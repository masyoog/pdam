<?php

class Base_model extends CI_Model {

    function __construct() {
        parent::__construct();
//        $this->db->close();
    }

    function execute_count($SQL, $whr = "") {

        $where = "";
        if (count($whr) > 0) {

            $where = $where == "" ? " WHERE " : "";
            $whrTemp = "";
            foreach ($whr as $key => $cond) {
                $whrTemp .= $whrTemp == "" ? "" : " AND ";
                $whrTemp .= $cond;
            }
            $where .= $whrTemp;
        }

        $query = $this->db->query($SQL . $where);
//        echo $this->db->last_query();
        $row = $query->row()->JUMLAH;
        return $row;
    }

    function execute($SQL) {
        $out = "";
        $rs = $this->db->query($SQL);
        if ($rs->num_rows() > 0) {
            $out = $rs->result();
        }
//        $this->db->close();
        return $out;
    }

    function insert_data($table = '', $datas = '', $isRetID = TRUE) {
//        $this->db->cache_delete();
        $insert_id = "";
        $this->db->insert($table, $datas);
        
//        $this->db->close();
        if ($isRetID == TRUE) {
            $insert_id = $this->db->insert_id($table."_id_seq");
            
        }
        return $insert_id;
    }

    function update_data($table = '', $datas = '', $wheres = '') {
//        $this->db->cache_delete();
        if ($wheres != '') {
            $this->db->update($table, $datas, $wheres);
        }
//        $this->db->close();
    }

    function update_data_adv($table = '', $datas = '', $wheres = '') {
//        $this->db->cache_delete();
        if ($wheres != '') {
            foreach ($datas as $key => $value) {
                if (is_array($value)) {
                    $this->db->set($key, _get_raw_item($value, 0), _get_raw_item($value, 1));
                } else {
                    $this->db->set($key, $value);
                }
            }
            $this->db->where($wheres);
            $this->db->update($table);
        }
//        $this->db->close();
    }

    function delete_data($table = '', $wheres = '') {
//        $this->db->cache_delete();
        if ($wheres != '') {
            $this->db->delete($table, $wheres);
        }
//        $this->db->close();
    }
    
    


    /*
     * Digunakan untuk insert table yang mempunyai constraint, sehingga mengabaikan violation :D
     */

    function insert_ignore_data($table = "", $datas = "") {

        $qry = $this->db->insert_string('my_table', $data_item);

        $qry = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $insert_query);

        $this->db->query($qry);

        return $this->db->insert_id();
    }

    function count_all_data($field, $table, $join = "", $where = "", $distinct = "") {
        $out = '';

        $this->db->select($field);
        if ($distinct != "") {
            $this->db->distinct($distinct);
            $this->db->group_by($distinct);
        }
        $this->db->from($table);
        if (is_array($join)) {
            foreach ($join as $key => $value) {
                $this->db->join($key, $value, "left");
            }
        }

        if (is_array(_get_raw_item($where, 0))) {
            foreach ($where as $whr) {
                $this->db->where($whr);
            }
        } else {
            $this->db->where($where);
        }

        $out = $this->db->count_all_results();
//        $this->db->close();
        return $out;
    }

    function list_single_data($field, $table, $join, $where = "", $escapes = TRUE, $order_by = "", $distinc="") {
//        _debug_var($escapes, TRUE);
        $out = '';
        if ('' != $where)
            $out = $this->list_data($field, $table, $join, $where, $order_by, 1, "", $distinc, $escapes);

        return _get_raw_item($out, 0);
    }

    function list_data($field, $table, $join, $where = "", $order = "", $limit = "", $offset = "", $distinct = "", $escapes = TRUE) {
        $out = '';

        $this->db->select($field, $escapes);
        $this->db->from($table);

        if ($distinct != "") {
            $this->db->distinct($distinct);
            $this->db->group_by($distinct);
        }

        if (is_array($join)) {
            foreach ($join as $key => $value) {
                $this->db->join($key, $value, "left");
            }
        }

        //_debug_var($where);
        if ($where != "") {
            if (is_array(_get_raw_item($where, 0))) {
                foreach ($where as $whr) {
                    $this->db->where($whr);
                }
            } else {
                $this->db->where($where);
            }
        }

        if (is_array($order)) {
            foreach ($order as $ord) {
                $this->db->order_by($ord);
            }
        }

        if ($limit !== '') {
            if ($offset != ''){
                $this->db->limit($limit, $offset);
            }else{
                $this->db->limit($limit);
            }    
        }



        $rs = $this->db->get();
//        echo $this->db->last_query();

        if ($rs->num_rows() > 0) {
            $out = $rs->result();
        }
//        }
//        $this->db->close();
        return $out;
    }

}
