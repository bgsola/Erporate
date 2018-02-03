<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
* 
*/
class Login extends CI_Controller
{
	private $lvl="kasir";
	private $lvl1="pelayan";
	private $lvl2="admin";

	function __construct()
	{
		# code...
		parent::__construct();
		$this->load->model('User_model');
	}

	function index(){
		if(!empty($this->session->userdata('id'))){
			if($this->session->userdata('level')===$this->lvl1){
				redirect('pelayan/Dashboard');
			}elseif($this->session->userdata('level')===$this->lvl){
				redirect('kasir/Dashboard');
			}elseif($this->session->userdata('level')===$this->lvl2){
				redirect('admin/Dashboard');
			}else{
				session_destroy();
				redirect("Login");
			}
		}
		$data['title']="Login User";
		$this->load->view('Login_page',$data);
	}

	function check_login(){
		if($this->input->post("submit")){
			$this->form_validation->set_rules('email','email','required|valid_email');
			$this->form_validation->set_rules('password','password','required');
			if($this->form_validation->run()==TRUE){
				$data=array(
					'email'=>$this->input->post('email',TRUE),
					'password'=>sha1($this->input->post('password',TRUE))
					);

				$ret=$this->User_model->get($data);
				if($ret->num_rows()==1){
					foreach($ret->result() as $usr){
						$sess['logged_in']	= "sudah login";
						$sess['id']		= $usr->id;
						$sess['email']	= $usr->email;
						$sess['level']	= $usr->level;
						$this->session->set_userdata($sess);
					}
					Log_activity("Login",'User Login');
					$id=$this->session->userdata('id');
					if($this->session->userdata('level')==$this->lvl1){
						redirect('pelayan/Dashboard');
					}elseif($this->session->userdata('level')==$this->lvl){
						redirect('kasir/Dashboard');
					}elseif($this->session->userdata('level')==$this->lvl2){
						redirect('admin/Dashboard');
					}else{
						redirect('Login');
					}
				}else{
					$this->session->set_flashdata('message','<div class="alert alert-warning alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><i class="fa fa-warning"></i><strong> Warning:</strong> Username or password not found!!</div>');
					redirect('Login');
				}
			}else{
				$this->session->set_flashdata('message','<div class="alert alert-warning alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><i class="fa fa-warning"></i><strong> Warning:</strong> Username or password is not found!!</div>');
				redirect('Login');
			}
		}else show_404();
	}

	function logout(){
		if(!empty($this->session->userdata('id'))){
			Log_activity('Logout','User Logout');
			session_destroy();
			redirect('Login');
		}else show_404();
	}
}