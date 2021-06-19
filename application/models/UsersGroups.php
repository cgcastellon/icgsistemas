<?php
class UsersGroups extends CI_Model {
  private $table = 'usersGroups';

  function __construct(){
    parent::__construct();
  }

  function insert($data){
    return $this->db->insert($this->table, $data);
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

  function allJoinUser($where){
    $this->db->select('*')
    ->from($this->table.' ug')
    ->join('users u','ug.idUser = u.id')
    ->where($where);
    return $this->db->get()->result();
  }

  function allJoinGroupSummary($where){
    $this->db->select('g.*, s.*, g.id as idGroup, u.id as idUser')
    ->from($this->table.' ug')
    ->join('users u','ug.idUser = u.id')
    ->join('groups g','ug.idGroup = g.id')
    ->join('summary s','g.idSummary = s.id')
    ->where($where);
    return $this->db->get()->result();
  }

}
