<div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Daftar Voucher</h3>
                  <a class='pull-right btn btn-primary btn-sm' href='<?php echo base_url().$this->router->fetch_class(); ?>/tambah_voucher'>Tambahkan Data</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style='width:20px'>No</th>
                        <th>Judul Voucher</th>
                        <th>Kode Voucher</th>
                        <th>Nilai Voucher</th>
                        <th>Jumlah Digunakan</th>
                        <th>Terpakai</th>
                        <th>Tanggal Dibuat</th>
                        <th style='width:120px'>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 

                    foreach ($voucher as $row){
                      if($row[type]==1){
                        $nilai = 'Rp. '.rupiah($row[nilai_voucher]);
                      } else if($row[type]==2){
                        $nilai = 'Gratis Ongkir';
                      }
                        echo "<tr>
                            <td>#</td>
                            <td>".$row[judul_voucher]."</td>
                            <td>".$row[kode_voucher]."</td>
                            <td style='color:green;'>".$nilai."</td>
                            <td>".$row[batas_jumlah_digunakan]."</td>
                            <td>".$row[jumlah_digunakan]."</td>
                            <td>".$row[tgl]."</td>
                              <td><center>
                                <a class='btn btn-success btn-xs' title='Detail Data' href='".base_url().$this->router->fetch_class()."/detail_voucher/$row[id]'><span class='glyphicon glyphicon-search'></span> Detail</a>
                                <a class='btn btn-danger btn-xs' title='Delete Data' href='".base_url().$this->router->fetch_class()."/delete_voucher/".$row[id]."' onclick=\"return confirm('Apa anda yakin untuk hapus Data ini?')\"><span class='glyphicon glyphicon-remove'></span></a>
                              </center></td>
                          </tr>";
                    }
                  ?>
                  </tbody>
                </table>
              </div>