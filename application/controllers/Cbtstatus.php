<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 14:24
 */

class Cbtstatus extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
		} else if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->library('upload');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function output_json($data, $encode = true) {
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

		$user = $this->ion_auth->user()->row();
		$data = [
			'user' => $user,
			'judul' => 'Status Ujian Siswa',
			'subjudul' => 'Status Siswa',
			'setting' => $this->dashboard->getSetting(),
		];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

		if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $data['jadwal'] = $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt);
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();

            $jadwals = $this->cbt->getJadwalKelas($tp->id_tp, $smt->id_smt);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? '');
                foreach ($kls as $kl) {
                    array_push($arrKls, $kl['kelas_id']);
                }
            }
            $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);

            $this->load->view('_templates/dashboard/_header', $data);
			$this->load->view('cbt/status/data');
			$this->load->view('_templates/dashboard/_footer');
		}else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['jadwal'] = $this->dropdown->getAllJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();
            $data['pengawas'] = $this->cbt->getPengawasByGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);

            $jadwals = $this->cbt->getJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? '');
                foreach ($kls as $kl) {
                    array_push($arrKls, $kl['kelas_id']);
                }
            }
            $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);

			$this->load->view('members/guru/templates/header', $data);
            $this->load->view('members/guru/cbt/status/data');
			$this->load->view('members/guru/templates/footer');
		}
	}

    public function status_ruang() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Status Ujian Siswa',
            'subjudul' => 'Status Siswa',
            'setting' => $this->dashboard->getSetting(),
        ];

        $this->db->trans_start();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        $data['guru'] = $guru;

        $info = $this->cbt->getJadwalById($jadwal);
        $siswas = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $sesi, $info->bank_level);

        $durasies = $this->cbt->getDurasiSiswaByJadwal($jadwal);
        $logs = $this->cbt->getLogUjianByJadwal($jadwal);
        $pengawas = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal, $sesi, $ruang);
        $ids_pengawas = [];
        if ($pengawas && count($pengawas) > 0) {
            foreach ($pengawas as $pws) {
                $ids_pengawas = explode(',', $pws->id_guru ?? '');
            }
        }

        $arrDur = [];
        foreach ($siswas as $siswa) {
            $dur_siswa = null;
            foreach ($durasies as $durasi) {
                if ($durasi->id_siswa == $siswa->id_siswa) {
                    if ($durasi->lama_ujian == null) {
                        $mins = (strtotime($durasi->selesai) - strtotime($durasi->mulai)) / 60;
                        $durasi->lama_ujian = round($mins, 2) . ' m';
                    } else {
                        $lamanya = $durasi->lama_ujian;
                        if (strpos($lamanya, ':') !== false) {
                            $elap = explode(":", $lamanya ?? '');
                            $ed = $elap[2] == "00" ? 0 : 1;
                            $ej = $elap[0] == "00" ? '' : intval($elap[0]) . ' j ';
                            $em = $elap[1] == "00" ? '' : (intval($elap[1]) + $ed) . ' m';

                            $dd = $ej . $em;
                            $durasi->lama_ujian = $dd == '' ? '0 m' : $dd;
                        } else {
                            $durasi->lama_ujian .= 'm';
                        }
                    }
                    $dur_siswa = $durasi;
                }
            }

            $log_siswa = [];
            foreach ($logs as $log) {
                if ($log->id_siswa == $siswa->id_siswa) {
                    array_push($log_siswa, $log);
                }
            }
            $arrDur[$siswa->id_siswa] = [
                'dur' => $dur_siswa,//$this->cbt->getDurasiSiswa($siswa->id_siswa . $jadwal),
                'log' => $log_siswa//$this->cbt->getLogUjian($siswa->id_siswa, $jadwal)
            ];
        }
        $this->db->trans_complete();

        $data['siswa'] = $siswas;
        $data['durasi_siswa'] = $arrDur;
        $data['info'] = $info;
        $data['ids_pengawas'] = $ids_pengawas;
        $guru_ngawas = [];
        if ($ids_pengawas && count($ids_pengawas) > 0) {
            $guru_ngawas = $this->master->getGuruByArrId($ids_pengawas);
        }
        $data['pengawas'] = $guru_ngawas;

        $this->load->view('members/guru/templates/header', $data);
        $this->load->view('members/guru/cbt/status/status');
        $this->load->view('members/guru/templates/footer');

    }

	public function getJadwalUjianByJadwal() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $jadwal = $this->input->get('id_jadwal');
        $info = $this->cbt->getJadwalById($jadwal);

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $kelas = $this->maybe_unserialize($info->bank_kelas ?? '');
        $kelases = [];
        foreach ($kelas as $key=>$value) {
            $kelases[$value['kelas_id']] = $this->dropdown->getNamaKelasById($info->id_tp, $info->id_smt, $value['kelas_id']);
        }

        $this->output_json($kelases);
    }

    public function getJadwalUjianByKelas() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $kelas = $this->input->get('id_kelas');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if ($this->ion_auth->in_group('guru')) {
            $user = $this->ion_auth->user()->row();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $id_guru = $guru->id_guru;
        } else {
            $id_guru = null;
        }

        $jadwals = $this->cbt->getAllJadwal($tp->id_tp, $smt->id_smt, $id_guru);

        $jdwl = [];
        foreach ($jadwals as $jadwal) {
            $kls = $this->maybe_unserialize($jadwal->bank_kelas ?? '');
            foreach ($kls as $kl) {
                if ($kl['kelas_id'] == $kelas) {
                    $jdwl[$jadwal->id_jadwal] = $jadwal->bank_kode;
                }
            }
        }

        $this->output_json($jdwl);
    }

    public function getSiswaKelas() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

		$kelas = $this->input->get('kelas');
		//$sesi = $this->input->get('sesi');
		$jadwal = $this->input->get('jadwal');

        $this->db->trans_start();
		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$info = $this->cbt->getJadwalById($jadwal);
		$siswas = $this->cbt->getSiswaByKelas($tp->id_tp, $smt->id_smt, $kelas);

		$durasies = $this->cbt->getDurasiSiswaByJadwal($jadwal);
		$logs = $this->cbt->getLogUjianByJadwal($jadwal);
        $pengawas = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal);
        $ids_pengawas = [];
        foreach ($pengawas as $pws) {
            $ids_pengawas = explode(',', $pws->id_guru ?? '');
        }

		$arrDur = [];
		foreach ($siswas as $ks=>$siswa) {
            if ($info->soal_agama != '-' && $info->soal_agama != '0' && $info->soal_agama !== $siswa->agama) {
                unset($siswas[$ks]);
            }

            $dur_siswa = null;
            foreach ($durasies as $durasi) {
                if ($durasi->id_siswa == $siswa->id_siswa) {
                    $mulai = new DateTime($durasi->mulai);
                    $interval = $mulai->diff(new DateTime());
                    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                    $durasi->ada_waktu = $minutes < $info->durasi_ujian;
                    if ($durasi->lama_ujian == null) {
                        $mins = (strtotime($durasi->selesai) - strtotime($durasi->mulai)) / 60;
                        $durasi->lama_ujian = round($mins, 2) . ' m';
                    } else {
                        $lamanya = $durasi->lama_ujian;
                        if (strpos($lamanya, ':') !== false) {
                            $elap = explode(":", $lamanya ?? '');
                            $ed = $elap[2] === "00" ? 0 : 1;
                            $ej = $elap[0] === "00" ? '' : (int)$elap[0] . ' j ';
                            $em = $elap[1] === "00" ? '' : ((int)$elap[1] + $ed) . ' m';

                            $dd = $ej . $em;
                            $durasi->lama_ujian = $dd == '' ? '0 m' : $dd;
                        } else {
                            $durasi->lama_ujian .= 'm';
                        }
                    }

                    $dur_siswa = $durasi;
                }
            }

			$log_siswa = [];
			foreach ($logs as $log) {
			    if ($log->id_siswa == $siswa->id_siswa) {
			        $log_siswa[] = $log;
                }
            }
			$arrDur[$siswa->id_siswa] = [
				'dur' => $dur_siswa,//$this->cbt->getDurasiSiswa($siswa->id_siswa . $jadwal),
				'log' => $log_siswa//$this->cbt->getLogUjian($siswa->id_siswa, $jadwal)
			];
		}
        $this->db->trans_complete();

        $data['siswa'] = array_values($siswas);
		$data['durasi'] = $arrDur;
		$data['info'] = $info;
        $data['pengawas'] = $this->master->getGuruByArrId($ids_pengawas);
        //$data['pengawas'] = $pengawas;

		$this->output_json($data);
	}

	public function getSiswaRuang() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

		$ruang = $this->input->get('ruang');
		$sesi = $this->input->get('sesi');
		$jadwal = $this->input->get('jadwal');

        $this->db->trans_start();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $info = $this->cbt->getJadwalById($jadwal);
        $siswas = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $sesi, $info->bank_level);

        $durasies = $this->cbt->getDurasiSiswaByJadwal($jadwal);
        $logs = $this->cbt->getLogUjianByJadwal($jadwal);
        $pengawas = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal, $sesi, $ruang);
        $ids_pengawas = [];
        foreach ($pengawas as $pws) {
            $ids_pengawas = explode(',', $pws->id_guru ?? '');
        }

        $arrDur = [];
        foreach ($siswas as $ks=>$siswa) {
            if ($info->soal_agama != '-' && $info->soal_agama != '0' && $info->soal_agama !== $siswa->agama) {
                unset($siswas[$ks]);
            }
            $dur_siswa = null;
            foreach ($durasies as $durasi) {
                if ($durasi->id_siswa == $siswa->id_siswa) {
                    $mulai = new DateTime($durasi->mulai);
                    $interval = $mulai->diff(new DateTime());
                    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                    $durasi->ada_waktu = $minutes < $info->durasi_ujian;

                    if ($durasi->lama_ujian == null) {
                        $mins = (strtotime($durasi->selesai) - strtotime($durasi->mulai)) / 60;
                        $durasi->lama_ujian = round($mins, 2) . ' m';
                    } else {
                        $lamanya = $durasi->lama_ujian;
                        if (strpos($lamanya, ':') !== false) {
                            $elap = explode(":", $lamanya ?? '');
                            $ed = $elap[2] == "00" ? 0 : 1;
                            $ej = $elap[0] == "00" ? '' : intval($elap[0]).' j ';
                            $em = $elap[1] == "00" ? '' : (intval($elap[1]) + $ed).' m';

                            $dd = $ej.$em;
                            $durasi->lama_ujian = $dd == '' ? '0 m' : $dd;
                        } else {
                            $durasi->lama_ujian .= 'm';
                        }
                    }
                    $dur_siswa = $durasi;
                }
            }

            $log_siswa = [];
            foreach ($logs as $log) {
                if ($log->id_siswa == $siswa->id_siswa) {
                    array_push($log_siswa, $log);
                }
            }
            $arrDur[$siswa->id_siswa] = [
                'dur' => $dur_siswa,//$this->cbt->getDurasiSiswa($siswa->id_siswa . $jadwal),
                'log' => $log_siswa//$this->cbt->getLogUjian($siswa->id_siswa, $jadwal)
            ];
        }
        $this->db->trans_complete();

		$data['siswa'] = array_values($siswas);
		$data['durasi'] = $arrDur;
		$data['info'] = $info;
        $data['pengawas'] = $this->master->getGuruByArrId($ids_pengawas);
		//$data['pengawas'] = $pengawas;

		$this->output_json($data);
	}

	public function detail() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $siswa = $this->input->get('siswa');
        $jadwal = $this->input->get('jadwal');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Detail Status Siswa',
            'subjudul' => 'Status Siswa',
            'setting' => $this->dashboard->getSetting(),
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['siswa'] = $this->master->getSiswaById($siswa);
        $data['soal'] = $this->cbt->getSoalSiswaByJadwal($jadwal, $siswa);

        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('cbt/status/detail');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('cbt/status/detail');
            $this->load->view('members/guru/templates/footer');
        }
    }
}
