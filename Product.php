<?php
defined('BASEPATH')OR exit('No direct script access allowed.');
/**
* 
*/
class Product extends CI_Controller
{
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id')) || $this->session->userdata('level')!='admin'){
			session_destroy();
			show_404();
		}
		$this->load->model('user/Produk_model','product');
	}

	function index(){
		$data['title']='Data product';
		$this->load->model('User_model');
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$this->load->view('admin/header_adm',$data);
		$this->load->view('admin/product_adm');
		$this->load->view('admin/footer_adm');
	}

	function Addpro(){
		$data['title']='Add product';
		$this->load->model('User_model');
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$this->load->view('admin/header_adm',$data);
		$this->load->view('admin/Addpro_adm');
		$this->load->view('admin/footer_adm');
	}

	function Editpro($id=NULL){
		if(isset($id)){
			$data['title']='Edit product';
			$this->load->model('User_model');
			$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
			$data['produk']=$this->product->get_by_id(array('id'=>$id));
			$this->load->view('admin/header_adm',$data);
			$this->load->view('admin/Editpro_adm',$data);
			$this->load->view('admin/footer_adm');
		}else show_404();
	}

	function ajax_list(){
		$list=$this->product->get_datatables();
		$data=array();
		$no=$_POST['start'];
		foreach($list as $pro){
			$no++;
			$row= array();
			$row[]= $no;
			$row[]= $pro->nama;
			if(empty($pro->foto)){$row[]= '<img src="'.base_url('assets/images/empty.jpg').'" class="img-responsive" style="width:50px">';}else{$row[]= '<img src="'.base_url('assets/images/produk/'.$pro->foto).'" class="img-responsive" style="width:50px">';}
			$row[]= "Rp.".number_format($pro->harga,0,',','.');
			if($pro->status=="close"){
				$row[]='<span class="label label-danger">'.$pro->status.'</span>';
			}elseif($pro->status=="unready"){
				$row[]='<span class="label label-warning">'.$pro->status.'</span>';
			}else{
				$row[]='<span class="label label-success">'.$pro->status.'</span>';
			}
			$row[]= '<a class="btn btn-sm btn-default" href="'.base_url('admin/Product/Editpro/'.$pro->id).'" title="Update"><i class="fa fa-pencil"></i></a>
			<a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_product('."'".$pro->id."'".')"><i class="fa fa-trash"></i></a>';

			$data[]= $row;
		}
		$output=array(
			"draw"				=> $_POST['draw'],
			"recordsTotal"		=> $this->product->count_all(),
			"recordsFiltered"	=> $this->product->count_filtered(),
			"data"				=> $data
			);
		echo json_encode($output);
	}

	function ajax_save(){
		//if($this->input->post('submit')){
			$this->form_validation->set_rules('nama','nama','required|min_length[3]');
			$this->form_validation->set_rules('harga','harga','required|min_length[3]');
			if($this->form_validation->run()==TRUE){
				$search=" ";
				$replace="_";
				$subject=$this->input->post('nama');
				$slug=str_replace($search, $replace, $subject);
				$data=array(
					'nama'		=> $this->input->post('nama'),
					'slug'		=> $slug,
					'keterangan'=> $this->input->post('keterangan'),
					'harga'		=> $this->input->post('harga'),
					'stok'		=> $this->input->post('stok'),
					'status'	=> $this->input->post('status')
					);
				if(!empty($_FILES['foto']['name'])){
					$gambar=$this->_do_upload();
					$data['foto']=$gambar;
				}
				$this->product->save($data);
				Log_activity("Add","Add data produk $subject");
				redirect('admin/Product');
				//echo json_encode(array("status"=>true));
			}else{
				redirect('admin/Product/Addpro');
			}
		//}else show_404();
	}

	function ajax_edit($id=NULL){
		if(isset($id)){
			$data=$this->product->get_by_id(array('id'=>$id));
			echo json_encode($data);
		}else show_404();
	}

	function ajax_update(){
		$this->form_validation->set_rules('nama','nama','required|min_length[3]');
		$this->form_validation->set_rules('harga','harga','required|min_length[3]');
		if($this->form_validation->run()==true){
			$search=" ";
			$replace="_";
			$subject=$this->input->post('nama');
			$slug=str_replace($search, $replace, $subject);
			$data=array(
				'nama'		=> $this->input->post('nama'),
				'slug'		=> $slug,
				'keterangan'=> $this->input->post('keterangan'),
				'harga'		=> $this->input->post('harga'),
				'stok'		=> $this->input->post('stok'),
				'status'	=> $this->input->post('status')
				);
			if(!empty($_FILES['foto']['name'])){
				$gambar=$this->_do_upload();
				$up=$this->product->get_to_cart(array('id'=>$this->input->post('id')));
				if(file_exists('assets/images/produk/'.$up->foto)&& $up->foto)
					unlink('assets/images/produk/'.$up->foto);
				$data['foto']=$gambar;
			}
			$this->product->update(array('id'=>$this->input->post('id')),$data);
			Log_activity("Update","Update data produk $subject");
			redirect('admin/Product');
		}else{
			redirect('admin/Product/Editpro');
		}
	}

	function _do_upload(){
		$this->load->library('upload');
		$config['upload_path']	= 'assets/images/produk/';
		$config['allowed_types']= 'jpg|png';
		$config['max_size']		= 1000;
		$config['max_width']	= 1000;
		$config['max_height']	= 1000;
		$config['file_name']	= 'produk_'.round(microtime(true)*1000);

		$this->upload->initialize($config);
		if($this->upload->do_upload('foto')){
			return $this->upload->data('file_name');
		}else return false; 
	}

	function ajax_delete($id=NULL){
		if(isset($id)){
			$pro=$this->product->get_to_cart(array('id'=>$id));
			$nama=$pro->nama;
			if(file_exists('assets/images/produk/'.$pro->foto)&& $pro->foto)
				unlink('assets/images/produk/'.$pro->foto);
			Log_activity("Delete","Delete data produk $nama");
			$this->product->delete_by_id(array('id'=>$id));
			echo json_encode(array("status"=>TRUE));
		}else show_404();
	}
}
?>