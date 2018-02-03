<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* 
*/
class Dashboard extends CI_Controller
{
	private $lvl='pelayan';
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id')) || $this->session->userdata('level')!==$this->lvl){
			session_destroy();
			show_404();
		}
	}

	function index(){
		$this->load->model('User_model');
		$data['title']="Dashboard";
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$this->load->view('pelayan/Head',$data);
		$this->load->view('pelayan/Homev');
		$this->load->view('pelayan/Foot');
	}
}
?>