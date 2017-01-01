<?php

class Base_model extends CI_Model {
   
    function __construct() {
        parent::__construct();
    }
    
    function get_all_join_data($field, $table, $join, $where = "", $order = "", $distinct = "")
    {
        $out = '';
        
        $this->db->select($field);
        $this->db->from($table);
        
        if($distinct != ""){
            $this->db->distinct($distinct);
            $this->db->group_by($distinct);
        }
        
        if(is_array($join)){
            foreach($join as $key => $value){
                $this->db->join($key, $value, "left");
            }
        }
        
        if($where != ""){
            $this->db->where($where);
        }
        
        if($order != ""){
            $this->db->order_by($order);
        }
        
        $rs = $this->db->get();

        if($rs->num_rows() > 0){
            foreach($rs->result() as $row)
                $out[] = $row;
        } 
        
        return $out;
    }
    
    function get_all_data2($table='', $where='')
    {
        $out = "";
        if($table !=''){
            $this->db->select('*');
            $this->db->from($table);
            if($where != ""){
                $this->db->where($where);
            }
            $rs = $this->db->get();
            if($rs->num_rows() > 0){
                $out = $rs->result();
            } 
        }
        
//        echo $this->db->last_query();
        return $out;
    }
        
    function get_all_data($table='', $where='')
    {
        $out = 0;
        if($table !=''){
            $this->db->select('*');
            $this->db->from($table);
            if($where != ""){
                $this->db->where($where);
            }
            $rs = $this->db->get();
            if($rs->num_rows() > 0){
                foreach($rs->result() as $row)
                    $out[] = $row;
            } 
        }

        return $out;
    }
            
    function get_count_all_data($table='', $field_key='', $where='')
    {
        $out = 0;
        if($table !=''){
            $this->db->select($field_key);
            $this->db->from($table);
            if($where != ""){
                $this->db->where($where);
            }
            $rs = $this->db->get();
            $out = $rs->num_rows();
        }
        return $out;
    }
    
    function get_count_all_join_data($fieldKey, $table, $join, $where = "", $distinct = "")
    {
        $out = '';

        $this->db->select($fieldKey);
        
        if($distinct != ""){
            $this->db->distinct($distinct);
            $this->db->group_by($distinct);
        }
        $this->db->from($table);
        if(is_array($join)){
            
            foreach($join as $key => $value){
                if(is_array($value)){
                    $kriteria = _get_raw_item($value, 0);
                    $join_side = _get_raw_item($value, 1);
                } else {
                    $kriteria = $value;
                    $join_side = "left";
                }
                $this->db->join($key, $kriteria, $join_side);
//                $this->db->join($key, $value, "left");
            }
        }
        
        if($where != ""){
            $this->db->where($where);
        }
                
        $rs = $this->db->get();
        
        $out = $rs->num_rows();
        
        return $out;
    }    
   
    
    function get_redirect_last_page()
    {
        $dir = str_replace('/', '', $this->router->fetch_directory());
        $class = str_replace('/', '', $this->router->fetch_class());
        return site_url( array($dir, $class, 'index', $this->session->userdata('last_paging_offset')));
    }


    function get_child_menu()
    {
        $parent_uri = _string_human(str_replace('/', '', $this->router->fetch_directory()));
        $parent_id = $this->get_single_data_by_condition('m_menu', array('lower(menu)'=>$parent_uri, 'status'=>1));
        $child = '';
        
        if(_get_raw_object($parent_id, 'id') != '')
        {
            $child = $this->list_data_by_condition('m_menu', array('id_parent'=> _get_raw_object($parent_id, 'id'), 'status'=>1), 'urutan ASC ', '100', '0');
        }
        return $child;
    }
    
    function list_all_data($table, $limit = "", $offset = "")
    {
        $out = '';
        $limit = $limit == "" ? $this->config->item("MY_MAX_ROWS_LIMIT") : $limit;
        $offset = $offset == "" ?  0 : $offset;
        
//        if($where != ""){
//            $this->db->where($where);
//        }
        
        $rs = $this->db->get($table, $limit, $offset);
        
        if($rs->num_rows() > 0){
            foreach($rs->result() as $row)
                $out[] = $row;
        } 
//        echo $this->db->last_query();
        return $out;
    }
    
