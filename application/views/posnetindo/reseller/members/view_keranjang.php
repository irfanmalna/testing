<p class='sidebar-title text-danger produk-title'> Berikut Data Pesanan anda</p>
<div class='col-md-8'>
<?php   
        echo "<form action='".base_url()."members/selesai_belanja' method='POST'>";
        echo $error_reseller; 
        if($this->session->idp == ''){
          echo "<center style='padding:10%'><i class='text-danger'>Maaf, Keranjang belanja anda saat ini masih kosong,...</i><br><a class='btn btn-warning btn-sm' href='".base_url()."members/reseller'>Klik Disini Untuk mulai Belanja!</a></center>";
        } else {
      ?>
      <?php 
        echo "<a class='btn btn-success btn-sm' href='".base_url()."members/produk_reseller/$rows[id_reseller]'>Lanjut Belanja</a> <a class='btn btn-danger btn-sm' href='".base_url()."members/batalkan_transaksi' onclick=\"return confirm('Apa anda yakin untuk Batalkan Transaksi ini?')\">Batalkan Transaksi</a> <a class='btn btn-info btn-sm' href='#' onclick=\"get_voucher();\">Masukkan Kode Voucher</a>"; 
      ?>
      <div style="clear:both"><br></div>
      <table class="table table-striped table-condensed">
          <tbody>
        <?php 
          if(isset($_SESSION['voucher'])){
            unset($_SESSION['voucher']);       
          }
          $no = 1;
          foreach ($record as $row){
          $ex = explode(';', $row['gambar']);
          if (trim($ex[0])==''){ $foto_produk = 'no-image.png'; }else{ $foto_produk = $ex[0]; }
          $sub_total = ($row['harga_jual']*$row['jumlah'])-$row['diskon'];
          echo "<tr><td>$no</td>
                    <td width='70px'><img style='border:1px solid #cecece; width:60px' src='".base_url()."asset/foto_produk/$foto_produk'></td>
                    <td><a style='color:#ab0534' href='".base_url()."produk/detail/$row[produk_seo]'><b>$row[nama_produk]</b></a>
                        <br>Qty. <b>$row[jumlah]</b>, Harga. Rp ".rupiah($row['harga_jual']-$row['diskon'])." / $row[satuan], 
                        <br>Berat. <b>".($row['berat']*$row['jumlah'])." Gram</b></td>
                    <td>Rp ".rupiah($sub_total)."</td>
                    <td width='30px'><a class='btn btn-danger btn-xs' title='Delete' href='".base_url()."members/keranjang_delete/$row[id_penjualan_detail]'><span class='glyphicon glyphicon-remove'></span></a></td>
                </tr>";
            $no++;
          }

          $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total, sum(b.berat*a.jumlah) as total_berat FROM `rb_penjualan_detail` a JOIN rb_produk b ON a.id_produk=b.id_produk where a.id_penjualan='".$this->session->idp."'")->row_array();
          echo "<tr class='success'>
                  <td colspan='3'><b>Total Berat</b></td>
                  <td><b>$total[total_berat] Gram</b></td>
                  <td></td>
                </tr>
        </tbody>
      </table>
      <div class='col-md-4 pull-right'>
        <center>Total Bayar <br><h2 id='totalbayar'></h2>   
        <button type='submit' name='submit' id='oksimpan' class='btn btn-success btn-flat btn-sm' style='display: none'>Lakukan Pembayaran</button>
        </center>
      </div>";
      $ket = $this->db->query("SELECT * FROM rb_keterangan where id_reseller='".$rows['id_reseller']."'")->row_array();
      $diskon_total = '0';
      ?>
      <input type="hidden" name="total" id="total" value="<?php echo $total['total']; ?>"/>
      <input type="hidden" name="ongkir" id="ongkir" value="0"/>
      <input type="hidden" name="tarif_original" id="tarif_original" value="0"/>
      <input type="hidden" name="berat" value="<?php echo $total['total_berat']; ?>"/>
      <input type="hidden" name="diskonnilai" id="diskonnilai" value="<?php echo $diskon_total; ?>"/>
      <div class="form-group">
          <label class="col-sm-2 control-label" for="">Pilih Kurir</label>
          <div class="col-md-10">
                      <?php       
                      $kurir=array('jne','pos','tiki','jnt');
                      foreach($kurir as $rkurir){
                      ?>          
                      <label class="radio-inline">
                      <input type="radio" name="kurir" class="kurir" value="<?php echo $rkurir;?>"/> <?php if($rkurir=='jnt'){echo 'J&T';}else{echo strtoupper($rkurir);} ?>
                      </label>
                  <?php
              }
              ?>
              <label class="radio-inline"><input type="radio" name="kurir" class="kurir" value="cod"/> COD (Cash on delivery)</label>
          </div>
          <div class="row" id="loading_ongkir" style="display:none;">
            <div class="col-md-10 p-3 text-center">
              <div class="spiner-sm"></div>
            </div>
          </div>
      </div>
      <div id="kuririnfo" style="display: none;">
          <div class="form-group">
              <div class="col-md-12">
                  <div class='alert alert-info' style='padding:5px; border-radius:0px; margin-bottom:0px'>Service</div>
                  <p class="form-control-static" id="kurirserviceinfo"></p>
              </div>
          </div>
      </div>
      <div class="overlay" id="overley">
        <div class="spiner_center">
          <div class="spiner"></div>
          <br/><h2 id="overlay_msg"></h2>
        </div>
      </div>
      <input type="hidden" id="vgrt" value="0">

<script>
function get_voucher(){
  var s = prompt('Masukkan Voucher');
  if((s !='') && (s != null)){
      $('#overley').show();
      $('#overlay_msg').html('CHECKING VOUCHER');
      var data = new FormData();
      data.append("v", s);
      data.append("tid", "<?php echo $record[0]['id_penjualan'];?>");
      <?php
      foreach($record as $r){
        echo 'data.append("pid[]", "'.$r['id_produk'].'");';
      }
      ?>
      $.ajax({
        url: "/members/voucher_post",
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        type: "POST",
        success: function(r) {
          if(r.status==2){
            var total = <?php echo $total['total']; ?>;
            var ongkir=$("#ongkir").val();
            if(total < r.nilai){
              $('#total').val('0');
              $("#totalbayar").html('Rp 0,00');
            } else{
              var bayar=(parseFloat($("#total").val())-parseFloat(r.nilai));
              $('#total').val(bayar);
              $("#totalbayar").html(toDuit(bayar));
            }
            if(r.type==2){
              $('#vgrt').val('1');
              $('#kuririnfo').hide();
            }
          } else{
            alert(r.mes);
          }
        },
        complete: function(){
          $('#overley').hide();  
        },
        error: function(data) {
          alert('Maaf, Terjadi Gangguan!');
          $('#overley').hide();
          }
        });   
  }
}
$(document).ready(function(){
$(".kurir").each(function(o_index,o_val){
    $(this).on("change",function(){
        $('#loading_ongkir').show();
        var did=$(this).val();
        var berat="<?php echo $total['total_berat']; ?>";
        var kota="<?php echo $rowsk['kota_id']; ?>";
        t = false;
        if($('#dropship').is(':disabled')){
          data = "kurir="+did+"&berat="+berat+"&kota="+kota;
          t = true;
        } else{
          city = $('#city :selected').val();
          if(city == ''){
            $('#loading_ongkir').hide();
            t = false;
            alert('Pilih Kota dropship!');
            $('input[name="kurir"]').prop('checked',false);
          } else{
            data = "d=1&kurir="+did+"&berat="+berat+"&kota="+city;
            t = true;
          }
        }
        if(t){
          $.ajax({
            method: "get",
            dataType:"html",
            url: "<?php echo base_url(); ?>produk/kurirdata",
            data: data,
            beforeSend:function(){
              $("#oksimpan").hide();
            }
          })
          .done(function( x ) {       
              $("#kurirserviceinfo").html(x);
              $("#kuririnfo").show();
              $('#loading_ongkir').hide();
          })
          .fail(function(  ) {
              $("#kurirserviceinfo").html("");
              $("#kuririnfo").hide();
              $('#loading_ongkir').hide();
          });
        }

    });
});
$("#diskon").html(toDuit(0));
hitung();
});
function hitung(){
    var diskon=$('#diskonnilai').val();
    var total=$('#total').val();
    var ongkir=$("#ongkir").val();
    var bayar=(parseFloat(total)+parseFloat(ongkir));
    if($('#vgrt').val() != 1){
      if(parseFloat(ongkir) > 0){
          $("#oksimpan").show();
      }else{
          $("#oksimpan").hide();
      }
    } else{
      $("#oksimpan").show();
    }
    $("#totalbayar").html(toDuit(bayar));
    $('#total').val(bayar);
}
</script>
<?php 
  echo "<div style='clear:both'></div><hr><br>$ket[keterangan]"; 
}
if (!($this->session->idp == '')){
?>
  <label class="switch">
    <input type="checkbox" id="d_btn" onchange="open_dropship('dropship_form');">
    <span class="slider round"></span>
  </label>&nbsp;<span style="font-weight:bold;font-size:19px;">&nbsp;Dropship</span>
  <div style="display:none;" id="dropship_form">
    <div>&nbsp;</div>
    <h3>Kirim Ke</h3>
    <hr/>
      <p class="contact-form-email">
        <label for="c_email">Atas Nama<span class="required">*</span></label>
        <input type="text" name='a' id="a" placeholder="Kirim Ke" class="required" disabled/>
      </p>
      <p class="contact-form-message">
        <label for="c_message">Provinsi<span class="required">*</span></label>
        <?php 
        $daerah = $this->db->query("SELECT * FROM rb_provinsi ORDER BY provinsi_id ASC")->result_array();
        echo "<select style='margin-left:5px' class='form-control' name='b' id='state' disabled>
                      <option value=''>- Pilih -</option>";
                      foreach ($daerah as $r) {
                        echo "<option value='$r[provinsi_id]'>$r[nama_provinsi]</option>";
                      }
        echo "</select>"; 
        ?>
      </p>
      <p class="contact-form-message">
      <label for="c_message">Kota<span class="required">*</span></label>
        <select style='margin-left:5px' class='form-control' name='c' id='city' disabled>
          <option value=''>- Pilih -</option>
        </select>
      </p>
      <p class="contact-form-message">
        <label for="c_message">Alamat lengkap<span class="required">*</span></label>
        <textarea class="form-control" name="d" id="d" disabled placeholder="Kecamatan, desa dusun RT/RW, Nomor Telfon"></textarea>
      </p>
      <input type="hidden" name="dropship" id="dropship" value="1" disabled>
  </div>
  <div>&nbsp;</div>
  <div>&nbsp;</div>
  <?php 
    }
  ?>
