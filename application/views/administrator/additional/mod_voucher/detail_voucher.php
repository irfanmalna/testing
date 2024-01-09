<div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Detail Voucher</h3>
                  <a class='pull-right btn btn-default btn-sm' href='<?php echo base_url().$this->router->fetch_class(); ?>/voucher'>Kembali</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table class='table table-condensed table-bordered'>
                  <tbody>
                  
                    <tr>
                      <th width='180px' scope='row'>Nama Voucher</th>  
                      <td><?php echo $rows[0]['judul_voucher']; ?></td>
                    </tr>
                    <tr>
                      <th scope='row'>Kode Voucher</th>                 
                      <td><?php echo $rows[0]['kode_voucher']; ?></td>
                    </tr>
                    <tr>
                      <th scope='row'>Type Voucher</th>               
                      <td><?php if($rows[0][type]==1){echo 'Nilai Nominal';}else{echo "Gratis Ongkir";} ?></td>
                    </tr>
                    <?php if($rows[0][type]==1){
                            echo '<tr id="nilai_voucher_show">
                                <th scope="row">Nilai Voucher</th>               
                                <td>Rp. '.rupiah($rows[0][nilai_voucher]).'</td>
                                  </tr>';
                          }
                    ?>
                    <tr>
                      <th scope='row'>Jumlah Batas Digunakan</th>               
                      <td><?php echo $rows[0][batas_jumlah_digunakan]; ?></td>
                    </tr>
                    <tr>
                      <th scope='row'>Terpakai</th>               
                      <td><?php echo $rows[0][jumlah_digunakan]; ?></td>
                    </tr>
                    <tr>
                      <th scope='row'>Produk</th>               
                      <td><?php if($data_produk==0){echo'Untuk Semua Produk';} else{echo $nama_produk;} ?></td>
                    </tr>
                    <tr>
                      <th scope='row'>Tanggal Dibuat</th>               
                      <td><?php echo $rows[0][tgl]; ?></td>
                    </tr>
                  </tbody>
                  </table>
              </div>