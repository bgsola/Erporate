<?php
defined('BASEPATH')OR exit('No direct script access allowed.');
/**
* 
*/
class Menu extends CI_Controller
{
	private $lvl="pelayan";
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id'))){
			show_404();
		}elseif($this->session->userdata('level')!==$this->lvl){
			session_destroy();
			show_404();
		}
		$this->load->model('user/Produk_model','produk');
	}

	function index(){
		$this->load->model('User_model');
		$data['title']="Dashboard";
		$data['breadcrumb']="Menu";
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$data['product']=$this->produk->get_all();
		$this->load->view('pelayan/Head',$data);
		$this->load->view('pelayan/Menuv',$data);
		$this->load->view('pelayan/Foot');
	}

	function Detail($slug=NULL){
		if(isset($slug)){
			$this->load->model('User_model');
			$data['title']="Detail";
			$data['breadcrumb']="Detail";
			$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
			$data['product']=$this->produk->get_by_id(array('slug'=>$slug));
			$this->load->view('pelayan/Head',$data);
			$this->load->view('pelayan/Detail',$data);
			$this->load->view('pelayan/Foot');
		}else show_404();
	}
}