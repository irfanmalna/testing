            <div class="col-xs-12">  
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Banner Link</h3>
                  <a class='pull-right btn btn-primary btn-sm' href='<?php echo base_url().$this->router->fetch_class(); ?>/tambah_banner'>Tambahkan Data</a>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style='width:20px'>No</th>
                        <th>Judul</th>
                        <th>Link</th>
                        <th>Tgl Posting</th>
                        <th style='width:70px'>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                    $no = 1;
                    foreach ($record as $row){
                    $tgl_Posting = tgl_indo($row['tgl_posting']);
                    echo "<tr><td>$no</td>
                              <td>".stripslashes($row[judul])."</td>
                              <td><a target='_BLANK' href=\"".$row[url]."\">".stripslashes($row[url])."</a></td>
                              <td>$tgl_Posting</td>
                              <td><center>
                                <a class='btn btn-success btn-xs' title='Edit Data' href='".base_url().$this->router->fetch_class()."/edit_banner/$row[id_banner]'><span class='glyphicon glyphicon-edit'></span></a>
                                <a class='btn btn-danger btn-xs' title='Delete Data' href='".base_url().$this->router->fetch_class()."/delete_banner/$row[id_banner]' onclick=\"return confirm('Apa anda yakin untuk hapus Data ini?')\"><span class='glyphicon glyphicon-remove'></span></a>
                              </center></td>
                          </tr>";
                      $no++;
                    }
                  ?>
                  </tbody>
                </table>
              </div>