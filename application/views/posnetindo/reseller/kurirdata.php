<style type="text/css">
@media (min-width: 992px){
.col-md-4 {
    width: 28%;
}
}
</style>
<?php
if(!empty($data)){
	$i=0;
		foreach($data as $row){
			$i+=1;
				if($_SESSION['voucher']['vtype']==2){	
					$tarif		= 0;
					$tarif_text = 'Ongkir Voucher Gratis';
				} else{
					$tarif		= $row['cost'][0]['value'];
					$tarif_text = 'Rp. '.number_format($tarif,0);
				}
			$service=$row['service'];
			$deskripsi=$row['description'];
			$waktu=$row['cost'][0]['etd']?$row['cost'][0]['etd']:"-";
			?>
			<div class="col-md-4">
				<div class="radio" style='margin: 0px;'>
					<label>
						<input type="radio" name="service" class="service" data-id="<?php echo $i; ?>" value="<?php echo $service; ?>"/><?php echo $deskripsi; ?>
					</label>
				</div>
				<input type="hidden" name="tarif" id="tarif<?php echo $i; ?>" tarif-original="<?php echo $row['cost'][0]['value'];?>" value="<?php echo $tarif; ?>"/>
				<p style='margin-left: 19px;'>
					Tarif <b><?php echo $tarif_text; ?></b><br/>
					Estimasi sampai <b><?php echo $waktu; ?> hari</b>
				</p>
			</div>
			<?php			
		}
	
?>
<?php 
}
if($ongkir=='0'){ ?>
<div class="radio" style='margin: 0px;'>
	<div style='font-weight:bold; color:blue'>
		Pilih alamat COD dibawah ini.
	</div>
	<div style="clear:both"></div>
		<?php 
			$ress = $this->model_reseller->penjualan_konsumen_detail($this->session->idp)->row_array();
			$cod = $this->db->query("SELECT * FROM rb_reseller_cod where id_reseller='$ress[id_reseller]'");
			$i = 1;
			foreach ($cod->result_array() as $ros) {
				if($_SESSION['voucher']['vtype']==2){	
					$biaya = 'Ongkir Voucher Gratis';
					$trf   = 0;
				} else{
					$biaya = "Rp ".number_format($ros['biaya_cod'],0);
					$trf   = $ros['biaya_cod'];
				}
				echo "<div class='col-md-4'>
						<input type='radio' name='service' class='service' data-id='$i' value='Cash on delivery'/> $ros[nama_alamat]<br> Tarif. <b>".$biaya."</b>
					  	<input type='hidden' name='tarif' id='tarif$i' tarif-original='".$ros['biaya_cod']."' value='$trf'/>
			  </div>";
				$i++;
			}
		?>

</div>
<?php } ?>
<script>
$(document).ready(function(){
$(".service").each(function(o_index,o_val){
	$(this).on("change",function(){	
		var old_ongkir = $("#ongkir").val();
		var total_hrg = $("#total").val();
		$('#total').val(parseFloat(total_hrg)-parseFloat(old_ongkir));
		$("#ongkir").val('0');
		var did=$(this).attr('data-id');
		var tarif=$("#tarif"+did).val();
		$("#ongkir").val(tarif);
		$("#tarif_original").val($("#tarif"+did).attr('tarif-original'));
		hitung();
	});
});
});
</script>