</div>
<div class="col-sm-4 colom4">
  <?php 
    $res = $this->db->query("SELECT a.*, b.nama_kota, c.nama_provinsi FROM rb_reseller a JOIN rb_kota b ON a.kota_id=b.kota_id JOIN rb_provinsi c ON b.provinsi_id=c.provinsi_id where a.id_reseller='$rows[id_reseller]'")->row_array(); 
  ?>
  <table class='table table-condensed'>
  <tbody>
    <tr class='alert alert-info'><th scope='row' style='width:90px'>Pengirim</th> <td><?php echo $res['nama_reseller']?></td></tr>
    <tr class='alert alert-info'><th scope='row'>Alamat</th> <td><?php echo $res['alamat_lengkap'].', '.$res['nama_kota'].', '.$res['nama_provinsi']; ?></td></tr>
  </tbody>
  </table>
  <?php 
  $usr = $this->db->query("SELECT a.*, b.nama_kota, c.nama_provinsi FROM rb_konsumen a JOIN rb_kota b ON a.kota_id=b.kota_id JOIN rb_provinsi c ON b.provinsi_id=c.provinsi_id
                  where a.id_konsumen='".$this->session->id_konsumen."'")->row_array(); ?>
  <table class='table table-condensed'>
  <tbody>
    <tr class='alert alert-danger'>
      <th scope='row' style='width:90px'>Penerima</th> 
      <td><?php echo $usr['nama_lengkap']?></td></tr>
      <tr><th scope='row'>Alamat</th> 
      <td><?php echo $usr['alamat_lengkap'].', '.$usr['nama_kota'].', '.$usr['nama_provinsi']; ?></td>
    </tr>
  </tbody>
  </table>
<?php
echo form_close();
?>
  <div>&nbsp;</div>
    <img style='width:100%' src='<?php echo base_url(); ?>asset/foto_pasangiklan/ekpedisi2.jpg'>
</div>
<script>
      function open_dropship(a){
        if ($('#d_btn').is(':checked')) {
          $("#kurirserviceinfo").html("");
          $("#kuririnfo").hide();
          $("#oksimpan").hide();
          $('#'+a).show();
          $('#a').prop("disabled", false);
          $('#state').prop("disabled", false);
          $('#city').prop("disabled", false);
          $('#d').prop("disabled", false);
          $('#dropship').prop("disabled", false);
          $('#a').prop('required',true);;
          $('#state').prop('required',true);
          $('#city').prop('required',true);
          $('#d').prop('required',true);
          $('#dropship').prop('required',true);
        } else{
          $("#kurirserviceinfo").html("");
          $("#kuririnfo").hide();
          $("#oksimpan").hide();
          $('#'+a).hide();
          $('#a').prop("disabled", true);
          $('#state').prop("disabled", true);
          $('#city').prop("disabled", true);
          $('#d').prop("disabled", true);
          $('#dropship').prop("disabled", true);
          $('#a').prop('required',false);;
          $('#state').prop('required',false);
          $('#city').prop('required',false);
          $('#d').prop('required',false);
          $('#dropship').prop('required',false);
        }
      }
      $(document).ready(function(){
        $('#state').change(function(){
          $("#kurirserviceinfo").html("");
          $("#kuririnfo").show();
          $("#oksimpan").hide();
          var state_id = $(this).val();
          $.ajax({
            type:"POST",
            url:"/auth/city",
            data:"stat_id="+state_id,
            success: function(response){
              $('#city').html(response);
            }
          })
        })
      })
</script>