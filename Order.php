<?php
defined('BASEPATH')OR exit('No direct sript access allowed.');
/**
* 
*/
class Order extends CI_Controller
{
	private $lvl="pelayan";
	
	function __construct()
	{
		# code...
		parent::__construct();
		if(empty($this->session->userdata('id')) || $this->session->userdata('level')!=$this->lvl){
			redirect('Login');
		}
		$this->load->model('user/Produk_model','produk');
	}

	function index(){
		$this->load->model('User_model');
		$data['title']="Order";
		$data['breadcrumb']="Order";
		$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
		$data['product']=$this->produk->get_all();
		$data['ord']=$this->cart->contents();
		$data['items']=$this->cart->total_items();
		$data['totals']=$this->cart->total();
		$this->load->view('pelayan/Head',$data);
		$this->load->view('pelayan/Orderv',$data);
		$this->load->view('pelayan/Foot');
	}

	function Buy($id=NULL){
		if(isset($id)){
			$pro=$this->produk->get_to_cart(array('id'=>$id));
			if(!empty($pro) && $pro->status==='ready'){
				# code...
				$data=array(
					'id'	=> $id,
					'qty'	=> 1,
					'price'	=> $pro->harga,
					'name'	=> $pro->nama,
					'foto'	=> $pro->foto,
					'slug'	=> $pro->slug
					);
				$this->cart->insert($data);
				Log_activity("Order","Order produk");
				redirect('pelayan/Order');
			}else redirect('pelayan/Menu');
		}else show_404();
	}

	function update(){
		if($this->input->post('submit')){
			$cart_info=$_POST;
			foreach($cart_info as $id=>$cart){
				$rowid	= $cart['rowid'];
				$id 	= $cart['id'];
				$qty 	= $cart['qty'];
				$data=array(
					'rowid'	=> $rowid,
					'id'	=> $id,
					'qty'	=> $qty,
					);
				$this->cart->update($data);
			}
			redirect('pelayan/Order');
		}else redirect('pelayan/Menu');
	}

	function remove($rowid=NULL){
		if(isset($rowid)){
			$this->cart->remove($rowid);
			echo json_encode(array('status'=>TRUE));
		}else show_404();
	}

	function destroy_cart(){
		$this->cart->destroy();
		redirect('pelayan/Menu');
	}

	function productshow($slug=NULL){
		if(isset($slug)){			
			$data['product']=$this->produk->get_by_id(array('slug'=>$slug));
			if(!empty($data['product'])){
				$this->load->model('User_model');
				$data['title']="Product details";
				$data['breadcrumb']="Menu";
				$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
				$this->load->view('pelayan/Head',$data);
				$this->load->view('pelayan/Orderdetail',$data);
				$this->load->view('pelayan/Foot');
			}else redirect('pelayan/Menu/');
		}else show_404();
	}

	function Ordercomplete(){
		if($this->cart->total_items()!=0){
			$this->load->model('User_model');
			$data['title']="Order";
			$data['breadcrumb']="Order";
			$data['ord']=$this->cart->contents();
			$data['items']=$this->cart->total_items();
			$data['totals']=$this->cart->total();
			$data['dataLog']=$this->User_model->get(array('id'=>$this->session->userdata('id')));
			$this->load->view('pelayan/Head',$data);
			$this->load->view('pelayan/Ordercomplete',$data);
			$this->load->view('pelayan/Foot');
		}else redirect('pelayan/Menu');
	}

	function Ordersave(){
		if($this->cart->total_items()!=0){
			$order='active';
			$tgl=date('Y-m-d');
			$data=array(
				'id'		=> 'ERP'.$tgl.'-00',
				'id_pelayan'=> $this->session->userdata('id'),
				'nomormeja'	=> $this->input->post('nomormeja'),
				'total'		=> $this->input->post('total'),
				'status'	=> $order,
				'tanggal'	=> date('Y-m-d H:i:s')
				);
			$save_inv=$this->Invoice_model->save($data);
			if($save_inv){
				foreach ($this->cart->contents() as $cart) {
					# code...
					$data2=array(
						'id_invoice'=>$save_inv,
						'id_product'=>$cart['id'],
						'price'		=>$cart['price'],
						'quantity'	=>$cart['qty']
						);
					$this->Order_model->save($data2);
				}
				Log_activity("Order","Order dengan kode $save_inv");
				redirect('pelayan/Order/Vieworder');
			}else redirect('pelayan/Order');
		}else redirect('pelayan/Order');
	}
}