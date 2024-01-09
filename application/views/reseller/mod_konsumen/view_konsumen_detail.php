      <div class='col-md-12'>
              <div class='box box-info'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Detail Data Konsumen</h3>
                  <a class='pull-right btn btn-default btn-sm' href='<?php echo $_SERVER['HTTP_REFERER'];?>'>Kembali</a>
                </div>
                <div class='box-body'>
                    <table class='table table-condensed table-bordered'>
                    <tbody>
                      <?php 
                       $koprov = $this->db->query("SELECT a.*, b.nama_provinsi FROM rb_kota a JOIN rb_provinsi b ON b.provinsi_id = a.provinsi_id WHERE a.kota_id='".$rows['kota_id']."'")->result_array();
                        if (trim($rows['foto'])==''){ 
                          $foto_user = 'blank_avatar.png'; 
                        } else{ 
                          $foto_user = $rows['foto']; 
                        } 
                      ?>
                      <tr bgcolor='#e3e3e3'><th rowspan='12' width='110px'><center><?php echo "<img style='border:1px solid #cecece; height:85px; width:85px' src='".base_url()."asset/foto_user/$foto_user' class='img-circle img-thumbnail'>"; ?></center></th></tr>
                      <tr><th width='130px' scope='row'>Username</th> <td><?php echo $rows['username']?></td></tr>
                      <tr><th scope='row'>Nama Lengkap</th> <td><?php echo $rows['nama_lengkap']?></td></tr>
                      <tr><th scope='row'>Alamat Email</th> <td><?php echo $rows['email']?></td></tr>
                      <tr><th scope='row'>No Hp</th> <td><?php echo $rows['no_hp']?></td></tr>
                      <tr><th scope='row'>Jenis Kelamin</th> <td><?php echo $rows['jenis_kelamin']?></td></tr>
                      <tr><th scope='row'>Tanggal Lahir</th> <td><?php echo tgl_indo($rows['tanggal_lahir']); ?></td></tr>
                      <tr><th scope='row'>Alamat Lengkap</th> <td><?php echo $rows['alamat_lengkap'].', '.$koprov[0]['nama_kota'].', '.$koprov[0]['nama_provinsi']?></td></tr>
                      <tr><th scope='row'>Tanggal Daftar</th> <td><?php echo tgl_indo($rows['tanggal_daftar']); ?></td></tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>