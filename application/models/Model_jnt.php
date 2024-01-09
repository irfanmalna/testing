<?php

class Model_jnt extends CI_model {

    public function jnt($beratKG,$asal,$tujuan){
        $array = array(
            'beratKgjnt'        => $beratKG,
            'exp_name'          => 'jnt',
            'exp_title'         => 'J & T Express',
            'kotaAsaljnt'       => strtoupper($asal), //format KECAMATAN, KABUPATEN
            'kotaAsaljnt_val'   => strtoupper($asal),
            'kotaTujuanjnt'     => strtoupper($tujuan), //format KECAMATAN, KABUPATEN
            'kotaTujuanjnt_val' => strtoupper($tujuan),
            'lebar'             => '0',
            'panel_type'        => 'danger',
            'panjang'           => '0',
            'tinggi'            => '0'
        );
        $source = $this->ambil_page($array,'https://cektarif.com/exp/jnt/jnt.tarif.php');
        $pecah = @explode('<td align="right">', $source);
        $ambil = @explode('</td>', $pecah[1]);
        return strip_tags($ambil[0]);
    }

    public function get_daerah($type,$id){
        if($type==1){
            $kota = $this->db->query("SELECT * FROM `rb_kota` WHERE `kota_id`=$id");
            return $kota->row_array();
        } elseif($type==2){
            $kota = $this->db->query("SELECT * FROM `rb_provinsi` WHERE `provinsi_id`=$id");
            return $kota->row_array();
        } else{
            return false;
        }

    }

    public function ambil_page($url,$html=null){
        $u = isset($_SERVER['HTTP_USER_AGENT']);
        if($u){
            $ua = $u;
        } else{
            $ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36';
        }
        if($html){
            $ch=curl_init();
            curl_setopt_array($ch,array(
            CURLOPT_URL => $html,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $url,
            CURLOPT_USERAGENT => $ua,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_COOKIEJAR => 'coker_log',
            CURLOPT_COOKIEFILE => 'coker_log',));
            $cx=curl_exec($ch);
            curl_close($ch);
            return ($cx);
        } else{
            $ch=curl_init();
            curl_setopt_array($ch,array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $ua,
            CURLOPT_COOKIEJAR => 'coker_log',
            CURLOPT_COOKIEFILE => 'coker_log',));
            $cx=curl_exec($ch);
            curl_close($ch);
            return ($cx);
        }
    }

}

?>