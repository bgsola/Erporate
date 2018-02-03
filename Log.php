<?php
defined('BASEPATH')OR exit('No direct script access allowed.');
/**
* 
*/
class Log extends CI_Controller
{
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id')) || $this->session->userdata('level')!='admin'){
			session_destroy();
			show_404();
		}
		$this->load->model('user/Log_model','logg');
	}

	function index(){
		$this->load->model('User_model');
		$data['title']="Log";
		$data['breadcrumb']="Log";
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$this->load->view('admin/header_adm',$data);
		$this->load->view('admin/Logview');
		$this->load->view('admin/footer_adm');
	}

	function ajax_list(){
		$list=$this->logg->get_datatables();
		$data=array();
		$no=$_POST['start'];
		foreach($list as $lg){
			# code...
			$no++;
			$row=array();
			$row[]= $no;
			$row[]= $lg->name;
			$row[]= $lg->log_type;
			$row[]= $lg->log_action;
			$row[]= $lg->log_desc;
			$row[]= $lg->log_time;
			$row[]= '<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Edit" onclick="remove_cart('."'".$lg->id."'".')"><i class="fa fa-trash"></i></a>';

			$data[]= $row;
		}
		$output = array(
			"draw"				=> $_POST['draw'],
			"recordsTotal"		=> $this->logg->count_all(),
			"recordsFiltered"	=> $this->logg->count_filtered(),
			"data"				=> $data
			);
		echo json_encode($output);
	}

	function ajax_delete($id=NULL){
		if(isset($id)){			
			Log_activity("Delete","Delete log by id=$id");
			$this->logg->delete_by_id(array('id'=>$id));
			echo json_encode(array("status"=>TRUE));
		}else show_404();
	}

	function ajax_truncate(){
		$this->logg->delete_all();
		Log_activity("Delete","Delete all log");
		echo json_encode(array("status"=>TRUE));
	}
}
?>