<p class='sidebar-title text-danger produk-title'> Detail Pesanan Anda</p>
<div class="col-sm-8">
      <table class="table table-striped table-condensed">
          <tbody>
        <?php 
          $no = 1;
          foreach ($record as $row){
          $ex = explode(';', $row['gambar']);
          if (trim($ex[0])==''){ $foto_produk = 'no-image.png'; }else{ $foto_produk = $ex[0]; }
          $sub_total = ($row['harga_jual']*$row['jumlah'])-$row['diskon'];
          echo "<tr><td>$no</td>
                    <td width='70px'><img style='border:1px solid #cecece; width:60px' src='".base_url()."asset/foto_produk/$foto_produk'></td>
                    <td><a style='color:#ab0534' href='".base_url()."produk/detail/$row[produk_seo]'><b>$row[nama_produk]</b></a>
                        <br>Qty. <b>$row[jumlah]</b>, Harga. Rp ".rupiah($row['harga_jual']-$row['diskon'])." / $row[satuan], 
                        <br>Berat. <b>".($row['berat']*$row['jumlah'])." Gram</b></td>
                    <td>Rp ".rupiah($sub_total)."</td>
                </tr>";
            $no++;
          }
          $detail = $this->db->query("SELECT * FROM rb_penjualan where id_penjualan='".$this->uri->segment(3)."'")->row_array();
          $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total, sum(b.berat*a.jumlah) as total_berat FROM `rb_penjualan_detail` a JOIN rb_produk b ON a.id_produk=b.id_produk where a.id_penjualan='".$this->uri->segment(3)."'")->row_array();
          if ($rows['proses']=='0'){ $proses = '<i class="text-danger">Pending</i>'; $status = 'Proses'; }elseif($rows['proses']=='1'){ $proses = '<i class="text-success">Proses</i>'; }else{ $proses = '<i class="text-info">Konfirmasi</i>'; }
          $vcr = $this->db->query("SELECT b.*, a.nilai_voucher, a.type FROM kode_voucher_gnr b INNER JOIN kode_voucher a ON a.id = b.vcr_id WHERE `trx_id`='".$detail['kode_transaksi']."'");
          if(($vcr->num_rows())>0){
            $ambil = $vcr->result_array()[0]; 
            if($ambil['type']==2){
              $terbilang = '';
              $onkir = 'Voucher Gratis Ongkir';  
              $totals = $total['total'];
              $totalv = $total['total'];
              $vn = '';
            } else{
              $terbilang = '('.terbilang($detail['ongkir']).')';
              $onkir = 'Rp. '.rupiah($detail['ongkir']);  
              $totals = ($total['total']+$detail['ongkir']-$ambil['nilai_voucher']);
              $totalv = $total['total'];
              $vn = "<tr>
                      <td colspan='3'><b>Voucher </b></td>
                      <td><b>- Rp ".rupiah($ambil['nilai_voucher'])."</b></td>
                    </tr>";
            }
          } else{
            $terbilang = '('.terbilang($detail['ongkir']).')';
            $onkir = 'Rp. '.rupiah($detail['ongkir']);
            $totals = $total['total']+$detail['ongkir'];
            $totalv = $total['total'];
            $vn = '';
          }

          echo "<tr class='success'>
                  <td colspan='3'><b>Berat</b> <small><i class='pull-right'>(".terbilang($total['total_berat'])." Gram)</i></small></td>
                  <td><b>$total[total_berat] Gram</b></td>
                </tr>";

                echo "<tr>
                  <td colspan='3'><b><span style='text-transform:uppercase'>$detail[kurir]</span> - $detail[service]</b> <small><i class='pull-right'>".$terbilang."</i></small></td>
                  <td><b>".$onkir."</b></td>
                </tr>";


          echo  "<tr>
                  <td colspan='3'><b>Total </b> <small><i class='pull-right'>(".terbilang($totalv)." Rupiah)</i></small></td>
                  <td><b>Rp ".rupiah($totalv)."</b></td>
                </tr>

                ".$vn."

                <tr>
                  <td style='color:Red' colspan='3'><b>Subtotal </b> <small><i class='pull-right'>(".terbilang($totals)." Rupiah)</i></small></td>
                  <td style='color:Red'><b>Rp ".rupiah($totals)."</b></td>
                </tr>
                
                <tr><td align=center colspan='4'><b>$proses</b></td></tr>

        </tbody>
      </table>";
      
      $cek = $this->db->query("SELECT * FROM `trx_dropship` WHERE `trx_id`='".$detail['kode_transaksi']."'");
      if(($cek->num_rows())>0){
        $ambil = $cek->result_array()[0];
        $al = $this->db->query("SELECT a.*, b.nama_provinsi FROM rb_kota a JOIN rb_provinsi b ON b.provinsi_id=a.provinsi_id WHERE kota_id='".$ambil['kota_id']."'")->result_array()[0];
        echo '<h3>Dropship</h3> 
        <hr/>
        <table id="tb">
          <tr><td>Atas Nama</td><td> : '.$ambil['atas_nama'].'</td></tr>
          <tr><td>Alamat Lengkap</td><td> : '.ucwords($ambil['alamat_lengkap'].', '.$al['nama_kota'].', '.$al['nama_provinsi']).'</td></tr>
        </table>';
  
      }
?>
</div>
<style>
  #tb {
    width:100%;
  }
  #tb tr td {
    font-size:16px;
    padding:8px;
  }
</style>
<div class="col-sm-4 colom44">
  <?php $res = $this->db->query("SELECT a.*, b.nama_kota, c.nama_provinsi FROM rb_reseller a JOIN rb_kota b ON a.kota_id=b.kota_id 
                JOIN rb_provinsi c ON b.provinsi_id=c.provinsi_id
                  where a.id_reseller='$rows[id_reseller]'")->row_array(); ?>
  <table class='table table-condensed'>
  <tbody>
    <tr class='alert alert-info'><th scope='row' style='width:90px'>Pengirim</th> <td><?php echo $res['nama_reseller']?></td></tr>
    <tr class='alert alert-info'><th scope='row'>No Telpon</th> <td><?php echo $res['no_telpon']; ?></td></tr>
    <tr class='alert alert-info'><th scope='row'>Alamat</th> <td><?php echo $res['alamat_lengkap'].', '.$res['nama_kota'].', '.$res['nama_provinsi']; ?></td></tr>
  </tbody>
  </table>

  <?php $usr = $this->db->query("SELECT a.*, b.nama_kota, c.nama_provinsi FROM rb_konsumen a JOIN rb_kota b ON a.kota_id=b.kota_id 
                JOIN rb_provinsi c ON b.provinsi_id=c.provinsi_id
                  where a.id_konsumen='".$this->session->id_konsumen."'")->row_array(); ?>
  <table class='table table-condensed'>
  <tbody>
    <tr class='alert alert-danger'><th scope='row' style='width:90px'>Penerima</th> <td><?php echo $usr['nama_lengkap']?></td></tr>
    <tr\><th scope='row'>No Telpon</th> <td><?php echo $usr['no_hp']; ?></td></tr>
    <tr><th scope='row'>Alamat</th> <td><?php echo $usr['alamat_lengkap'].', '.$usr['nama_kota'].', '.$usr['nama_provinsi']; ?></td></tr>
  </tbody>
  </table>
  <hr>
</div>
  <hr>