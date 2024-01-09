<div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Data Konfirmasi Pembayaran Konsumen</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style='width:30px'>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tagihan</th>
                        <th>Ke Rekening</th>
                        <th>Nama Pengirim</th>
                        <th>Waktu Trx</th>
                        <th>Butki TF</th>
                        <th>Status</th>
                        <th>#</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                    $no = 1;
                    foreach ($record->result_array() as $row){
                    if ($row['proses']=='0'){ 
                        $proses = '<i class="text-danger">Pending</i>'; 
                      } elseif($row['proses']=='1'){ 
                        $proses = '<i class="text-success">Di Proses</i>';  
                      } else{ 
                        $proses = '<i class="text-info">Dibayar</i>'; 
                      } 
                    $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total FROM `rb_penjualan_detail` a where a.id_penjualan='$row[id_penjualan]'")->row_array();
                    echo "<tr>
                              <td>$no</td>
                              <td><a href='".base_url().$this->uri->segment(1)."/detail_penjualan/$row[id_penjualan]'>$row[kode_transaksi]</a></td>
                              <td>$row[total_transfer]</td>
                              <td>$row[nama_bank]</td>
                              <td>$row[nama_pengirim]</td>
                              <td>".tgl_indo($row['tanggal_transfer'])."</td>
                              <td><a href='".base_url()."reseller/download/$row[bukti_transfer]'>Download File</a></td>
                              <td>$proses</td>
                              <td>";
                              if($row['konf']==1){
                                echo "<a class='btn btn-success btn-xs' title='Konfirmasi Pesanan' href='/".$this->router->fetch_class()."/konfirmasi_kepelapak/".$row['id_konfirmasi_pembayaran']."' onclick='return confirm(\"Yakin Transaksi Sudah benar?,\");'>Konfirmasi Pesanan</a>";
                              } else{
                                echo '<a style="margin-right:3px;border-radius:0px;" class="btn btn-default btn-xs m-1" href="#" onclick="return confirm(\'Pesanan ini sudah di konfirmasi!\')">Di konfirmasi</a>';
                              }
                              echo "</td>
                          </tr>";
                      $no++;
                    }
                  ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>