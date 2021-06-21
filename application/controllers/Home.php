<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

  function __construct() {
		parent::__construct();
		$this->load->model('icgTblContact');
		// $this->load->model('cheffm');
		// $this->load->library('logg');
		// $this->output->enable_profiler(ENVIRONMENT!='production');
		// $this->data['data'] = array();
		// $this->session->keep_flashdata(array('redirect', 'order_id'));
		// $this->data['data']['store'] 			= $this->sm->detail($this->store_id);
	}

	public function index() {
		$this->load->view('template');
	}

  public function contact() {
    $data = $this->input->post('data');
    $this->icgTblContact->insert($data);
    redirect('/');
  }
}
