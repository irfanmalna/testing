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
    echo "<div class='col-md-12'>    
              <div class='box box-info'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Tambah Produk Baru</h3>
                </div>
              <div class='box-body'>";
              $attributes = array('class'=>'form-horizontal','role'=>'form','name'=>'demo');
              echo form_open_multipart($this->router->fetch_class().'/tambah_produk',$attributes); 
          echo "<div class='col-md-12'>";
              if(isset($_SESSION['mes'])){
                echo "<div class='alert alert-warning'>".$_SESSION['mes']."</div>";
                unset($_SESSION['mes']);
              }          
          echo "<table class='table table-condensed table-bordered'>
                  <tbody>
                    <input type='hidden' name='id' value=''>
                    <tr>
                      <th scope='row'>Kategori</th>                   
                      <td><select name='a' class='form-control' onchange=\"showSub()\" required>
                            <option value='' selected>- Pilih Kategori Produk -</option>";
                            foreach ($record as $row){
                              echo "<option value='$row[id_kategori_produk]'>".stripslashes($row[nama_kategori])."</option>";
                            }
                    echo "</td>
                    </tr>
                    <tr>
                      <th scope='row'>Sub Kategori</th>                   
                      <td><select name='aa' class='form-control' id='sub_kategori_produk'>
                            <option value='' selected>- Pilih Sub Kategori Produk -</option>
                      </td>
                    </tr>
                    <tr>
                      <th width='130px' scope='row'>Nama Produk</th>  
                      <td><input type='text' class='form-control' name='b' required></td>
                    </tr>
                    <tr>
                      <th scope='row'>Satuan</th>                     
                      <td><input type='text' class='form-control' name='c'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Berat / Gram</th>                      
                      <td><input type='number' class='form-control' name='berat'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Harga Modal</th>                 
                      <td><input type='number' class='form-control' name='d'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Harga Reseller</th>             
                      <td><input type='number' class='form-control' name='e'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Harga Konsumen</th>             
                      <td><input type='number' class='form-control' name='f'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Diskon</th>                 
                      <td><input type='number' class='form-control' name='diskon'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Stok Awal</th>                 
                      <td><input type='number' class='form-control' name='stok'></td>
                    </tr>
                    <tr>
                      <th scope='row'>Keterangan</th>                 
                      <td><textarea id='editor1' class='form-control' name='ff'></textarea>
                      </td>
                    </tr>
                    <tr>
                      <th scope='row'>Foto Produk</th>
                      <td align='center' class='upload_area p-5'>
                        <div id='img-1' class='upload_inp_main'>
                          <div class='upload_inp' onclick='op(1);' id='pv-1'>
                              &nbsp;
                          </div>
                          <input type='file' id='fileupload-1' class='form-control hidden' name='userfile[]' accept='image/*' disabled>
                        </div>
                        <div id='img-2' class='upload_inp_main'>
                          <div class='upload_inp' onclick='op(2);' id='pv-2'>
                          &nbsp;
                          </div>
                          <input type='file' id='fileupload-2' class='form-control hidden' name='userfile[]' accept='image/*' disabled>
                        </div>
                        <div id='img-3' class='upload_inp_main'>
                          <div class='upload_inp' onclick='op(3);' id='pv-3'>
                          &nbsp;
                          </div>
                          <input type='file' id='fileupload-3' class='form-control hidden' name='userfile[]' accept='image/*' disabled>
                        </div>
                        <div id='img-4' class='upload_inp_main'>
                          <div class='upload_inp' onclick='op(4);' id='pv-4'>
                          &nbsp;
                          </div>
                          <input type='file' id='fileupload-4' class='form-control hidden' name='userfile[]' accept='image/*' disabled>
                        </div>
                        <div id='img-5' class='upload_inp_main'>
                          <div class='upload_inp' onclick='op(5);' id='pv-5'>
                          &nbsp;
                          </div>
                          <input type='file' id='fileupload-5' class='form-control hidden' name='userfile[]' accept='image/*' disabled>
                        </div>
                        <div id='img-6' class='upload_inp_main'>
                          <div class='upload_inp' onclick='op(6);' id='pv-6'>
                          &nbsp;
                          </div>
                          <input type='file' id='fileupload-6' class='form-control hidden' name='userfile[]' accept='image/*' disabled>
                        </div>
                        <div>&nbsp;</div>
                        Allowed File : .gif, jpg, png
                        <div id='dvPreview'></div> 
                        </td>
                    </tr>
                  </tbody>
                  </table>
                </div>
              </div>
              <div class='box-footer'>
                    <button type='submit' name='submit' class='btn btn-info'>Tambahkan</button>
                    <a href='/".$this->router->fetch_class()."/produk'><button type='button' class='btn btn-default pull-right'>Cancel</button></a>
                  </div>
            </div>";
?>
<script>
  function op(x){
    img = $('#fileupload-'+x);
    img.attr('disabled', false);
    if(img.click()){
      img.change(function() {
        gpvw(this,x);
      });
    }
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