<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Konfirmasi extends CI_Controller {
	function index(){
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$bukti = $_FILES['f']['name'];
			if(!empty($bukti)){
				$ex = @end(@explode('.', $bukti));
				$array_file = array('gif','jpg','png','jpeg');
				if(in_array($ex, $array_file)){
						$config['upload_path'] = 'asset/files/';
						$config['allowed_types'] = 'gif|jpg|png|jpeg';
						$config['max_size'] = '10000'; // kb
						$this->load->library('upload', $config);
						$this->upload->do_upload('f');
						$hasil=$this->upload->data();
						if ($hasil['file_name']==''){
							$data = array(
										'id_penjualan'=>$this->input->post('id'),
										'total_transfer'=>$this->input->post('b'),
										'id_rekening'=>$this->input->post('c'),
										'nama_pengirim'=>$this->input->post('d'),
										'tanggal_transfer'=>$this->input->post('e'),
										'konf'=>1,
										'status_read'=>1,
										'waktu_konfirmasi'=>date('Y-m-d H:i:s'));
							$this->model_app->insert('rb_konfirmasi_pembayaran_konsumen',$data);
						} else{
							$data = array(
										'id_penjualan'=>$this->input->post('id'),
										'total_transfer'=>$this->input->post('b'),
										'id_rekening'=>$this->input->post('c'),
										'nama_pengirim'=>$this->input->post('d'),
										'tanggal_transfer'=>$this->input->post('e'),
										'bukti_transfer'=>$hasil['file_name'],
										'konf'=>1,
										'status_read'=>1,
										'waktu_konfirmasi'=>date('Y-m-d H:i:s'));
							$this->model_app->insert('rb_konfirmasi_pembayaran_konsumen',$data);
						}
							$data1 = array('proses'=>'2');
							$idtr = $this->input->post('id');
							$where = array('id_penjualan' => $idtr);
							$this->model_app->update('rb_penjualan', $data1, $where);
							$iden = $this->model_app->view_where('identitas',array('id_identitas'=>'1'))->row_array();
							$cekres = $this->model_app->view_where('rb_penjualan',array('id_penjualan'=>$idtr))->row_array();
							$kons = $this->model_reseller->profile_konsumen($cekres['id_pembeli'])->row_array();
							$res = $this->model_app->view_where('rb_reseller',array('id_reseller'=>$cekres['id_penjual']))->row_array();							
							$email_tujuan = $iden['email'];
							$tglaktif = date("d-m-Y H:i:s");
							$subject      = "$iden[nama_website] - Orderan anda masuk";
							$message      = "<html><body>Halooo! <b>Admin</b> ... <br> Hari ini pada tanggal <span style='color:red'>$tglaktif</span> ada orderan yang harus di setujui.
								<br><table style='width:100%;'>
									<tr><td style='background:#337ab7; color:#fff; pading:20px' cellpadding=6 colspan='2'><b>Berikut Data Konsumen : </b></td></tr>
									<tr><td width='140px'><b>Nama Lengkap</b></td><td> : ".$kons['nama_lengkap']."</td></tr>
									<tr><td><b>Alamat Email</b></td>			<td> : ".$kons['email']."</td></tr>
									<tr><td><b>No Telpon</b></td>				<td> : ".$kons['no_hp']."</td></tr>
									<tr><td><b>Alamat</b></td>					<td> : ".$kons['alamat_lengkap']." </td></tr>
									<tr><td><b>Negara</b></td>					<td> : ".$kons['negara']." </td></tr>
									<tr><td><b>Provinsi</b></td>				<td> : ".$kons['propinsi']." </td></tr>
									<tr><td><b>Kabupaten/Kota</b></td>			<td> : ".$kons['kota']." </td></tr>
									<tr><td><b>Kecamatan</b></td>				<td> : ".$kons['kecamatan']." </td></tr>
								</table><br>
								<table style='width:100%;'>
									<tr><td style='background:#337ab7; color:#fff; pading:20px' cellpadding=6 colspan='2'><b>Berikut Data Reseller : </b></td></tr>
									<tr><td width='140px'><b>Nama Reseller</b></td>	<td> : ".$res['nama_reseller']."</td></tr>
									<tr><td><b>Alamat</b></td>					<td> : ".$res['alamat_lengkap']."</td></tr>
									<tr><td><b>No Telpon</b></td>				<td> : ".$res['no_telpon']."</td></tr>
									<tr><td><b>Email</b></td>					<td> : ".$res['email']." </td></tr>
									<tr><td><b>Keterangan</b></td>				<td> : ".$res['keterangan']." </td></tr>
								</table><br>
								No Transaksi : <b>".$cekres['kode_transaksi']."</b><br>
								Berikut Detail Data Orderan :
								<table style='width:100%;' class='table table-striped'>
									  <thead>
										<tr bgcolor='#337ab7'>
										  <th style='width:40px'>No</th>
										  <th width='47%'>Nama Produk</th>
										  <th>Harga</th>
										  <th>Qty</th>
										  <th>Berat</th>
										  <th>Subtotal</th>
										</tr>
									  </thead>
									  <tbody>";
									  $no = 1;
									  $belanjaan = $this->model_app->view_join_where('rb_penjualan_detail','rb_produk','id_produk',array('id_penjualan'=>$idtr),'id_penjualan_detail','ASC');
									  foreach ($belanjaan as $row){
									  $sub_total = ($row['harga_jual']*$row['jumlah']);
  									  $message .= "<tr bgcolor='#e3e3e3'><td>$no</td>
														<td>$row[nama_produk]</td>
														<td>".rupiah($row['harga_jual'])."</td>
														<td>$row[jumlah]</td>
														<td>".($row['berat']*$row['jumlah'])." Kg</td>
														<td>Rp ".rupiah($sub_total)."</td>
													</tr>";
										$no++;
									  }
									  $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total, sum(b.berat*a.jumlah) as total_berat FROM `rb_penjualan_detail` a JOIN rb_produk b ON a.id_produk=b.id_produk where a.id_penjualan='".$idtr."'")->row_array();
							$message .= "<tr bgcolor='lightgreen'>
											  <td colspan='5'><b>Total Harga</b></td>
											  <td><b>Rp ".rupiah($total['total'])."</b></td>
											</tr>
											<tr bgcolor='lightblue'>
											  <td colspan='5'><b>Total Berat</b></td>
											  <td><b>$total[total_berat] g</b></td>
											</tr>
									</tbody>
								  </table></body></html> \n";
							$this->email->from($iden['email'], $iden['nama_website']);
							$this->email->to($email_tujuan);
							$this->email->cc('');
							$this->email->bcc('');				
							$this->email->subject($subject);
							$this->email->message($message);
							$this->email->set_mailtype("html");
							$this->email->send();
							$config['protocol'] = 'sendmail';
							$config['mailpath'] = '/usr/sbin/sendmail';
							$config['charset'] = 'utf-8';
							$config['wordwrap'] = TRUE;
							$config['mailtype'] = 'html';
							$this->email->initialize($config);
						redirect('members/keranjang_detail/'.$this->input->post('id'));
				} else{
					redirect('members/keranjang_detail/'.$this->input->post('id'));
				}
			} else{
				redirect('members/keranjang_detail/'.$this->input->post('id'));
			}
		} else{
			$data['title'] = 'Konfirmasi Orderan anda';
			$data['description'] = description();
			$data['keywords'] = keywords();
			if (isset($_POST['submit1']) OR $_GET['kode']){
				if ($_GET['kode']!=''){
					$kode_transaksi = filter($this->input->get('kode'));
				} else{
					$kode_transaksi = filter($this->input->post('a'));
				}
				$row = $this->db->query("SELECT a.id_penjualan, b.id_reseller FROM `rb_penjualan` a jOIN rb_reseller b ON a.id_penjual=b.id_reseller where status_penjual='reseller' AND a.kode_transaksi='$kode_transaksi'")->row_array();
				$data['record'] = $this->model_app->view('rb_rekening');
				$data['total'] = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total, a.id_penjualan FROM `rb_penjualan_detail` a where a.id_penjualan='".$row['id_penjualan']."'")->row_array();
				$data['rows'] = $this->model_app->view_where('rb_penjualan',array('id_penjualan'=>$row['id_penjualan']))->row_array();
				$this->template->load(template().'/template',template().'/reseller/view_konfirmasi_pembayaran',$data);
			} else{
				$this->template->load(template().'/template',template().'/reseller/view_konfirmasi_pembayaran',$data);
			}
		}
	}

	function tracking(){
		if (isset($_POST['submit1']) OR $this->uri->segment(3)!=''){
			if ($this->uri->segment(3)!=''){
				$kode_transaksi = filter($this->uri->segment(3));
			} else{
				$kode_transaksi = filter($this->input->post('a'));
			}
			$cek = $this->model_app->view_where('rb_penjualan',array('kode_transaksi'=>$kode_transaksi));
			if ($cek->num_rows()>=1){
				$data['title'] = 'Tracking Order '.$kode_transaksi;
				$data['description'] = description();
				$data['keywords'] = keywords();
				$data['rows'] = $this->db->query("SELECT * FROM rb_penjualan a JOIN rb_konsumen b ON a.id_pembeli=b.id_konsumen JOIN rb_kota c ON b.kota_id=c.kota_id where a.kode_transaksi='$kode_transaksi'")->row_array();
				$data['record'] = $this->db->query("SELECT a.kode_transaksi, b.*, c.nama_produk, c.satuan, c.berat, c.produk_seo FROM `rb_penjualan` a JOIN rb_penjualan_detail b ON a.id_penjualan=b.id_penjualan JOIN rb_produk c ON b.id_produk=c.id_produk where a.kode_transaksi='".$kode_transaksi."'");
				$data['total'] = $this->db->query("SELECT a.kode_transaksi, a.kurir, a.service, a.proses, a.ongkir, sum(b.harga_jual*b.jumlah) as total, sum(b.diskon*b.jumlah) as diskon_total, sum(c.berat*b.jumlah) as total_berat FROM `rb_penjualan` a JOIN rb_penjualan_detail b ON a.id_penjualan=b.id_penjualan JOIN rb_produk c ON b.id_produk=c.id_produk where a.kode_transaksi='".$kode_transaksi."'")->row_array();
				$this->template->load(template().'/template',template().'/reseller/view_tracking_view',$data);
			}else{
				redirect('konfirmasi/tracking');
			}
		}else{
			$data['title'] = 'Tracking Order';
			$data['description'] = description();
			$data['keywords'] = keywords();
			$this->template->load(template().'/template',template().'/reseller/view_tracking',$data);
		}
	}
}
