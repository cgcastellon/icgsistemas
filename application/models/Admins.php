<?php
class Admins extends CI_Model {
  private $table = 'admins';

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

  function allJoinGroupSummary(){
    $this->db->select('*, u.id AS idUser')
    ->from($this->table.' u')
    ->join('groups g','u.idGroup = g.id')
    ->join('summary s','g.idSummary = s.id');
    return $this->db->get()->result();
  }

}
