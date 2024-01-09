            <div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Data Transaksi Penjualan / Orderan Konsumen</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped table-condensed">
                    <thead>
                      <tr>
                        <th style='width:40px'>No</th>
                        <th>Kode Transaksi</th>
                        <th>Reseller</th>
                        <th>Total Tagihan</th>
                        <th>No Rekening</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                  foreach($voucher as $row){
                      if($row['type']==1){
                        $nilai = rupiah($row['nilai_voucher']);
                      } elseif($row['type']==2){
                        $nilai = 'Ongkir Rp.0000';
                      }
                      $rekening = $this->db->query("SELECT * FROM `rb_rekening_reseller` WHERE id_reseller='".$row['resellerid']."' LIMIT 1");
                      if(($rekening->num_rows())>0){
                          $arek = $rekening->result_array()[0];
                          $rek = $arek['nama_bank'].", AN:".strtoupper($arek['pemilik_rekening'])." - ".$arek['no_rekening'];
                      } else{
                          $rek = 'Belum ada rekening';
                      }
                    echo "<tr>
                            <td>#</td>
                            <td>".$row['trx_id']."</td>
                            <td>".$row['nama_reseller']."</td>
                            <td>".$nilai."</td>
                            <td>".$rek."</td>
                            <td><center>
                                <a class='btn btn-danger btn-xs' title='Delete Data' href='#' onclick=\"return confirm('Apa anda yakin untuk hapus Data ini?')\"><span class='glyphicon glyphicon-remove'></span></a>

                                </center></td>
                          </tr>";
                  }
                  ?>
                  </tbody>
                </table>
              </div>
              </div>
            </div>
              