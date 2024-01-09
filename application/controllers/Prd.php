<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prd extends CI_Controller {

    public function index(){
        $id = $this->uri->segment(2);
        if(preg_match("/^[A-Z0-9]+$/", $id)){
            $this->load->model('Model_utama');
            $a = $this->model_utama->view_where('kode_produk',array('kode'=>$id));
            $cek = $a->num_rows();
            if($cek>0){
                $gid = $a->row_array();
                $b = $this->model_utama->view_where('rb_produk',array('id_produk'=>$gid['produk_id']))->row_array();
                redirect(base_url().'produk/detail/'.$b['produk_seo']); 
            } else{
                redirect(base_url());
            }
        } else{
            redirect(base_url());
        }
    }

}
