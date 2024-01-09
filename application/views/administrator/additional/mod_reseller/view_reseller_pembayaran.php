            <div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Data Konfirmasi Pembayaran Reseller</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style='width:30px'>No</th>
                        <th>Kode Transaksi</th>
                        <th>Total Pembayaran</th>
                        <th>Rekening Reseller</th>
                        <th>Status Transaksi</th>
                        <th>Request Tanggal</th>
                        <th>#</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                    $no = 1;
                    foreach ($record->result_array() as $row){
                    $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total FROM `rb_penjualan_detail` a where a.id_penjualan='$row[id_penjualan]'")->row_array();
                    $rekening = $this->db->query("SELECT * FROM `rb_rekening_reseller` WHERE `id_reseller`='".$row['id_penjual']."'")->result_array()[0]; 
                    if(empty($rekening['no_rekening'])){
                      $rekeningq = 'Belum ada rekening';
                    } else{
                      $rekeningq = 'Bank: '.$rekening['nama_bank'].'; <br/>AN: '.$rekening['pemilik_rekening'].';<br/> NO rek: '.$rekening['no_rekening'].'';
                    }
                    if(($this->db->query("SELECT * FROM `trx_done` WHERE id_penjualan='".$row['id_penjualan']."'")->num_rows())>0){
                      $status = '<font color="green">Sudah dibayar</font>';
                    } else{
                      $status = '<font color="red">Belum dibayar</font>';
                    }
                    $ttl = ($total['total']+$row['ongkir']);
                    echo '<tr>
                              <td>'.$no.'</td>
                              <td>'.$row['kode_transaksi'].'</td>
                              <td>Rp '.rupiah($ttl).'</td>
                              <td>'.$rekeningq.'</td>
                              <td>'.$status.'</td>
                              <td>'.tgl_indo($row['waktu_transaksi']).'</td>
                              <td><a class="btn btn-primary btn-xs" title="Pending Data" href="/'.$this->router->fetch_class().'/pembayaran_reseller_act/'.$row['id_penjualan'].'" onclick="return confirm(\'Apakah Anda sudah membayarnya?\')">Ubah Status</a></td>
                          </tr>';
                      $no++;
                    }
                  ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>