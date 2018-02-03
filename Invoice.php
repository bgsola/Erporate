<?php
defined('BASEPATH')OR exit('No direct script access allowed');
/**
* 
*/
class Invoice extends CI_Controller
{
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id')) || $this->session->userdata('level')!="admin"){
			session_destroy();
			show_404();
		}
		$this->load->model('user/Invoice_model','invoice');
	}

	function index(){
		$this->load->model('User_model');
		$data['title']="List Order";
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$this->load->view('admin/header_adm',$data);
		$this->load->view('admin/invoice_adm');
		$this->load->view('admin/footer_adm');
	}

	function ajax_list(){
		$list=$this->invoice->get_datatables();
		$data=array();
		$no=$_POST['start'];
		foreach ($list as $inv) {
			# code...
			$no++;
			$row=array();
			$row[]= $no;
			$row[]= $inv->id;
			$row[]= $inv->name;
			$row[]= $inv->nomormeja;
			$row[]= "Rp.".number_format($inv->total,0,',','.');
			if($inv->status=="active"){
				$row[]='<span class="label label-danger">'.$inv->status.'</span>';
			}elseif($inv->status=="fixed"){
				$row[]='<span class="label label-warning">'.$inv->status.'</span>';
			}else{
				$row[]='<span class="label label-success">'.$inv->status.'</span>';
			}
			$row[]= $inv->tanggal;
			if(empty($inv->tanggaledit)){
				$row[]="<---->";
			}else{
				$row[]= $inv->tanggaledit;
			}
			$row[]= '<a class="btn btn-default" href="javascript:void(0)" title="Edit invoice" onclick=('."'".$inv->id."'".')><i class="fa fa-pencil"></i></a>';
			
			$data[]=$row;
		}

		$output=array(
			"draw"				=> $_POST["draw"],
			"recordsTotal"		=> $this->invoice->count_all(),
			"recordsFiltered"	=> $this->invoice->count_filtered(),
			"data"				=> $data
			);
		echo json_encode($output);
	}
}
?>