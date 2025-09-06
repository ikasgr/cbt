<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 03/08/20
 * Time: 13:19
 */

class Kelasmaterijadwal extends CI_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
		//} else if (!$this->ion_auth->is_admin() || !$this->ion_auth->in_group('guru')) {
		//	show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Cbt_model', 'cbt');
		$this->load->model('Log_model', 'logging');
		$this->load->model('Kelas_model', 'kelas');
		$this->load->model('Dropdown_model', 'dropdown');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function output_json($data, $encode = true) {
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function index() {
		$user = $this->ion_auth->user()->row();
		$data = [
			'user' => $user,
			'judul' => 'Jadwal Pelajaran',
			'subjudul' => 'Set Jadwal Pelajaran',
			'setting'		=> $this->dashboard->getSetting()
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

		$data['kelas']	= $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
		$data['id_kelas'] = '0';
        $data['method'] = '';
        $data['jmlIst'] = [];
        $data['jmlMapel'] = [];

        $data['thn_selected'] = $tp->tahun;
        $bln = $smt->id_smt == '1' ? '7' : '1';
        $tahun = explode('/', $tp->tahun ?? '');
        $thn = $smt->id_smt == '1' ? $tahun[0] : $tahun[1];
        $data['bln_selected'] = $bln;
        $data['date_selected'] = $thn . '-' . $bln . '-' . date('d');

		if ( $this->ion_auth->is_admin() ) {
			$data['profile'] = $this->dashboard->getProfileAdmin($user->id);
			$this->load->view('_templates/dashboard/_header', $data);
			$this->load->view('kelas/materijadwal/data');
			$this->load->view('_templates/dashboard/_footer');
		} elseif ($this->ion_auth->in_group('guru')) {
			$guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
			$data['guru'] = $guru;
			$this->load->view('members/guru/templates/header', $data);
            $this->load->view('kelas/materijadwal/data');
			$this->load->view('members/guru/templates/footer');
		}
	}

	public function kelas() {
        $tahun = $this->input->get('tahun');
        $bulan = $this->input->get('bulan');

        $kelas = $this->input->get('kelas');
        $date = $this->input->get('date');
        $user = $this->ion_auth->user()->row();
        $setting = $this->dashboard->getSetting();
		$data = [
			'user' => $user,
			'judul' => 'Jadwal Materi / Tugas',
			'subjudul' => 'Set Jadwal Materi / Tugas',
			'setting'		=> $setting
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;
		$data['kelas']	= $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

		$jadk = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $kelas);
		if ($jadk==null) {
			$data['jadwal_kbm'] = json_decode(json_encode([
				'id_tp' => $tp->tahun,
				'id_smt' => $smt->smt,
				'id_kelas' => $kelas,
				'kbm_jam_pel' => '',
				'kbm_jam_mulai' => '',
				'kbm_jml_mapel_hari' => '',
				'istirahat' => serialize([]),
				'ada' => false
			]));
		} else {
			$data['jadwal_kbm'] = $jadk;
		}

		$data['id_kelas'] = $kelas;
		$jadm = $this->kelas->getJadwalMapelGroupJam($tp->id_tp, $smt->id_smt, $kelas);
		$jml_mapel = $jadk==null ? 1 : $jadk->kbm_jml_mapel_hari;
		if ($jadm==null) {
			for ($i=0;$i<$jml_mapel;$i++) {
				$jadwal_mapel[] = [
					'jadwal' => $this->kelas->getDummyJadwalMapel($tp->id_tp, $smt->id_smt, $i+1, $kelas)
				];
			}
			$data['method'] = 'add';
		} else {
			foreach ($jadm as $j) {
				$jadwal_mapel[] = [
					'jadwal' => $this->kelas->getJadwalMapelByHari($tp->id_tp, $smt->id_smt, $j->jam_ke, $kelas)
				];
			}
			$data['method'] = 'edit';
		}

		$data['jadwal_mapel'] = $jadwal_mapel;
		$data['mapels'] = $this->master->getAllMapel();
		/*
		$senin = date("Y-m-d", strtotime('monday this week', strtotime($date)));
        $selasa = date("Y-m-d", strtotime('tuesday this week', strtotime($date)));
        $rabu = date("Y-m-d", strtotime('wednesday this week', strtotime($date)));
        $kamis = date("Y-m-d", strtotime('thursday this week', strtotime($date)));
        $jumat = date("Y-m-d", strtotime('friday this week', strtotime($date)));
        $sabtu = date("Y-m-d", strtotime('saturday this week', strtotime($date)));
		*/

        $week = [
            date("Y-m-d", strtotime('monday this week', strtotime($date))),
            date("Y-m-d", strtotime('tuesday this week', strtotime($date))),
            date("Y-m-d", strtotime('wednesday this week', strtotime($date))),
            date("Y-m-d", strtotime('thursday this week', strtotime($date))),
            date("Y-m-d", strtotime('friday this week', strtotime($date))),
            date("Y-m-d", strtotime('saturday this week', strtotime($date)))
        ];
        $data['thn_selected'] = $tahun;
        $data['bln_selected'] = $bulan;
        $data['date_selected'] = $date;
        $data['week'] = $week;
        $data['opsi_materi'] = $this->kelas->getAllMateriByKelas($tp->id_tp, $smt->id_smt);;

        $semua_materi = $this->kelas->getAllJadwalMateriByKelas($tp->id_tp, $smt->id_smt);
        $data['detail_jadwal_materi'] = isset($semua_materi[1]) ? $semua_materi[1] : [];
        $data['detail_jadwal_tugas'] = isset($semua_materi[2]) ? $semua_materi[2] : [];

		if ($this->ion_auth->is_admin()) {
			$data['profile'] = $this->dashboard->getProfileAdmin($user->id);
			$this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('kelas/materijadwal/data');
			$this->load->view('_templates/dashboard/_footer');
		} elseif ($this->ion_auth->in_group('guru')) {
			$data['guru'] = $this->dashboard->getDetailGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
			$this->load->view('members/guru/templates/header', $data);
            $this->load->view('kelas/materijadwal/data');
			$this->load->view('members/guru/templates/footer');
		}
	}

	public function setJadwal() {
		$istirahat = [];
		for ($i = 1; $i < 5; $i++) {
			$jamke = $this->input->post('ist' . $i, true);
			$durasi = $this->input->post('dur_ist' . $i, true);
			if ($jamke) {
				$istirahat[] = [
					'ist' => $jamke,
					'dur' => $durasi
				];
			}
		}

		$id_tp = $this->master->getTahunActive()->id_tp;
		$id_smt = $this->master->getSemesterActive()->id_smt;
		$id_kelas = $this->input->post('id_kelas', true);

		$insert = [
			'id_kbm'				=> $id_tp . $id_smt . $id_kelas,
			'id_tp'					=> $id_tp,
			'id_smt'				=> $id_smt,
			'id_kelas' 				=> $id_kelas,
			'kbm_jam_pel' 			=> $this->input->post('jam_mapel', true),
			'kbm_jam_mulai' 		=> $this->input->post('jam_mulai', true),
			'kbm_jml_mapel_hari' 	=> $this->input->post('jml_mapel', true),
			'istirahat'				=> serialize($istirahat)
		];
		$update = $this->db->replace('kelas_jadwal_kbm', $insert);
		//$this->master->create('kelas_jadwal_kbm', $insert, false);
		$this->logging->saveLog(3, 'merubah jadwal pelajaran');

		$data['status'] = $update;
		$this->output_json($data);
	}

	public function setMapel() {
		$input = json_decode($this->input->post('data', true));
		$id_kelas = $this->input->post('id_kelas', true);
		foreach ($input as $d) {
			$data = [
				'id_jadwal'	=> $d->id_tp . $d->id_smt .'0'. $id_kelas .'0'. $d->id_hari . $d->jam_ke,
				'id_tp'		=> $d->id_tp,
				'id_smt'	=> $d->id_smt,
				'id_kelas'	=> $id_kelas,
				'id_hari'	=> $d->id_hari,
				'jam_ke'	=> $d->jam_ke,
				'id_mapel'	=> $d->id_mapel,
			];
			$update = $this->db->replace('kelas_jadwal_mapel', $data);
		}
		$res['status'] = $update;
		$this->output_json($res);
	}

    public function saveJadwal() {
        $input_materi = json_decode($this->input->post('materi', true));
        $input_tugas = json_decode($this->input->post('tugas', true));

        foreach ($input_materi as $im) {
            $insert = [
                'jenis' => '1',
                'id_kjm' => $im->id_kjm,
                'id_tp' => $im->id_tp,
                'id_smt' => $im->id_smt,
                'id_kelas' => $im->id_kelas,
                'id_materi' => $im->id_materi,
                'id_mapel' => $im->id_mapel,
                'jadwal_materi' => $im->jadwal_materi
            ];
            $update = $this->db->replace('kelas_jadwal_materi', $insert);
        }

        foreach ($input_tugas as $im) {
            $insert = [
                'jenis' => '2',
                'id_kjm' => $im->id_kjm,
                'id_tp' => $im->id_tp,
                'id_smt' => $im->id_smt,
                'id_kelas' => $im->id_kelas,
                'id_materi' => $im->id_materi,
                'id_mapel' => $im->id_mapel,
                'jadwal_materi' => $im->jadwal_materi
            ];
            $update = $this->db->replace('kelas_jadwal_materi', $insert);
        }

        $this->logging->saveLog(3, 'merubah jadwal materi dan tugas');

        $this->output_json($update);
    }
}
