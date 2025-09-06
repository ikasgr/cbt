<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datasiswa extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }else if ( !$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru') ){
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library('upload');
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->form_validation->set_error_delimiters('', '');
    }

    public function output_json($data, $encode = true) {
        if ($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Siswa', 'subjudul' => 'Data Siswa', 'setting' => $this->dashboard->getSetting()];

        $tp = $this->dashboard->getTahun();
        $smt = $this->dashboard->getSemester();
        $data['tp'] = $tp;
        $data['smt'] = $smt;

        $searchTp = array_search('1', array_column($tp, 'active'));
        $searchSmt = array_search('1', array_column($smt, 'active'));
        $tpAktif = $tp[$searchTp];
        $smtAktif = $smt[$searchSmt];
        $data['tp_active'] = $tpAktif;
        $data['smt_active'] = $smtAktif;

        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
        $data['kelass'] = $this->dropdown->getAllKelas($tpAktif->id_tp, $smtAktif->id_smt);

        //$count_siswa = $this->db->count_all('master_siswa');
        //$lists = $this->master->getAllSiswa($tpAktif->id_tp, $smtAktif->id_smt, '0', '10');
        //$data['lists'] = [
        //    'data' => $lists,
        //    'pages' => 20 //ceil($count_siswa / 10)
        //];

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/siswa/data');
        $this->load->view('_templates/dashboard/_footer');
        /*
        $this->db->select('uid');
        $this->db->from('master_siswa');
        $this->db->where('uid', '');
        $query = $this->db->get()->result();

        $count_siswa = $this->db->count_all('master_siswa');
        $count_induk = $this->db->count_all('buku_induk');

        if (count($query) > 0) {
            $this->load->library('Uuid', 'uuid');
            $siswas = $this->db->get('master_siswa')->result();
            $input = array();
            foreach ($siswas as $siswa) {
                $input[] = array(
                    'id_siswa' => $siswa->id_siswa,
                    'uid' => $this->uuid->v4() //$this->db->set('uid','UUID()',FALSE)
                );
            }
            $this->db->update_batch('master_siswa', $input, 'id_siswa');
            redirect('datasiswa');
        } elseif ($count_siswa > $count_induk) {
            $uids = $this->db->select('id_siswa, uid')->from('master_siswa')->get()->result();
            foreach ($uids as $uid) {
                $check = $this->db->select('id_siswa')->from('buku_induk')->where('id_siswa', $uid->id_siswa);
                if ($check->get()->num_rows() == 0) {
                    $this->db->insert('buku_induk', $uid);
                }
            }
            redirect('datasiswa');
        } else {
        }
        */
    }

    public function data() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $this->output_json($this->master->getDataSiswa($tp->id_tp, $smt->id_smt), false);
    }

    public function list() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $page = $this->input->post('page', true);
        $limit = $this->input->post('limit', true);
        $search = $this->input->post('search', true);
        $filter = $this->input->post('filter', true);
        //$sortBy = $this->input->post('sort', true);
        //$sortOrder = $this->input->post('order', true);

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $count_siswa = $this->master->getSiswaTotalPage($tp->id_tp, $smt->id_smt, $filter, $search);
        $lists = $this->master->getSiswaPage($tp->id_tp, $smt->id_smt, $offset, $limit, $filter, $search);
        $data = [
            'lists' => $lists,
            'total' => $count_siswa,
            'pages' => ceil($count_siswa / $limit),
            'search' => $search,
            'perpage' => $limit,
            'filter' => $filter
        ];

        $this->output_json($data);
    }

    public function add() {
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Siswa', 'subjudul' => 'Tambah Data Siswa', 'setting' => $this->dashboard->getSetting()];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
        $data['tipe'] = 'add';

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/siswa/add');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function create() {
        $this->load->model('Master_model', 'master');

        $nis           = $this->input->post('nis', true);
        $nisn          = $this->input->post('nisn', true);
        $username      = $this->input->post('username', true);

        $u_nis = '|is_unique[master_siswa.nis]';
        $u_nisn = '|is_unique[master_siswa.nisn]';
        $u_name = '|is_unique[master_siswa.username]';

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[6]|max_length[20]'. $u_nisn);
        $this->form_validation->set_rules('username', 'Username', 'required|trim' . $u_name);

        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['text'] = 'Data Sudah ada, Pastikan NIS, NISN dan Username belum digunakan siswa lain';
        } else {
            $insert = [
                "nama"          => $this->input->post('nama_siswa', true),
                "nis"           => $nis,
                "nisn"          => $nisn,
                "jenis_kelamin" => $this->input->post('jenis_kelamin', true),
                "kelas_awal"    => $this->input->post('kelas_awal', true),
                "tahun_masuk"   => $this->input->post('tahun_masuk', true),
                "username"      => $username,
                "password"      => $this->input->post('password', true),
                "foto"          => 'uploads/foto_siswa/'.$nis.'jpg'
            ];

            $this->db->set('uid','UUID()',FALSE);
            $data['insert'] = $this->db->insert('master_siswa', $insert);
            $id = $this->db->insert_id();

            $siswa = $this->master->getSiswaById($id);
            $induk = [
                "id_siswa"   => $id,
                "uid"        => $siswa->uid,
                "status"     => 1
            ];

            $this->db->insert('buku_induk', $induk);
            $data['text'] = 'Siswa berhasil ditambahkan';
        }
        $this->output_json($data);
        //$this->output->set_content_type('application/json')->set_output($data);
    }

    public function edit($id) {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $siswa = $this->master->getSiswaById($id);
        $inputData = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'value' => $siswa->nama,
                'icon' => 'far fa-user',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'NIS',
                'name' => 'nis',
                'value' => $siswa->nis,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'number'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'value' => $siswa->nisn,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $siswa->jenis_kelamin,
                'icon' => 'fas fa-venus-mars',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'kelas_awal',
                'label' => 'Diterima di kelas',
                'value' => $siswa->kelas_awal,
                'icon' => 'fas fa-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tahun_masuk',
                'label' => 'Tgl diterima',
                'value' => $siswa->tahun_masuk,
                'icon' => 'tahun far fa-calendar-alt',
                'class' => 'tahun',
                'type' => 'text'
            ],
            [
                'name' => 'sekolah_asal',
                'label' => 'Sekolah Asal',
                'value' => $siswa->sekolah_asal,
                'icon' => 'fas fa-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
            'name' => 'status',
            'label' => 'Status',
            'value' => $siswa->status,
                'icon' => 'far fa-user',
            'class' => 'status',
            'type' => 'text'
        ]
        ];
        $inputBio = [
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $siswa->tempat_lahir,
                'icon' => 'far fa-map',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $siswa->tanggal_lahir,
                'icon' => 'far fa-calendar',
                'class' => 'tahun',
                'type' => 'text'
            ],
            ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $siswa->agama, 'icon' => 'far fa-calendar', 'type' => 'text'],
            ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $siswa->alamat, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $siswa->rt, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $siswa->rw, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $siswa->kelurahan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $siswa->kecamatan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $siswa->kabupaten, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $siswa->kode_pos, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $siswa->hp, 'icon' => 'far fa-user', 'type' => 'text']
        ];

        $inputOrtu = [
            ['name' => 'status_keluarga', 'label' => 'Status Keluarga', 'value' => $siswa->status_keluarga, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'anak_ke', 'label' => 'Anak ke', 'value' => $siswa->anak_ke, 'icon' => 'far fa-user', 'type' => 'number'],
            ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $siswa->nama_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            //['name' => 'pendidikan_ayah', 'label' => 'Pendidikan Ayah', 'value' => $siswa->pendidikan_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $siswa->pekerjaan_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $siswa->alamat_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $siswa->nohp_ayah, 'icon' => 'far fa-user', 'type' => 'number'],
            ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $siswa->nama_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            //['name' => 'pendidikan_ibu', 'label' => 'Pendidikan Ibu', 'value' => $siswa->pendidikan_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $siswa->pekerjaan_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $siswa->alamat_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $siswa->nohp_ibu, 'icon' => 'far fa-user', 'type' => 'number']
        ];

        $inputWali = [
            ['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $siswa->nama_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            //['name' => 'pendidikan_wali', 'label' => 'Pendidikan Wali', 'value' => $siswa->pendidikan_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $siswa->pekerjaan_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $siswa->alamat_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $siswa->nohp_wali, 'icon' => 'far fa-user', 'type' => 'number']
        ];

        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Siswa', 'subjudul' => 'Edit Data Siswa', 'siswa' => $siswa, 'setting' => $this->dashboard->getSetting()];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['smt'] = $this->dashboard->getSemester();
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['input_data'] = json_decode(json_encode($inputData), FALSE);
        $data['input_bio'] = json_decode(json_encode($inputBio), FALSE);
        $data['input_ortu'] = json_decode(json_encode($inputOrtu), FALSE);
        $data['input_wali'] = json_decode(json_encode($inputWali), FALSE);
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);

        if ( $this->ion_auth->is_admin() ) {
            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('master/siswa/edit');
            $this->load->view('_templates/dashboard/_footer');
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('master/siswa/edit');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function updateData() {
        $this->load->model('Master_model', 'master');

        $id_siswa = $this->input->post('id_siswa', true);
        $nis           = $this->input->post('nis', true);
        $nisn          = $this->input->post('nisn', true);

        $siswa = $this->master->getSiswaById($id_siswa);
        $u_nis = $siswa->nis === $nis ? "" : "|is_unique[master_siswa.nis]";
        $u_nisn = $siswa->nisn === $nisn ? "" : "|is_unique[master_siswa.nisn]";

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['text'] = 'Nama belum diisi atau NIS kurang dari 6 angka, atau NIS Sudah ada, Pastikan NIS dan NISN belum digunakan siswa lain';
        } else {
            $tgl_lahir = $this->input->post('tanggal_lahir', true);
            $tgl_masuk = $this->input->post('tahun_masuk', true);

            $input = [
            'nisn'   => $this->input->post('nisn', true),
            'nis'     => $this->input->post('nis', true),
            'nama'   => $this->input->post('nama', true),
            'jenis_kelamin'   => $this->input->post('jenis_kelamin', true),
            'tempat_lahir'   => $this->input->post('tempat_lahir', true),
            'tanggal_lahir'   => $this->strContains($tgl_lahir, '0000-') ? null : $tgl_lahir,
            'agama'   => $this->input->post('agama', true),
            'status_keluarga'   => $this->input->post('status_keluarga', true),
            'anak_ke'   => $this->input->post('anak_ke', true),
            'alamat'   => $this->input->post('alamat', true),
            'rt'   =>	$this->input->post('rt', true),
            'rw'   =>	$this->input->post('rw', true),
            'kelurahan'   => $this->input->post('kelurahan', true),
            'kecamatan'   => $this->input->post('kecamatan', true),
            'kabupaten'   => $this->input->post('kabupaten', true),
            'provinsi'   =>	$this->input->post('provinsi', true),
            'kode_pos'   =>	$this->input->post('kode_pos', true),
            'hp'   =>	$this->input->post('hp', true),
            'nama_ayah'   =>	$this->input->post('nama_ayah', true),
            'nohp_ayah'   =>	$this->input->post('nohp_ayah', true),
            'pendidikan_ayah'   =>	$this->input->post('pendidikan_ayah', true),
            'pekerjaan_ayah'   =>	$this->input->post('pekerjaan_ayah', true),
            'alamat_ayah'   =>	$this->input->post('alamat_ayah', true),
            'nama_ibu'   =>	$this->input->post('nama_ibu', true),
            'nohp_ibu'   =>	$this->input->post('nohp_ibu', true),
            'pendidikan_ibu'   =>	$this->input->post('pendidikan_ibu', true),
            'pekerjaan_ibu'   =>	$this->input->post('pekerjaan_ibu', true),
            'alamat_ibu'   =>	$this->input->post('alamat_ibu', true),
            'nama_wali'   =>	$this->input->post('nama_wali', true),
            'pendidikan_wali'   =>	$this->input->post('pendidikan_wali', true),
            'pekerjaan_wali'   =>	$this->input->post('pekerjaan_wali', true),
            'nohp_wali'   =>	$this->input->post('nohp_wali', true),
            'alamat_wali'   =>	$this->input->post('alamat_wali', true),
            'tahun_masuk'   =>	$this->strContains($tgl_masuk, '0000-') ? null : $tgl_masuk,
            'kelas_awal'   =>	$this->input->post('kelas_awal', true),
            'tgl_lahir_ayah'   =>	$this->input->post('tgl_lahir_ayah', true),
            'tgl_lahir_ibu'   =>	$this->input->post('tgl_lahir_ibu', true),
            'tgl_lahir_wali'   =>	$this->input->post('tgl_lahir_wali', true),
            'sekolah_asal'   =>	$this->input->post('sekolah_asal', true),
            'foto'   =>	$siswa->foto != null && $siswa->foto != "" ? $siswa->foto : 'uploads/foto_siswa/'.$nis.'.jpg'
            ];

            $this->master->update('master_siswa', $input, 'id_siswa', $id_siswa);

            $this->db->set('status', $this->input->post('status', true));
            $this->db->where('id_siswa', $siswa->id_siswa);
            $this->db->update('buku_induk');

            $data['insert'] = $input;
            $data['text'] = 'Siswa berhasil diperbaharui';
        }

        $this->output_json($data);
    }

    function strContains($string, $val) {
        return strpos($string, $val) !== false;
    }

    function uploadFile($id_siswa){
        $this->load->model('Master_model', 'master');

        $siswa = $this->master->getSiswaById($id_siswa);
        if(isset($_FILES["foto"]["name"])){

            $config['upload_path'] = './uploads/foto_siswa/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
            $config['overwrite'] = true;
            //$config['encrypt_name'] = TRUE;
            $config['file_name'] = $siswa->nis;

            $this->upload->initialize($config);
            if(!$this->upload->do_upload('foto')){
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            }else{
                $result = $this->upload->data();
                $data['src'] = base_url().'uploads/foto_siswa/'.$result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;

                $this->db->set('foto', 'uploads/foto_siswa/'.$result['file_name']);
                $this->db->where('id_siswa', $id_siswa);
                $this->db->update('master_siswa');
            }

            $data['type'] = $_FILES['foto']['type'];
            $data['size'] = $_FILES['foto']['size'];
        } else {
            $data['src'] = '';
        }


        $this->output_json($data);
    }

    function deleteFile($id_siswa) {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        if ($file_name != 'assets/img/siswa.png') {
            if (unlink($file_name)) {
                $this->db->set('foto', '');
                $this->db->where('id_siswa', $id_siswa);
                $this->db->update('master_siswa');

                echo 'File Delete Successfully';
            }
        }
    }

    public function delete() {
        $this->load->model('Master_model', 'master');

        $chk = $this->input->post('checked', true);
        $aksi = $this->input->post('aksi', true);
        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            $last = $aksi;
            if ($aksi == 'pindah') {
                foreach ($chk as $id) {
                    $this->db->set('status', '3');
                    $this->db->where('id_siswa', $id);
                    $this->db->update('buku_induk');
                }
                //$last = 'pindah';
            } elseif ($aksi == 'keluar') {
                foreach ($chk as $id) {
                    $this->db->set('status', '4');
                    $this->db->where('id_siswa', $id);
                    $this->db->update('buku_induk');
                }
                //$last = 'keluar';
            } elseif ($aksi == 'hapus') {
                if ($this->master->delete('master_siswa', $chk, 'id_siswa')) {
                    $this->master->delete('buku_induk', $chk, 'id_siswa');
                }
                //$last = 'hapus';
            }
            $this->output_json(['status' => true, 'total' => count($chk), 'last'=>$last]);
        }
    }

    /*
    public function previewExcel() {
        $this->load->model('Master_model', 'master');

        $config['upload_path'] = './uploads/import/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = true;

        //$this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('upload_file')) {
            $error = $this->upload->display_errors();
            echo $error;
            die;
        } else {
            $file = $this->upload->data('full_path');
            $ext = $this->upload->data('file_ext');

            switch ($ext) {
                case '.xlsx':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    break;
                case '.xls':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                    break;
                case '.csv':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                    break;
                default:
                    echo "unknown file ext";
                    die;
            }

            $spreadsheet = $reader->load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            $data = [];
            $arr_nisn = [];
            $arr_nis = [];
            $arr_username = [];
            for ($i = 1; $i < count($sheetData); $i++) {
                if ($sheetData[$i][0] != null) {
                    $nisn = str_replace("'", "", $sheetData[$i][1]);
                    $arr_nisn[] = $nisn;
                    $nis = str_replace("'", "", $sheetData[$i][2]);
                    $arr_nis[] = $nis;
                    $username = $sheetData[$i][5];
                    $arr_username[] = $username;
                    $data['siswa'][] = [
                        'nisn' => $nisn,
                        'nis' => $nis,
                        'nama' => $sheetData[$i][3],
                        'jenis_kelamin' => $sheetData[$i][4],
                        'username' => $username,
                        'password' => $sheetData[$i][6],
                        'tempat_lahir' => $sheetData[$i][10],
                        'tanggal_lahir' => $sheetData[$i][11],
                        'agama' => $sheetData[$i][12],
                        'status_keluarga' => $sheetData[$i][16],
                        'anak_ke' => $sheetData[$i][15],
                        'alamat' => $sheetData[$i][17],
                        'rt' => $sheetData[$i][18],
                        'rw' => $sheetData[$i][19],
                        'kelurahan' => $sheetData[$i][20],
                        'kecamatan' => $sheetData[$i][21],
                        'kabupaten' => $sheetData[$i][22],
                        'provinsi' => $sheetData[$i][23],
                        'kode_pos' => $sheetData[$i][24],
                        'hp' => str_replace("'", "", $sheetData[$i][13]),
                        'nama_ayah' => $sheetData[$i][25],
                        'nohp_ayah' => str_replace("'", "", $sheetData[$i][29]),
                        'pendidikan_ayah' => $sheetData[$i][27],
                        'pekerjaan_ayah' => $sheetData[$i][28],
                        'alamat_ayah' => $sheetData[$i][30],
                        'nama_ibu' => $sheetData[$i][31],
                        'nohp_ibu' => str_replace("'", "", $sheetData[$i][35]),
                        'pendidikan_ibu' => $sheetData[$i][33],
                        'pekerjaan_ibu' => $sheetData[$i][34],
                        'alamat_ibu' => $sheetData[$i][36],
                        'nama_wali' => $sheetData[$i][37],
                        'pendidikan_wali' => $sheetData[$i][39],
                        'pekerjaan_wali' => $sheetData[$i][40],
                        'nohp_wali' => str_replace("'", "", $sheetData[$i][41]),
                        'alamat_wali' => $sheetData[$i][42],
                        'tahun_masuk' => $sheetData[$i][8],
                        'kelas_awal' => $sheetData[$i][7],
                        'tgl_lahir_ayah' => $sheetData[$i][26],
                        'tgl_lahir_ibu' => $sheetData[$i][32],
                        'tgl_lahir_wali' => $sheetData[$i][38],
                        'sekolah_asal' => $sheetData[$i][9],
                        'id_siswa' => isset($sheetData[$i][43]) ? $sheetData[$i][43] : ''
                        ];
                    //$jurusan[] = $sheetData[$i][0];
                }
            }

            unlink($file);

            $data['exist'] = $this->master->getSiswaByArrNisn($arr_nisn, $arr_nis, $arr_username);
            echo json_encode($data);
        }
    }
    */

    public function do_import() {
        $input = $this->input->post('siswa', true);
        $errors = [];
        $duplikat = [];

        foreach ($input as $value) {
            $data = [
                "nisn" =>             $value["2"]  ?? '', //2 = 	NISN*
                "nis" =>              $value["3"]  ?? '', //3 = 	NIS*
                "nama" =>             $value["4"]  ?? '', //4 = 	NAMA SISWA*
                "username" =>         $value["6"]  ?? '', //6 = 	USERNAME*
                "password" =>         $value["7"]  ?? '', //7 = 	PASSWORD*
            ];
            //foreach (((array)$input)[$key1] as $key => $val) {
            //    $data[$key] = $val;
            //}

            $this->form_validation->set_data($data);

            $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]|is_unique[master_siswa.nis]');
            $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[6]|max_length[20]|is_unique[master_siswa.nisn]');
            $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[master_siswa.username]');
            $this->form_validation->set_rules('password', 'Password', 'required|trim|is_unique[master_siswa.username]');
            if ($this->form_validation->run() == FALSE) {
                $duplikat[] = $data;
                $errors[$data['nama']] = [
                    'nama' => form_error('nama'),
                    'nis' => form_error('nis'),
                    'nisn' => form_error('nisn'),
                    'username' => form_error('username'),
                    'password' => form_error('password'),
                ];
            }
        }

        if (count($errors) > 0) {
            $data = [
                'status'	=> false,
                'errors'	=> $errors,
                'duplikat'  => $duplikat
            ];
            $this->output_json($data);
        } else {
            $this->db->trans_start();
            foreach ($input as $value) {
                $siswa = [
                    //1 = 	NO
                    "nisn" =>             $value["2"]  ?? '', //2 = 	NISN*
                    "nis" =>              $value["3"]  ?? '', //3 = 	NIS*
                    "nama" =>             $value["4"]  ?? '', //4 = 	NAMA SISWA*
                    "jenis_kelamin" =>    $value["5"]  ?? '', //5 = 	JENIS KELAMIN
                    "username" =>         $value["6"]  ?? '', //6 = 	USERNAME*
                    "password" =>         $value["7"]  ?? '', //7 = 	PASSWORD*
                    "kelas_awal" =>       $value["8"]  ?? '', //8 = 	KELAS AWAL *
                    "tahun_masuk" =>      $value["9"]  ?? '', //9 = 	TANGGAL DI TERIMA
                    "sekolah_asal" =>     $value["10"] ?? '', //10 = 	SEKOLAH ASAL
                    "tempat_lahir" =>     $value["11"] ?? '', //11 = 	TEMPAT LAHIR
                    "tanggal_lahir" =>    $value["12"] ?? '', //12 = 	TANGGAL LAHIR
                    "agama" =>            $value["13"] ?? '', //13 = 	AGAMA
                    "hp" =>               $value["14"] ?? '0', //14 = 	NOMOR TELEPON
                    "email" =>            $value["15"] ?? '', //15 = 	EMAIL
                    "anak_ke" =>          $value["16"] ?? '1', //16 = 	ANAK KE
                    "status_keluarga" =>  $value["17"] ?? '1', //17 = 	STATUS DALAM KELUARGA
                    "alamat" =>           $value["18"] ?? '', //18 = 	ALAMAT
                    "rt" =>               $value["19"] ?? '', //19 = 	RT
                    "rw" =>               $value["20"] ?? '', //20 = 	RW
                    "kelurahan" =>        $value["21"] ?? '', //21 = 	DESA/KELURAHAN
                    "kecamatan" =>        $value["22"] ?? '', //22 = 	KECAMATAN
                    "kabupaten" =>        $value["23"] ?? '', //23 = 	KABUPATEN/KOTA
                    "provinsi" =>         $value["24"] ?? '', //24 = 	PROVINSI
                    "kode_pos" =>         $value["25"] ?? '', //25 = 	KODE POS
                    "nama_ayah" =>        $value["26"] ?? '', //26 = 	NAMA AYAH
                    "tgl_lahir_ayah" =>   $value["27"] ?? '', //27 = 	TANGGAL LAHIR AYAH
                    "pendidikan_ayah" =>  $value["28"] ?? '', //28 = 	PENDIDIKAN AYAH
                    "pekerjaan_ayah" =>   $value["29"] ?? '', //29 = 	PEKERJAAN AYAH
                    "nohp_ayah" =>        $value["30"] ?? '', //30 = 	NOMOR TELEPON AYAH
                    "alamat_ayah" =>      $value["31"] ?? '', //31 = 	ALAMAT AYAH
                    "nama_ibu" =>         $value["32"] ?? '', //32 = 	NAMA IBU
                    "tgl_lahir_ibu" =>    $value["33"] ?? '', //33 = 	TANGGAL LAHIR IBU
                    "pendidikan_ibu" =>   $value["34"] ?? '', //34 = 	PENDIDIKAN IBU
                    "pekerjaan_ibu" =>    $value["35"] ?? '', //35 = 	PEKERJAAN IBU
                    "nohp_ibu" =>         $value["36"] ?? '', //36 = 	NOMOR TELEPON IBU
                    "alamat_ibu" =>       $value["37"] ?? '', //37 = 	ALAMAT IBU
                    "nama_wali" =>        $value["38"] ?? '', //38 = 	NAMA WALI
                    "tgl_lahir_wali" =>   $value["39"] ?? '', //39 = 	TANGGAL LAHIR WALI
                    "pendidikan_wali" =>  $value["40"] ?? '', //40 = 	PENDIDIKAN WALI
                    "pekerjaan_wali" =>   $value["41"] ?? '', //41 = 	PEKERJAAN WALI
                    "nohp_wali" =>        $value["42"] ?? '', //42 = 	NOMOR TELEPON WALI
                    "alamat_wali" =>      $value["43"] ?? '', //43 = 	ALAMAT WALI
                ];
                $siswa['foto'] = 'uploads/foto_siswa/'.$siswa['nis'].'.jpg';
                $this->db->set('uid','UUID()',FALSE);
                $save = $this->db->insert('master_siswa', $siswa);
            }

            $uids = $this->db->select('id_siswa, uid')->from('master_siswa')->get()->result();

            foreach ($uids as $uid) {
                $check = $this->db->select('id_siswa')->from('buku_induk')->where('id_siswa', $uid->id_siswa);
                if ($check->get()->num_rows() == 0) {
                    $this->db->insert('buku_induk', $uid);
                }
            }

            $this->db->trans_complete();
            $data = [
                'status'	=> true,
                'errors'	=> [],
            ];
            $this->output_json($data);
        }
    }

    public function update() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Update Data Siswa', 'subjudul' => 'Update Data Siswa', 'setting' => $this->dashboard->getSetting()];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['tp'] = $this->dashboard->getTahun();
        $data['smt'] = $this->dashboard->getSemester();
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
        $data['tipe'] = 'update';
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/siswa/update');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function downloadData($id_kelas) {
        $this->load->model('Master_model', 'master');

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $siswas = $this->master->getSiswaByKelas($tp->id_tp, $smt->id_smt, $id_kelas);
        foreach ($siswas as $ind=>$siswa) {
            $siswa->no = ($ind + 1);
        }
        $this->output_json(['status' => true, 'siswa'=>$siswas]);
    }

    public function updateAll() {
        $input = $this->input->post('siswa', true);
        $this->db->trans_start();
        foreach ($input as $value) {
            $siswa = [
                //1 = 	NO
                "nisn" =>             $value["2"]  ?? '', //2 = 	NISN*
                "nis" =>              $value["3"]  ?? '', //3 = 	NIS*
                "nama" =>             $value["4"]  ?? '', //4 = 	NAMA SISWA*
                "jenis_kelamin" =>    $value["5"]  ?? '', //5 = 	JENIS KELAMIN
                "username" =>         $value["6"]  ?? '', //6 = 	USERNAME*
                "password" =>         $value["7"]  ?? '', //7 = 	PASSWORD*
                "kelas_awal" =>       $value["8"]  ?? '', //8 = 	KELAS AWAL *
                "tahun_masuk" =>      $value["9"]  ?? '', //9 = 	TANGGAL DI TERIMA
                "sekolah_asal" =>     $value["10"] ?? '', //10 = 	SEKOLAH ASAL
                "tempat_lahir" =>     $value["11"] ?? '', //11 = 	TEMPAT LAHIR
                "tanggal_lahir" =>    $value["12"] ?? '', //12 = 	TANGGAL LAHIR
                "agama" =>            $value["13"] ?? '', //13 = 	AGAMA
                "hp" =>               $value["14"] ?? '0', //14 = 	NOMOR TELEPON
                "email" =>            $value["15"] ?? '', //15 = 	EMAIL
                "anak_ke" =>          $value["16"] ?? '1', //16 = 	ANAK KE
                "status_keluarga" =>  $value["17"] ?? '1', //17 = 	STATUS DALAM KELUARGA
                "alamat" =>           $value["18"] ?? '', //18 = 	ALAMAT
                "rt" =>               $value["19"] ?? '', //19 = 	RT
                "rw" =>               $value["20"] ?? '', //20 = 	RW
                "kelurahan" =>        $value["21"] ?? '', //21 = 	DESA/KELURAHAN
                "kecamatan" =>        $value["22"] ?? '', //22 = 	KECAMATAN
                "kabupaten" =>        $value["23"] ?? '', //23 = 	KABUPATEN/KOTA
                "provinsi" =>         $value["24"] ?? '', //24 = 	PROVINSI
                "kode_pos" =>         $value["25"] ?? '', //25 = 	KODE POS
                "nama_ayah" =>        $value["26"] ?? '', //26 = 	NAMA AYAH
                "tgl_lahir_ayah" =>   $value["27"] ?? '', //27 = 	TANGGAL LAHIR AYAH
                "pendidikan_ayah" =>  $value["28"] ?? '', //28 = 	PENDIDIKAN AYAH
                "pekerjaan_ayah" =>   $value["29"] ?? '', //29 = 	PEKERJAAN AYAH
                "nohp_ayah" =>        $value["30"] ?? '', //30 = 	NOMOR TELEPON AYAH
                "alamat_ayah" =>      $value["31"] ?? '', //31 = 	ALAMAT AYAH
                "nama_ibu" =>         $value["32"] ?? '', //32 = 	NAMA IBU
                "tgl_lahir_ibu" =>    $value["33"] ?? '', //33 = 	TANGGAL LAHIR IBU
                "pendidikan_ibu" =>   $value["34"] ?? '', //34 = 	PENDIDIKAN IBU
                "pekerjaan_ibu" =>    $value["35"] ?? '', //35 = 	PEKERJAAN IBU
                "nohp_ibu" =>         $value["36"] ?? '', //36 = 	NOMOR TELEPON IBU
                "alamat_ibu" =>       $value["37"] ?? '', //37 = 	ALAMAT IBU
                "nama_wali" =>        $value["38"] ?? '', //38 = 	NAMA WALI
                "tgl_lahir_wali" =>   $value["39"] ?? '', //39 = 	TANGGAL LAHIR WALI
                "pendidikan_wali" =>  $value["40"] ?? '', //40 = 	PENDIDIKAN WALI
                "pekerjaan_wali" =>   $value["41"] ?? '', //41 = 	PEKERJAAN WALI
                "nohp_wali" =>        $value["42"] ?? '', //42 = 	NOMOR TELEPON WALI
                "alamat_wali" =>      $value["43"] ?? '', //43 = 	ALAMAT WALI
            ];
            $siswa['foto'] = 'uploads/foto_siswa/'.$value['3'].'.jpg';
            $save = $this->db->update('master_siswa', $siswa, array('id_siswa' => $value['44']));
        }
        $this->db->trans_complete();
        $data = [
            'status'	=> $save ?? false,
            'errors'	=> [],
        ];
        $this->output_json($data);
    }

    public function update_foto() {
        $input = $this->input->post('siswa', true);
        $errors = [];
        $duplikat = [];

        foreach ($input as $value) {
            $this->form_validation->set_data($value);
            $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]');
            if ($this->form_validation->run() == FALSE) {
                $duplikat[] = $value;
                $errors[$value['nama']] = [
                    'nis' => form_error('nis'),
                ];
            }
        }

        if (count($errors) > 0) {
            $data = [
                'status'	=> false,
                'errors'	=> $errors,
                'duplikat'  => $duplikat
            ];
            $this->output_json($data);
        } else {
            $this->db->trans_start();
            foreach ($input as $value) {
                $foto = 'uploads/foto_siswa/' . trim($value['nis'] ?? '00') . '.jpg';
                if (isset($value['foto'])) {
                    $base64_image_string = $value['foto'];
                    $extension = $value['ext'];
                    if ($extension == 'jpeg') $extension = 'jpg';
                    $output_file = trim($value['nis'] ?? '00') . '.' . $extension;
                    file_put_contents('./uploads/foto_siswa/' . $output_file, base64_decode($base64_image_string));
                    $foto = 'uploads/foto_siswa/'.$output_file;
                }

                $siswa = [
                    "nis" => $value["nis"]  ?? '',
                    "foto" => $foto
                ];
                $save = $this->db->update('master_siswa', $siswa, array('id_siswa' => $value['id']));
            }
            $this->db->trans_complete();
            $data = [
                'status'	=> true,
                'errors'	=> [],
            ];
            $this->output_json($data);
        }
    }

    /*
    public function previewExcelNis() {
        $config['upload_path'] = './uploads/import/';
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = true;

        //$this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('upload_file')) {
            $error = $this->upload->display_errors();
            echo $error;
            die;
        } else {
            $file = $this->upload->data('full_path');
            $ext = $this->upload->data('file_ext');

            switch ($ext) {
                case '.xlsx':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    break;
                case '.xls':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                    break;
                case '.csv':
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                    break;
                default:
                    echo "unknown file ext";
                    die;
            }

            $spreadsheet = $reader->load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            $data = [];
            for ($i = 1; $i < count($sheetData); $i++) {
                if ($sheetData[$i][0] != null) {
                    $data[] = [
                        'nisn' => str_replace("'", "", $sheetData[$i][1]),
                        'nis' => str_replace("'", "", $sheetData[$i][2])
                    ];
                }
            }

            unlink($file);
            echo json_encode($data);
        }
    }
    */

    public function updateNisByNisn() {
        $input = json_decode($this->input->post('siswa', true));
        //$this->db->trans_start();

        foreach ($input as $val) {
            $this->db->set('nis', trim($val->nis ?? ''));
            $this->db->where('nisn', trim($val->nisn ?? ''));
            $save = $this->db->update('master_siswa');
        }
        $this->db->trans_complete();

        $this->output_json($save);
    }

    public function editLogin() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $id_siswa 	= $this->input->post('id_siswa', true);
        $username 	= $this->input->post('username', true);
        $pass 	= $this->input->post('new', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $siswa_lain = $this->dashboard->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
        $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

        if ($siswa_lain && $siswa_lain->id_siswa != $id_siswa) {
            $data = [
                //'siswa' => $siswa,
                'status' => false,
                'errors' => ['username' => 'Username sudah digunakan']
            ];
        } elseif ($this->form_validation->run() === FALSE){
            $data = [
                'status' => false,
                'errors' => [
                    'old' => form_error('old'),
                    'new' => form_error('new'),
                    'new_confirm' => form_error('new_confirm')
                ]
            ];
        }else{
            $siswa =$this->db->get_where('master_siswa', 'id_siswa="'.$id_siswa.'"')->row();
            $nama = explode(' ', $siswa->nama ?? '');
            $first_name = $nama[0];
            $last_name = end($nama);
            $username = trim($username ?? '');
            $password = trim($pass ?? '');
            $email = $siswa->nis.'@siswa.com';
            $additional_data = [
                'first_name'	=> $first_name,
                'last_name'		=> $last_name
            ];
            $group = array('3');

            $user_siswa = $this->db->get_where('users', 'email="'.$email.'"')->row();
            $deleted = true;
            if ($user_siswa != null) {
                $deleted = $this->ion_auth->delete_user($user_siswa->id);
            }

            if ($deleted) {
                $this->ion_auth->register($username, $password, $email, $additional_data, $group);
                $this->db->set('username', $username);
                $this->db->set('password', $password);
                $this->db->where('id_siswa', $id_siswa);
                $status = $this->db->update('master_siswa');
                $msg	 = !$status ? 'Gagal mengganti username/passsword.' : 'berhasil mengganti username/passsword.';
            } else {
                $status = false;
                $msg	 = 'Gagal mengganti username/passsword.';
            }
            $data['status'] = $status;
            $data['text'] = $msg;
        }
        $this->output_json($data);
    }

    private function registerSiswa($username, $password, $email, $additional_data, $group) {
        $reg = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
        $data['status'] = true;
        $data['id'] = $reg;
        if ($reg == false) {
            $data['status'] = false;
        }
        return $data;
    }
}