    function list_join_data($field, $table, $join, $where = "", $order = "", $limit = "", $offset = "", $distinct = "")
    {
        $out = '';
        $limit = $limit == "" ? $this->config->item("MY_MAX_ROWS_LIMIT"): $limit;
        $offset = $offset == "" ? 0: $offset;
        
        $this->db->select($field);
        $this->db->from($table);
        
        if($distinct != ""){
            $this->db->distinct($distinct);
            $this->db->group_by($distinct);
        }
        
        if(is_array($join)){
            foreach($join as $key => $value){
                if(is_array($value)){
                    $kriteria = _get_raw_item($value, 0);
                    $join_side = _get_raw_item($value, 1);
                } else {
                    $kriteria = $value;
                    $join_side = "left";
                }
                $this->db->join($key, $kriteria, $join_side);
            }
        }
        
        if($where != ""){
            $this->db->where($where);
        }
        
        if($order != ""){
            $this->db->order_by($order);
        }
        
        
        $this->db->limit($limit,$offset);
        
        $rs = $this->db->get();
//        echo $this->db->last_query();
        
        if($rs->num_rows() > 0){
            foreach($rs->result() as $row)
                $out[] = $row;
        } 
        
        return $out;
    }
    
    
    function list_data ($table, $id, $label, $wheres='', $orders=''){
        
        $out = array();
        $field = $id . ',' . $label;
        $this->db->select($field);
        $this->db->from($table);
        
        if($wheres != ""){
            $this->db->where($wheres);
        }
        
        if($orders != ""){
            $this->db->order_by($orders);
        }
        
        $rs = $this->db->get();
        
//        echo $this->db->last_query();
        
        if($rs->num_rows() > 0){
            foreach($rs->result() as $row)
                $out[$row->$id] = $row->$label;
        } 
        
        return $out;
    }
    
    function list_data_by_condition($table, $wheres, $orders='', $limit = '', $offset = '')
    {
        $out = '';
        
        $limit = $limit == "" ? $this->config->item("MY_MAX_ROWS_LIMIT") : $limit;
        
        $offset = $offset == "" ?  0 : $offset;
        
        $this->db->select('*');
        $this->db->from($table);
        
        if($orders != ""){
            $this->db->order_by($orders);
        }
        
        if($wheres != ""){
            $this->db->where($wheres);
        }
        
        $this->db->limit($limit,$offset);
        
        $rs = $this->db->get_where();
        
//        echo $this->db->last_query();
        if($rs->num_rows() > 0){
            foreach($rs->result() as $row)
                $out[] = $row;
        } 

        return $out;
    }
    
    function list_group_data_by_condition($table, $fields, $wheres, $orders='', $limit = '', $offset = '', $groups = '')
    {
        $out = '';
        
        $limit = $limit == "" ? $this->config->item("MY_MAX_ROWS_LIMIT") : $limit;
        
        $offset = $offset == "" ?  0 : $offset;
        
        $this->db->select($fields);
        $this->db->from($table);
        
        if($wheres != ""){
            $this->db->where($wheres);
        }
        
        if($orders != ""){
            $this->db->order_by($orders);
        }
        
        if($groups != ""){
            $this->db->group_by($groups);
        }
        
        $this->db->limit($limit,$offset);
        
        $rs = $this->db->get();
        
//        echo $this->db->last_query();
        if($rs->num_rows() > 0){
            foreach($rs->result() as $row)
                $out[] = $row;
        } 

        return $out;
    }
    
    function get_single_data_by_condition($table='', $wheres='')
    {
        $out = '';
        $rs = $this->db->get_where($table, $wheres, 1, 0);
        if($rs->num_rows() > 0){
            $out = $rs->row();
        } 
//        _debug_var($out);
//        echo $this->db->last_query();
        return $out;
    }
    
    function get_single_data_join_by_condition($field, $table, $join, $where = ""){
        
        if($where == ""){
            return "";
        }
        
        $data = array();
        $rs = $this->list_join_data($field, $table, $join, $where, '', 1, 0);
        
        if($rs != ""){
            foreach($rs as $key=>$value){
                foreach($value as $keys=>$values){
                    $data[$keys] = $values;
                }
            }
        }
        $data = json_decode(json_encode($data), false);
        return $data;
    }
    
    function insert_data($table='', $datas='')
    {
        $this->db->cache_delete();
        $this->db->insert($table, $datas);   
//        echo $this->db->last_query();
        return $this->db->insert_id();
    }
    
    function update_data($table='', $datas='', $wheres='')
    {
        $this->db->cache_delete();
        $this->db->update($table, $datas, $wheres);
//        echo $this->db->last_query();
    }
    
    function delete_data($table='', $wheres='')
    {
        $this->db->cache_delete();
        $this->db->delete($table, $wheres);
    }
    
    function query($query){
        return $this->db->query($query);
    }
    
    function get_last_query()
    {
        return $this->db->last_query();
    }
    
    public function export($query) 
    {
        $queryExport = $this->db->query($query);
        $this->load->dbutil();
        $delimiter = ",";
        $newline = "\r\n";
        $queryResult = $this->dbutil->csv_from_result($queryExport, $delimiter, $newline);
        $queryResult = str_replace(array("\n"), ' ',$queryResult);

        return $queryResult;
    }
    
    function export_join($field, $table, $join, $where = "", $order = "", $limit = "", $offset = "")
    {
        
        $limit = $limit == "" ? $this->config->item("MY_MAX_ROWS_LIMIT"): $limit;
        $offset = $offset == "" ? 0: $offset;
        
        $this->db->select($field);
        $this->db->from($table);
        if(is_array($join)){
            foreach($join as $key => $value){
                $this->db->join($key, $value, "left");
            }
        }
        
        if($where != ""){
            $this->db->where($where);
        }
        
        if($order != ""){
            $this->db->order_by($order);
        }
        
        
        $this->db->limit($limit,$offset);
        
        $rs = $this->db->get();
        
//        echo $this->db->last_query();
        
        return $rs;
    }
    
    
}

