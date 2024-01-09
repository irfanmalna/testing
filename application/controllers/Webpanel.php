<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webpanel extends CI_Controller {
	function index(){
		if (isset($_POST['submit'])){
            if ($this->input->post() && (strtolower($this->input->post('security_code')) == strtolower($this->session->userdata('mycaptcha')))) {
                $username = $this->input->post('a');
    			$password = hash("sha512", md5($this->input->post('b')));
    			$cek = $this->model_app->cek_login($username,$password,'users');
    		    $row = $cek->row_array();
    		    $total = $cek->num_rows();
    			if ($total > 0){
    				$this->session->set_userdata('upload_image_file_manager',true);
    				$this->session->set_userdata(array(
                                       'username'=>$row['username'],
    								   'level'=>$row['level'],
    								   'id_admin'=>$row['id'],
                                       'id_session'=>$row['id_session']));
    				redirect($this->router->fetch_class().'/home');
    			}else{
                    echo $this->session->set_flashdata('message', '<div class="alert alert-danger"><center>Username dan Password Salah!!</center></div>');
    				redirect($this->router->fetch_class().'/index');
    			}
            }else{
                echo $this->session->set_flashdata('message', '<div class="alert alert-danger"><center>Security Code salah!</center></div>');
                redirect($this->router->fetch_class().'/index');
            }
		}else{
            if (($this->session->level == 'admin') or ($this->session->level == 'user') or ($this->session->level == 'kontributor')){
                redirect($this->router->fetch_class().'/home');
            }else{
                $this->load->helper('captcha');
                $vals = array(
                    'img_path'   => './captcha_dir/',
                    'img_url'    => base_url().'captcha_dir/',
                    'font_path' => base_url().'asset/Tahoma.ttf',
                    'font_size'     => 17,
                    'img_width'  => '320',
                    'img_height' => 33,
                    'border' => 0, 
                    'word_length'   => 5,
                    'expiration' => 7200
                );
                $cap = create_captcha($vals);
                $data['image'] = $cap['image'];
                $this->session->set_userdata('mycaptcha', $cap['word']);
                $data['title'] = 'Administrator &rsaquo; Log In';
    			$this->load->view('administrator/view_login',$data);
            }
		}
	}

    function reset_password(){
        if (isset($_POST['submit'])){
            $usr = $this->model_app->edit('users', array('id_session' => $this->input->post('id_session')));
            if ($usr->num_rows()>=1){
                if ($this->input->post('a')==$this->input->post('b')){
                    $data = array('password'=>hash("sha512", md5($this->input->post('a'))));
                    $where = array('id_session' => $this->input->post('id_session'));
                    $this->model_app->update('users', $data, $where);

                    $row = $usr->row_array();
                    $this->session->set_userdata('upload_image_file_manager',true);
                    $this->session->set_userdata(array('username'=>$row['username'],
                                       'level'=>$row['level'],
                                       'id_session'=>$row['id_session']));
                    redirect($this->router->fetch_class().'/home');
                }else{
                    $data['title'] = 'Password Tidak sama!';
                    $this->load->view('administrator/view_reset',$data);
                }
            }else{
                $data['title'] = 'Terjadi Kesalahan!';
                $this->load->view('administrator/view_reset',$data);
            }
        }else{
            $this->session->set_userdata(array('id_session'=>$this->uri->segment(3)));
            $data['title'] = 'Reset Password';
            $this->load->view('administrator/view_reset',$data);
        }
    }

    function lupapassword(){
        if (isset($_POST['lupa'])){
            $email = strip_tags($this->input->post('email'));
            $cekemail = $this->model_app->edit('users', array('email' => $email))->num_rows();
            if ($cekemail <= 0){
                $data['title'] = 'Alamat email tidak ditemukan';
                $this->load->view('administrator/view_login',$data);
            }else{
                $iden = $this->model_app->edit('identitas', array('id_identitas' => 1))->row_array();
                $usr = $this->model_app->edit('users', array('email' => $email))->row_array();
                $this->load->library('email');

                $tgl = date("d-m-Y H:i:s");
                $subject      = 'Lupa Password ...';
                $message      = "<html><body>
                                    <table style='margin-left:25px'>
                                        <tr><td>Halo $usr[nama_lengkap],<br>
                                        Seseorang baru saja meminta untuk mengatur ulang kata sandi Anda di <span style='color:red'>$iden[url]</span>.<br>
                                        Klik di sini untuk mengganti kata sandi Anda.<br>
                                        Atau Anda dapat copas (Copy Paste) url dibawah ini ke address Bar Browser anda :<br>
                                        <a href='".base_url().$this->router->fetch_class()."/reset_password/$usr[id_session]'>".base_url().$this->router->fetch_class()."/reset_password/$usr[id_session]</a><br><br>

                                        Tidak meminta penggantian ini?<br>
                                        Jika Anda tidak meminta kata sandi baru, segera beri tahu kami.<br>
                                        Email. $iden[email], No Telp. $iden[no_telp]</td></tr>
                                    </table>
                                </body></html> \n";
                
                $this->email->from($iden['email'], $iden['nama_website']);
                $this->email->to($usr['email']);
                $this->email->cc('');
                $this->email->bcc('');

                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->set_mailtype("html");
                $this->email->send();
                
                $config['protocol'] = 'sendmail';
                $config['mailpath'] = '/usr/sbin/sendmail';
                $config['charset'] = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $this->email->initialize($config);

                $data['title'] = 'Password terkirim ke '.$usr['email'];
                $this->load->view('administrator/view_login',$data);
            }
        }else{
            redirect($this->router->fetch_class());
        }
    }

	function home(){
        if ($this->session->level=='admin'){
		  $this->template->load('administrator/template','administrator/view_home_admin');
        } elseif ($this->session->level=='user'){
          $data['users'] = $this->model_app->view_where('users',array('username'=>$this->session->username))->row_array();
          $data['modul'] = $this->model_app->view_join_one('users','users_modul','id_session','id_umod','DESC');
          $this->template->load('administrator/template','administrator/view_home_users',$data);
        } elseif ($this->session->level=='kontributor'){
            $data['users'] = $this->model_app->view_where('users',array('username'=>$this->session->username))->row_array();
            $data['modul'] = $this->model_app->view_join_one('users','users_modul','id_session','id_umod','DESC');
            $this->template->load('administrator/template','administrator/view_home_users',$data);
        } else{
            redirect($this->router->fetch_class());
        }


	}


	function identitaswebsite(){
		cek_session_akses($this->router->fetch_class(),'identitaswebsite',$this->session->id_session);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/images/';
            $config['allowed_types'] = 'gif|jpg|png|ico';
            $config['max_size'] = '500'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('j');
            $hasil=$this->upload->data();

            if ($hasil['file_name']==''){
            	$data = array('nama_website'=>$this->db->escape_str($this->input->post('a')),
                                'email'=>$this->db->escape_str($this->input->post('b')),
                                'url'=>$this->db->escape_str($this->input->post('c')),
                                'facebook'=>$this->input->post('d'),
                                'rekening'=>$this->db->escape_str($this->input->post('e')),
                                'no_telp'=>$this->db->escape_str($this->input->post('f')),
                                'meta_deskripsi'=>$this->input->post('g'),
                                'meta_keyword'=>$this->db->escape_str($this->input->post('h')),
                                'maps'=>$this->input->post('i'));
            }else{
            	$data = array('nama_website'=>$this->db->escape_str($this->input->post('a')),
                                'email'=>$this->db->escape_str($this->input->post('b')),
                                'url'=>$this->db->escape_str($this->input->post('c')),
                                'facebook'=>$this->input->post('d'),
                                'rekening'=>$this->db->escape_str($this->input->post('e')),
                                'no_telp'=>$this->db->escape_str($this->input->post('f')),
                                'meta_deskripsi'=>$this->input->post('g'),
                                'meta_keyword'=>$this->db->escape_str($this->input->post('h')),
                                'favicon'=>$hasil['file_name'],
                                'maps'=>$this->input->post('i'));
            }
	    	$where = array('id_identitas' => $this->input->post('id'));
			$this->model_app->update('identitas', $data, $where);

			redirect($this->router->fetch_class().'/identitaswebsite');
		}else{
			$proses = $this->model_app->edit('identitas', array('id_identitas' => 1))->row_array();
			$data = array('record' => $proses);
			$this->template->load('administrator/template','administrator/mod_identitas/view_identitas',$data);
		}
	}

	// Controller Modul Menu Website

	function menuwebsite(){
		cek_session_akses($this->router->fetch_class(),'menuwebsite',$this->session->id_session);
        $data['record'] = $this->db->query("SELECT * FROM menu order by position, urutan");
        $data['halaman'] = $this->model_app->view_ordering('halamanstatis','id_halaman','DESC');
        $data['kategori'] = $this->model_app->view_ordering('kategori','id_kategori','DESC');
		$this->template->load('administrator/template','administrator/mod_menu/view_menu',$data);
	}

    function save_menuwebsite(){
        cek_session_akses($this->router->fetch_class(),'menuwebsite',$this->session->id_session);
        $link = $_POST['link'].$_POST['page'].$_POST['kategori'];
        if($_POST['id'] != ''){
            $this->db->query("UPDATE menu SET nama_menu = '".$_POST['label']."', link  = '".$link."' where id_menu = '".$_POST['id']."' ");
            $arr['type']  = 'edit';
            $arr['label'] = $_POST['label'];
            $arr['link']  = $_POST['link'];
            $arr['page']  = $_POST['page'];
            $arr['kategori']  = $_POST['kategori'];
            $arr['id']    = $_POST['id'];
        }else{
            $row = $this->db->query("SELECT max(urutan)+1 as urutan FROM menu")->row_array();
            $this->db->query("INSERT into menu VALUES('','0','".$_POST['label']."', '".$link."','Ya','Bottom','".$row['urutan']."')");
            $id = $this->db->insert_id();
            $arr['menu'] = '<li class="dd-item dd3-item" data-id="'.$id.'" >
                                <div class="dd-handle dd3-handle Bottom">Drag</div>
                                <div class="dd3-content"><span id="label_show'.$id.'">'.$_POST['label'].'</span>
                                    <span class="span-right">/<span id="link_show'.$id.'">'.$link.'</span> &nbsp;&nbsp; 
                                        <a href="'.base_url().'/'.$this->router->fetch_class().'/posisi_menuwebsite/'.$id.'" style="cursor:pointer"><i class="fa fa-chevron-circle-up text-success"></i></a> &nbsp; 
                                        <a class="edit-button" id="'.$id.'" label="'.$_POST['label'].'" link="'.$_POST['link'].'" ><i class="fa fa-pencil"></i></a> &nbsp; 
                                        <a class="del-button" id="'.$id.'"><i class="fa fa-trash"></i></a>
                                    </span> 
                                </div>';
            $arr['type'] = 'add';
        }
        print json_encode($arr);
    }

    function save(){
        $data = json_decode($_POST['data']);
        function parseJsonArray($jsonArray, $parentID = 0) {
          $return = array();
          foreach ($jsonArray as $subArray) {
            $returnSubSubArray = array();
            if (isset($subArray->children)) {
                $returnSubSubArray = parseJsonArray($subArray->children, $subArray->id);
            }

            $return[] = array('id' => $subArray->id, 'parentID' => $parentID);
            $return = array_merge($return, $returnSubSubArray);
          }
          return $return;
        }
        $readbleArray = parseJsonArray($data);

        $i=0;
        foreach($readbleArray as $row){
          $i++;
            $this->db->query("UPDATE menu SET id_parent = '".$row['parentID']."', urutan = '".$i."' where id_menu = '".$row['id']."' ");
        }
    }

    function posisi_menuwebsite(){
        cek_session_akses($this->router->fetch_class(),'menuwebsite',$this->session->id_session);
        $cek = $this->model_app->view_where('menu',array('id_menu'=>$this->uri->segment(3)))->row_array();
        $posisi = ($cek['position'] == 'Top' ? 'Bottom' : 'Top');
        $data = array('position'=>$posisi);
        $where = array('id_menu' => $this->uri->segment(3));
        $this->model_app->update('menu', $data, $where);
        redirect($this->router->fetch_class().'/menuwebsite');
    }

	function delete_menuwebsite(){
        cek_session_akses($this->router->fetch_class(),'menuwebsite',$this->session->id_session);
		$idm = array('id_menu' => $this->input->post('id'));
		$this->model_app->delete('menu',$idm);
        $idm = array('id_parent' => $this->input->post('id'));
        $this->model_app->delete('menu',$idm);
	}


	// Controller Modul Halaman Baru

	function halamanbaru(){
		cek_session_akses($this->router->fetch_class(),'halamanbaru',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('halamanstatis','id_halaman','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('halamanstatis',array('username'=>$this->session->username),'id_halaman','DESC');
        }
		$this->template->load('administrator/template','administrator/mod_halaman/view_halaman',$data);
	}

	function tambah_halamanbaru(){
		cek_session_akses($this->router->fetch_class(),'halamanbaru',$this->session->id_session);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_statis/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                    $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                    'judul_seo'=>seo_title($this->input->post('a')),
                                    'isi_halaman'=>$this->input->post('b'),
                                    'tgl_posting'=>date('Y-m-d'),
                                    'username'=>$this->session->username,
                                    'dibaca'=>'0',
                                    'jam'=>date('H:i:s'),
                                    'hari'=>hari_ini(date('w')));
            }else{
            		$data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                    'judul_seo'=>seo_title($this->input->post('a')),
                                    'isi_halaman'=>$this->input->post('b'),
                                    'tgl_posting'=>date('Y-m-d'),
                                    'gambar'=>$hasil['file_name'],
                                    'username'=>$this->session->username,
                                    'dibaca'=>'0',
                                    'jam'=>date('H:i:s'),
                                    'hari'=>hari_ini(date('w')));
            }
            $this->model_app->insert('halamanstatis',$data);
			redirect($this->router->fetch_class().'/halamanbaru');
		}else{
			$this->template->load('administrator/template','administrator/mod_halaman/view_halaman_tambah');
		}
	}

	function edit_halamanbaru(){
		cek_session_akses($this->router->fetch_class(),'halamanbaru',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_statis/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                    $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                    'judul_seo'=>seo_title($this->input->post('a')),
                                    'isi_halaman'=>$this->input->post('b'));
            }else{
            		$data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                    'judul_seo'=>seo_title($this->input->post('a')),
                                    'isi_halaman'=>$this->input->post('b'),
                                    'gambar'=>$hasil['file_name']);
            }
            $where = array('id_halaman' => $this->input->post('id'));
			$this->model_app->update('halamanstatis', $data, $where);
			redirect($this->router->fetch_class().'/halamanbaru');
		}else{
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('halamanstatis', array('id_halaman' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('halamanstatis', array('id_halaman' => $id, 'username' => $this->session->username))->row_array();
            }
			$data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_halaman/view_halaman_edit',$data);
		}
	}

	function delete_halamanbaru(){
        cek_session_akses($this->router->fetch_class(),'halamanbaru',$this->session->id_session);
		if ($this->session->level=='admin'){
            $id = array('id_halaman' => $this->uri->segment(3));
        }else{
            $id = array('id_halaman' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
		$this->model_app->delete('halamanstatis',$id);
		redirect($this->router->fetch_class().'/halamanbaru');
	}

	// Controller Modul List Berita

	function listberita(){
		cek_session_akses($this->router->fetch_class(),'listberita',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('berita','id_berita','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('berita',array('username'=>$this->session->username),'id_berita','DESC');
        }
        $data['rss'] = $this->model_utama->view_joinn('berita','users','kategori','username','id_kategori','id_berita','DESC',0,10);
        $data['iden'] = $this->model_utama->view_where('identitas',array('id_identitas' => 1))->row_array();
        $this->load->view('administrator/rss',$data);
		$this->template->load('administrator/template','administrator/mod_berita/view_berita',$data);
	}

	function tambah_listberita(){
		cek_session_akses($this->router->fetch_class(),'listberita',$this->session->id_session);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_berita/';
	        $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
	        $config['max_size'] = '3000'; // kb
	        $this->load->library('upload', $config);
	        $this->upload->do_upload('k');
	        $hasil=$this->upload->data();
            $config['source_image'] = 'asset/foto_berita/'.$hasil['file_name'];
            $config['wm_text'] = 'posnetindo.co.id';
            $config['wm_type'] = 'text';
            $config['wm_font_path'] = './system/fonts/texb.ttf';
            $config['wm_font_size'] = '26';
            $config['wm_font_color'] = 'ffffff';
            $config['wm_vrt_alignment'] = 'middle';
            $config['wm_hor_alignment'] = 'center';
            $config['wm_padding'] = '20';
            $this->load->library('image_lib',$config);
            $this->image_lib->watermark();
            if ($this->session->level == 'kontributor'){ $status = 'N'; }else{ $status = 'Y'; }
            if ($this->input->post('j')!=''){
                $tag_seo = $this->input->post('j');
                $tag=implode(',',$tag_seo);
            }else{
                $tag = '';
            }
            if ($hasil['file_name']==''){
                    $data = array('id_kategori'=>$this->db->escape_str($this->input->post('a')),
                                    'username'=>$this->session->username,
                                    'judul'=>$this->db->escape_str($this->input->post('b')),
                                    'sub_judul'=>$this->db->escape_str($this->input->post('c')),
                                    'youtube'=>$this->db->escape_str($this->input->post('d')),
                                    'judul_seo'=>seo_title($this->input->post('b')),
                                    'headline'=>$this->db->escape_str($this->input->post('e')),
                                    'aktif'=>$this->db->escape_str($this->input->post('f')),
                                    'utama'=>$this->db->escape_str($this->input->post('g')),
                                    'isi_berita'=>$this->input->post('h'),
                                    'keterangan_gambar'=>$this->input->post('i'),
                                    'hari'=>hari_ini(date('w')),
                                    'tanggal'=>date('Y-m-d'),
                                    'jam'=>date('H:i:s'),
                                    'dibaca'=>'0',
                                    'tag'=>$tag,
                                    'status'=>$status);
            }else{
                    $data = array('id_kategori'=>$this->db->escape_str($this->input->post('a')),
                                    'username'=>$this->session->username,
                                    'judul'=>$this->db->escape_str($this->input->post('b')),
                                    'sub_judul'=>$this->db->escape_str($this->input->post('c')),
                                    'youtube'=>$this->db->escape_str($this->input->post('d')),
                                    'judul_seo'=>seo_title($this->input->post('b')),
                                    'headline'=>$this->db->escape_str($this->input->post('e')),
                                    'aktif'=>$this->db->escape_str($this->input->post('f')),
                                    'utama'=>$this->db->escape_str($this->input->post('g')),
                                    'isi_berita'=>$this->input->post('h'),
                                    'keterangan_gambar'=>$this->input->post('i'),
                                    'hari'=>hari_ini(date('w')),
                                    'tanggal'=>date('Y-m-d'),
                                    'jam'=>date('H:i:s'),
                                    'gambar'=>$hasil['file_name'],
                                    'dibaca'=>'0',
                                    'tag'=>$tag,
                                    'status'=>$status);
            }
            $this->model_app->insert('berita',$data);
			redirect($this->router->fetch_class().'/listberita');
		}else{
            $data['tag'] = $this->model_app->view_ordering('tag','id_tag','DESC');
            $data['record'] = $this->model_app->view_ordering('kategori','id_kategori','DESC');
			$this->template->load('administrator/template','administrator/mod_berita/view_berita_tambah',$data);
		}
	}

	function edit_listberita(){
		cek_session_akses($this->router->fetch_class(),'listberita',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_berita/';
	        $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
	        $config['max_size'] = '3000'; // kb
	        $this->load->library('upload', $config);
	        $this->upload->do_upload('k');
	        $hasil=$this->upload->data();

            $config['source_image'] = 'asset/foto_berita/'.$hasil['file_name'];
            $config['wm_text'] = '';
            $config['wm_type'] = 'text';
            $config['wm_font_path'] = './system/fonts/texb.ttf';
            $config['wm_font_size'] = '26';
            $config['wm_font_color'] = 'ffffff';
            $config['wm_vrt_alignment'] = 'middle';
            $config['wm_hor_alignment'] = 'center';
            $config['wm_padding'] = '20';
            $this->load->library('image_lib',$config);
            $this->image_lib->watermark();

            if ($this->session->level == 'kontributor'){ $status = 'N'; }else{ $status = 'Y'; }
            if ($this->input->post('j')!=''){
                $tag_seo = $this->input->post('j');
                $tag=implode(',',$tag_seo);
            }else{
                $tag = '';
            }
            if ($hasil['file_name']==''){
                    $data = array('id_kategori'=>$this->db->escape_str($this->input->post('a')),
                                    'username'=>$this->session->username,
                                    'judul'=>$this->db->escape_str($this->input->post('b')),
                                    'sub_judul'=>$this->db->escape_str($this->input->post('c')),
                                    'youtube'=>$this->db->escape_str($this->input->post('d')),
                                    'judul_seo'=>seo_title($this->input->post('b')),
                                    'headline'=>$this->db->escape_str($this->input->post('e')),
                                    'aktif'=>$this->db->escape_str($this->input->post('f')),
                                    'utama'=>$this->db->escape_str($this->input->post('g')),
                                    'isi_berita'=>$this->input->post('h'),
                                    'keterangan_gambar'=>$this->input->post('i'),
                                    'hari'=>hari_ini(date('w')),
                                    'tanggal'=>date('Y-m-d'),
                                    'jam'=>date('H:i:s'),
                                    'dibaca'=>'0',
                                    'tag'=>$tag,
                                    'status'=>$status);
            }else{
                    $data = array('id_kategori'=>$this->db->escape_str($this->input->post('a')),
                                    'username'=>$this->session->username,
                                    'judul'=>$this->db->escape_str($this->input->post('b')),
                                    'sub_judul'=>$this->db->escape_str($this->input->post('c')),
                                    'youtube'=>$this->db->escape_str($this->input->post('d')),
                                    'judul_seo'=>seo_title($this->input->post('b')),
                                    'headline'=>$this->db->escape_str($this->input->post('e')),
                                    'aktif'=>$this->db->escape_str($this->input->post('f')),
                                    'utama'=>$this->db->escape_str($this->input->post('g')),
                                    'isi_berita'=>$this->input->post('h'),
                                    'keterangan_gambar'=>$this->input->post('i'),
                                    'hari'=>hari_ini(date('w')),
                                    'tanggal'=>date('Y-m-d'),
                                    'jam'=>date('H:i:s'),
                                    'gambar'=>$hasil['file_name'],
                                    'dibaca'=>'0',
                                    'tag'=>$tag,
                                    'status'=>$status);
            }
            $where = array('id_berita' => $this->input->post('id'));
			$this->model_app->update('berita', $data, $where);
			redirect($this->router->fetch_class().'/listberita');
		}else{
			$tag = $this->model_app->view_ordering('tag','id_tag','DESC');
            $record = $this->model_app->view_ordering('kategori','id_kategori','DESC');
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('berita', array('id_berita' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('berita', array('id_berita' => $id, 'username' => $this->session->username))->row_array();
            }
			$data = array('rows' => $proses,'tag' => $tag,'record' => $record);
			$this->template->load('administrator/template','administrator/mod_berita/view_berita_edit',$data);
		}
	}

	function publish_listberita(){
        cek_session_admin();
		if ($this->uri->segment(4)=='Y'){
			$data = array('status'=>'N');
		}else{
			$data = array('status'=>'Y');
		}
        $where = array('id_berita' => $this->uri->segment(3));
		$this->model_app->update('berita', $data, $where);
		redirect($this->router->fetch_class().'/listberita');
	}

	function delete_listberita(){
        cek_session_akses($this->router->fetch_class(),'listberita',$this->session->id_session);
        if ($this->session->level=='admin'){
    		$id = array('id_berita' => $this->uri->segment(3));
        }else{
            $id = array('id_berita' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
		$this->model_app->delete('berita',$id);
		redirect($this->router->fetch_class().'/listberita');
	}


	// Controller Modul Kategori Berita

	function kategoriberita(){
		cek_session_akses($this->router->fetch_class(),'kategoriberita',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('kategori','id_kategori','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('kategori',array('username'=>$this->session->username),'id_kategori','DESC');
        }
		$this->template->load('administrator/template','administrator/mod_kategori/view_kategori',$data);
	}

	function tambah_kategoriberita(){
		cek_session_akses($this->router->fetch_class(),'kategoriberita',$this->session->id_session);
		if (isset($_POST['submit'])){
			$data = array('nama_kategori'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'kategori_seo'=>seo_title($this->input->post('a')),
                        'aktif'=>$this->db->escape_str($this->input->post('b')),
                        'sidebar'=>$this->db->escape_str($this->input->post('c')));
			$this->model_app->insert('kategori',$data);
			redirect($this->router->fetch_class().'/kategoriberita');
		}else{
			$this->template->load('administrator/template','administrator/mod_kategori/view_kategori_tambah');
		}
	}

	function edit_kategoriberita(){
		cek_session_akses($this->router->fetch_class(),'kategoriberita',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$data = array('nama_kategori'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'kategori_seo'=>seo_title($this->input->post('a')),
                        'aktif'=>$this->db->escape_str($this->input->post('b')),
                        'sidebar'=>$this->db->escape_str($this->input->post('c')));
			$where = array('id_kategori' => $this->input->post('id'));
			$this->model_app->update('kategori', $data, $where);
			redirect($this->router->fetch_class().'/kategoriberita');
		}else{
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('kategori', array('id_kategori' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('kategori', array('id_kategori' => $id, 'username' => $this->session->username))->row_array();
            }
			$data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_kategori/view_kategori_edit',$data);
		}
	}

	function delete_kategoriberita(){
		cek_session_akses($this->router->fetch_class(),'kategoriberita',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_kategori' => $this->uri->segment(3));
        }else{
            $id = array('id_kategori' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
		$this->model_app->delete('kategori',$id);
		redirect($this->router->fetch_class().'/kategoriberita');
	}


	// Controller Modul Tag Berita

	function tagberita(){
		cek_session_akses($this->router->fetch_class(),'tagberita',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('tag','id_tag','DESC');
        } else{
            $data['record'] = $this->model_app->view_where_ordering('tag',array('username'=>$this->session->username),'id_tag','DESC');
        }
		$this->template->load('administrator/template','administrator/mod_tag/view_tag',$data);
	}

	function tambah_tagberita(){
		cek_session_akses($this->router->fetch_class(),'tagberita',$this->session->id_session);
		if (isset($_POST['submit'])){
			$data = array(
                        'nama_tag'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'tag_url'=>$this->input->post('b')
                    );
			$this->model_app->insert('tag',$data);	
			redirect($this->router->fetch_class().'/tagberita');
		}else{
			$this->template->load('administrator/template','administrator/mod_tag/view_tag_tambah');
		}
	}

	function edit_tagberita(){
		cek_session_akses($this->router->fetch_class(),'tagberita',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$data = array(
                        'nama_tag'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'tag_url'=>$this->input->post('b')
                    );
			$where = array('id_tag' => $this->input->post('id'));
			$this->model_app->update('tag', $data, $where);
			redirect($this->router->fetch_class().'/tagberita');
		}else{
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('tag', array('id_tag' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('tag', array('id_tag' => $id, 'username' => $this->session->username))->row_array();
            }
			$data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_tag/view_tag_edit',$data);
		}
	}

	function delete_tagberita(){
        cek_session_akses($this->router->fetch_class(),'tagberita',$this->session->id_session);
		if ($this->session->level=='admin'){
            $id = array('id_tag' => $this->uri->segment(3));
        }else{
            $id = array('id_tag' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
		$this->model_app->delete('tag',$id);
		redirect($this->router->fetch_class().'/tagberita');
	}


	// Controller Modul Komentar Berita

	function komentarberita(){
		cek_session_akses($this->router->fetch_class(),'komentarberita',$this->session->id_session);
		$data['record'] = $this->model_app->view_ordering('komentar','id_komentar','DESC');
		$this->template->load('administrator/template','administrator/mod_komentar/view_komentar',$data);
	}

	function edit_komentarberita(){
		cek_session_akses($this->router->fetch_class(),'komentarberita',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$data = array('nama_komentar'=>$this->input->post('a'),
                        'url'=>$this->input->post('b'),
                        'isi_komentar'=>$this->input->post('c'),
                        'aktif'=>$this->input->post('d'),
                        'email'=>$this->input->post('e'));
			$where = array('id_komentar' => $this->input->post('id'));
			$this->model_app->update('komentar', $data, $where);
			redirect($this->router->fetch_class().'/komentarberita');
		}else{
			$proses = $this->model_app->edit('komentar', array('id_komentar' => $id))->row_array();
			$data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_komentar/view_komentar_edit',$data);
		}
	}

	function delete_komentarberita(){
        cek_session_akses($this->router->fetch_class(),'komentarberita',$this->session->id_session);
		$id = array('id_komentar' => $this->uri->segment(3));
		$this->model_app->delete('komentar',$id);
		redirect($this->router->fetch_class().'/komentarberita');
	}


	// Controller Modul Sensor Komentar Berita

	function sensorkomentar(){
		cek_session_akses($this->router->fetch_class(),'sensorkomentar',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('katajelek','id_jelek','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('katajelek',array('username'=>$this->session->username),'id_jelek','DESC');
        }
		$this->template->load('administrator/template','administrator/mod_sensorkomentar/view_sensorkomentar',$data);
	}

	function tambah_sensorkomentar(){
		cek_session_akses($this->router->fetch_class(),'sensorkomentar',$this->session->id_session);
		if (isset($_POST['submit'])){
			$data = array('kata'=>$this->input->post('a'),
                        'username'=>$this->session->username,
                        'ganti'=>$this->input->post('b'));
			$this->model_app->insert('katajelek',$data);	
			redirect($this->router->fetch_class().'/sensorkomentar');
		}else{
			$this->template->load('administrator/template','administrator/mod_sensorkomentar/view_sensorkomentar_tambah');
		}
	}

	function edit_sensorkomentar(){
		cek_session_akses($this->router->fetch_class(),'sensorkomentar',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$this->model_berita->tag_berita_update();
			$data = array('kata'=>$this->input->post('a'),
                        'username'=>$this->session->username,
                        'ganti'=>$this->input->post('b'));
			$where = array('id_jelek' => $this->input->post('id'));
			$this->model_app->update('katajelek', $data, $where);
			redirect($this->router->fetch_class().'/sensorkomentar');
		}else{
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('katajelek', array('id_jelek' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('katajelek', array('id_jelek' => $id, 'username' => $this->session->username))->row_array();
            }
			$data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_sensorkomentar/view_sensorkomentar_edit',$data);
		}
	}

	function delete_sensorkomentar(){
        cek_session_akses($this->router->fetch_class(),'sensorkomentar',$this->session->id_session);
		if ($this->session->level=='admin'){
            $id = array('id_jelek' => $this->uri->segment(3));
        }else{
            $id = array('id_jelek' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
		$this->model_app->delete('katajelek',$id);
		redirect($this->router->fetch_class().'/sensorkomentar');
	}


    // Controller Modul Album

    function album(){
        cek_session_akses($this->router->fetch_class(),'album',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('album','id_album','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('album',array('username'=>$this->session->username),'id_album','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_album/view_album',$data);
    }

    function tambah_album(){
        cek_session_akses($this->router->fetch_class(),'album',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_album/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('jdl_album'=>$this->input->post('a'),
                            'album_seo'=>seo_title($this->input->post('a')),
                            'keterangan'=>$this->input->post('b'),
                            'aktif'=>'Y',
                            'hits_album'=>'0',
                            'tgl_posting'=>date('Y-m-d'),
                            'jam'=>date('H:i:s'),
                            'hari'=>hari_ini(date('w')),
                            'username'=>$this->session->username);
            }else{
                $data = array('jdl_album'=>$this->input->post('a'),
                            'album_seo'=>seo_title($this->input->post('a')),
                            'keterangan'=>$this->input->post('b'),
                            'gbr_album'=>$hasil['file_name'],
                            'aktif'=>'Y',
                            'hits_album'=>'0',
                            'tgl_posting'=>date('Y-m-d'),
                            'jam'=>date('H:i:s'),
                            'hari'=>hari_ini(date('w')),
                            'username'=>$this->session->username);
            }

            $this->model_app->insert('album',$data);  
            redirect($this->router->fetch_class().'/album');
        }else{
            $this->template->load('administrator/template','administrator/mod_album/view_album_tambah');
        }
    }

    function edit_album(){
        cek_session_akses($this->router->fetch_class(),'album',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_album/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('jdl_album'=>$this->input->post('a'),
                            'album_seo'=>seo_title($this->input->post('a')),
                            'keterangan'=>$this->input->post('b'),
                            'aktif'=>$this->input->post('d'));
            }else{
                $data = array('jdl_album'=>$this->input->post('a'),
                            'album_seo'=>seo_title($this->input->post('a')),
                            'keterangan'=>$this->input->post('b'),
                            'gbr_album'=>$hasil['file_name'],
                            'aktif'=>$this->input->post('d'));
            }
            $where = array('id_album' => $this->input->post('id'));
            $this->model_app->update('album', $data, $where);
            redirect($this->router->fetch_class().'/album');
        }else{
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('album', array('id_album' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('album', array('id_album' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_album/view_album_edit',$data);
        }
    }

    function delete_album(){
        cek_session_akses($this->router->fetch_class(),'album',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_album' => $this->uri->segment(3));
        }else{
            $id = array('id_album' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('album',$id);
        redirect($this->router->fetch_class().'/album');
    }


    // Controller Modul Gallery

    function gallery(){
        cek_session_akses($this->router->fetch_class(),'gallery',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_join_one('gallery','album','id_album','id_gallery','DESC');
        }else{
            $data['record'] = $this->model_app->view_join_where('gallery','album','id_album',array('gallery.username'=>$this->session->username),'id_gallery','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_gallery/view_gallery',$data);
    }

    function tambah_gallery(){
        cek_session_akses($this->router->fetch_class(),'gallery',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_galeri/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('d');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('id_album'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_gallery'=>$this->input->post('b'),
                            'gallery_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'));
            }else{
                $data = array('id_album'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_gallery'=>$this->input->post('b'),
                            'gallery_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'),
                            'gbr_gallery'=>$hasil['file_name']);
            }
            $this->model_app->insert('gallery',$data);  
            redirect($this->router->fetch_class().'/gallery');
        }else{
            $data['record'] = $this->model_app->view_ordering('album','id_album','DESC');
            $this->template->load('administrator/template','administrator/mod_gallery/view_gallery_tambah',$data);
        }
    }

    function edit_gallery(){
        cek_session_akses($this->router->fetch_class(),'gallery',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_galeri/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('d');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('id_album'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_gallery'=>$this->input->post('b'),
                            'gallery_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'));
            }else{
                $data = array('id_album'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_gallery'=>$this->input->post('b'),
                            'gallery_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'),
                            'gbr_gallery'=>$hasil['file_name']);
            }
            $where = array('id_gallery' => $this->input->post('id'));
            $this->model_app->update('gallery', $data, $where);
            redirect($this->router->fetch_class().'/gallery');
        }else{
            $record = $this->model_app->view_ordering('album','id_album','DESC');
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('gallery', array('id_gallery' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('gallery', array('id_gallery' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses,'record' => $record);
            $this->template->load('administrator/template','administrator/mod_gallery/view_gallery_edit',$data);
        }
    }

    function delete_gallery(){
        cek_session_akses($this->router->fetch_class(),'gallery',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_gallery' => $this->uri->segment(3));
        }else{
            $id = array('id_gallery' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('gallery',$id);
        redirect($this->router->fetch_class().'/gallery');
    }


    // Controller Modul Video

    function video(){
        cek_session_akses($this->router->fetch_class(),'video',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_join_one('video','playlist','id_playlist','id_video','DESC');
        }else{
            $data['record'] = $this->model_app->view_join_where('video','playlist','id_playlist',array('video.username'=>$this->session->username),'id_video','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_video/view_video',$data);
    }

    function tambah_video(){
        cek_session_akses($this->router->fetch_class(),'video',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_video/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('d');
            $hasil=$this->upload->data();

            if ($this->input->post('f')!=''){
                $tag_seo = $this->input->post('f');
                $tag=implode(',',$tag_seo);
            }else{
                $tag = '';
            }
            
            if ($hasil['file_name']==''){
                $data = array('id_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_video'=>$this->input->post('b'),
                            'video_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'),
                            'video'=>'',
                            'youtube'=>$this->input->post('e'),
                            'dilihat'=>'0',
                            'hari'=>hari_ini(date('w')),
                            'tanggal'=>date('Y-m-d'),
                            'jam'=>date('H:i:s'),
                            'tagvid'=>$tag);
            }else{
                $data = array('id_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_video'=>$this->input->post('b'),
                            'video_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'),
                            'gbr_video'=>$hasil['file_name'],
                            'video'=>'',
                            'youtube'=>$this->input->post('e'),
                            'dilihat'=>'0',
                            'hari'=>hari_ini(date('w')),
                            'tanggal'=>date('Y-m-d'),
                            'jam'=>date('H:i:s'),
                            'tagvid'=>$tag);
            }
            $this->model_app->insert('video',$data);  
            redirect($this->router->fetch_class().'/video');
        }else{
            $data['record'] = $this->model_app->view_ordering('playlist','id_playlist','DESC');
            $data['tag'] = $this->model_app->view_ordering('tagvid','id_tag','DESC');
            $this->template->load('administrator/template','administrator/mod_video/view_video_tambah',$data);
        }
    }

    function edit_video(){
        cek_session_akses($this->router->fetch_class(),'video',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_video/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('d');
            $hasil=$this->upload->data();

            if ($this->input->post('f')!=''){
                $tag_seo = $this->input->post('f');
                $tag=implode(',',$tag_seo);
            }else{
                $tag = '';
            }

            if ($hasil['file_name']==''){
                $data = array('id_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_video'=>$this->input->post('b'),
                            'video_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'),
                            'video'=>'',
                            'youtube'=>$this->input->post('e'),
                            'tagvid'=>$tag);
            }else{
                $data = array('id_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'jdl_video'=>$this->input->post('b'),
                            'video_seo'=>seo_title($this->input->post('b')),
                            'keterangan'=>$this->input->post('c'),
                            'gbr_video'=>$hasil['file_name'],
                            'video'=>'',
                            'youtube'=>$this->input->post('e'),
                            'tagvid'=>$tag);
            }

            $where = array('id_video' => $this->input->post('id'));
            $this->model_app->update('video', $data, $where);
            redirect($this->router->fetch_class().'/video');
        }else{
            $record = $this->model_app->view_ordering('playlist','id_playlist','DESC');
            $tag = $this->model_app->view_ordering('tagvid','id_tag','DESC');
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('video', array('id_video' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('video', array('id_video' => $id, 'username' => $this->session->username))->row_array();
            }
            
            $data = array('rows' => $proses,'record' => $record, 'tag' => $tag);
            $this->template->load('administrator/template','administrator/mod_video/view_video_edit',$data);
        }
    }

    function delete_video(){
        cek_session_akses($this->router->fetch_class(),'video',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_video' => $this->uri->segment(3));
        }else{
            $id = array('id_video' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('video',$id);
        redirect($this->router->fetch_class().'/video');
    }


    // Controller Modul Playlist

    function playlist(){
        cek_session_akses($this->router->fetch_class(),'playlist',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('playlist','id_playlist','DESC');
        $this->template->load('administrator/template','administrator/mod_playlist/view_playlist',$data);
    }

    function tambah_playlist(){
        cek_session_akses($this->router->fetch_class(),'playlist',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_playlist/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('b');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('jdl_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'playlist_seo'=>seo_title($this->input->post('a')),
                            'aktif'=>'Y');
            }else{
                $data = array('jdl_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'playlist_seo'=>seo_title($this->input->post('a')),
                            'gbr_playlist'=>$hasil['file_name'],
                            'aktif'=>'Y');
            }
            $this->model_app->insert('playlist',$data);  
            redirect($this->router->fetch_class().'/playlist');
        }else{
            $this->template->load('administrator/template','administrator/mod_playlist/view_playlist_tambah');
        }
    }

    function edit_playlist(){
        cek_session_akses($this->router->fetch_class(),'playlist',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/img_playlist/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('b');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('jdl_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'playlist_seo'=>seo_title($this->input->post('a')),
                            'aktif'=>$this->input->post('c'));
            }else{
                $data = array('jdl_playlist'=>$this->input->post('a'),
                            'username'=>$this->session->username,
                            'playlist_seo'=>seo_title($this->input->post('a')),
                            'gbr_playlist'=>$hasil['file_name'],
                            'aktif'=>$this->input->post('c'));
            }
            $where = array('id_playlist' => $this->input->post('id'));
            $this->model_app->update('playlist', $data, $where);
            redirect($this->router->fetch_class().'/playlist');
        }else{
            $proses = $this->model_app->edit('playlist', array('id_playlist' => $id))->row_array();
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_playlist/view_playlist_edit',$data);
        }
    }

    function delete_playlist(){
        cek_session_akses($this->router->fetch_class(),'playlist',$this->session->id_session);
        $id = array('id_playlist' => $this->uri->segment(3));
        $this->model_app->delete('playlist',$id);
        redirect($this->router->fetch_class().'/playlist');
    }


    // Controller Modul Tag Video

    function tagvideo(){
        cek_session_akses($this->router->fetch_class(),'tagvideo',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('tagvid','id_tag','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('tagvid',array('username'=>$this->session->username),'id_tag','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_tagvideo/view_tag',$data);
    }

    function tambah_tagvideo(){
        cek_session_akses($this->router->fetch_class(),'tagvideo',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('nama_tag'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'tag_seo'=>seo_title($this->input->post('a')),
                        'count'=>'0');
            $this->model_app->insert('tagvid',$data);  
            redirect($this->router->fetch_class().'/tagvideo');
        }else{
            $this->template->load('administrator/template','administrator/mod_tagvideo/view_tag_tambah');
        }
    }

    function edit_tagvideo(){
        cek_session_akses($this->router->fetch_class(),'tagvideo',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('nama_tag'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'tag_seo'=>seo_title($this->input->post('a')));
            $where = array('id_tag' => $this->input->post('id'));
            $this->model_app->update('tagvid', $data, $where);
            redirect($this->router->fetch_class().'/tagvideo');
        }else{
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('tagvid', array('id_tag' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('tagvid', array('id_tag' => $id, 'username' => $this->session->username))->row_array();
            }
            
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_tagvideo/view_tag_edit',$data);
        }
    }

    function delete_tagvideo(){
        cek_session_akses($this->router->fetch_class(),'tagvideo',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_tag' => $this->uri->segment(3));
        }else{
            $id = array('id_tag' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('tagvid',$id);
        redirect($this->router->fetch_class().'/tagvideo');
    }


    // Controller Modul Komentar Video

    function komentarvideo(){
        cek_session_akses($this->router->fetch_class(),'komentarvideo',$this->session->id_session);
        $data['record'] = $this->model_app->view_join_one('komentarvid','video','id_video','id_komentar','DESC');
        $this->template->load('administrator/template','administrator/mod_komentarvideo/view_komentar',$data);
    }

    function edit_komentarvideo(){
        cek_session_akses($this->router->fetch_class(),'komentarvideo',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('nama_komentar'=>$this->input->post('a'),
                        'url'=>$this->input->post('b'),
                        'isi_komentar'=>$this->input->post('c'),
                        'aktif'=>$this->input->post('d'));
            $where = array('id_komentar' => $this->input->post('id'));
            $this->model_app->update('komentarvid', $data, $where);
            redirect($this->router->fetch_class().'/komentarvideo');
        }else{
            $proses = $this->model_app->edit('komentarvid', array('id_komentar' => $id))->row_array();
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_komentarvideo/view_komentar_edit',$data);
        }
    }

    function delete_komentarvideo(){
        cek_session_akses($this->router->fetch_class(),'komentarvideo',$this->session->id_session);
        $id = array('id_komentar' => $this->uri->segment(3));
        $this->model_app->delete('komentarvid',$id);
        redirect($this->router->fetch_class().'/komentarvideo');
    }

    // Controller Modul Iklan Atas

    function iklanatas(){
        cek_session_akses($this->router->fetch_class(),'iklanatas',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('iklanatas','id_iklanatas','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('iklanatas',array('username'=>$this->session->username),'id_iklanatas','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_iklanatas/view_iklanatas',$data);
    }

    function tambah_iklanatas(){
        cek_session_akses($this->router->fetch_class(),'iklanatas',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_iklanatas/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG|swf';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'tgl_posting'=>date('Y-m-d'));
            }else{
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'gambar'=>$hasil['file_name'],
                                'tgl_posting'=>date('Y-m-d'));
            }
            $this->model_app->insert('iklanatas',$data);  
            redirect($this->router->fetch_class().'/iklanatas');
        }else{
            $this->template->load('administrator/template','administrator/mod_iklanatas/view_iklanatas_tambah');
        }
    }

    function edit_iklanatas(){
        cek_session_akses($this->router->fetch_class(),'iklanatas',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_iklanatas/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG|swf';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'tgl_posting'=>date('Y-m-d'));
            }else{
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'gambar'=>$hasil['file_name'],
                                'tgl_posting'=>date('Y-m-d'));
            }
            $where = array('id_iklanatas' => $this->input->post('id'));
            $this->model_app->update('iklanatas', $data, $where);
            redirect($this->router->fetch_class().'/iklanatas');
        }else{
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('iklanatas', array('id_iklanatas' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('iklanatas', array('id_iklanatas' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_iklanatas/view_iklanatas_edit',$data);
        }
    }

    function delete_iklanatas(){
        cek_session_akses($this->router->fetch_class(),'iklanatas',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_iklanatas' => $this->uri->segment(3));
        }else{
            $id = array('id_iklanatas' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('iklanatas',$id);
        redirect($this->router->fetch_class().'/iklanatas');
    }


	// Controller Modul Iklan Home

	function iklanhome(){
		cek_session_akses($this->router->fetch_class(),'iklanhome',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('iklantengah','id_iklantengah','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('iklantengah',array('username'=>$this->session->username),'id_iklantengah','DESC');
        }
		$this->template->load('administrator/template','administrator/mod_iklanhome/view_iklanhome',$data);
	}

	function tambah_iklanhome(){
		cek_session_akses($this->router->fetch_class(),'iklanhome',$this->session->id_session);
		if (isset($_POST['submit'])){
    		$config['upload_path'] = 'asset/foto_iklantengah/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG|swf';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'tgl_posting'=>date('Y-m-d'));
            }else{
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'gambar'=>$hasil['file_name'],
                                'tgl_posting'=>date('Y-m-d'));
            }
            $this->model_app->insert('iklantengah',$data);  
			redirect($this->router->fetch_class().'/iklanhome');
		}else{
			$this->template->load('administrator/template','administrator/mod_iklanhome/view_iklanhome_tambah');
		}
	}

	function edit_iklanhome(){
		cek_session_akses($this->router->fetch_class(),'iklanhome',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_iklantengah/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG|swf';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'tgl_posting'=>date('Y-m-d'));
            }else{
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'gambar'=>$hasil['file_name'],
                                'tgl_posting'=>date('Y-m-d'));
            }
            $where = array('id_iklantengah' => $this->input->post('id'));
            $this->model_app->update('iklantengah', $data, $where);
			redirect($this->router->fetch_class().'/iklanhome');
		}else{
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('iklantengah', array('id_iklantengah' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('iklantengah', array('id_iklantengah' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_iklanhome/view_iklanhome_edit',$data);
		}
	}

	function delete_iklanhome(){
        cek_session_akses($this->router->fetch_class(),'iklanhome',$this->session->id_session);
		if ($this->session->level=='admin'){
            $id = array('id_iklantengah' => $this->uri->segment(3));
        }else{
            $id = array('id_iklantengah' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('iklantengah',$id);
		redirect($this->router->fetch_class().'/iklanhome');
	}


    // Controller Modul Iklan Sidebar

    function iklansidebar(){
        cek_session_akses($this->router->fetch_class(),'iklansidebar',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('pasangiklan','id_pasangiklan','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('pasangiklan',array('username'=>$this->session->username),'id_pasangiklan','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_iklansidebar/view_iklansidebar',$data);
    }

    function tambah_iklansidebar(){
        cek_session_akses($this->router->fetch_class(),'iklansidebar',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_pasangiklan/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG|swf';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'tgl_posting'=>date('Y-m-d'));
            }else{
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'gambar'=>$hasil['file_name'],
                                'tgl_posting'=>date('Y-m-d'));
            }
            $this->model_app->insert('pasangiklan',$data);
            redirect($this->router->fetch_class().'/iklansidebar');
        }else{
            $this->template->load('administrator/template','administrator/mod_iklansidebar/view_iklansidebar_tambah');
        }
    }

    function edit_iklansidebar(){
        cek_session_akses($this->router->fetch_class(),'iklansidebar',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_pasangiklan/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG|swf';
            $config['max_size'] = '3000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('c');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'tgl_posting'=>date('Y-m-d'));
            }else{
                $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'url'=>$this->input->post('b'),
                                'gambar'=>$hasil['file_name'],
                                'tgl_posting'=>date('Y-m-d'));
            }
            $where = array('id_pasangiklan' => $this->input->post('id'));
            $this->model_app->update('pasangiklan', $data, $where);
            redirect($this->router->fetch_class().'/iklansidebar');
        }else{
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('pasangiklan', array('id_pasangiklan' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('pasangiklan', array('id_pasangiklan' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_iklansidebar/view_iklansidebar_edit',$data);
        }
    }

    function delete_iklansidebar(){
        cek_session_akses($this->router->fetch_class(),'iklansidebar',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_pasangiklan' => $this->uri->segment(3));
        }else{
            $id = array('id_pasangiklan' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('pasangiklan',$id);
        redirect($this->router->fetch_class().'/iklansidebar');
    }


    // Controller Modul banner Link

    function banner(){
        cek_session_akses($this->router->fetch_class(),'banner',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('banner','id_banner','DESC');
        $this->template->load('administrator/template','administrator/mod_banner/view_banner',$data);
    }

    function tambah_banner(){
        cek_session_akses($this->router->fetch_class(),'banner',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                            'url'=>$this->input->post('b'),
                            'tgl_posting'=>date('Y-m-d'));
            $this->model_app->insert('banner',$data);  
            redirect($this->router->fetch_class().'/banner');
        }else{
            $this->template->load('administrator/template','administrator/mod_banner/view_banner_tambah');
        }
    }

    function edit_banner(){
        cek_session_akses($this->router->fetch_class(),'banner',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                            'url'=>$this->input->post('b'),
                            'tgl_posting'=>date('Y-m-d'));
          
            $where = array('id_banner' => $this->input->post('id'));
            $this->model_app->update('banner', $data, $where);
            redirect($this->router->fetch_class().'/banner');
        }else{
            $proses = $this->model_app->edit('banner', array('id_banner' => $id))->row_array();
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_banner/view_banner_edit',$data);
        }
    }

    function delete_banner(){
        cek_session_akses($this->router->fetch_class(),'banner',$this->session->id_session);
        $id = array('id_banner' => $this->uri->segment(3));
        $this->model_app->delete('banner',$id);
        redirect($this->router->fetch_class().'/banner');
    }


    // Controller Modul Logo

    function logowebsite(){
        cek_session_akses($this->router->fetch_class(),'logowebsite',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/logo/';
            $config['allowed_types'] = 'gif|jpg|png|JPG';
            $config['max_size'] = '2000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('logo');
            $hasil=$this->upload->data();
            $datadb = array('gambar'=>$hasil['file_name']);
            $where = array('id_logo' => $this->input->post('id'));
            $this->model_app->update('logo', $datadb, $where);
            redirect($this->router->fetch_class().'/logowebsite');
        }else{
            $data['record'] = $this->model_app->view('logo');
            $this->template->load('administrator/template','administrator/mod_logowebsite/view_logowebsite',$data);
        }
    }


    // Controller Modul Template Website

    function templatewebsite(){
        cek_session_akses($this->router->fetch_class(),'templatewebsite',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('templates','id_templates','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('templates',array('username'=>$this->session->username),'id_templates','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_template/view_template',$data);
    }

    function tambah_templatewebsite(){
        cek_session_akses($this->router->fetch_class(),'templatewebsite',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'pembuat'=>$this->input->post('b'),
                                'folder'=>$this->input->post('c'));
            $this->model_app->insert('templates',$data);
            redirect($this->router->fetch_class().'/templatewebsite');
        }else{
            $this->template->load('administrator/template','administrator/mod_template/view_template_tambah');
        }
    }

    function edit_templatewebsite(){
        cek_session_akses($this->router->fetch_class(),'templatewebsite',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('judul'=>$this->db->escape_str($this->input->post('a')),
                                'username'=>$this->session->username,
                                'pembuat'=>$this->input->post('b'),
                                'folder'=>$this->input->post('c'));
            $where = array('id_templates' => $this->input->post('id'));
            $this->model_app->update('templates', $data, $where);
            redirect($this->router->fetch_class().'/templatewebsite');
        }else{
            if ($this->session->level=='admin'){
                $proses = $this->model_app->edit('templates', array('id_templates' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('templates', array('id_templates' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_template/view_template_edit',$data);
        }
    }

    function aktif_templatewebsite(){
        cek_session_akses($this->router->fetch_class(),'templatewebsite',$this->session->id_session);
        $id = $this->uri->segment(3);
        if ($this->uri->segment(4)=='Y'){ $aktif = 'N'; }else{ $aktif = 'Y'; }

        $data = array('aktif'=>$aktif);
        $where = array('id_templates' => $id);
        $this->model_app->update('templates', $data, $where);

        $dataa = array('aktif'=>'N');
        $wheree = array('id_templates !=' => $id);
        $this->model_app->update('templates', $dataa, $wheree);

        redirect($this->router->fetch_class().'/templatewebsite');
    }

    function delete_templatewebsite(){
        cek_session_akses($this->router->fetch_class(),'templatewebsite',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_templates' => $this->uri->segment(3));
        }else{
            $id = array('id_templates' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('templates',$id);
        redirect($this->router->fetch_class().'/templatewebsite');
    }


    // Controller Modul Download

    function background(){
        cek_session_akses($this->router->fetch_class(),'background',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('gambar'=>$this->input->post('a'));
            $where = array('id_background' => 1);
            $this->model_app->update('background', $data, $where);
            redirect($this->router->fetch_class().'/background');
        }else{
            $proses = $this->model_app->edit('background', array('id_background' => 1))->row_array();
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_background/view_background',$data);
        }
    }

    // Controller Modul Sekilas Info

    function sekilasinfo(){
        cek_session_akses($this->router->fetch_class(),'sekilasinfo',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('sekilasinfo','id_sekilas','DESC');
        $this->template->load('administrator/template','administrator/mod_sekilasinfo/view_sekilasinfo',$data);
    }

    function tambah_sekilasinfo(){
        cek_session_akses($this->router->fetch_class(),'sekilasinfo',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_info/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '2500'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('b');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('info'=>$this->input->post('a'),
                              'tgl_posting'=>date('Y-m-d'),
                              'aktif'=>'Y');
            }else{
                $data = array('info'=>$this->input->post('a'),
                              'tgl_posting'=>date('Y-m-d'),
                              'gambar'=>$hasil['file_name'],
                              'aktif'=>'Y');
            }
            $this->model_app->insert('sekilasinfo',$data);
            redirect($this->router->fetch_class().'/sekilasinfo');
        }else{
            $this->template->load('administrator/template','administrator/mod_sekilasinfo/view_sekilasinfo_tambah');
        }
    }

    function edit_sekilasinfo(){
        cek_session_akses($this->router->fetch_class(),'sekilasinfo',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_info/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '2500'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('b');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('info'=>$this->input->post('a'),
                              'aktif'=>$this->input->post('f'));
            }else{
                $data = array('info'=>$this->input->post('a'),
                              'gambar'=>$hasil['file_name'],
                              'aktif'=>$this->input->post('f'));
            }

            $where = array('id_sekilas' => $this->input->post('id'));
            $this->model_app->update('sekilasinfo', $data, $where);
            redirect($this->router->fetch_class().'/sekilasinfo');
        }else{
            $proses = $this->model_app->edit('sekilasinfo', array('id_sekilas' => $id))->row_array();
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_sekilasinfo/view_sekilasinfo_edit',$data);
        }
    }

    function delete_sekilasinfo(){
        cek_session_akses($this->router->fetch_class(),'sekilasinfo',$this->session->id_session);
        $id = array('id_sekilas' => $this->uri->segment(3));
        $this->model_app->delete('sekilasinfo',$id);
        redirect($this->router->fetch_class().'/sekilasinfo');
    }



    // Controller Modul Jajak Pendapat

    function jajakpendapat(){
        cek_session_akses($this->router->fetch_class(),'jajakpendapat',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('poling','id_poling','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('poling',array('username'=>$this->session->username),'id_poling','DESC');
        }
        $this->template->load('administrator/template','administrator/mod_jajakpendapat/view_jajakpendapat',$data);
    }

    function tambah_jajakpendapat(){
        cek_session_akses($this->router->fetch_class(),'jajakpendapat',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('pilihan'=>$this->input->post('a'),
                          'status'=>$this->input->post('b'),
                          'username'=>$this->session->username,
                          'rating'=>'0',
                          'aktif'=>$this->input->post('c'));
            $this->model_app->insert('poling',$data);
            redirect($this->router->fetch_class().'/jajakpendapat');
        }else{
            $this->template->load('administrator/template','administrator/mod_jajakpendapat/view_jajakpendapat_tambah');
        }
    }

    function edit_jajakpendapat(){
        cek_session_akses($this->router->fetch_class(),'jajakpendapat',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('pilihan'=>$this->input->post('a'),
                          'status'=>$this->input->post('b'),
                          'aktif'=>$this->input->post('c'));
            $where = array('id_poling' => $this->input->post('id'));
            $this->model_app->update('poling', $data, $where);
            redirect($this->router->fetch_class().'/jajakpendapat');
        }else{
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('poling', array('id_poling' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('poling', array('id_poling' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_jajakpendapat/view_jajakpendapat_edit',$data);
        }
    }

    function delete_jajakpendapat(){
        cek_session_akses($this->router->fetch_class(),'jajakpendapat',$this->session->id_session);
        if ($this->session->level=='admin'){
            $id = array('id_poling' => $this->uri->segment(3));
        }else{
            $id = array('id_poling' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('poling',$id);
        redirect($this->router->fetch_class().'/jajakpendapat');
    }


	// Controller Modul YM

	function ym(){
		cek_session_akses($this->router->fetch_class(),'ym',$this->session->id_session);
		$data['record'] = $this->model_app->view_ordering('mod_ym','id','DESC');
		$this->template->load('administrator/template','administrator/mod_ym/view_ym',$data);
	}

	function tambah_ym(){
		cek_session_akses($this->router->fetch_class(),'ym',$this->session->id_session);
		if (isset($_POST['submit'])){
			$data = array('nama'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>seo_title($this->input->post('b')),
                        'ym_icon'=>$this->input->post('c'));
            $this->model_app->insert('mod_ym',$data);
			redirect($this->router->fetch_class().'/ym');
		}else{
			$this->template->load('administrator/template','administrator/mod_ym/view_ym_tambah');
		}
	}

	function edit_ym(){
		cek_session_akses($this->router->fetch_class(),'ym',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$data = array('nama'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>seo_title($this->input->post('b')),
                        'ym_icon'=>$this->input->post('c'));
            $where = array('id' => $this->input->post('id'));
            $this->model_app->update('mod_ym', $data, $where);
			redirect($this->router->fetch_class().'/ym');
		}else{
			$proses = $this->model_app->edit('mod_ym', array('id' => $id))->row_array();
            $data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_ym/view_ym_edit',$data);
		}
	}

	function delete_ym(){
        cek_session_akses($this->router->fetch_class(),'ym',$this->session->id_session);
		$id = array('id' => $this->uri->segment(3));
        $this->model_app->delete('mod_ym',$id);
		redirect($this->router->fetch_class().'/ym');
	}

    // Controller Modul Alamat

    function alamat(){
        cek_session_akses($this->router->fetch_class(),'alamat',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('alamat'=>$this->input->post('a'));
            $where = array('id_alamat' => 1);
            $this->model_app->update('mod_alamat', $data, $where);
            redirect($this->router->fetch_class().'/alamat');
        }else{
            $proses = $this->model_app->edit('mod_alamat', array('id_alamat' => 1))->row_array();
            $data = array('rows' => $proses);
            $this->template->load('administrator/template','administrator/mod_alamat/view_alamat',$data);
        }
    }


	// Controller Modul Pesan Masuk

	function pesanmasuk(){
		cek_session_akses($this->router->fetch_class(),'pesanmasuk',$this->session->id_session);
		$data['record'] = $this->model_app->view_ordering('hubungi','id_hubungi','DESC');
		$this->template->load('administrator/template','administrator/mod_pesanmasuk/view_pesanmasuk',$data);
	}

	function detail_pesanmasuk(){
		cek_session_akses($this->router->fetch_class(),'pesanmasuk',$this->session->id_session);
		$id = $this->uri->segment(3);
		$this->db->query("UPDATE hubungi SET dibaca='Y' where id_hubungi='$id'");
		if (isset($_POST['submit'])){
			$nama           = $this->input->post('a');
            $email           = $this->input->post('b');
            $subject         = $this->input->post('c');
            $message         = $this->input->post('isi')." <br><hr><br> ".$this->input->post('d');
            
            $this->email->from('robby.prihandaya@gmail.com', 'posnetindo.co.id');
            $this->email->to($email);
            $this->email->cc('');
            $this->email->bcc('');

            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->set_mailtype("html");
            $this->email->send();
            
            $config['protocol'] = 'sendmail';
            $config['mailpath'] = '/usr/sbin/sendmail';
            $config['charset'] = 'utf-8';
            $config['wordwrap'] = TRUE;
            $config['mailtype'] = 'html';
            $this->email->initialize($config);

			$proses = $this->model_app->edit('hubungi', array('id_hubungi' => $id))->row_array();
            $data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_pesanmasuk/view_pesanmasuk_detail',$data);
		}else{
			$proses = $this->model_app->edit('hubungi', array('id_hubungi' => $id))->row_array();
            $data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_pesanmasuk/view_pesanmasuk_detail',$data);
		}
	}

	function delete_pesanmasuk(){
        cek_session_akses($this->router->fetch_class(),'pesanmasuk',$this->session->id_session);
		$id = array('id_hubungi' => $this->uri->segment(3));
        $this->model_app->delete('hubungi',$id);
		redirect($this->router->fetch_class().'/pesanmasuk');
	}


	// Controller Modul User

	function manajemenuser(){
		cek_session_akses($this->router->fetch_class(),'manajemenuser',$this->session->id_session);
		$data['record'] = $this->model_app->view_ordering('users','username','DESC');
		$this->template->load('administrator/template','administrator/mod_users/view_users',$data);
	}

	function tambah_manajemenuser(){
		cek_session_akses($this->router->fetch_class(),'manajemenuser',$this->session->id_session);
		$id = $this->session->username;
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_user/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '1000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('f');
            $fileName = $this->upload->data()['file_name'];
            $fileName = str_replace(' ','_',$fileName);
            $user = trim(preg_replace('/([^A-Za-z0-9_]+)/', ' ', $this->input->post('a')));
            $user = mb_strtolower(str_replace(' ', '', $user));
            if ($fileName==''){
                    $data = array('username'=>$user,
                                    'password'=>hash("sha512", md5($this->input->post('b'))),
                                    'nama_lengkap'=>$this->db->escape_str($this->input->post('c')),
                                    'email'=>$this->db->escape_str($this->input->post('d')),
                                    'no_telp'=>$this->db->escape_str($this->input->post('e')),
                                    'level'=>$this->db->escape_str($this->input->post('g')),
                                    'blokir'=>'N',
                                    'id_session'=>md5($this->input->post('a')).'-'.date('YmdHis'));
            }else{
                    $data = array('username'=>$user,
                                    'password'=>hash("sha512", md5($this->input->post('b'))),
                                    'nama_lengkap'=>$this->db->escape_str($this->input->post('c')),
                                    'email'=>$this->db->escape_str($this->input->post('d')),
                                    'no_telp'=>$this->db->escape_str($this->input->post('e')),
                                    'foto'=>$fileName,
                                    'level'=>$this->db->escape_str($this->input->post('g')),
                                    'blokir'=>'N',
                                    'id_session'=>md5($this->input->post('a')).'-'.date('YmdHis'));
            }
            $this->model_app->insert('users',$data);
              $mod=count($this->input->post('modul'));
              $modul=$this->input->post('modul');
              $sess = md5($this->input->post('a')).'-'.date('YmdHis');
              for($i=0;$i<$mod;$i++){
                $datam = array('id_session'=>$sess,
                              'id_modul'=>$modul[$i]);
                $this->model_app->insert('users_modul',$datam);
              }
			redirect($this->router->fetch_class().'/edit_manajemenuser/'.$user);
		}else{
            $proses = $this->model_app->view_where_ordering('modul', array('publish' => 'Y','status' => 'user'), 'id_modul','DESC');
            $data = array('record' => $proses);
			$this->template->load('administrator/template','administrator/mod_users/view_users_tambah',$data);
		}
	}

	function edit_manajemenuser(){
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
			$config['upload_path'] = 'asset/foto_user/';
            $config['allowed_types'] = 'gif|jpg|png|JPG|JPEG';
            $config['max_size'] = '1000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('f');
            $fileName=$this->upload->data()['file_name'];
            $fileName = str_replace(' ','_',$fileName);
            if ($fileName=='' AND $this->input->post('b') ==''){
                    $data = array('username'=>$this->db->escape_str($this->input->post('a')),
                                    'nama_lengkap'=>$this->db->escape_str($this->input->post('c')),
                                    'email'=>$this->db->escape_str($this->input->post('d')),
                                    'no_telp'=>$this->db->escape_str($this->input->post('e')),
                                    'blokir'=>$this->db->escape_str($this->input->post('h')));
            }elseif ($fileName!='' AND $this->input->post('b') ==''){
                    $data = array('username'=>$this->db->escape_str($this->input->post('a')),
                                    'nama_lengkap'=>$this->db->escape_str($this->input->post('c')),
                                    'email'=>$this->db->escape_str($this->input->post('d')),
                                    'no_telp'=>$this->db->escape_str($this->input->post('e')),
                                    'foto'=>$fileName,
                                    'blokir'=>$this->db->escape_str($this->input->post('h')));
            }elseif ($fileName=='' AND $this->input->post('b') !=''){
                    $data = array('username'=>$this->db->escape_str($this->input->post('a')),
                                    'password'=>hash("sha512", md5($this->input->post('b'))),
                                    'nama_lengkap'=>$this->db->escape_str($this->input->post('c')),
                                    'email'=>$this->db->escape_str($this->input->post('d')),
                                    'no_telp'=>$this->db->escape_str($this->input->post('e')),
                                    'blokir'=>$this->db->escape_str($this->input->post('h')));
            }elseif ($fileName!='' AND $this->input->post('b') !=''){
                    $data = array('username'=>$this->db->escape_str($this->input->post('a')),
                                    'password'=>hash("sha512", md5($this->input->post('b'))),
                                    'nama_lengkap'=>$this->db->escape_str($this->input->post('c')),
                                    'email'=>$this->db->escape_str($this->input->post('d')),
                                    'no_telp'=>$this->db->escape_str($this->input->post('e')),
                                    'foto'=>$fileName,
                                    'blokir'=>$this->db->escape_str($this->input->post('h')));
            }
            $where = array('username' => $this->input->post('id'));
            $this->model_app->update('users', $data, $where);
            $mod=count($this->input->post('modul'));
            $modul=$this->input->post('modul');
            for($i=0;$i<$mod;$i++){
              $datam = array('id_session'=>$this->input->post('ids'),
                              'id_modul'=>$modul[$i]);
                $this->model_app->insert('users_modul',$datam);
            }
			redirect($this->router->fetch_class().'/edit_manajemenuser/'.$this->input->post('id'));
		}else{
            if ($this->session->username==$this->uri->segment(3) OR $this->session->level=='admin'){
                $proses = $this->model_app->edit('users', array('username' => $id))->row_array();
                $akses = $this->model_app->view_join_where('users_modul','modul','id_modul', array('id_session' => $proses['id_session']),'id_umod','DESC');
                $modul = $this->model_app->view_where_ordering('modul', array('publish' => 'Y','status' => 'user'), 'id_modul','DESC');
                $data = array('rows' => $proses, 'record' => $modul, 'akses' => $akses);
    			$this->template->load('administrator/template','administrator/mod_users/view_users_edit',$data);
            }else{
                redirect($this->router->fetch_class().'/edit_manajemenuser/'.$this->session->username);
            }
		}
	}

	function delete_manajemenuser(){
        cek_session_akses($this->router->fetch_class(),'manajemenuser',$this->session->id_session);
		$id = array('username' => $this->uri->segment(3));
        $this->model_app->delete('users',$id);
		redirect($this->router->fetch_class().'/manajemenuser');
	}

    function delete_akses(){
        cek_session_admin();
        $id = array('id_umod' => $this->uri->segment(3));
        $this->model_app->delete('users_modul',$id);
        redirect($this->router->fetch_class().'/edit_manajemenuser/'.$this->uri->segment(4));
    }

	

	// Controller Modul Modul

	function manajemenmodul(){
		cek_session_akses($this->router->fetch_class(),'manajemenmodul',$this->session->id_session);
        if ($this->session->level=='admin'){
            $data['record'] = $this->model_app->view_ordering('modul','id_modul','DESC');
        }else{
            $data['record'] = $this->model_app->view_where_ordering('modul',array('username'=>$this->session->username),'id_modul','DESC');
        }
		$this->template->load('administrator/template','administrator/mod_modul/view_modul',$data);
	}

	function tambah_manajemenmodul(){
		cek_session_akses($this->router->fetch_class(),'manajemenmodul',$this->session->id_session);
		if (isset($_POST['submit'])){
			$data = array('nama_modul'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'link'=>$this->db->escape_str($this->input->post('b')),
                        'static_content'=>'',
                        'gambar'=>'',
                        'publish'=>$this->db->escape_str($this->input->post('c')),
                        'status'=>$this->db->escape_str($this->input->post('e')),
                        'aktif'=>$this->db->escape_str($this->input->post('d')),
                        'urutan'=>'0',
                        'link_seo'=>'');
            $this->model_app->insert('modul',$data);
			redirect($this->router->fetch_class().'/manajemenmodul');
		}else{
			$this->template->load('administrator/template','administrator/mod_modul/view_modul_tambah');
		}
	}

	function edit_manajemenmodul(){
		cek_session_akses($this->router->fetch_class(),'manajemenmodul',$this->session->id_session);
		$id = $this->uri->segment(3);
		if (isset($_POST['submit'])){
            $data = array('nama_modul'=>$this->db->escape_str($this->input->post('a')),
                        'username'=>$this->session->username,
                        'link'=>$this->db->escape_str($this->input->post('b')),
                        'static_content'=>'',
                        'gambar'=>'',
                        'publish'=>$this->db->escape_str($this->input->post('c')),
                        'status'=>$this->db->escape_str($this->input->post('e')),
                        'aktif'=>$this->db->escape_str($this->input->post('d')),
                        'urutan'=>'0',
                        'link_seo'=>'');
            $where = array('id_modul' => $this->input->post('id'));
            $this->model_app->update('modul', $data, $where);
			redirect($this->router->fetch_class().'/manajemenmodul');
		}else{
            if ($this->session->level=='admin'){
                 $proses = $this->model_app->edit('modul', array('id_modul' => $id))->row_array();
            }else{
                $proses = $this->model_app->edit('modul', array('id_modul' => $id, 'username' => $this->session->username))->row_array();
            }
            $data = array('rows' => $proses);
			$this->template->load('administrator/template','administrator/mod_modul/view_modul_edit',$data);
		}
	}

	function delete_manajemenmodul(){
        cek_session_akses($this->router->fetch_class(),'manajemenmodul',$this->session->id_session);
		if ($this->session->level=='admin'){
            $id = array('id_modul' => $this->uri->segment(3));
        }else{
            $id = array('id_modul' => $this->uri->segment(3), 'username'=>$this->session->username);
        }
        $this->model_app->delete('modul',$id);
		redirect($this->router->fetch_class().'/manajemenmodul');
	}



    // RESELLER MODUL ==============================================================================================================================================================================

        // Controller Modul Konsumen

    function konsumen(){
        cek_session_akses($this->router->fetch_class(),'konsumen',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_konsumen','id_konsumen','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_konsumen/view_konsumen',$data);
    }

    function tambah_konsumen(){
        cek_session_akses($this->router->fetch_class(),'konsumen',$this->session->id_session);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_user/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '5000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('gg');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                $data = array('username'=>$this->input->post('aa'),
                            'password'=>hash("sha512", md5($this->input->post('a'))),
                            'nama_lengkap'=>$this->input->post('b'),
                            'email'=>$this->input->post('c'),
                            'jenis_kelamin'=>$this->input->post('d'),
                            'tanggal_lahir'=>$this->input->post('e'),
                            'alamat_lengkap'=>$this->input->post('g'),
                            'no_hp'=>$this->input->post('k'),
                            'kecamatan'=>$this->input->post('ia'),
                            'kota_id'=>$this->input->post('ga'),
                            'tanggal_daftar'=>date('Y-m-d'));
            }else{
                $data = array('username'=>$this->input->post('aa'),
                            'password'=>hash("sha512", md5($this->input->post('a'))),
                            'nama_lengkap'=>$this->input->post('b'),
                            'email'=>$this->input->post('c'),
                            'jenis_kelamin'=>$this->input->post('d'),
                            'tanggal_lahir'=>$this->input->post('e'),
                            'alamat_lengkap'=>$this->input->post('g'),
                            'no_hp'=>$this->input->post('k'),
                            'kecamatan'=>$this->input->post('ia'),
                            'kota_id'=>$this->input->post('ga'),
                            'foto'=>$hasil['file_name'],
                            'tanggal_daftar'=>date('Y-m-d'));
            }
            $this->model_app->insert('rb_konsumen',$data);
            redirect($this->router->fetch_class().'/konsumen');
        }else{
            $data['negara'] = $this->model_app->view_ordering('rb_provinsi','provinsi_id','DESC');
            $this->template->load('administrator/template','administrator/additional/mod_konsumen/view_konsumen_tambah',$data);
        }
    }

    function edit_konsumen(){
        cek_session_akses($this->router->fetch_class(),'konsumen',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_user/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '5000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('gg');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                if (trim($this->input->post('a')) != ''){
                    $data = array('password'=>hash("sha512", md5($this->input->post('a'))),
                                    'nama_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('b'))),
                                    'email'=>$this->db->escape_str(strip_tags($this->input->post('c'))),
                                    'jenis_kelamin'=>$this->db->escape_str($this->input->post('d')),
                                    'tanggal_lahir'=>$this->db->escape_str($this->input->post('e')),
                                    'alamat_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('g'))),
                                    'kecamatan'=>$this->input->post('ia'),
                                    'kota_id'=>$this->input->post('ga'),
                                    'no_hp'=>$this->input->post('k'));
                }else{
                   $data = array('nama_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('b'))),
                                    'email'=>$this->db->escape_str(strip_tags($this->input->post('c'))),
                                    'jenis_kelamin'=>$this->db->escape_str($this->input->post('d')),
                                    'tanggal_lahir'=>$this->db->escape_str($this->input->post('e')),
                                    'alamat_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('g'))),
                                    'kecamatan'=>$this->input->post('ia'),
                                    'kota_id'=>$this->input->post('ga'),
                                    'no_hp'=>$this->input->post('k'));
                }
            }else{
                if (trim($this->input->post('a')) != ''){
                    $data = array('password'=>hash("sha512", md5($this->input->post('a'))),
                                    'nama_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('b'))),
                                    'email'=>$this->db->escape_str(strip_tags($this->input->post('c'))),
                                    'jenis_kelamin'=>$this->db->escape_str($this->input->post('d')),
                                    'tanggal_lahir'=>$this->db->escape_str($this->input->post('e')),
                                    'alamat_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('g'))),
                                    'kecamatan'=>$this->input->post('ia'),
                                    'kota_id'=>$this->input->post('ga'),
                                    'no_hp'=>$this->input->post('k'),
                                    'foto'=>$hasil['file_name']);
                }else{
                   $data = array('nama_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('b'))),
                                    'email'=>$this->db->escape_str(strip_tags($this->input->post('c'))),
                                    'jenis_kelamin'=>$this->db->escape_str($this->input->post('d')),
                                    'tanggal_lahir'=>$this->db->escape_str($this->input->post('e')),
                                    'alamat_lengkap'=>$this->db->escape_str(strip_tags($this->input->post('g'))),
                                    'kecamatan'=>$this->input->post('ia'),
                                    'kota_id'=>$this->input->post('ga'),
                                    'no_hp'=>$this->input->post('k'),
                                    'foto'=>$hasil['file_name']);
                }
            }
            $where = array('id_konsumen' => $this->input->post('id'));
            $this->model_app->update('rb_konsumen', $data, $where);
            redirect($this->router->fetch_class().'/detail_konsumen/'.$this->input->post('id'));
        }else{
            $data['rows'] = $this->model_reseller->profile_konsumen($id)->row_array();
            $row = $this->model_reseller->profile_konsumen($id)->row_array();
            $data['provinsi'] = $this->model_app->view_ordering('rb_provinsi','provinsi_id','ASC');
            $data['kota'] = $this->model_app->view_ordering('rb_kota','kota_id','ASC');
            $data['rowse'] = $this->db->query("SELECT provinsi_id FROM rb_kota where kota_id='$row[kota_id]'")->row_array();
            $this->template->load('administrator/template','administrator/additional/mod_konsumen/view_konsumen_edit',$data);
        }
    }
    
    function detail_konsumen(){
        cek_session_akses($this->router->fetch_class(),'konsumen',$this->session->id_session);
        $id = $this->uri->segment(3);
        $record = $this->model_reseller->orders_report($id,'reseller');
        $edit = $this->model_reseller->profile_konsumen($id)->row_array();
        $data = array('rows' => $edit,'record'=>$record);
        $this->template->load('administrator/template','administrator/additional/mod_konsumen/view_konsumen_detail',$data);
    }

    function delete_konsumen(){
        cek_session_akses($this->router->fetch_class(),'konsumen',$this->session->id_session);
        $id = array('id_konsumen' => $this->uri->segment(3));
        $this->model_app->delete('rb_konsumen',$id);
        redirect($this->router->fetch_class().'/konsumen');
    }



    // Controller Modul Reseller

    function reseller(){
        cek_session_akses($this->router->fetch_class(),'reseller',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_reseller','id_reseller','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_reseller/view_reseller',$data);
    }

    function tambah_reseller(){
        cek_session_akses($this->router->fetch_class(),'reseller',$this->session->id_session);
        if (isset($_POST['submit'])){
            $cek  = $this->model_app->view_where('rb_reseller',array('username'=>$this->input->post('a')))->num_rows();
            if ($cek >= 1){
                $username = $this->input->post('a');
                echo "<script>window.alert('Maaf, Username $username sudah dipakai oleh orang lain!');
                                  window.location=('index.php?view=login')</script>";
            }else{
                $route = array('administrator','agenda','auth','berita','contact','download','gallery','konfirmasi','main','members','page','produk','reseller','testimoni','video');
                if (in_array($this->input->post('a'), $route)){
                    $username = $this->input->post('a');
                    echo "<script>window.alert('Maaf, Username $username sudah dipakai oleh orang lain!');
                                      window.location=('".base_url()."/".$this->input->post('i')."')</script>";
                }else{
                $config['upload_path'] = 'asset/foto_user/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = '5000'; // kb
                $this->load->library('upload', $config);
                $this->upload->do_upload('gg');
                $hasil=$this->upload->data();
                if ($hasil['file_name']==''){
                    $data = array('username'=>$this->input->post('a'),
                                'password'=>hash("sha512", md5($this->input->post('b'))),
                                'nama_reseller'=>$this->input->post('c'),
                                'jenis_kelamin'=>$this->input->post('d'),
                                'alamat_lengkap'=>$this->input->post('e'),
                                'no_telpon'=>$this->input->post('f'),
                                'email'=>$this->input->post('g'),
                                'kode_pos'=>$this->input->post('h'),
                                'keterangan'=>$this->input->post('i'),
                                'referral'=>$this->input->post('j'),
                                'tanggal_daftar'=>date('Y-m-d'));
                }else{
                    $data = array('username'=>$this->input->post('a'),
                                'password'=>hash("sha512", md5($this->input->post('b'))),
                                'nama_reseller'=>$this->input->post('c'),
                                'jenis_kelamin'=>$this->input->post('d'),
                                'alamat_lengkap'=>$this->input->post('e'),
                                'no_telpon'=>$this->input->post('f'),
                                'email'=>$this->input->post('g'),
                                'kode_pos'=>$this->input->post('h'),
                                'keterangan'=>$this->input->post('i'),
                                'foto'=>$hasil['file_name'],
                                'referral'=>$this->input->post('j'),
                                'tanggal_daftar'=>date('Y-m-d'));
                }
                $this->model_app->insert('rb_reseller',$data);
                redirect($this->router->fetch_class().'/reseller');
                }
            }
        }else{
            $this->template->load('administrator/template','administrator/additional/mod_reseller/view_reseller_tambah');
        }
    }

    function edit_reseller(){
        cek_session_akses($this->router->fetch_class(),'reseller',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $config['upload_path'] = 'asset/foto_user/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '5000'; // kb
            $this->load->library('upload', $config);
            $this->upload->do_upload('gg');
            $hasil=$this->upload->data();
            if ($hasil['file_name']==''){
                if (trim($this->input->post('b')) != ''){
                    $data = array('password'=>hash("sha512", md5($this->input->post('b'))),
                                'nama_reseller'=>$this->input->post('c'),
                                'jenis_kelamin'=>$this->input->post('d'),
                                'alamat_lengkap'=>$this->input->post('e'),
                                'no_telpon'=>$this->input->post('f'),
                                'email'=>$this->input->post('g'),
                                'kode_pos'=>$this->input->post('h'),
                                'keterangan'=>$this->input->post('i'),
                                'referral'=>$this->input->post('j'));
                }else{
                   $data = array('nama_reseller'=>$this->input->post('c'),
                                'jenis_kelamin'=>$this->input->post('d'),
                                'alamat_lengkap'=>$this->input->post('e'),
                                'no_telpon'=>$this->input->post('f'),
                                'email'=>$this->input->post('g'),
                                'kode_pos'=>$this->input->post('h'),
                                'keterangan'=>$this->input->post('i'),
                                'referral'=>$this->input->post('j'));
                }
            }else{
                if (trim($this->input->post('b')) != ''){
                    $data = array('password'=>hash("sha512", md5($this->input->post('b'))),
                                'nama_reseller'=>$this->input->post('c'),
                                'jenis_kelamin'=>$this->input->post('d'),
                                'alamat_lengkap'=>$this->input->post('e'),
                                'no_telpon'=>$this->input->post('f'),
                                'email'=>$this->input->post('g'),
                                'kode_pos'=>$this->input->post('h'),
                                'keterangan'=>$this->input->post('i'),
                                'foto'=>$hasil['file_name'],
                                'referral'=>$this->input->post('j'));
                } else{
                   $data = array('nama_reseller'=>$this->input->post('c'),
                                'jenis_kelamin'=>$this->input->post('d'),
                                'alamat_lengkap'=>$this->input->post('e'),
                                'no_telpon'=>$this->input->post('f'),
                                'email'=>$this->input->post('g'),
                                'kode_pos'=>$this->input->post('h'),
                                'keterangan'=>$this->input->post('i'),
                                'foto'=>$hasil['file_name'],
                                'referral'=>$this->input->post('j'));
                }
            }
            $where = array('id_reseller' => $this->input->post('id'));
            $this->model_app->update('rb_reseller', $data, $where);
            redirect($this->router->fetch_class().'/reseller');
        }else{
            $edit = $this->model_app->edit('rb_reseller',array('id_reseller'=>$id))->row_array();
            $data = array('rows' => $edit);
            $this->template->load('administrator/template','administrator/additional/mod_reseller/view_reseller_edit',$data);
        }
    }
    
    function detail_reseller(){
        cek_session_akses($this->router->fetch_class(),'reseller',$this->session->id_session);
        $id = $this->uri->segment(3);
        $record = $this->model_reseller->reseller_pembelian($id,'admin');
        $penjualan = $this->model_reseller->penjualan_list_konsumen($id,'reseller');
        $edit = $this->model_app->edit('rb_reseller',array('id_reseller'=>$id))->row_array();
        $reward = $this->model_app->view_ordering('rb_reward','id_reward','ASC');

        $data = array('rows' => $edit,'record'=>$record,'penjualan'=>$penjualan,'reward'=>$reward);
        $this->template->load('administrator/template','administrator/additional/mod_reseller/view_reseller_detail',$data);
    }

    function delete_reseller(){
        cek_session_akses($this->router->fetch_class(),'reseller',$this->session->id_session);
        $id = array('id_reseller' => $this->uri->segment(3));
        $this->model_app->delete('rb_reseller',$id);
        redirect($this->router->fetch_class().'/reseller');
    }


    // Controller Modul Supplier

    function supplier(){
        cek_session_akses($this->router->fetch_class(),'supplier',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_supplier','id_supplier','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_supplier/view_supplier',$data);
    }

    function tambah_supplier(){
        cek_session_akses($this->router->fetch_class(),'supplier',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('nama_supplier'=>$this->input->post('a'),
                        'kontak_person'=>$this->input->post('b'),
                        'alamat_lengkap'=>$this->input->post('c'),
                        'no_hp'=>$this->input->post('d'),
                        'alamat_email'=>$this->input->post('e'),
                        'kode_pos'=>$this->input->post('f'),
                        'no_telpon'=>$this->input->post('g'),
                        'fax'=>$this->input->post('h'),
                        'keterangan'=>$this->input->post('i'));
            $this->model_app->insert('rb_supplier',$data);
            redirect($this->router->fetch_class().'/supplier');
        }else{
            $this->template->load('administrator/template','administrator/additional/mod_supplier/view_supplier_tambah',$data);
        }
    }

    function edit_supplier(){
        cek_session_akses($this->router->fetch_class(),'supplier',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
           $data = array('nama_supplier'=>$this->input->post('a'),
                        'kontak_person'=>$this->input->post('b'),
                        'alamat_lengkap'=>$this->input->post('c'),
                        'no_hp'=>$this->input->post('d'),
                        'alamat_email'=>$this->input->post('e'),
                        'kode_pos'=>$this->input->post('f'),
                        'no_telpon'=>$this->input->post('g'),
                        'fax'=>$this->input->post('h'),
                        'keterangan'=>$this->input->post('i'));
            $where = array('id_supplier' => $this->input->post('id'));
            $this->model_app->update('rb_supplier', $data, $where);
            redirect($this->router->fetch_class().'/detail_supplier/'.$this->input->post('id'));
        }else{
            $data['rows'] = $this->model_app->view_where('rb_supplier',array('id_supplier'=>$id))->row_array();
            $this->template->load('administrator/template','administrator/additional/mod_supplier/view_supplier_edit',$data);
        }
    }
    
    function detail_supplier(){
        cek_session_akses($this->router->fetch_class(),'supplier',$this->session->id_session);
        $id = $this->uri->segment(3);
        $edit = $this->model_app->view_where('rb_supplier',array('id_supplier'=>$id))->row_array();
        $data = array('rows' => $edit);
        $this->template->load('administrator/template','administrator/additional/mod_supplier/view_supplier_detail',$data);
    }

    function delete_supplier(){
        cek_session_akses($this->router->fetch_class(),'supplier',$this->session->id_session);
        $id = array('id_supplier' => $this->uri->segment(3));
        $this->model_app->delete('rb_supplier',$id);
        redirect($this->router->fetch_class().'/supplier');
    }


    // Controller Modul Kategori Produk

    function kategori_produk(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_kategori_produk','id_kategori_produk','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_kategori_produk/view_kategori_produk',$data);
    }

    function tambah_kategori_produk(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('nama_kategori'=>$this->input->post('a'),'kategori_seo'=>seo_title($this->input->post('a')));
            $this->model_app->insert('rb_kategori_produk',$data);
            redirect($this->router->fetch_class().'/kategori_produk');
        }else{
            $this->template->load('administrator/template','administrator/additional/mod_kategori_produk/view_kategori_produk_tambah');
        }
    }

    function edit_kategori_produk(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('nama_kategori'=>$this->input->post('a'),'kategori_seo'=>seo_title($this->input->post('a')));
            $where = array('id_kategori_produk' => $this->input->post('id'));
            $this->model_app->update('rb_kategori_produk', $data, $where);
            redirect($this->router->fetch_class().'/kategori_produk');
        }else{
            $edit = $this->model_app->edit('rb_kategori_produk',array('id_kategori_produk'=>$id))->row_array();
            $data = array('rows' => $edit);
            $this->template->load('administrator/template','administrator/additional/mod_kategori_produk/view_kategori_produk_edit',$data);
        }
    }

    function delete_kategori_produk(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk',$this->session->id_session);
        $id = array('id_kategori_produk' => $this->uri->segment(3));
        $this->model_app->delete('rb_kategori_produk',$id);
        redirect($this->router->fetch_class().'/kategori_produk');
    }


    // Controller Modul Sub Kategori Produk

    function kategori_produk_sub(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk_sub',$this->session->id_session);
        $data['record'] = $this->db->query("SELECT * FROM rb_kategori_produk_sub a JOIN rb_kategori_produk b ON a.id_kategori_produk=b.id_kategori_produk ORDER BY a.id_kategori_produk_sub DESC");
        $this->template->load('administrator/template','administrator/additional/mod_kategori_produk/view_kategori_produk_sub',$data);
    }

    function tambah_kategori_produk_sub(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk_sub',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('id_kategori_produk'=>$this->input->post('b'),
                          'nama_kategori_sub'=>$this->input->post('a'),
                          'kategori_seo_sub'=>seo_title($this->input->post('a')));
            $this->model_app->insert('rb_kategori_produk_sub',$data);
            redirect($this->router->fetch_class().'/kategori_produk_sub');
        }else{
            $this->template->load('administrator/template','administrator/additional/mod_kategori_produk/view_kategori_produk_tambah_sub');
        }
    }

    function edit_kategori_produk_sub(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk_sub',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('id_kategori_produk'=>$this->input->post('b'),
                          'nama_kategori_sub'=>$this->input->post('a'),
                          'kategori_seo_sub'=>seo_title($this->input->post('a')));
            $where = array('id_kategori_produk_sub' => $this->input->post('id'));
            $this->model_app->update('rb_kategori_produk_sub', $data, $where);
            redirect($this->router->fetch_class().'/kategori_produk_sub');
        }else{
            $edit = $this->model_app->edit('rb_kategori_produk_sub',array('id_kategori_produk_sub'=>$id))->row_array();
            $data = array('rows' => $edit);
            $this->template->load('administrator/template','administrator/additional/mod_kategori_produk/view_kategori_produk_edit_sub',$data);
        }
    }

    function delete_kategori_produk_sub(){
        cek_session_akses($this->router->fetch_class(),'kategori_produk_sub',$this->session->id_session);
        $id = array('id_kategori_produk_sub' => $this->uri->segment(3));
        $this->model_app->delete('rb_kategori_produk_sub',$id);
        redirect($this->router->fetch_class().'/kategori_produk_sub');
    }


    // Controller Modul Produk

    function produk(){
        cek_session_akses($this->router->fetch_class(),'produk',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_produk','id_produk','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_produk/view_produk',$data);
    }

    function tambah_produk(){
        cek_session_akses($this->router->fetch_class(),'produk',$this->session->id_session);
        if (isset($_POST['submit'])){
            $cpt = count($_FILES['userfile']['name']);
			if($cpt>6){
				$_SESSION['mes'] = 'Input Tidak Valid.';
				redirect($this->router->fetch_class().'/tambah_produk');
			} else{
				$array = array('jpg','png','gif','jpeg');
				$true = true;
				for($i=0; $i<$cpt; $i++){
					if(!(empty($_FILES['userfile']['name'][$i]))){
						if(!in_array(@end(@explode('.',$_FILES['userfile']['name'][$i])), $array)){
							$true = false;
						}	
					}
				}
				if(!$true){
					$_SESSION['mes'] = 'Foto produk tidak valid..';
                    redirect($this->router->fetch_class().'/tambah_produk');
				} else{			
                    $files = $_FILES;
                    for($i=0; $i<$cpt; $i++){
                        $_FILES['userfile']['name']= $files['userfile']['name'][$i];
                        $_FILES['userfile']['type']= $files['userfile']['type'][$i];
                        $_FILES['userfile']['tmp_name']= $files['userfile']['tmp_name'][$i];
                        $_FILES['userfile']['error']= $files['userfile']['error'][$i];
                        $_FILES['userfile']['size']= $files['userfile']['size'][$i];
                        $this->load->library('upload');
                        $this->upload->initialize($this->set_upload_options());
                        $this->upload->do_upload();
                        $fileName = $this->upload->data()['file_name'];
                        $images[] = $fileName;
                    }
                    $fileName = implode(';',$images);
                    $fileName = str_replace(' ','_',$fileName);
					$cek_seo = $this->db->query("SELECT * FROM `rb_produk` WHERE `produk_seo`='".seo_title($this->input->post('b'))."'");
					if ($cek_seo->num_rows()>0){
						$seo = seo_title($this->input->post('b')).'-'.($cek_seo->num_rows()+1);
					} else{
						$seo = seo_title($this->input->post('b'));
					}
                    if (trim($fileName)!=''){
                        $data = array('id_kategori_produk'=>$this->input->post('a'),
                                    'id_kategori_produk_sub'=>$this->input->post('aa'),
                                    'nama_produk'=>$this->input->post('b'),
                                    'produk_seo'=>$seo,
                                    'satuan'=>$this->input->post('c'),
                                    'harga_beli'=>$this->input->post('d'),
                                    'harga_reseller'=>$this->input->post('e'),
                                    'harga_konsumen'=>$this->input->post('f'),
                                    'berat'=>$this->input->post('berat'),
                                    'gambar'=>$fileName,
                                    'keterangan'=>$this->input->post('ff'),
                                    'username'=>$this->session->username,
                                    'waktu_input'=>date('Y-m-d H:i:s'));
                    }else{
                        $data = array('id_kategori_produk'=>$this->input->post('a'),
                                    'id_kategori_produk_sub'=>$this->input->post('aa'),
                                    'nama_produk'=>$this->input->post('b'),
                                    'produk_seo'=>$seo,
                                    'satuan'=>$this->input->post('c'),
                                    'harga_beli'=>$this->input->post('d'),
                                    'harga_reseller'=>$this->input->post('e'),
                                    'harga_konsumen'=>$this->input->post('f'),
                                    'berat'=>$this->input->post('berat'),
                                    'keterangan'=>$this->input->post('ff'),
                                    'username'=>$this->session->username,
                                    'waktu_input'=>date('Y-m-d H:i:s'));
                    }
                    $this->model_app->insert('rb_produk',$data);
                    $id_produk = $this->db->insert_id();
                    $kode_produk = array(
                        'produk_id'	=>$id_produk,
                        'kode'		=>'PRD'.$id_produk.date('m').date('n').substr(time(), -2),
                        'viewer'	=> 0
                    );
                    $this->model_app->insert('kode_produk',$kode_produk);
                    /*
                    if ($this->input->post('stok') != ''){
						$kode_transaksi = "TRX-".date('YmdHis');
						$data = array('kode_transaksi'=>$kode_transaksi,
									'id_pembeli'=>$this->session->id_admin,
									'id_penjual'=>'1',
									'status_pembeli'=>'reseller',
									'status_penjual'=>'admin',
									'service'=>'Stok Otomatis (Pribadi)',
									'waktu_transaksi'=>date('Y-m-d H:i:s'),
									'proses'=>'1');
						$this->model_app->insert('rb_penjualan',$data);
						$idp = $this->db->insert_id();

						$data = array('id_penjualan'=>$idp,
									'id_produk'=>$id_produk,
									'jumlah'=>$this->input->post('stok'),
									'harga_jual'=>$this->input->post('e'),
									'satuan'=>$this->input->post('c'));
						$this->model_app->insert('rb_penjualan_detail',$data);
                    }
                    */
                    redirect($this->router->fetch_class().'/produk');
                }
            }

        }else{
            $data['record'] = $this->model_app->view_ordering('rb_kategori_produk','id_kategori_produk','DESC');
            $this->template->load('administrator/template','administrator/additional/mod_produk/view_produk_tambah',$data);
        }
    }

    function edit_produk(){
        cek_session_akses($this->router->fetch_class(),'produk',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $cpt = count($_FILES['userfile']['name']);
			if($cpt>6){
				$_SESSION['mes'] = 'Input Tidak Valid.';
                redirect($this->router->fetch_class().'/edit_produk/'.$this->input->post('produk_id'));
			} else{
				$array = array('jpg','png','gif','jpeg');
				$true = true;
				for($i=0; $i<$cpt; $i++){
					if(!(empty($_FILES['userfile']['name'][$i]))){
						if(!in_array(@end(@explode('.',$_FILES['userfile']['name'][$i])), $array)){
							$true = false;
						}	
					}
				}
				if(!$true){
					$_SESSION['mes'] = 'Foto produk tidak valid..';
					redirect($this->router->fetch_class().'/edit_produk/'.$this->input->post('produk_id'));
				} else{
					$files = $_FILES;
					$pc = explode(';', $this->input->post('gambar'));
					$t = null;
					for($i=0; $i<6; $i++){
						if(!empty($files['userfile']['name'][$i])){
							$_FILES['userfile']['name']		= $files['userfile']['name'][$i];
							$_FILES['userfile']['type']		= $files['userfile']['type'][$i];
							$_FILES['userfile']['tmp_name']	= $files['userfile']['tmp_name'][$i];
							$_FILES['userfile']['error']	= $files['userfile']['error'][$i];
							$_FILES['userfile']['size']		= $files['userfile']['size'][$i];
							$this->load->library('upload');
							$this->upload->initialize($this->set_upload_options());
							$this->upload->do_upload();
							$fileName = $this->upload->data()['file_name'];
							$images[] = $fileName;
							$t = true;
						} else{
							if($pc[$i]){
								$images[] = $pc[$i];
							} else{
								$images[] = '';
							}
						}
					}
					$del = $this->input->post('delete');
					if($t){
						$fileName = implode(';',array_filter($images));
                        $fileName = str_replace(' ','_',$fileName);	
					} elseif($del){
						$a = $this->input->post('gambar');
						$ap = explode(';', $a);
						$rm = $del;
						$xrm = explode(';', substr($rm, 0, -1));
						$st = 0;
						foreach($ap as $sg){
							$st++;
							if(!in_array($st-1, $xrm)){
								$kmp .= $sg.';';
							}							
						}
						$fileName = substr($kmp, 0, -1);
					} else{
						$fileName = implode(';',array_filter($images));
						$fileName = str_replace(' ','_',$fileName);	
                    }
                    if (trim($fileName)!=''){
                        $data = array('id_kategori_produk'=>$this->input->post('a'),
                                    'id_kategori_produk_sub'=>$this->input->post('aa'),
                                    'nama_produk'=>$this->input->post('b'),
                                    'produk_seo'=>$this->input->post('produk_seo'),
                                    'satuan'=>$this->input->post('c'),
                                    'harga_beli'=>$this->input->post('d'),
                                    'harga_reseller'=>$this->input->post('e'),
                                    'harga_konsumen'=>$this->input->post('f'),
                                    'berat'=>$this->input->post('berat'),
                                    'gambar'=>$fileName,
                                    'keterangan'=>$this->input->post('ff'),
                                    'username'=>$this->session->username);
                    } else{
                        $data = array('id_kategori_produk'=>$this->input->post('a'),
                                    'id_kategori_produk_sub'=>$this->input->post('aa'),
                                    'nama_produk'=>$this->input->post('b'),
                                    'produk_seo'=>$this->input->post('produk_seo'),
                                    'satuan'=>$this->input->post('c'),
                                    'harga_beli'=>$this->input->post('d'),
                                    'harga_reseller'=>$this->input->post('e'),
                                    'harga_konsumen'=>$this->input->post('f'),
                                    'berat'=>$this->input->post('berat'),
                                    'keterangan'=>$this->input->post('ff'),
                                    'username'=>$this->session->username);
                    }
                    $where = array('id_produk' => $this->input->post('produk_id'));
                    $this->model_app->update('rb_produk', $data, $where);
                }
            }
            redirect($this->router->fetch_class().'/produk');
        }else{
            $data['record'] = $this->model_app->view_ordering('rb_kategori_produk','id_kategori_produk','DESC');
            $data['rows'] = $this->model_app->edit('rb_produk',array('id_produk'=>$id))->row_array();
            $this->template->load('administrator/template','administrator/additional/mod_produk/view_produk_edit',$data);
        }
    }

    private function set_upload_options(){
        $config = array();
        $config['upload_path'] = 'asset/foto_produk/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '5000'; // kb
        $config['encrypt_name'] = FALSE;
        $this->load->library('upload', $config);
      return $config;
    }

    function delete_produk(){
        cek_session_akses($this->router->fetch_class(),'produk',$this->session->id_session);
        $id = array('id_produk' => $this->uri->segment(3));
        $get = $this->model_app->view_where('rb_produk',$id)->row_array();
		$pecah = explode(';',$get['gambar']);
		foreach($pecah as $s){
			$file = 'asset/foto_produk/'.$s;
			if(file_exists($file)){
				@unlink($file);
			}
		}
		$this->model_app->delete('kode_produk',array('produk_id'=>$this->uri->segment(3)));	
        $this->model_app->delete('rb_produk',$id);
        redirect($this->router->fetch_class().'/produk');
    }


    // Controller Modul Rekening

    function rekening(){
        cek_session_akses($this->router->fetch_class(),'rekening',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_rekening','id_rekening','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_rekening/view_rekening',$data);
    }

    function tambah_rekening(){
        cek_session_akses($this->router->fetch_class(),'rekening',$this->session->id_session);
        if (isset($_POST['submit'])){
            //$this->model_rekening->rekening_tambah();
            $data = array('nama_bank'=>$this->db->escape_str($this->input->post('a')),
                        'no_rekening'=>$this->db->escape_str($this->input->post('b')),
                        'pemilik_rekening'=>$this->db->escape_str($this->input->post('c')));
            $this->model_app->insert('rb_rekening',$data);
            redirect($this->router->fetch_class().'/rekening');
        }else{
            $this->template->load('administrator/template','administrator/additional/mod_rekening/view_rekening_tambah');
        }
    }

    function edit_rekening(){
        cek_session_akses($this->router->fetch_class(),'rekening',$this->session->id_session);
        $id = $this->uri->segment(3);
        if (isset($_POST['submit'])){
            $data = array('nama_bank'=>$this->db->escape_str($this->input->post('a')),
                        'no_rekening'=>$this->db->escape_str($this->input->post('b')),
                        'pemilik_rekening'=>$this->db->escape_str($this->input->post('c')));
            $where = array('id_rekening' => $this->input->post('id'));
            $this->model_app->update('rb_rekening', $data, $where);
            redirect($this->router->fetch_class().'/rekening');
        }else{
            $data['rows'] = $this->model_app->edit('rb_rekening',array('id_rekening'=>$id))->row_array();
            $this->template->load('administrator/template','administrator/additional/mod_rekening/view_rekening_edit',$data);
        }
    }

    function delete_rekening(){
        cek_session_akses($this->router->fetch_class(),'rekening',$this->session->id_session);
        $id = array('id_rekening' => $this->uri->segment(3));
        $this->model_app->delete('rb_rekening',$id);
        redirect($this->router->fetch_class().'/rekening');
    }


    // Controller Modul Setting Bonus

    function settingbonus(){
        cek_session_akses($this->router->fetch_class(),'settingbonus',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('referral'=>$this->input->post('aa'),
                            'tanggal_pencairan'=>$this->input->post('bb'));
            $where = array('id_setting' => $this->input->post('id'));
            $this->model_app->update('rb_setting', $data, $where);
            redirect($this->router->fetch_class().'/settingbonus');
        }elseif (isset($_POST['submit2'])){
            if ($this->input->post('idr')==''){
                $data = array('posisi'=>$this->input->post('a'),
                              'reward'=>$this->input->post('b'));
                $this->model_app->insert('rb_reward',$data);
            }else{
                $data = array('posisi'=>$this->input->post('a'),
                              'reward'=>$this->input->post('b'));
                $where = array('id_reward' => $this->input->post('idr'));
                $this->model_app->update('rb_reward', $data, $where);
            }
            redirect($this->router->fetch_class().'/settingbonus');
        }else{
            $data['record'] = $this->model_app->edit('rb_setting',array('aktif'=>'Y'))->row_array();
            $data['reward'] = $this->model_app->view_ordering('rb_reward','id_reward','ASC');
            if ($this->uri->segment(3)!=''){
                $data['rows'] = $this->model_app->edit('rb_reward',array('id_reward'=>$this->uri->segment(3)))->row_array();
            }
            $this->template->load('administrator/template','administrator/additional/mod_settingbonus/view_settingbonus',$data);
        }
    }

    function delete_reward(){
        cek_session_akses($this->router->fetch_class(),'settingbonus',$this->session->id_session);
        $id = array('id_reward' => $this->uri->segment(3));
        $this->model_app->delete('rb_reward',$id);
        redirect($this->router->fetch_class().'/settingbonus');
    }


    // Controller Modul Pembelian
    function voucher(){
        cek_session_akses($this->router->fetch_class(),'voucher',$this->session->id_session);
        $data['voucher'] = $this->model_app->view_ordering('kode_voucher','id','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_voucher/view_voucher',$data);
    }

    function tambah_voucher(){
        cek_session_akses($this->router->fetch_class(),'tambah_voucher',$this->session->id_session);
        if(isset($_POST['post_voucher'])){
            $nama_voucher = $this->input->post('a');
            $kode_voucher = $this->input->post('b');
            $nilai_voucher = $this->input->post('c');
            $batas_jumlah_digunakan = $this->input->post('d');
            $id_produk = $this->input->post('e');
            $type = $this->input->post('type_voucher');
            $cek = $this->db->query("SELECT * FROM `kode_voucher` WHERE `kode_voucher`='".$kode_voucher."'")->num_rows();
            if($cek>0){
                echo "<script>window.alert('Maaf, Kode Voucher sudah ada, Coba ulangi!');
                window.location=('".base_url()."".$this->router->fetch_class()."/tambah_voucher')</script>";
            } else{
                if($id_produk != 0){
                    $pid = $id_produk;
                } else{
                    $pid = 'all';
                }
                if($type==1){
                    $type_val = $type;
                    $nilai_vouchers = $nilai_voucher;
                } elseif($type==2){
                    $type_val = $type;
                    $nilai_vouchers = 0;                
                } else{
                    $type_val = 2;
                    $nilai_vouchers = 0;                
                }
                $array = array(
                    'judul_voucher'     => ucwords($nama_voucher),
                    'kode_voucher'      => $kode_voucher,
                    'nilai_voucher'     => $nilai_vouchers,
                    'jumlah_digunakan'  => 0,
                    'batas_jumlah_digunakan' => $batas_jumlah_digunakan,
                    'uid'               => 'admin',
                    'produk_id'         => $pid,  
                    'type'              => $type_val,
                    'tgl'               => date('Y-m-d')
                );
                $this->model_app->insert('kode_voucher',$array);
                redirect($this->router->fetch_class().'/voucher');    
            }
        } else{
            $data['barang'] = $this->model_app->view_ordering('rb_produk','id_produk','DESC');
            $this->template->load('administrator/template','administrator/additional/mod_voucher/tambah_voucher',$data);
        }
    }

    function edit_voucher(){
        cek_session_akses($this->router->fetch_class(),'edit_voucher',$this->session->id_session);
        $id = $this->uri->segment(3);
        if(isset($_POST['edit_voucher'])){
            $nama_voucher = $this->input->post('a');
            $kode_voucher = $this->input->post('b');
            $nilai_voucher = $this->input->post('c');
            $batas_jumlah_digunakan = $this->input->post('d');
            $id_produk = $this->input->post('e');
            $old_voucher = $this->input->post('old_voucher');
            $type = $this->input->post('type_voucher');
            if($id_produk != 0){
                $pid = $id_produk;
            } else{
                $pid = 'all';
            }
            if($kode_voucher != $old_voucher){
                $cek = $this->db->query("SELECT * FROM `kode_voucher` WHERE `kode_voucher`=?", array('kode_voucher'=>$kode_voucher))->num_rows();
                if($cek>0){
                    echo "<script>window.alert('Maaf, Kode Voucher tidak boleh sama, Coba ulangi!');
                    window.location=('".base_url()."".$this->router->fetch_class()."/edit_voucher/".$id."')</script>";
                } else{
                    if($type==1){
                        $type_val = $type;
                        $nilai_vouchers = $nilai_voucher;
                    } elseif($type==2){
                        $type_val = $type;
                        $nilai_vouchers = 0;                
                    } else{
                        $type_val = 2;
                        $nilai_vouchers = 0;                
                    }
                    $array = array(
                        'judul_voucher'     => $nama_voucher,
                        'kode_voucher'      => $kode_voucher,
                        'nilai_voucher'     => $nilai_vouchers,
                        'batas_jumlah_digunakan' => $batas_jumlah_digunakan,
                        'uid'               => 'admin',
                        'produk_id'         => $pid,     
                        'type'              => $type_val,
                    );
                    $this->model_app->update('kode_voucher',$array, array('id'=>$id));
                    redirect($this->router->fetch_class().'/voucher');    
                }
            } else{
                $array = array(
                    'judul_voucher'     => $nama_voucher,
                    'kode_voucher'      => $kode_voucher,
                    'nilai_voucher'     => $nilai_voucher,
                    'batas_jumlah_digunakan' => $batas_jumlah_digunakan,
                    'uid'               => 'admin',
                    'produk_id'         => $pid,     
                );
                $this->model_app->update('kode_voucher', $array, array('id'=>$id));
                redirect($this->router->fetch_class().'/voucher');    
            }
        } else{
            if(is_numeric($id)){
                $g = $this->db->query("SELECT * FROM `kode_voucher` WHERE id=?", array('id'=>$id));
                if($g->num_rows()>0){
                    $data['voucher']     = $g->result_array();
                    $data['barang']      = $this->model_app->view_ordering('rb_produk','id_produk','DESC');
                    $data['produk']      = $data['voucher'][0]['produk_id'];
                    $data['nama_produk'] = $this->db->query("SELECT * FROM `rb_produk` WHERE `id_produk`='".$data['produk']."'")->result_array()[0]['nama_produk'];
                    $this->template->load('administrator/template','administrator/additional/mod_voucher/edit_voucher',$data);
                } else{
                    redirect($this->router->fetch_class().'/voucher');
                }
            } else{
                redirect($this->router->fetch_class().'/voucher');
            }
        }
    }

    function detail_voucher(){
        cek_session_akses($this->router->fetch_class(),'detail_voucher',$this->session->id_session);
        $id = $this->uri->segment(3);
        $g = $this->db->query("SELECT * FROM `kode_voucher` WHERE id=?", array('id'=>$id));
        if($g->num_rows()>0){
            $data['rows']        = $g->result_array();
            $data['barang']      = $this->model_app->view_ordering('rb_produk','id_produk','DESC');
            $data['produk']      = $data['rows'][0]['produk_id'];
            $data['nama_produk'] = $this->db->query("SELECT * FROM `rb_produk` WHERE `id_produk`='".$data['produk']."'")->result_array()[0]['nama_produk'];
            $this->template->load('administrator/template','administrator/additional/mod_voucher/detail_voucher',$data);
        } else{
            redirect($this->router->fetch_class().'/voucher');
        }

    }

    function delete_voucher(){
        cek_session_akses($this->router->fetch_class(),'delete_voucher',$this->session->id_session);
        $id = $this->uri->segment(3);
        if(is_numeric($id)){
            $this->model_app->delete('kode_voucher',array('id'=>$id));
            redirect($this->router->fetch_class().'/voucher');
        } else{
            redirect($this->router->fetch_class().'/voucher');
        }
    }

    function tagihan_voucher(){
        cek_session_akses($this->router->fetch_class(),'delete_voucher',$this->session->id_session);
        $data['voucher'] = $this->db->query("SELECT a.*, b.nilai_voucher, b.type, c.nama_reseller FROM kode_voucher_gnr a INNER JOIN kode_voucher b ON b.id=a.vcr_id JOIN rb_reseller c ON c.id_reseller=a.resellerid ORDER BY id DESC")->result_array();
        $this->template->load('administrator/template','administrator/additional/mod_voucher/tagihan_voucher',$data);
    }

    function konfirmasi_tagihan_voucher(){
        cek_session_akses($this->router->fetch_class(),'konfirmasi_tagihan_voucher',$this->session->id_session);
        $id = $this->uri->segment(3);
        if(is_numeric($id)){
            $this->db->update('kode_voucher_gnr',array('status'=>2),array('id'=>$id));
            redirect($this->router->fetch_class().'/tagihan_voucher');
        } else{
            redirect($this->router->fetch_class().'/tagihan_voucher');
        }
    }

    // Controller Modul Pembelian

    function pembelian(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        $this->session->unset_userdata('idp');
        $data['record'] = $this->model_app->view_join_one('rb_pembelian','rb_supplier','id_supplier','id_pembelian','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_pembelian/view_pembelian',$data);
    }

    function detail_pembelian(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        $data['rows'] = $this->model_reseller->view_join_rows('rb_pembelian','rb_supplier','id_supplier',array('id_pembelian'=>$this->uri->segment(3)),'id_pembelian','DESC')->row_array();
        $data['record'] = $this->model_app->view_join_where('rb_pembelian_detail','rb_produk','id_produk',array('id_pembelian'=>$this->uri->segment(3)),'id_pembelian_detail','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_pembelian/view_pembelian_detail',$data);
    }

    function tambah_pembelian(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        if (isset($_POST['submit1'])){
            if ($this->session->idp == ''){
                $data = array('kode_pembelian'=>$this->input->post('a'),
                              'id_supplier'=>$this->input->post('b'),
                              'waktu_beli'=>date('Y-m-d H:i:s'));
                $this->model_app->insert('rb_pembelian',$data);
                $idp = $this->db->insert_id();
                $this->session->set_userdata(array('idp'=>$idp));
            }else{
                $data = array('kode_pembelian'=>$this->input->post('a'),
                              'id_supplier'=>$this->input->post('b'));
                $where = array('id_pembelian' => $this->session->idp);
                $this->model_app->update('rb_pembelian', $data, $where);
            }
            redirect($this->router->fetch_class().'/tambah_pembelian');

        }elseif(isset($_POST['submit'])){
            if ($this->input->post('idpd')==''){
                $data = array('id_pembelian'=>$this->session->idp,
                              'id_produk'=>$this->input->post('aa'),
                              'harga_pesan'=>$this->input->post('bb'),
                              'jumlah_pesan'=>$this->input->post('cc'),
                              'satuan'=>$this->input->post('dd'));
                $this->model_app->insert('rb_pembelian_detail',$data);
            }else{
                $data = array('id_produk'=>$this->input->post('aa'),
                              'harga_pesan'=>$this->input->post('bb'),
                              'jumlah_pesan'=>$this->input->post('cc'),
                              'satuan'=>$this->input->post('dd'));
                $where = array('id_pembelian_detail' => $this->input->post('idpd'));
                $this->model_app->update('rb_pembelian_detail', $data, $where);
            }
            redirect($this->router->fetch_class().'/tambah_pembelian');
        }else{
            $data['rows'] = $this->model_reseller->view_join_rows('rb_pembelian','rb_supplier','id_supplier',array('id_pembelian'=>$this->session->idp),'id_pembelian','DESC')->row_array();
            $data['record'] = $this->model_app->view_join_where('rb_pembelian_detail','rb_produk','id_produk',array('id_pembelian'=>$this->session->idp),'id_pembelian_detail','DESC');
            $data['barang'] = $this->model_app->view_where_ordering('rb_produk',array('id_reseller'=>'0'),'id_produk','ASC');
            $data['supplier'] = $this->model_app->view_ordering('rb_supplier','id_supplier','ASC');
            if ($this->uri->segment(3)!=''){
                $data['row'] = $this->model_app->view_where('rb_pembelian_detail',array('id_pembelian_detail'=>$this->uri->segment(3)))->row_array();
            }
            $this->template->load('administrator/template','administrator/additional/mod_pembelian/view_pembelian_tambah',$data);
        }
    }

    function edit_pembelian(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        if (isset($_POST['submit1'])){
            $data = array('kode_pembelian'=>$this->input->post('a'),
                          'id_supplier'=>$this->input->post('b'),
                          'waktu_beli'=>$this->input->post('c'));
            $where = array('id_pembelian' => $this->input->post('idp'));
            $this->model_app->update('rb_pembelian', $data, $where);
            redirect($this->router->fetch_class().'/edit_pembelian/'.$this->input->post('idp'));

        }elseif(isset($_POST['submit'])){
            if ($this->input->post('idpd')==''){
                $data = array('id_pembelian'=>$this->input->post('idp'),
                              'id_produk'=>$this->input->post('aa'),
                              'harga_pesan'=>$this->input->post('bb'),
                              'jumlah_pesan'=>$this->input->post('cc'),
                              'satuan'=>$this->input->post('dd'));
                $this->model_app->insert('rb_pembelian_detail',$data);
            }else{
                $data = array('id_produk'=>$this->input->post('aa'),
                              'harga_pesan'=>$this->input->post('bb'),
                              'jumlah_pesan'=>$this->input->post('cc'),
                              'satuan'=>$this->input->post('dd'));
                $where = array('id_pembelian_detail' => $this->input->post('idpd'));
                $this->model_app->update('rb_pembelian_detail', $data, $where);
            }
            redirect($this->router->fetch_class().'/edit_pembelian/'.$this->input->post('idp'));
        }else{
            $data['rows'] = $this->model_reseller->view_join_rows('rb_pembelian','rb_supplier','id_supplier',array('id_pembelian'=>$this->uri->segment(3)),'id_pembelian','DESC')->row_array();
            $data['record'] = $this->model_app->view_join_where('rb_pembelian_detail','rb_produk','id_produk',array('id_pembelian'=>$this->uri->segment(3)),'id_pembelian_detail','DESC');
            $data['barang'] = $this->model_app->view_where_ordering('rb_produk',array('id_reseller'=>'0'),'id_produk','ASC');
            $data['supplier'] = $this->model_app->view_ordering('rb_supplier','id_supplier','ASC');
            if ($this->uri->segment(4)!=''){
                $data['row'] = $this->model_app->view_where('rb_pembelian_detail',array('id_pembelian_detail'=>$this->uri->segment(4)))->row_array();
            }
            $this->template->load('administrator/template','administrator/additional/mod_pembelian/view_pembelian_edit',$data);
        }
    }

    function delete_pembelian(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        $id = array('id_pembelian' => $this->uri->segment(3));
        $this->model_app->delete('rb_pembelian',$id);
        $this->model_app->delete('rb_pembelian_detail',$id);
        redirect($this->router->fetch_class().'/pembelian');
    }

    function delete_pembelian_detail(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        $id = array('id_pembelian_detail' => $this->uri->segment(4));
        $this->model_app->delete('rb_pembelian_detail',$id);
        redirect($this->router->fetch_class().'/edit_pembelian/'.$this->uri->segment(3));
    }

    function delete_pembelian_tambah_detail(){
        cek_session_akses($this->router->fetch_class(),'pembelian',$this->session->id_session);
        $id = array('id_pembelian_detail' => $this->uri->segment(3));
        $this->model_app->delete('rb_pembelian_detail',$id);
        redirect($this->router->fetch_class().'/tambah_pembelian');
    }



    // Controller Modul Penjualan

    function penjualan(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        $this->session->unset_userdata('idp');
        $data['record'] = $this->model_reseller->penjualan_list(1,'admin');
        $this->template->load('administrator/template','administrator/additional/mod_penjualan/view_penjualan',$data);
    }

    function detail_penjualan(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);

        /*
        $data['rows'] = $this->model_reseller->penjualan_detail($this->uri->segment(3))->row_array();
        $data['record'] = $this->model_app->view_join_where('rb_penjualan_detail','rb_produk','id_produk',array('id_penjualan'=>$this->uri->segment(3)),'id_penjualan_detail','DESC');
        */
        $data['rows'] = $this->model_reseller->penjualan_konsumen_detail_reseller($this->uri->segment(3))->row_array();
		$data['record'] = $this->model_app->view_join_where('rb_penjualan_detail','rb_produk','id_produk',array('id_penjualan'=>$this->uri->segment(3)),'id_penjualan_detail','DESC');


        $this->template->load('administrator/template','administrator/additional/mod_penjualan/view_penjualan_detail',$data);
    }

    function tambah_penjualan(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        if (isset($_POST['submit1'])){
            if ($this->session->idp == ''){
                $data = array('kode_transaksi'=>$this->input->post('a'),
                              'id_pembeli'=>$this->input->post('b'),
                              'id_penjual'=>0,
                              'status_pembeli'=>'reseller',
                              'status_penjual'=>'admin',
                              'waktu_transaksi'=>date('Y-m-d H:i:s'),
                              'proses'=>'0');
                $this->model_app->insert('rb_penjualan',$data);
                $idp = $this->db->insert_id();
                $this->session->set_userdata(array('idp'=>$idp));
            }else{
                $data = array('kode_transaksi'=>$this->input->post('a'),
                              'id_pembeli'=>$this->input->post('b'));
                $where = array('id_penjualan' => $this->session->idp);
                $this->model_app->update('rb_penjualan', $data, $where);
            }
                redirect($this->router->fetch_class().'/tambah_penjualan');

        }elseif(isset($_POST['submit'])){
            $jual = $this->model_reseller->jual($this->input->post('aa'))->row_array();
            $beli = $this->model_reseller->beli($this->input->post('aa'))->row_array();
            $stok = $beli['beli']-$jual['jual'];
            if ($this->input->post('dd') > $stok){
                echo "<script>window.alert('Maaf, Stok Tidak Mencukupi!');
                                  window.location=('".base_url()."".$this->router->fetch_class()."/tambah_penjualan')</script>";
            }else{
                if ($this->input->post('idpd')==''){
                    $data = array('id_penjualan'=>$this->session->idp,
                                  'id_produk'=>$this->input->post('aa'),
                                  'jumlah'=>$this->input->post('dd'),
                                  'diskon'=>$this->input->post('cc'),
                                  'harga_jual'=>$this->input->post('bb'),
                                  'satuan'=>$this->input->post('ee'));
                    $this->model_app->insert('rb_penjualan_detail',$data);
                }else{
                    $data = array('id_produk'=>$this->input->post('aa'),
                                  'jumlah'=>$this->input->post('dd'),
                                  'diskon'=>$this->input->post('cc'),
                                  'harga_jual'=>$this->input->post('bb'),
                                  'satuan'=>$this->input->post('ee'));
                    $where = array('id_penjualan_detail' => $this->input->post('idpd'));
                    $this->model_app->update('rb_penjualan_detail', $data, $where);
                }
                redirect($this->router->fetch_class().'/tambah_penjualan');
            }
            
        }else{
            $data['rows'] = $this->model_reseller->penjualan_detail($this->session->idp)->row_array();
            $data['record'] = $this->model_app->view_join_where('rb_penjualan_detail','rb_produk','id_produk',array('id_penjualan'=>$this->session->idp),'id_penjualan_detail','DESC');
            $data['barang'] = $this->model_app->view_ordering('rb_produk','id_produk','ASC');
            $data['reseller'] = $this->model_app->view_ordering('rb_reseller','id_reseller','ASC');
            if ($this->uri->segment(3)!=''){
                $data['row'] = $this->model_app->view_where('rb_penjualan_detail',array('id_penjualan_detail'=>$this->uri->segment(3)))->row_array();
            }
            $this->template->load('administrator/template','administrator/additional/mod_penjualan/view_penjualan_tambah',$data);
        }
    }

    function edit_penjualan(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        if (isset($_POST['submit1'])){
            $data = array('kode_transaksi'=>$this->input->post('a'),
                          'id_pembeli'=>$this->input->post('b'),
                          'waktu_transaksi'=>$this->input->post('c'));
            $where = array('id_penjualan' => $this->input->post('idp'));
            $this->model_app->update('rb_penjualan', $data, $where);
            redirect($this->router->fetch_class().'/edit_penjualan/'.$this->input->post('idp'));

        }elseif(isset($_POST['submit'])){
            $cekk = $this->db->query("SELECT * FROM rb_penjualan_detail where id_penjualan='".$this->input->post('idp')."' AND id_produk='".$this->input->post('aa')."'")->row_array();
            $jual = $this->model_reseller->jual($this->input->post('aa'))->row_array();
            $beli = $this->model_reseller->beli($this->input->post('aa'))->row_array();
            $stok = $beli['beli']-$jual['jual']+$cekk['jumlah'];
            if ($this->input->post('dd') > $stok){
                echo "<script>window.alert('Maaf, Stok Tidak Mencukupi!');
                                  window.location=('".base_url()."".$this->router->fetch_class()."/edit_penjualan/".$this->input->post('idp')."')</script>";
            }else{
                if ($this->input->post('idpd')==''){
                    $data = array('id_penjualan'=>$this->input->post('idp'),
                                  'id_produk'=>$this->input->post('aa'),
                                  'jumlah'=>$this->input->post('dd'),
                                  'diskon'=>$this->input->post('cc'),
                                  'harga_jual'=>$this->input->post('bb'),
                                  'satuan'=>$this->input->post('ee'));
                    $this->model_app->insert('rb_penjualan_detail',$data);
                }else{
                    $data = array('id_produk'=>$this->input->post('aa'),
                                  'jumlah'=>$this->input->post('dd'),
                                  'diskon'=>$this->input->post('cc'),
                                  'harga_jual'=>$this->input->post('bb'),
                                  'satuan'=>$this->input->post('ee'));
                    $where = array('id_penjualan_detail' => $this->input->post('idpd'));
                    $this->model_app->update('rb_penjualan_detail', $data, $where);
                }
                redirect($this->router->fetch_class().'/edit_penjualan/'.$this->input->post('idp'));
            }
            
        }else{
            $data['rows'] = $this->model_reseller->penjualan_detail($this->uri->segment(3))->row_array();
            $data['record'] = $this->model_app->view_join_where('rb_penjualan_detail','rb_produk','id_produk',array('id_penjualan'=>$this->uri->segment(3)),'id_penjualan_detail','DESC');
            $data['barang'] = $this->model_app->view_ordering('rb_produk','id_produk','ASC');
            $data['reseller'] = $this->model_app->view_ordering('rb_reseller','id_reseller','ASC');
            if ($this->uri->segment(4)!=''){
                $data['row'] = $this->model_app->view_where('rb_penjualan_detail',array('id_penjualan_detail'=>$this->uri->segment(4)))->row_array();
            }
            $this->template->load('administrator/template','administrator/additional/mod_penjualan/view_penjualan_edit',$data);
        }
    }

    function proses_penjualan(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
            $data = array('proses'=>$this->uri->segment(4));
            $where = array('id_penjualan' => $this->uri->segment(3));
            $this->model_app->update('rb_penjualan', $data, $where);
            $order = $this->db->query("SELECT a.*, b.id_pembeli, b.kode_transaksi FROM rb_penjualan_detail a JOIN rb_penjualan b ON a.id_penjualan=b.id_penjualan where a.id_penjualan='".$this->uri->segment(3)."'");
            foreach ($order->result_array() as $row) {
                $cek_produk = $this->db->query("SELECT * FROM rb_produk where id_produk_perusahaan='$row[id_produk]' AND id_reseller='$row[id_pembeli]'");
                if ($cek_produk->num_rows()>=1){
                    $pro = $cek_produk->row_array();
                    $kode_transaksi = "TRX-".date('YmdHis');
                    $data = array('kode_transaksi'=>$kode_transaksi,
                                  'id_pembeli'=>$row['id_pembeli'],
                                  'id_penjual'=>'1',
                                  'status_pembeli'=>'reseller',
                                  'status_penjual'=>'admin',
                                  'service'=>$row['kode_transaksi'],
                                  'waktu_transaksi'=>date('Y-m-d H:i:s'),
                                  'proses'=>'1');
                    $this->model_app->insert('rb_penjualan',$data);
                    $idp = $this->db->insert_id();
                    $data = array('id_penjualan'=>$idp,
                                  'id_produk'=>$pro['id_produk'],
                                  'jumlah'=>$row['jumlah'],
                                  'harga_jual'=>$row['harga_jual'],
                                  'satuan'=>$row['satuan']);
                    $this->model_app->insert('rb_penjualan_detail',$data);
                }else{
                    $p = $this->db->query("SELECT * FROM rb_produk where id_produk='$row[id_produk]'")->row_array();
                    $data = array('id_produk_perusahaan'=>$p['id_produk'],
                                  'id_kategori_produk'=>$p['id_kategori_produk'],
                                  'id_kategori_produk_sub'=>$p['id_kategori_produk_sub'],
                                  'id_reseller'=>$row['id_pembeli'],
                                  'nama_produk'=>$p['nama_produk'],
                                  'produk_seo'=>$p['produk_seo'],
                                  'satuan'=>$p['satuan'],
                                  'harga_beli'=>$p['harga_beli'],
                                  'harga_reseller'=>$p['harga_reseller'],
                                  'harga_konsumen'=>$p['harga_konsumen'],
                                  'berat'=>$p['berat'],
                                  'gambar'=>$p['gambar'],
                                  'keterangan'=>$p['keterangan'],
                                  'username'=>$p['username'],
                                  'waktu_input'=>date('Y-m-d H:i:s'));
                    $this->model_app->insert('rb_produk',$data);
                    $id_produk = $this->db->insert_id();

                    $kode_transaksi = "TRX-".date('YmdHis');
                    $data = array('kode_transaksi'=>$kode_transaksi,
                                  'id_pembeli'=>$row['id_pembeli'],
                                  'id_penjual'=>'1',
                                  'status_pembeli'=>'reseller',
                                  'status_penjual'=>'admin',
                                  'service'=>$row['kode_transaksi'],
                                  'waktu_transaksi'=>date('Y-m-d H:i:s'),
                                  'proses'=>'1');
                    $this->model_app->insert('rb_penjualan',$data);
                    $idp = $this->db->insert_id();

                    $data = array('id_penjualan'=>$idp,
                                  'id_produk'=>$id_produk,
                                  'jumlah'=>$row['jumlah'],
                                  'harga_jual'=>$row['harga_jual'],
                                  'satuan'=>$row['satuan']);
                    $this->model_app->insert('rb_penjualan_detail',$data);
                }
            }

            redirect($this->router->fetch_class().'/penjualan');
    }

    function proses_penjualan_detail(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        $data = array('proses'=>$this->uri->segment(4));
        $where = array('id_penjualan' => $this->uri->segment(3));
        $this->model_app->update('rb_penjualan', $data, $where);
        redirect($this->router->fetch_class().'/detail_penjualan/'.$this->uri->segment(3));
    }

    function delete_penjualan(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        $id = array('id_penjualan' => $this->uri->segment(3));
        $this->model_app->delete('rb_penjualan',$id);
        $this->model_app->delete('rb_penjualan_detail',$id);
        redirect($this->router->fetch_class().'/penjualan');
    }

    function delete_penjualan_detail(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        $id = array('id_penjualan_detail' => $this->uri->segment(4));
        $this->model_app->delete('rb_penjualan_detail',$id);
        redirect($this->router->fetch_class().'/edit_penjualan/'.$this->uri->segment(3));
    }

    function delete_penjualan_tambah_detail(){
        cek_session_akses($this->router->fetch_class(),'penjualan',$this->session->id_session);
        $id = array('id_penjualan_detail' => $this->uri->segment(3));
        $this->model_app->delete('rb_penjualan_detail',$id);
        redirect($this->router->fetch_class().'/tambah_penjualan');
    }

    function pembayaran_konsumen(){
        cek_session_akses($this->router->fetch_class(),'pembayaran_konsumen',$this->session->id_session);
        $data['record'] = $this->db->query("SELECT a.*, b.*, c.kode_transaksi, c.proses FROM `rb_konfirmasi_pembayaran_konsumen` a JOIN rb_rekening b ON a.id_rekening=b.id_rekening JOIN rb_penjualan c ON a.id_penjualan=c.id_penjualan AND c.status_penjual='reseller' ORDER BY id_konfirmasi_pembayaran DESC");
        $this->template->load('administrator/template','administrator/additional/mod_konsumen/pembayaran_konsumen',$data);
    }

    function konfirmasi_kepelapak(){
        cek_session_akses($this->router->fetch_class(),'konfirmasi_kepelapak',$this->session->id_session);
        $id = $this->uri->segment(3);
        if(is_numeric($id)){
            $cek = $this->db->query("SELECT * FROM `rb_konfirmasi_pembayaran_konsumen` WHERE `id_konfirmasi_pembayaran`=?", array($id));
            if(($cek->num_rows())>0){
                $this->model_app->update('rb_konfirmasi_pembayaran_konsumen',array('konf'=>2,'status_read'=>1),array('id_konfirmasi_pembayaran'=>$id));
                $ambil = $cek->result_array()[0];
                $idtr  = $ambil['id_penjualan'];
                $iden = $this->model_app->view_where('identitas',array('id_identitas'=>'1'))->row_array();
                $cekres = $this->model_app->view_where('rb_penjualan',array('id_penjualan'=>$idtr))->row_array();
                $kons = $this->model_reseller->profile_konsumen($cekres['id_pembeli'])->row_array();
                $res = $this->model_app->view_where('rb_reseller',array('id_reseller'=>$cekres['id_penjual']))->row_array();
                $email_tujuan = $res['email'];
                $tglaktif     = date("d-m-Y H:i:s");
                $subject      = "$iden[nama_website] - Orderan masuk";
                $message      = "<html><body>Halooo! <b>".$res['nama_reseller']."</b> ... <br> Hari ini pada tanggal <span style='color:red'>$tglaktif</span> toko anda mendapat order.
				<br><table style='width:100%;'>
	   				<tr><td style='background:#337ab7; color:#fff; pading:20px' cellpadding=6 colspan='2'><b>Berikut Data Pembeli : </b></td></tr>
                        <tr><td width='140px'><b>Nama Lengkap</b></td><td>: ".$kons['nama_lengkap']."</td></tr>
                        <tr><td><b>Alamat Email</b></td>			<td> : ".$kons['email']."</td></tr>
                        <tr><td><b>No Telpon</b></td>				<td> : ".$kons['no_hp']."</td></tr>
                        <tr><td><b>Alamat</b></td>					<td> : ".$kons['alamat_lengkap']." </td></tr>
                        <tr><td><b>Negara</b></td>					<td> : ID </td></tr>
                        <tr><td><b>Provinsi</b></td>				<td> : ".$kons['propinsi']." </td></tr>
                        <tr><td><b>Kabupaten/Kota</b></td>			<td> : ".$kons['kota']." </td></tr>
                        <tr><td><b>Kecamatan</b></td>				<td> : ".$kons['kecamatan']." </td></tr>
                    </table><br>
                    No Transaksi : <b>".$cekres['kode_transaksi']."</b><br>
                    Berikut Detail Data Orderan :
                    <table style='width:100%;' class='table table-striped'>
                          <thead>
                            <tr bgcolor='#337ab7'>
                              <th style='width:40px'>No</th>
                              <th width='47%'>Nama Produk</th>
                              <th>Harga</th>
                              <th>Qty</th>
                              <th>Berat</th>
                              <th>Subtotal</th>
                            </tr>
                          </thead>
                          <tbody>";
                          $no = 1;
                          $belanjaan = $this->model_app->view_join_where('rb_penjualan_detail','rb_produk','id_produk',array('id_penjualan'=>$idtr),'id_penjualan_detail','ASC');
                          foreach ($belanjaan as $row){
                          $sub_total = ($row['harga_jual']*$row['jumlah']);
                            $message .= "<tr bgcolor='#e3e3e3'><td>$no</td>
                                            <td>$row[nama_produk]</td>
                                            <td>".rupiah($row['harga_jual'])."</td>
                                            <td>$row[jumlah]</td>
                                            <td>".($row['berat']*$row['jumlah'])." Kg</td>
                                            <td>Rp ".rupiah($sub_total)."</td>
                                        </tr>";
                            $no++;
                          }
                          $total = $this->db->query("SELECT sum((a.harga_jual*a.jumlah)-a.diskon) as total, sum(b.berat*a.jumlah) as total_berat FROM `rb_penjualan_detail` a JOIN rb_produk b ON a.id_produk=b.id_produk where a.id_penjualan='".$idtr."'")->row_array();
                $message .= "<tr bgcolor='lightgreen'>
                                  <td colspan='5'><b>Total Harga</b></td>
                                  <td><b>Rp ".rupiah($total['total'])."</b></td>
                                </tr>
                                <tr bgcolor='lightblue'>
                                  <td colspan='5'><b>Total Berat</b></td>
                                  <td><b>$total[total_berat] g</b></td>
                                </tr>
                        </tbody>
                      </table><br/>
                      Silahkan <a style='text-decoration:none;' href='".base_url()."/reseller'>Login Disini</a> untuk lebih detailnya.
                      </body></html> \n";
                $this->email->from($iden['email'], $iden['nama_website']);
                $this->email->to($email_tujuan);
                $this->email->cc('');
                $this->email->bcc('');				
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->set_mailtype("html");
                $this->email->send();
                $config['protocol'] = 'sendmail';
                $config['mailpath'] = '/usr/sbin/sendmail';
                $config['charset'] = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $this->email->initialize($config);
                redirect('/'.$this->router->fetch_class().'/pembayaran_konsumen');
            } else{
                redirect('/'.$this->router->fetch_class().'/pembayaran_konsumen');
            }
        } else{
            redirect('/'.$this->router->fetch_class().'/pembayaran_konsumen');
        }        
    }

    function pembayaran_reseller_act(){
        cek_session_akses($this->router->fetch_class(),'pembayaran_reseller_act',$this->session->id_session);
        $id = $this->uri->segment(3);
        if(is_numeric($id)){
            $c = $this->db->query("SELECT * FROM `rb_penjualan` WHERE id_penjualan=?", array($id));
            if(($c->num_rows())>0){
                $array = array(
                    'id_penjualan' =>$id,
                    'status'       =>1,
                    'tgl'          =>date('Y-m-d')
                );
                $this->model_app->insert('trx_done',$array);
                redirect('/'.$this->router->fetch_class().'/pembayaran_reseller');
            } else{
                redirect(base_url());
            }
        } else{
            redirect(base_url());
        }
    }

    function pembayaran_reseller(){
        cek_session_akses($this->router->fetch_class(),'pembayaran_reseller',$this->session->id_session);
        $data['record'] = $this->db->query("SELECT * FROM `rb_penjualan` WHERE proses='3' ORDER BY id_penjualan DESC");
        $this->template->load('administrator/template','administrator/additional/mod_reseller/view_reseller_pembayaran',$data);
    }

    function download_bukti(){
        cek_session_akses($this->router->fetch_class(),'pembayaran_reseller',$this->session->id_session);
        $name = $this->uri->segment(3);
        $data = file_get_contents("asset/files/".$name);
        force_download($name, $data);
    }

    function keuangan(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $data['record'] = $this->model_app->view_ordering('rb_reseller','id_reseller','DESC');
        $this->template->load('administrator/template','administrator/additional/mod_keuangan/view_keuangan',$data);
    }

    function bayar_bonus(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        if (isset($_POST['submit'])){
            $data = array('id_reseller'=>$this->input->post('idk'),
                            'bonus_referral'=>$this->input->post('a'),
                            'waktu_pencairan'=>date('YmdHis'));
            $this->model_app->insert('rb_pencairan_bonus',$data);
            redirect($this->router->fetch_class().'/bayar_bonus/'.$this->input->post('idk'));
        } else{
            $id = $this->uri->segment(3);
            $record = $this->model_reseller->reseller_pembelian($id,'admin');
            $penjualan = $this->model_reseller->penjualan_list_konsumen($id,'reseller');
            $edit = $this->model_app->edit('rb_reseller',array('id_reseller'=>$id))->row_array();
            $reward = $this->model_app->view_ordering('rb_reward','id_reward','ASC');

            $data = array('rows' => $edit,'record'=>$record,'penjualan'=>$penjualan,'reward'=>$reward);
            $this->template->load('administrator/template','administrator/additional/mod_keuangan/view_bayar_bonus',$data);
        }
    }

    function bayar_reward(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $data = array('id_reseller'=>$this->uri->segment(3),
                        'id_reward'=>$this->uri->segment(4),
                        'reward_date'=>$this->uri->segment(5),
                        'waktu_pencairan'=>date('YmdHis'));
        $this->model_app->insert('rb_pencairan_reward',$data);
        redirect($this->router->fetch_class().'/bayar_bonus/'.$this->uri->segment(3));
    }

    function batalkan_reward(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $id = array('id_konsumen' => $this->uri->segment(4),'id_reward' => $this->uri->segment(3));
        $this->model_app->delete('rb_pencairan_reward',$id);
        redirect($this->router->fetch_class().'/bayar_bonus/'.$this->uri->segment(4));
    }

    function history_reward(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $data['record'] = $this->db->query("SELECT a.*, b.nama_reseller, c.posisi, c.reward FROM `rb_pencairan_reward` a JOIN rb_reseller b ON a.id_reseller=b.id_reseller JOIN rb_reward c ON a.id_reward=c.id_reward ORDER BY a.id_pencairan_reward DESC");
        $this->template->load('administrator/template','administrator/additional/mod_keuangan/view_history_reward',$data);
    }

    function history_referral(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $data['record'] = $this->db->query("SELECT a.*, b.nama_reseller FROM `rb_pencairan_bonus` a JOIN rb_reseller b ON a.id_reseller=b.id_reseller ORDER BY a.id_pencairan_bonus DESC");
        $this->template->load('administrator/template','administrator/additional/mod_keuangan/view_history_referral',$data);
    }

    function delete_history_reward(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $id = array('id_pencairan_reward' => $this->uri->segment(3));
        $this->model_app->delete('rb_pencairan_reward',$id);
        redirect($this->router->fetch_class().'/history_reward');
    }

    function delete_history_referral(){
        cek_session_akses($this->router->fetch_class(),'keuangan',$this->session->id_session);
        $id = array('id_pencairan_bonus' => $this->uri->segment(3));
        $this->model_app->delete('rb_pencairan_bonus',$id);
        redirect($this->router->fetch_class().'/history_referral');
    }

	function logout(){
		$this->session->sess_destroy();
        redirect($this->router->fetch_class());
	}
}
