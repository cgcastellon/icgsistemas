<?php
class Groups extends CI_Model {
  private $table = 'groups';

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

  function all_group_order($where, $puntos){
    if ($puntos) {
      return $this->db->where($where)->group_by('Grupo')->order_by('Grupo')->order_by('Puntos', 'DESC')->order_by('GF', 'DESC')->get($this->table)->result();
    }else{
      return $this->db->where($where)->group_by('Grupo')->order_by('Grupo')->order_by('GF', 'DESC')->get($this->table)->result();
    }
  }

  function all_group_order_limit($where, $limit){
    return $this->db->where($where)->group_by('Grupo')->order_by('Puntos', 'DESC')->order_by('GF', 'DESC')->limit($limit)->get($this->table)->result();
  }

  function delete($where){
    $this->db->where($where)->delete($this->table);
  }

  function allJoinSummary($where=false){
    $this->db->select('g.*, s.curso, s.siglas')
    ->from($this->table.' g')
    ->join('summary s','g.idSummary = s.id', 'left');

    if ($where) { return $this->db->where($where); }

    return $this->db->get()->result();
  }

  function searchJoinSummary($where){
    $this->db->select('g.*, s.curso, s.siglas')
    ->from($this->table.' g')
    ->join('summary s','g.idSummary = s.id', 'left')
    ->where($where);
    return $this->db->get()->row();
  }

}
