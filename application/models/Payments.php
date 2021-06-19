<?php
class Payments extends CI_Model {
  private $table = 'payments';

  function __construct(){
    parent::__construct();
  }

  function insert($data){
    $this->db->insert($this->table, $data);
    return $this->db->insert_id();
  }

  function update($where, $set){
    $this->db->where($where)->update($this->table, $set);
  }

  function all($where=false, $order=false){
    if ($where) {
      if ($order) {
        return $this->db->where($where)->order_by($order)->get($this->table)->result();
      } else {
        return $this->db->where($where)->get($this->table)->result();
      }
    }else{
      if ($order) {
        return $this->db->order_by($order)->get($this->table)->result();
      } else {
        return $this->db->get($this->table)->result();
      }
    }
  }

  function search($where=false, $order=false){
    if ($order) {
      return $this->_order($where, $order);
    }else{
      return $this->db->where($where)->get($this->table)->row();
    }
  }

  function search_not_in($where, $where_not, $array, $order=false){
    if ($order) {
      return $this->db->where($where)->where_not_in($where_not, $array)->order_by($order)->get($this->table)->row();
    }else{
      return $this->db->where($where)->where_not_in($where_not, $array)->get($this->table)->row();
    }
  }

  function _order($where, $order){
    if ($where) {
      return $this->db->where($where)->order_by($order)->get($this->table)->row();
    }else{
      return $this->db->order_by($order)->get($this->table)->row();
    }
  }

  function all_order_limit($where, $order, $limit){
    if ($where) {
      return $this->db->where($where)->order_by($order)->limit($limit)->get($this->table)->result();
    }else{
      return $this->db->order_by($order)->limit($limit)->get($this->table)->result();
    }
  }

  function delete($where){
    $this->db->where($where)->delete($this->table);
  }

  function allJoinGroupSummary($where){
    $this->db->select('*')
    ->from($this->table.' p')
    ->join('groups g','p.idGroup = g.id')
    ->join('summary s','p.idSummary = s.id')
    ->where($where);
    return $this->db->get()->result();
  }

}
