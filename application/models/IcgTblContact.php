<?php
class IcgTblContact extends CI_Model {

  private $table = "icg_tblContact";

  function __construct(){
    parent::__construct();
  }

  function insert($data){
    $this->db->insert($this->table, $data);
    return $this->db->insert_id();
  }
}
 ?>
