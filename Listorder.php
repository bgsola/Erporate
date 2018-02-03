<?php
defined('BASEPATH')OR exit('No direct script access allowed.');
/**
* 
*/
class Listorder extends CI_Controller
{
	private $lvl='pelayan';
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id')) || $this->session->userdata('level')!=$this->lvl){
			session_destroy();
			show_404();
		}
		$this->load->model('user/Invoice_model','invoice');
	}

	function index(){
		$this->load->model('User_model');
		$data['title']="List";
		$data['breadcrumb']="List";
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$this->load->view('pelayan/Head',$data);
		$this->load->view('pelayan/Orderlist');
		$this->load->view('pelayan/Foot');
	}

	function ajax_list(){
		$list=$this->invoice->get_datatables();
		$data=array();
		$no=$_POST['start'];
		foreach($list as $inv){
			# code...
			$no++;
			$row=array();
			$row[]= $no;
			$row[]= $inv->nomormeja;
			$row[]= "Rp.".number_format($inv->total,0,',','.');
			$row[]= $inv->status;
			$row[]= $inv->tanggal;
			$row[]= '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_city('."'".$inv->id."'".')"><i class="fa fa-pencil"></i></a> 
			<a class="btn btn-sm btn-default" href="javascript:void(0)" title="Delete" onclick="delete_city('."'".$inv->id."'".')"><i class="fa fa-eye"></i></a>';

			$data[]= $row;
		}
		$output = array(
			"draw"				=> $_POST['draw'],
			"recordsTotal"		=> $this->invoice->count_all(),
			"recordsFiltered"	=> $this->invoice->count_filtered(),
			"data"				=> $data
			);
		echo json_encode($output);
	}

	function ajax_edit($id){
		$data=$this->invoice->get_by_id(array('id'=>$id));
		echo json_encode($data);
	}

	function ajax_update(){
		$stts="close";
		$data=array(
			'status'=>$stts,
			);
		$this->invoice->update(array('id'=>$this->input->post('id')),$data);
		echo json_encode(array("status"=>TRUE));
	}
}
?>