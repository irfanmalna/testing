<div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Data Pesanan terbaru / Orderan Terbaru</h3>
                </div>
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped table-condensed">
                    <thead>
                      <tr>
                        <th style='width:40px'>No</th>
                        <th>Kode Transaksi</th>
                        <th>Nama Konsumen</th>
                        <th>Kurir</th>
                        <th>Status</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                    $no = 1;
                    foreach ($record->result_array() as $row){
                    if ($row['proses']=='0'){ 
                      $proses = '<i class="text-danger">Pending</i>'; 
                      $status = 'Proses'; $icon = 'star-empty'; 
                      $ubah = 1; 
                    } elseif($row['proses']=='1'){ 
                      $proses = '<i class="text-success">Di Proses</i>'; 
                      $status = 'Pending'; 
                      $icon = 'star text-yellow'; 
                      $ubah = 0; 
                    } elseif($row['proses']=='3'){
                      $proses = '<i class="text-success">Diterima</i>'; 
                    } else{ 
                      $proses = '<i class="text-danger">Belum Diproses</i>'; 
                      $status = 'Proses'; $icon = 'star'; $ubah = 1; 
                    }
                    $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total FROM `rb_penjualan_detail` a where a.id_penjualan='$row[id_penjualan]'")->row_array();
                    $vcr = $this->db->query("SELECT b.*, a.nilai_voucher, a.type FROM kode_voucher_gnr b INNER JOIN kode_voucher a ON a.id = b.vcr_id WHERE `trx_id`='".$row['kode_transaksi']."'");
                    if(($vcr->num_rows())>0){
                      $ambil = $vcr->result_array()[0]; 
                      if($ambil['type']==1){
                        $total_tr = rupiah($total['total']+$row['ongkir']-$ambil['nilai_voucher']);  
                      } elseif($ambil['type']==2){
                        $total_tr = rupiah($total['total']);  
                      }
                    } else{
                      $total_tr = rupiah($total['total']+$row['ongkir']);
                    }
                    echo "<tr><td>$no</td>
                              <td>$row[kode_transaksi]</td>
                              <td><a href='".base_url()."reseller/detail_konsumen/$row[id_konsumen]'>".stripslashes($row[nama_lengkap])."</a></td>
                              <td><span style='text-transform:uppercase'>$row[kurir]</span> - $row[service]</td>
                              <td>$proses</td>
                              <td><center>
                                <a class='btn btn-success btn-xs' title='Detail Data' href='".base_url()."reseller/detail_penjualan/$row[id_penjualan]'><span class='glyphicon glyphicon-search'></span> Detail</a>";
                                if($row['proses'] !== '3'){
                                 echo "&nbsp;<a class='btn btn-primary btn-xs' title='$status Pesanan' href='".base_url()."reseller/proses_penjualan/$row[id_penjualan]/$ubah' onclick=\"return confirm('Apa anda yakin untuk ubah status jadi $status?')\"><span class='glyphicon glyphicon-$icon'></span></a>&nbsp;";
                                } else{
                                  echo '&nbsp;';
                                }
                                echo "<a class='btn btn-danger btn-xs' title='Delete Data' href='".base_url()."reseller/delete_penjualan/$row[id_penjualan]' onclick=\"return confirm('Apa anda yakin untuk hapus Data ini?')\"><span class='glyphicon glyphicon-remove'></span></a>
                              </center></td>
                          </tr>";
                      $no++;
                    }
                  ?>
                  </tbody>
                </table>
              </div>
              </div>
              </div>
              