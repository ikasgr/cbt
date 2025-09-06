<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 14:24
 */

class Cbtactivate extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
        } else if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
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
        $this->load->model('Cbt_model', 'cbt');

		$user = $this->ion_auth->user()->row();
		$data = [
			'user' => $user,
			'judul' => 'Aktivasi Siswa',
			'subjudul' => 'Aktifkan/Nonaktifkan Siswa',
			'profile'		=> $this->dashboard->getProfileAdmin($user->id),
			'setting'		=> $this->dashboard->getSetting()
		];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();

            $jadwals = $this->cbt->getJadwalKelas($tp->id_tp, $smt->id_smt);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? '');
                foreach ($kls as $kl) {
                    $arrKls[] = $kl['kelas_id'];
                }
            }
            $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('cbt/aktivasi/data.php');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();
            $data['pengawas'] = $this->cbt->getPengawasByGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);

            $jadwals = $this->cbt->getJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? '');
                foreach ($kls as $kl) {
                    $arrKls[] = $kl['kelas_id'];
                }
            }
            $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('cbt/aktivasi/data.php');
            $this->load->view('members/guru/templates/footer');
        }
	}

    public function getSiswaRuang() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswas = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $sesi);
        $this->output_json($siswas);
    }

    public function data() {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->getJenis(), false);
    }

    public function aktifkanSemua() {
        $posts = $this->input->post('ids', true);
        $this->db->where_in('id_siswa', $posts);
        $siswas = $this->db->get('master_siswa')->result();
        $jum = 0;
        foreach ($siswas as $siswa) {
            $this->aktifkan($siswa);
            ++$jum;
        }

        $data = [
            'status' => true,
            'jumlah' => $jum,
            'msg'	 => $jum . ' siswa diaktifkan.'
        ];
        $this->output_json($data);
    }

    public function nonaktifkanSemua() {
        $posts = $this->input->post('ids', true);

        $this->load->model('Users_model', 'users');
        $siswas = $this->users->getSiswaAktif($posts);
        $jum = 0;
        foreach ($siswas as $siswa) {
            $del = $this->nonaktifkan($siswa, $siswa->nama);
            if ($del['status']) $jum +=1;
            //else $this->output_json($del);
        }
        $data = [
            'status' => true,
            'jumlah' => $jum,
            'msg'	 => $jum.' siswa dinonaktifkan.'
        ];
        $this->output_json($data);
    }

    public function aktifkanSesi() {
        $this->load->model('Dashboard_model', 'dashboard');

        $posts = $this->input->post('ids', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arrRuang = [];
        $arrSesi = [];
        foreach ($posts as $post) {
            $split = explode('-', $post);
            $arrRuang[] = $split[0];
            $arrSesi[] = $split[1];
        }
        // get all ruang & sesi
        $this->db->select('a.siswa_id, a.ruang_id, a.sesi_id, b.nama, b.username, b.password, b.email, b.nis');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->where_in('a.ruang_id', $arrRuang)->where_in('a.sesi_id', $arrSesi);
        $this->db->join('master_siswa b', 'a.siswa_id=b.id_siswa');
        $this->db->join('kelas_siswa c', 'a.siswa_id=c.id_siswa AND c.id_tp='.$tp->id_tp.' AND c.id_smt='.$smt->id_smt);
        $siswas = $this->db->get()->result();

        $jum = 0;
        foreach ($siswas as $siswa) {
            $this->aktifkan($siswa);
            ++$jum;
        }
        $data = [
            'status' => true,
            'jumlah' => $jum,
            'msg'	 => $jum . ' siswa diaktifkan.'
        ];
        $this->output_json($data);
    }

    public function nonaktifkanSesi() {
        $this->load->model('Dashboard_model', 'dashboard');

        $posts = $this->input->post('ids', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arrRuang = [];
        $arrSesi = [];
        foreach ($posts as $post) {
            $split = explode('-', $post);
            $arrRuang[] = $split[0];
            $arrSesi[] = $split[1];
        }
        // get all ruang & sesi
        $this->db->select('a.siswa_id, a.ruang_id, a.sesi_id, b.nama, b.username, b.password, b.email, b.nis, b.nisn, d.id');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->where_in('a.ruang_id', $arrRuang)->where_in('a.sesi_id', $arrSesi);
        $this->db->join('master_siswa b', 'a.siswa_id=b.id_siswa');
        $this->db->join('users d', 'b.username=d.username');
        $this->db->join('kelas_siswa c', 'a.siswa_id=c.id_siswa AND c.id_tp='.$tp->id_tp.' AND c.id_smt='.$smt->id_smt);
        $siswas = $this->db->get()->result();

        $jum = 0;
        foreach ($siswas as $siswa) {
            $this->nonaktifkan($siswa, $siswa->nama);
            ++$jum;
        }

        $data = [
            'status' => true,
            'jumlah' => $jum,
            'data' => $siswas,
            'msg'	 => $jum . ' siswa dinonaktifkan.'
        ];
        $this->output_json($data);
    }

    private function aktifkan($siswa) {
        $nama = explode(' ', $siswa->nama ?? '');

        $first_name = $nama[0];
        $last_name = end($nama);
        $username = trim($siswa->username ?? '');
        $password = trim($siswa->password ?? '');
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
            $reg = $this->registerSiswa($username, $password, $email, $additional_data, $group);
            $data = [
                'status'	=> $reg,
                'msg'	 => !$reg ? 'Akun '.$siswa->nama.' gagal diaktifkan.' : 'Akun '.$siswa->nama.' diaktifkan.'
            ];
        } else {
            $data = [
                'status' => false,
                'msg'	 => 'Akun siswa tidak tersedia (sudah digunakan).'
            ];
        }
        return $data;
    }

    private function nonaktifkan($user, $nama) {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            $data = [
                'status'	=> false,
                'msg'		=> 'You must be an administrator to view this page.'
            ];
        } else {
            if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                $deleted = $this->ion_auth->delete_user($user->id);
                $data = [
                    'status'	=> $deleted,
                    'msg'	 => $deleted ? 'Siswa '.urldecode($nama).' dinonaktifkan.' : 'Siswa '.urldecode($nama).' gagal dinonaktifkan.'
                ];
            } else {
                $data = [
                    'status'	=> false,
                    'msg'	 => 'Anda bukan admin.'
                ];
            }
        }
        return $data;
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
