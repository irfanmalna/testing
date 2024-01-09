<script language="JavaScript" type="text/JavaScript">
  function showSub(){
    <?php
    $query = $this->db->query("SELECT * FROM rb_kategori_produk");
    foreach ($query->result_array() as $data) {
       $id_kategori_produk = $data['id_kategori_produk'];
       echo "if (document.demo.a.value == \"".$id_kategori_produk."\")";
       echo "{";
       $query_sub_kategori = $this->db->query("SELECT * FROM rb_kategori_produk_sub where id_kategori_produk='$id_kategori_produk'");
       $content = "document.getElementById('sub_kategori_produk').innerHTML = \"  <option value=''>- Pilih Sub Kategori Produk -</option>";
       foreach ($query_sub_kategori->result_array() as $data2) {
           $content .= "<option value='".$data2['id_kategori_produk_sub']."'>".$data2['nama_kategori_sub']."</option>";
       }
       $content .= "\"";
       echo $content;
       echo "}\n";
    }
    ?>
    }
</script>

<?php 
    echo "<div class='col-md-12'>";
            if(isset($_SESSION['mes'])){
              echo "<div class='alert alert-warning'>".$_SESSION['mes']."</div>";
              unset($_SESSION['mes']);
            }          
             echo "<div class='box box-info'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Edit Produk Terpilih</h3>
                </div>
              <div class='box-body'>";
              $attributes = array('class'=>'form-horizontal','role'=>'form','name'=>'demo');
              echo form_open_multipart($this->router->fetch_class().'/edit_produk',$attributes);
              $disk = $this->model_app->edit('rb_produk_diskon',array('id_produk'=>$rows['id_produk']))->row_array();
          echo "<div class='col-md-12'>
                  <table class='table table-condensed table-bordered'>
                  <tbody>
                    <input type='hidden' name='id' value='$rows[id_produk]'>
                    <tr><th scope='row'>Kategori</th>                   
                    <td><select name='a' class='form-control' onchange=\"showSub()\" required>
                                                                            <option value='' selected>- Pilih Kategori Produk -</option>";
                                                                            foreach ($record as $row){
                                                                              if ($rows['id_kategori_produk']==$row['id_kategori_produk']){
                                                                                echo "<option value='$row[id_kategori_produk]' selected>$row[nama_kategori]</option>";
                                                                              }else{
                                                                                echo "<option value='$row[id_kategori_produk]'>$row[nama_kategori]</option>";
                                                                              }
                                                                            }
                    echo "</select></td></tr>
                    <tr><th scope='row'>Sub Kategori</th>                   
                    <td><select name='aa' class='form-control' id='sub_kategori_produk'>
                                                                                <option value='' selected>- Pilih Sub Kategori Produk -</option>";
                                                                                $sub_kategori_produk = $this->db->query("SELECT * FROM rb_kategori_produk_sub");
                                                                                foreach ($sub_kategori_produk->result_array() as $row){
                                                                                  if ($rows['id_kategori_produk_sub']==$row['id_kategori_produk_sub']){
                                                                                    echo "<option value='$row[id_kategori_produk_sub]' selected>$row[nama_kategori_sub]</option>";
                                                                                  }else{
                                                                                    echo "<option value='$row[id_kategori_produk_sub]'>$row[nama_kategori_sub]</option>";
                                                                                  }
                                                                                }
                    echo "</select></td></tr>
                    <tr><th width='130px' scope='row'>Nama Produk</th>  <td><input type='text' class='form-control' name='b' value=\"".stripslashes($rows[nama_produk])."\" required></td></tr>
                    <tr><th scope='row'>Satuan</th>                     <td><input type='text' class='form-control' name='c' value='$rows[satuan]'></td></tr>
                    <tr><th scope='row'>Berat / Gram</th>                      <td><input type='number' class='form-control' name='berat' value='$rows[berat]'></td></tr>
                    <tr><th scope='row'>Harga Modal</th>                 <td><input type='number' class='form-control' name='d' value='$rows[harga_beli]'></td></tr>
                    <tr><th scope='row'>Harga Reseller</th>             <td><input type='number' class='form-control' name='e' value='$rows[harga_reseller]'></td></tr>
                    <tr><th scope='row'>Harga Konsumen</th>             <td><input type='number' class='form-control' name='f' value='$rows[harga_konsumen]'></td></tr>
                    <tr><th scope='row'>Diskon</th>                 <td><input type='number' class='form-control' name='diskon' value='$disk[diskon]'></td></tr>
                    <tr><th scope='row'>Keterangan</th>                 <td><textarea  id='editor1' class='form-control' name='ff'>$rows[keterangan]</textarea></td></tr>
                    <tr><th scope='row'>Foto Produk</th>                     
                    <td align='center' class='upload_area p-5'>";
                    $p = explode(';', $rows[gambar]);
                    if(!($p[0]=='')){
                      $i1 = 'style=\'background-image:url('.base_url().'asset/foto_produk/'.$p[0].');background-size:cover;border:1px solid #bbb;\'';
                    }
                    if(!($p[1]=='')){
                      $i2 = 'style=\'background-image:url('.base_url().'asset/foto_produk/'.$p[1].');background-size:cover;border:1px solid #bbb;\'';
                    }
                    if(!($p[2]=='')){
                      $i3 = 'style=\'background-image:url('.base_url().'asset/foto_produk/'.$p[2].');background-size:cover;border:1px solid #bbb;\'';
                    }
                    if(!($p[3]=='')){
                      $i4 = 'style=\'background-image:url('.base_url().'asset/foto_produk/'.$p[3].');background-size:cover;border:1px solid #bbb;\'';
                    }
                    if(!($p[4]=='')){
                      $i5 = 'style=\'background-image:url('.base_url().'asset/foto_produk/'.$p[4].');background-size:cover;border:1px solid #bbb;\'';
                    }
                    if(!($p[5]=='')){
                      $i6 = 'style=\'background-image:url('.base_url().'asset/foto_produk/'.$p[5].');background-size:cover;border:1px solid #bbb;\'';
                    }                          
                    echo "<div id='img-1' class='upload_inp_main'>
                        <div class='upload_inp' onclick='op(1);' id='pv-1' $i1>
                            &nbsp;
                         </div>
                         <a href='javascript:void(0)' onclick='delf(1);' class='";if(!$i1){echo'delete_icon_file_hidden';} else{echo 'delete_icon_file';}echo"'>&nbsp;</a>
                         <input type='file' id='fileupload-1' class='form-control hidden' name='userfile[0]' accept='image/*'>
                      </div>
                      <div id='img-2' class='upload_inp_main'>
                        <div class='upload_inp' onclick='op(2);' id='pv-2' $i2>
                        &nbsp;
                        </div>
                        <a href='javascript:void(0)' onclick='delf(2);' class='";if(!$i2){echo'delete_icon_file_hidden';} else{echo 'delete_icon_file';}echo"'>&nbsp;</a>
                        <input type='file' id='fileupload-2' class='form-control hidden' name='userfile[1]' accept='image/*'>
                      </div>
                      <div id='img-3' class='upload_inp_main'>
                        <div class='upload_inp' onclick='op(3);' id='pv-3' $i3>
                        &nbsp;
                        </div>
                        <a href='javascript:void(0)' onclick='delf(3);' class='";if(!$i3){echo'delete_icon_file_hidden';} else{echo 'delete_icon_file';}echo"'>&nbsp;</a>
                        <input type='file' id='fileupload-3' class='form-control hidden' name='userfile[2]' accept='image/*'>
                      </div>
                      <div id='img-4' class='upload_inp_main'>
                        <div class='upload_inp' onclick='op(4);' id='pv-4' $i4>
                        &nbsp;
                        </div>
                        <a href='javascript:void(0)' onclick='delf(4);' class='";if(!$i4){echo'delete_icon_file_hidden';} else{echo 'delete_icon_file';}echo"'>&nbsp;</a>
                        <input type='file' id='fileupload-4' class='form-control hidden' name='userfile[3]' accept='image/*'>
                      </div>
                      <div id='img-5' class='upload_inp_main'>
                        <div class='upload_inp' onclick='op(5);' id='pv-5' $i5>
                        &nbsp;
                        </div>
                          <a href='javascript:void(0)' onclick='delf(5);' class='";if(!$i5){echo'delete_icon_file_hidden';} else{echo 'delete_icon_file';}echo"'>&nbsp;</a>                              
                        <input type='file' id='fileupload-5' class='form-control hidden' name='userfile[4]' accept='image/*'>
                      </div>
                      <div id='img-6' class='upload_inp_main'>
                        <div class='upload_inp' onclick='op(6);' id='pv-6' $i6>
                        &nbsp;
                        </div>
                        <a href='javascript:void(0)' onclick='delf(6);' class='";if(!$i6){echo'delete_icon_file_hidden';} else{echo 'delete_icon_file';}echo"'>&nbsp;</a>
                        <input type='file' id='fileupload-6' class='form-control hidden' name='userfile[5]' accept='image/*'>
                      </div>
                      <div>&nbsp;</div>
                      Ketuk foto untuk mengubahnya, File Di izinkan : .gif, jpg, png
                      <div id='dvPreview'></div> ";                    
              echo "</td>
                    </tr>
                  </tbody>
                  </table>
                </div>
              </div>
              <div class='box-footer'>
                <input type='hidden' name='produk_seo' value='".$rows[produk_seo]."'>
                <input type='hidden' name='produk_id' value='".$rows[id_produk]."'>
                <input type='hidden' name='harga_reseller' value='".$rows[harga_reseller]."'>
                <input type='hidden' name='gambar' value='".$rows[gambar]."'>
                <input type='hidden' name='delete' id='delete'>
                    <button type='submit' name='submit' class='btn btn-info'>Update</button>
                    <a href='/".$this->router->fetch_class()."/produk'><button type='button' class='btn btn-default pull-right'>Cancel</button></a>
                  </div>
            </div>";
?>
<script>
  function op(x){
    img = $('#fileupload-'+x);
    if(img.click()){
      img.change(function() {
      gpvw(this,x);
      });
    }
  }
  function delf(id){
    $('#pv-'+id).removeAttr('style');
    $('#delete').val($('#delete').val()+(id-1)+';');
  }
  function gpvw(input,id){
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
      $('#pv-'+id).css({'background-image': 'url(' + e.target.result + ')', 'background-size': 'cover', 'border': '1px solid #bbb'});
      }  
      reader.readAsDataURL(input.files[0]);
    }
    }
</script>