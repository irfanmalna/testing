              <p class='sidebar-title text-danger produk-title'> Laporan Data Pesanan Anda</p>
              <?php 
                if ($this->uri->segment(3)=='success'){
                  echo "<div class='alert alert-success'><b>SUCCESS</b> - Terima kasih telah Melakukan Konfirmasi Pembayaran!</div>";
                }elseif($this->uri->segment(3)=='orders'){
                  echo "<div class='alert alert-success'><b>SUCCESS</b> - Orderan anda sukses terkirim, silahkan melakukan pembayaran ke rekening reseller pesanan anda dan selanjutnya lakukan konfirmasi pembayaran!</div>";
                }
              ?>
              <table id='example2' style='overflow-x:scroll; width:96%' class="table table-striped table-condensed">
                <thead>
                  <tr>
                    <th width="20px">No</th>
                    <th>Kode Transaksi</th>
                    <th>Nama Lapak</th>
                    <th>Subtotal</th>
                    <th>Ongkir</th>
                    <th>Status</th>
                    <th>Total + Ongkir</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $no = 1;
                    foreach ($record->result_array() as $row){
                      $btn_terima = '';
                    if ($row['proses']=='0'){ 
                        $proses = '<i class="text-danger">Pending</i>'; 
                    } elseif($row['proses']=='1'){ 
                        $proses = '<i class="text-success">Sedang Proses</i>'; 
                        $btn_terima = "<a class='btn btn-primary btn-xs m-1' style='border-radius:0px;' title='Konfirmasi terima barang' href='".base_url()."members/acc/$row[id_penjualan]' onclick='return confirm(\"Apakah anda yakin sudah terima barang ini?\")'>Terima Barang</a>";
                    } elseif($row['proses']=='3'){
                      $proses = '<i class="text-success">Diterima</i>';                     
                    } elseif(!($row['proses']==4)){ 
                      $proses = '<i class="text-info">Konfirmasi</i>'; 
                    }
                    $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total FROM `rb_penjualan_detail` a where a.id_penjualan='$row[id_penjualan]'")->row_array();
                    $vcr = $this->db->query("SELECT b.*, a.nilai_voucher, a.type FROM kode_voucher_gnr b INNER JOIN kode_voucher a ON a.id = b.vcr_id WHERE `trx_id`='".$row['kode_transaksi']."'");
                    if(($vcr->num_rows())>0){
                      $ambil = $vcr->result_array()[0]; 
                      if($ambil['type']==1){
                        $voucher = '<font color="red">[ - '.rupiah($ambil['nilai_voucher']).' VOUCHER ]</font>';
                        $total_tr = rupiah($total['total']+$row['ongkir']-$ambil['nilai_voucher']);  
                        $vonkir = "Rp ".rupiah($row['ongkir']);
                      } else{
                        $voucher = '';
                        $total_tr = rupiah($total['total']);  
                        $vonkir = "Voucher";
                      }
                    } else{
                      $voucher = '';
                      $total_tr = rupiah($total['total']+$row['ongkir']);
                      $vonkir = "Rp ".rupiah($row['ongkir']);
                    }
                    echo "<tr><td>$no</td>
                              <td><span class='text-success'>$row[kode_transaksi]</span></td>
                              <td><a href='".base_url()."members/detail_reseller/$row[id_reseller]'>".stripslashes($row[nama_reseller])."</a></td>
                              <td><span style='color:blue;'>Rp ".rupiah($total['total'])." ".$voucher." </span></td>
                              <td><i style='color:green;'><b style='text-transform:uppercase'>$row[kurir]</b> - ".$vonkir."</i></td>
                              <td>$proses</td>
                              <td style='color:red;'>Rp ".$total_tr."</td>
                              <td width='130px'>";
                if ($row['proses']=='0'){
                  echo "<a style='margin-right:3px;border-radius:0px;' class='btn btn-success btn-xs m-1' title='Bayar Tagihan' href='".base_url()."konfirmasi?kode=$row[kode_transaksi]'>Bayar Tagihan</a>";
                }else{
                  echo "<a style='margin-right:3px;border-radius:0px;' class='btn btn-default btn-xs m-1' href='#'  onclick=\"return confirm('Pembayaran ini sudah di konfirmasi!')\">Konfirmasi</2a>";
                }
              echo "    
              <a class='btn btn-info btn-xs' style='border-radius:0px;' title='Detail data pesanan' href='".base_url()."members/keranjang_detail/$row[id_penjualan]'><span class='glyphicon glyphicon-info-sign'></span></a>
              ".$btn_terima."
              </td>
              </tr>";
              $no++;
              }
              ?>
              </tbody>
              </table>