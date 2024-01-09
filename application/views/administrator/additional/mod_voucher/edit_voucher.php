<?php 
    echo "<div class='col-md-12'>
              <div class='box box-info'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Edit Kode Voucher</h3>
                </div>
              <div class='box-body'>";
              $attributes = array('class'=>'form-horizontal','role'=>'form');
              echo form_open_multipart($this->router->fetch_class().'/edit_voucher/'.$this->uri->segment(3),$attributes); 
          echo "<div class='col-md-12'>
                  <table class='table table-condensed table-bordered'>
                  <tbody>
                    <tr>
                      <th width='180px' scope='row'>Nama Voucher <font color='red'>*</font></th>  
                      <td><input class='form-control' type='text' name='a' placeholder='Judul Voucher' value=\"".stripslashes($voucher[0]['judul_voucher'])."\" required></td>
                    </tr>
                    <tr>
                      <th scope='row'>Kode Voucher <font color='red'>*</font></th>                
                      <td>
                        <div class='input-group'>
                            <input type='text' class='form-control' name='b' id='b'  value=\"".stripslashes($voucher[0]['kode_voucher'])."\" required/>
                            <span class='input-group-addon'><a href='javascript:void(0)' class='btn-primary p-2 pt-0 pb-1' onclick='get_random();'>Random Char</a></span>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <th scope='row'>Type Voucher</th>               
                      <td><select class='form-control' name='type_voucher' onchange='type_vcr(this.value);'>";
                          if($voucher[0]['type']==1){
                            echo '<option value=\'1\'>Nilai Nominal</option><option value=\'2\'>Gratis Ongkir</option>';
                          } elseif($voucher[0]['type']==2){
                            echo '<option value=\'2\'>Gratis Ongkir</option><option value=\'1\'>Nilai Nominal</option>';
                          }
                      echo "</select></td>
                    </tr>";
                    if($voucher[0]['type']==1){
                      echo "<tr>
                              <th scope='row'>Nilai Voucher (Rp.) <font color='red'>*</font></th>               
                              <td><input class='form-control' type='number' name='c' placeholder='20000'  value=\"".stripslashes($voucher[0]['nilai_voucher'])."\" required></td>
                            </tr>";
                    } elseif($voucher[0]['type']==2){
                      echo "<tr id='nilai_voucher'>
                            <th scope='row'>Nilai Voucher (Rp.) <font color='red'>*</font></th>               
                            <td><input class='form-control' type='number' name='c' placeholder='20000' id='inp-nilai' required></td>
                          </tr><script>$('#nilai_voucher').hide();</script>";
                    }                    
                    echo "<tr>
                      <th scope='row'>Batas jumlah digunakan <font color='red'>*</font></th>                        
                      <td><input class='form-control' type='number' name='d' placeholder='12' value=\"".stripslashes($voucher[0]['batas_jumlah_digunakan'])."\" required></td>
                    </tr>
                    <tr>
                      <th scope='row'>Produk <font color='red'>*</font></th>                        
                      <td>
                        <select class='form-control' name='e'>";
                          if($produk != 0){
                            echo '<option value="'.$produk.'">'.$nama_produk.'</option>
                                  <option value="">Semua Produk</option>';
                          } else{
                            echo '<option value="">Semua Produk</option>';
                          }
                          foreach ($barang as $r){
                            echo '<option value="'.$r['id_produk'].'">'.$r['nama_produk'].' - ('.$r['username'].')</option>';
                          }
                        echo "</select>
                      </td>
                    </tr>
                  </tbody>
                  </table>
                </div>
              </div>
              <div class='box-footer'>
                    <input type='hidden' value='".$voucher[0]['kode_voucher']."' name='old_voucher'>
                    <button type='submit' name='edit_voucher' class='btn btn-info'>Tambahkan Voucher</button>
                    <a href='".base_url().$this->router->fetch_class()."/edit_voucher'><button type='button' class='btn btn-default pull-right'>Cancel</button></a>
                  </div>
            </div>";
?>
<script>
    function type_vcr(v){
      if(v==1){
        $('#nilai_voucher').show();
        $('#inp-nilai').prop('required',true);
      } else if(v==2){
        $('#nilai_voucher').hide();
        $('#inp-nilai').prop('required',false);
      }
    }
    function get_random(){
        var textArray = ['15','17','10','20'];
        var randomNumber = Math.floor(Math.random()*textArray.length);
        $('#b').val(rnd(textArray[randomNumber]));
    }
    function rnd(length) {
    var result           = '';
    var characters       = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz_0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
    }
</script>