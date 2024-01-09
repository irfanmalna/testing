            <div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Daftar Semua Supplier</h3>
                  <a class='pull-right btn btn-primary btn-sm' href='<?php echo base_url().$this->router->fetch_class();?>/tambah_supplier'>Tambahkan Data</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style='width:20px'>No</th>
                        <th>Nama Supplier</th>
                        <th>Kontak Person</th>
                        <th>No Hp</th>
                        <th>No Telpon</th>
                        <th>Alamat Email</th>
                        <th style='width:120px'>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                    $no = 1;
                    foreach ($record as $row){
                    echo "<tr><td>$no</td>
                              <td>".stripslashes($row[nama_supplier])."</td>
                              <td>".stripslashes($row[kontak_person])."</td>
                              <td>$row[no_hp]</td>
                              <td>$row[no_telpon]</td>
                              <td>$row[alamat_email]</td>
                              <td><center>
                                <a class='btn btn-success btn-xs' title='Detail Data' href='".base_url().$this->router->fetch_class()."/detail_supplier/$row[id_supplier]'><span class='glyphicon glyphicon-search'></span> Detail</a>
                                <a class='btn btn-warning btn-xs' title='Edit Data' href='".base_url().$this->router->fetch_class()."/edit_supplier/$row[id_supplier]'><span class='glyphicon glyphicon-edit'></span></a>
                                <a class='btn btn-danger btn-xs' title='Delete Data' href='".base_url().$this->router->fetch_class()."/delete_supplier/$row[id_supplier]' onclick=\"return confirm('Apa anda yakin untuk hapus Data ini?')\"><span class='glyphicon glyphicon-remove'></span></a>
                              </center></td>
                          </tr>";
                      $no++;
                    }
                  ?>
                  </tbody>
                </table>
              </div>