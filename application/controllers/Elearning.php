<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 14:24
 */

class Elearning extends MY_Controller {

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

    private function isUpdated()
    {
        $latest = 20240812000000;
        $uptodate = ['latest' => $latest];
        if ($this->db->table_exists('migrations') && $this->db->field_exists('version', 'migrations')) {
            $versi = $this->db->get('migrations')->first_row();
            $uptodate['current'] = (int)$versi->version;
            $uptodate['is_latest'] = (int)$versi->version >= $latest;
            $uptodate['need_convert'] = (int)$versi->version < $latest;
        } else {
            $uptodate['current'] = 0;
            $uptodate['is_latest'] = false;
            $uptodate['need_convert'] = true;
        }
        return json_decode(json_encode($uptodate));
    }

    private function convertNewJadwal($tps, $smts)
    {
        $this->db->select('*');
        $this->db->from('kelas_jadwal_kbm');
        $result = $this->db->get()->result();
        $jadwal_kbm = [];
        if ($result) {
            foreach ($result as $row) {
                $row->istirahat = $this->maybe_unserialize($row->istirahat);
                $jadwal_kbm[$row->id_tp][$row->id_smt][$row->id_kelas] = $row;
            }
        }

        $jadwal_harian = $this->elearning->getAllJadwalHarian();
        $all_kelas = $this->elearning->getDataKelas();
        return [
            'mapel' => $jadwal_harian,
            'kelas' => $all_kelas,
            'jadwal' => $jadwal_kbm,
            'tps' => $tps,
            'smts' => $smts
        ];
    }

    public function convertMateri()
    {
        $materis = $this->db->get('kelas_jadwal_materi')->result();
        $logs = $this->db->get('log_materi')->result();
        if ($logs) {
            foreach ($logs as $keyl=>$log) {
                $log->id_log = $keyl+1;
            }
        }
        $success = true;
        if ($materis) {
            foreach ($materis as $key=>$materi) {
                $id_kjm = $key+1;

                $idx_log = array_search($materi->id_kjm, array_column($logs, 'id_materi'));
                if (isset($logs[$idx_log])) {
                    $logs[$idx_log]->id_materi = $id_kjm;
                }
                $materi->id_kjm = $id_kjm;
            }
            $this->db->empty_table('kelas_jadwal_materi');
            $this->db->empty_table('log_materi');

            $this->db->insert_batch('log_materi', $logs);
            $success = $this->db->insert_batch('kelas_jadwal_materi', $materis);
        }
        $data['success'] = $success;
        $data['data'] = $materis;
        $this->output_json($data);
    }

    public function index()
    {
        redirect('elearning/jadwal');
    }

	public function jadwal() {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

		$user = $this->ion_auth->user()->row();
		$data = [
			'user' => $user,
            'judul' => 'Jadwal Pelajaran',
            'subjudul' => 'Set Jadwal Pelajaran',
            'setting'		=> $this->dashboard->getSetting()
		];
        $data['uptodate'] = $isUpdated;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
		$data['tp'] = $tps;
		$data['tp_active'] = $tp;
		$data['smt'] = $smts;
		$data['smt_active'] = $smt;

        if (!$isUpdated->is_latest) {
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/init.php');
            $this->load->view('_templates/dashboard/_footer');
        } else {
            $all_kelas = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['kelas']	= $all_kelas;
            $data['method'] = '';
            $data['id_hari'] = '-1';

            $dataConvert['convert'] = false;
            $jadk = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt);
            $data['setting_kbm'] = ['libur'=> '7'];
            if (!empty($jadk)) {
                $exist = array_key_first($jadk);
                $istirahat = $this->maybe_unserialize($jadk[$exist]->istirahat);
                $jadk[$exist]->istirahat = is_array($istirahat) ? (object) $istirahat : json_decode($istirahat);
                if ($jadk[$exist]->kbm_jam_selesai == null) {
                    $dataConvert = $this->convertNewJadwal($tps, $smts);
                    $dataConvert['convert'] = true;
                }
                $data['setting_kbm'] = $jadk[$exist];
            }
            $data['all_jadwal'] = $dataConvert;

            if($this->ion_auth->is_admin()){
                $data['profile'] = $this->dashboard->getProfileAdmin($user->id);

                $this->load->view('_templates/dashboard/_header', $data);
                $this->load->view('elearning/jadwalkelas.php');
                $this->load->view('_templates/dashboard/_footer');
            }else{
                $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $data['guru'] = $guru;

                $this->load->view('members/guru/templates/header', $data);
                $this->load->view('elearning/jadwalkelas.php');
                $this->load->view('members/guru/templates/footer');
            }
        }
	}

    public function hari($hari) {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        if (!$isUpdated->is_latest) {
            $this->output_json(['']);
        } else {
            $all_kelas = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
            $jadk = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt); // array
            $jadwal_mapel = $this->elearning->getJadwalHarian($tp->id_tp, $smt->id_smt, $hari);
            if (empty($jadwal_mapel)) {
                foreach ($all_kelas as $keyKls=>$kelas) {
                    for ($i = 0; $i < 6; $i++) {
                        for ($j = 1; $j<7;$j++) {
                            $jadwal_mapel[$keyKls][] = [
                                'dari' => '',
                                'sampai' => '',
                                'rows' => '1',
                                'id_kelas' => $keyKls,
                                'id_hari' => $i,
                                'id_mapel' => '',
                                'kode' => ''
                            ];
                        }
                    }
                }
            }

            $minTime = "07:00";
            $maxTime = "09:00";
            $libur = '7';
            $kelasKeys = array_keys($all_kelas);
            foreach ($jadk as $idKelas => $kelas) {
                if ($kelas->kbm_jam_selesai > $maxTime) {
                    $maxTime = $kelas->kbm_jam_selesai;
                }
                if ($kelas->kbm_jam_mulai < $minTime) {
                    $minTime = $kelas->kbm_jam_mulai;
                    $libur = $kelas->libur;
                }
                if (in_array($idKelas, $kelasKeys)) {
                    $details = [];
                    $mapel = $jadwal_mapel[$idKelas] ?? [];
                    $lastDari = '00:00';
                    $lastSampai = '01:00';
                    $latestMpl = '0';
                    $lastDay = '';
                    foreach ($mapel as $jadwal) {
                        $durasiMapel = round(abs(strtotime($jadwal->sampai ?? $lastSampai) - strtotime($jadwal->dari ?? $lastDari)) / 60, 2);
                        if ($latestMpl !== '0' && $jadwal->id_mapel === $latestMpl && $jadwal->id_hari === $lastDay) {
                            $details[$lastDari]['rows'] += (isset($jadwal->dari) ? $durasiMapel / 5 : 1);
                            $details[$lastDari]['sampai'] = $jadwal->sampai ?? $lastSampai;
                        } else {
                            $lastDay = $jadwal->id_hari ?? '0';
                            $latestMpl = $jadwal->id_mapel ?? '0';
                            $details[$jadwal->dari ?? $lastDari] = [
                                'id_jadwal' => $jadwal->id_jadwal ?? '0',
                                'dari' => $jadwal->dari ?? $lastDari,
                                'sampai' => $jadwal->sampai ?? $lastSampai,
                                'rows' => isset($jadwal->dari) ? $durasiMapel / 5 : 1,
                                'id_kelas' => $jadwal->id_kelas ?? '',
                                'id_hari' => $jadwal->id_hari ?? '',
                                'id_mapel' => $jadwal->id_mapel ?? '0',
                                'kode' => $jadwal->kode ?? ''
                            ];
                        }
                        $lastDari = $jadwal->dari ?? $lastDari;
                        $lastSampai = $jadwal->sampai ?? $lastSampai;
                    }
                    unset($kelas->istirahat);
                    $kelas->detail = $details;
                }
            }

            // Membuat dummy timeline
            $longDay = round(abs(strtotime($maxTime) - strtotime($minTime)) / 60, 2) + 5;
            $startTime = new DateTime($minTime);
            $currentTime = clone $startTime;
            $timeSlots = [];

            for ($m = 0; $m < ($longDay / 5); $m++) {
                try {
                    $currentTime->add(new DateInterval('PT5M'));
                    $formattedStart = $startTime->format('H:i');

                    if (strpos($formattedStart, '00') === 3 || strpos($formattedStart, '30') === 3) {
                        $lastIndex = max(0, $m - 6);
                        $currentDiff = isset($timeSlots[$lastIndex]['start'])
                            ? round(abs(strtotime($timeSlots[$lastIndex]['start']) - strtotime($formattedStart)) / 60, 2)
                            : 0;

                        if ($currentDiff <= 30 && isset($timeSlots[$lastIndex]['start'])) {
                            $timeSlots[$lastIndex]['span'] = ($currentDiff / 5) * 2;
                        }
                    }

                    $timeSlots[] = ['start' => $formattedStart];
                    $startTime->add(new DateInterval('PT5M'));
                } catch (Exception $e) {}
            }
            $data['times'] = $timeSlots;
            $data['longdays'] = $longDay;
            // Akhir timeline

            $data['jadwal_kbm'] = count($jadk) > 0 ? $jadk : [];
            $data['setting_kbm'] = ['mulai' => $minTime, 'selesai'=>$maxTime, 'libur' => $libur];

            $data['id_hari'] = $hari;
            $data['mapels'] = $this->elearning->getAllKodeMapel();
            $data['kelas']	= $all_kelas;

            $this->output_json($data);
        }
    }

    public function convertJadwal() {
        $this->load->model('Log_model', 'logging');
        $details = $this->input->post('kbm', true);

        $this->db->select('*');
        $this->db->from('kelas_jadwal_kbm');
        $result_kbm = $this->db->get()->result();

        $insertKbm = [];
        if ($details) {
            foreach ($details as $keys => $kbm) {
                $split = explode('_', $keys);
                $id_kbm = $split[0];
                $id_tp = $split[1];
                $id_smt = $split[2];
                $id_kelas = $split[3];

                $idx = array_search($id_kbm, array_column($result_kbm, 'id_kbm'));
                $result_kbm[$idx]->kbm_jam_selesai = $kbm['selesai'];

                $insertKbm[] = [
                    "id_tp" => $id_tp,
                    "id_smt" => $id_smt,
                    'id_kelas' => $id_kelas,
                    "kbm_jam_mulai" => $result_kbm[$idx]->kbm_jam_mulai,
                    "kbm_jam_selesai" => $kbm['selesai'],
                    "kbm_jam_pel" => $result_kbm[$idx]->kbm_jam_pel,
                    "kbm_jml_mapel_hari" => $result_kbm[$idx]->kbm_jml_mapel_hari,
                    "istirahat" => $result_kbm[$idx]->istirahat,
                    "libur" => $result_kbm[$idx]->libur,
                ];

                $this->db->where('id_kbm', $id_kbm);
                $this->db->delete('kelas_jadwal_kbm');
            }
            $update = $this->db->insert_batch('kelas_jadwal_kbm', $insertKbm);
        } else {
            $update = false;
        }

        $this->logging->saveLog(3, 'merubah jadwal pelajaran');

        $data['status'] = $update;
        $data['data'] = $insertKbm;
        $this->output_json($data);
    }

    public function convertMapel() {
        $this->load->model('Log_model', 'logging');

        $details = $this->input->post('jadwal', true);
        $insertMapel = [];
        $arrTp = [];
        $arrSmt = [];
        $arrKls = [];
        $arrHari = [];
        foreach ($details as $keys => $jadwal) {
            $split = explode('_', $keys);
            $id_jadwal = $split[0];
            $id_tp = $split[1];
            $id_smt = $split[2];
            $id_kelas = $split[3];
            $id_hari = $split[4];
            $jam_ke = $split[5];

            if (!in_array($id_tp, $arrTp)) $arrTp[] = $id_tp;
            if (!in_array($id_smt, $arrSmt)) $arrSmt[] = $id_smt;
            if (!in_array($id_kelas, $arrKls)) $arrKls[] = $id_kelas;
            if (!in_array($id_hari, $arrHari)) $arrHari[] = $id_hari;

            $insertMapel[] = [
                'id_tp'		=> $id_tp,
                'id_smt'	=> $id_smt,
                'id_kelas'	=> $id_kelas,
                'id_hari'	=> $id_hari,
                'jam_ke'	=> $jam_ke,
                'id_mapel'	=> $jadwal['id_mapel'],
                'dari'      => $jadwal['dari'],
                'sampai'    => $jadwal['sampai']
            ];
            $this->db->where('id_jadwal', $id_jadwal);
            $this->db->delete('kelas_jadwal_mapel');
        }

        $update = $this->db->insert_batch('kelas_jadwal_mapel', $insertMapel);
        $this->logging->saveLog(3, 'merubah jadwal pelajaran');

        $data['status'] = $update;
        $data['jadwal'] = $insertMapel;
        $data['tp'] = $arrTp;
        $data['smt'] = $arrSmt;
        $data['kelas'] = $arrKls;
        $data['hari'] = $arrHari;
        $this->output_json($data);
    }

    public function setJadwal() {
        $this->load->model('Elearning_model',  'elearning');
        $mulai = $this->input->post('jam_mulai', true);
        $selesai = $this->input->post('jam_selesai', true);
        $libur = $this->input->post('hari_libur', true);
        $tp = $this->input->post('id_tp', true);
        $smt = $this->input->post('id_smt', true);
        $jadk = $this->elearning->getJadwalKbm($tp, $smt); // array id_kelas
        $insertKbm = [];
        foreach ($jadk as $kelas) {
            $kelas->kbm_jam_mulai = $mulai;
            $kelas->kbm_jam_selesai = $selesai;
            $kelas->libur = $libur;
            $insertKbm[] = $kelas;
        }
        $update = $this->db->update_batch('kelas_jadwal_kbm', $insertKbm, 'id_kbm');
        $data['status'] = true;
        $data['data'] = $insertKbm;
        $this->output_json($data);
    }

    public function setMapel()
    {
        $id_tp = $this->input->post('tp', true);
        $id_smt = $this->input->post('smt', true);
        $id_kelas = $this->input->post('kelas', true);
        $id_hari = $this->input->post('hari', true);
        $id_jadwal = $this->input->post('jadwal', true);
        $id_mapel = $this->input->post('mapel', true);
        $dari = $this->input->post('dari', true);
        $sampai = $this->input->post('sampai', true);

        $kbm_id = $this->input->post('id_kbm', true);
        $kbm_mulai = $this->input->post('kbm_mulai', true);
        $kbm_selesai = $this->input->post('kbm_selesai', true);
        $kbm_libur = $this->input->post('kbm_libur', true);

        $insertMapel = [
            'id_tp'		=> $id_tp,
            'id_smt'	=> $id_smt,
            'id_kelas'	=> $id_kelas,
            'id_hari'	=> $id_hari,
            'id_mapel'	=> $id_mapel,
            'dari'      => $dari,
            'sampai'    => $sampai
        ];

        $insertKbm = [
            "id_tp" => $id_tp,
            "id_smt" => $id_smt,
            'id_kelas' => $id_kelas,
            "kbm_jam_mulai" => $kbm_mulai,
            "kbm_jam_selesai" => $kbm_selesai,
            "libur" => $kbm_libur
        ];

        if ($kbm_id) {
            $this->db->where('id_kbm', $kbm_id);
            $this->db->update('kelas_jadwal_kbm', $insertKbm);
        } else {
            $this->db->insert('kelas_jadwal_kbm', $insertKbm);
        }

        if ($id_jadwal) {
            $this->db->where('id_jadwal', $id_jadwal);
            $update = $this->db->update('kelas_jadwal_mapel', $insertMapel);
        } else {
            $update = $this->db->insert('kelas_jadwal_mapel', $insertMapel);
        }

        $data['status'] = $update;
        $data['mulai'] = $kbm_mulai;
        $data['selesai'] = $kbm_selesai;
        $data['libur'] = $kbm_libur;
        $data['data'] = $insertMapel;
        $this->output_json($data);
    }

    public function delMapel($id_jadwal)
    {
        $this->db->where('id_jadwal', $id_jadwal);
        $delete = $this->db->delete('kelas_jadwal_mapel');
        $data['status'] = $delete;
        $this->output_json($data);
    }

    public function materi()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Materi Belajar',
            'subjudul' => 'Materi',
            'setting'		=> $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['jurusan'] = $this->elearning->getAllJurusan();
        $data['level'] = $this->elearning->getAllLevel($setting->jenjang);

        $arr_kelas = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['kelas'] = $arr_kelas;
        $data['jenis'] = '1';

        $jadmpl = $this->elearning->getJadwalMapel($tp->id_tp, $smt->id_smt);
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';
        foreach ($jadmpl as $ind=>$jad) {
            if ($latestMpl !== '0' && $jad->id_mapel === $latestMpl && $jad->id_hari === $lastDay) {
                $jadmpl[$lastInd]->sampai = $jad->sampai;
                unset($jadmpl[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }
        $data['jadwal_mapel'] = array_values($jadmpl);

        if ($this->ion_auth->is_admin()) {
            $id_guru = $this->input->get('id');

            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $allGuru = $this->elearning->getAllGuru();
            $allGuru['00'] = 'Semua Guru';
            $data['gurus'] = $allGuru;
            $data['id_guru'] = $id_guru==null ? '' : $id_guru;

            $materi = [];
            $kelas_materi = [];
            $jadwal_materi = [];
            if ($id_guru!=null) {
                $materi = $this->elearning->getAllMateriKelas($id_guru, '1');
                foreach ($materi as $m) {
                    $arrKls = $this->maybe_unserialize($m->materi_kelas);
                    if (count($arrKls) > 0) {
                        $km = $this->elearning->getNamaKelasById($this->maybe_unserialize($m->materi_kelas));
                        if ($km == null) {
                            $km = $this->elearning->getNamaKelasByKode($this->maybe_unserialize($m->materi_kelas));
                        }
                        $kelas_materi[$m->id_materi] = $km;
                        $jadwal_materi[$m->id_materi] = $this->elearning->getJadwalByMateri($m->id_materi, '1', $tp->id_tp, $smt->id_smt);
                    }
                }
            }

            $data['materi'] = $materi;
            $data['kelas_materi'] = $kelas_materi;
            $data['jadwal_materi'] = $jadwal_materi;

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/materi');
            $this->load->view('_templates/dashboard/_footer');
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $materi = $this->elearning->getAllMateriKelas($guru->id_guru, '1');
            $kelas_materi = [];
            $jadwal_materi = [];
            foreach ($materi as $m) {
                $kelas_materi[$m->id_materi] = $this->elearning->getNamaKelasById($this->maybe_unserialize($m->materi_kelas));
                $jadwal_materi[$m->id_materi] = $this->elearning->getJadwalByMateri($m->id_materi, '1', $tp->id_tp, $smt->id_smt);
            }

            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['gurus'] = $nguru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;
            $data['materi'] = $materi;
            $data['kelas_materi'] = $kelas_materi;
            $data['jadwal_materi'] = $jadwal_materi;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/materi');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function tugas()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Tugas Belajar',
            'subjudul' => 'Tugas',
            'setting'		=> $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['jurusan'] = $this->elearning->getAllJurusan();
        $data['level'] = $this->elearning->getAllLevel($setting->jenjang);

        $arr_kelas = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['kelas'] = $arr_kelas;
        $data['jenis'] = '2';

        $jadmpl = $this->elearning->getJadwalMapel($tp->id_tp, $smt->id_smt);
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';
        foreach ($jadmpl as $ind=>$jad) {
            if ($latestMpl !== '0' && $jad->id_mapel === $latestMpl && $jad->id_hari === $lastDay) {
                $jadmpl[$lastInd]->sampai = $jad->sampai;
                unset($jadmpl[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }
        $data['jadwal_mapel'] = array_values($jadmpl);

        if ($this->ion_auth->is_admin()) {
            $id_guru = $this->input->get('id');

            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $allGuru = $this->elearning->getAllGuru();
            $allGuru['00'] = 'Semua Guru';
            $data['gurus'] = $allGuru;
            $data['id_guru'] = $id_guru==null ? '' : $id_guru;

            $materi = [];
            $kelas_materi = [];
            $jadwal_materi = [];
            if ($id_guru!=null) {
                $materi = $this->elearning->getAllMateriKelas($id_guru, '2');
                foreach ($materi as $m) {
                    $arrKls = $this->maybe_unserialize($m->materi_kelas);
                    if (count($arrKls) > 0) {
                        $km = $this->elearning->getNamaKelasById($this->maybe_unserialize($m->materi_kelas));
                        if ($km == null) {
                            $km = $this->elearning->getNamaKelasByKode($this->maybe_unserialize($m->materi_kelas));
                        }
                        $kelas_materi[$m->id_materi] = $km;
                        $jadwal_materi[$m->id_materi] = $this->elearning->getJadwalByMateri($m->id_materi, '2', $tp->id_tp, $smt->id_smt);
                    }
                }
            }

            $data['materi'] = $materi;
            $data['kelas_materi'] = $kelas_materi;
            $data['jadwal_materi'] = $jadwal_materi;

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/materi');
            $this->load->view('_templates/dashboard/_footer');
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $materi = $this->elearning->getAllMateriKelas($guru->id_guru, '2');
            $kelas_materi = [];
            $jadwal_materi = [];
            foreach ($materi as $m) {
                $kelas_materi[$m->id_materi] = $this->elearning->getNamaKelasById($this->maybe_unserialize($m->materi_kelas));
                $jadwal_materi[$m->id_materi] = $this->elearning->getJadwalByMateri($m->id_materi, '2', $tp->id_tp, $smt->id_smt);
            }

            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['gurus'] = $nguru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;
            $data['materi'] = $materi;
            $data['kelas_materi'] = $kelas_materi;
            $data['jadwal_materi'] = $jadwal_materi;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/materi');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function saveJadwalMateri() {
        $this->load->model('Log_model', 'logging');

        $id_materi = $this->input->post('id_materi',true);
        $id_mapel = $this->input->post('id_mapel',true);
        $id_kelas = $this->input->post('id_kelas',true);
        $jenis = $this->input->post('jenis',true);
        $jadwal = $this->input->post('jadwal_materi', true);

        $tp = $this->input->post('id_tp', true);
        $smt = $this->input->post('id_smt', true);

        $check = $this->db->get_where('kelas_jadwal_materi', [
            'id_tp' => $tp,
            'id_smt' => $smt,
            'id_kelas' => $id_kelas,
            'id_materi' => $id_materi,
            'id_mapel' => $id_mapel,
            'jadwal_materi' => $jadwal,
            'jenis' => $jenis])->row();

        if ($check) {
            $update = false;
            $msg = 'Jadwal tanggal '.$jadwal.' sudah ada';
        } else {
            $insert = [
                'id_tp' => $tp,
                'id_smt' => $smt,
                'id_kelas' => $id_kelas,
                'id_materi' => $id_materi,
                'id_mapel' => $id_mapel,
                'jadwal_materi' => $jadwal,
                'jenis' => $jenis
            ];

            $update = $this->db->insert('kelas_jadwal_materi', $insert);
            $this->logging->saveLog(3, 'merubah jadwal materi');
            $msg = 'success';
        }
        $this->output_json(['success'=>$update, 'message'=> $msg]);
    }

    public function delJadwalMateri($id) {
        $this->db->where('id_kjm', $id);
        $update = $this->db->delete('kelas_jadwal_materi');

        $this->output_json($update);
    }

    public function addMateri($jenis, $id_materi = null) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $title = $jenis=='1' ? 'Materi' : 'Tugas';
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => $title,
            'subjudul' => $id_materi == null ? 'Buat ' . $title .' Baru' : 'Edit '. $title,
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['id_materi'] = $id_materi;
        $data['jenis'] = $jenis;

        if ($this->ion_auth->is_admin()) {
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            if ($id_materi == null) {
                $data['materi'] = json_decode(json_encode($this->elearning->getDummyMateri()));
                $data['id_guru'] = '';
            } else {
                $materi = $this->elearning->getMateriKelasById($id_materi, $jenis);
                $data['materi'] = $materi;
                $data['id_guru'] = $materi->id_guru;
            }
            $data['gurus'] = $this->elearning->getAllGuru();

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/materi_add');
            $this->load->view('_templates/dashboard/_footer');
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($id_materi == null) {
                $data['materi'] = json_decode(json_encode($this->elearning->getDummyMateri()));
            } else {
                $data['materi'] = $this->elearning->getMateriKelasById($id_materi, $jenis);
            }
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['gurus'] = $nguru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/materi_add');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function saveMateri() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $jenis = $this->input->post('jenis', true);
        $id_materi = $this->input->post('id_materi', true);
        $kelas = count($this->input->post('kelas', true));
        $attach = json_decode($this->input->post('attach', true));

        $src_file = [];
        foreach ($attach as $at) {
            if ($at->name != null) {
                $src_file[] = [
                    'src' => $at->src,
                    'size' => $at->size,
                    'type' => $at->type,
                    'name' => $at->name
                ];
            }
        }

        $id_kelas = [];
        for ($i = 0; $i < $kelas; $i++) {
            $id_kelas[] = $this->input->post('kelas[' . $i . ']', true);
        }

        $isi_materi = $this->input->post('isi_materi', false);

        $dom = new DOMDocument();
        $dom->loadHTML($isi_materi, LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');
        $numimg = 1;
        foreach ($images as $image) {
            $base64_image_string = $image->getAttribute('src');
            if (strpos($base64_image_string, 'http') !== false) {
                $pathUpload = 'uploads';
                $forReplace = explode($pathUpload, $base64_image_string);
                $image->setAttribute('src', $pathUpload.$forReplace[1]);
            } else {
                $splited = explode(',', substr( $base64_image_string , 5 ) , 2);
                $mime=$splited[0];
                $data=$splited[1];

                $mime_split_without_base64=explode(';', $mime,2);
                $mime_split=explode('/', $mime_split_without_base64[0],2);
                $output_file = '';
                if(count($mime_split)==2) {
                    $extension=$mime_split[1];
                    if($extension=='jpeg')$extension='jpg';
                    $output_file = 'img_'.date('YmdHis').$numimg.'.'.$extension;
                }
                file_put_contents( './uploads/materi/' . $output_file, base64_decode($data) );
                $image->setAttribute('src', 'uploads/materi/' . $output_file);
                $numimg ++;
            }
        }

        $isi = $dom->saveHTML();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data = [
            'jenis' => $jenis,
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'kode_materi' => $this->input->post('kode_materi', true),
            'id_guru' => $this->input->post('guru', true),
            'id_mapel' => $this->input->post('mapel', true),
            'judul_materi' => $this->input->post('judul', true),
            'isi_materi' => $isi,
            'materi_kelas' => serialize($id_kelas),
            'file' => serialize($src_file)
        ];

        if ($id_materi === '') {
            $data['created_on'] = date('Y-m-d H:i:s');
            $data['updated_on'] = date('Y-m-d H:i:s');
            $saved = $this->master->create('kelas_materi', $data);

            $result['result_id'] = $this->db->insert_id();
            $result['status'] = $saved;
            $result['message'] = 'Materi berhasil dibuat';
            $this->logging->saveLog(3, 'membuat materi');
        } else {
            $cek_materi = $this->elearning->getMateriKelasById($id_materi, $jenis);
            if ($cek_materi->id_tp == $tp->id_tp && $cek_materi->id_smt == $smt->id_smt) {
                $data['updated_on'] = date('Y-m-d H:i:s');
                $data['id_materi'] = $id_materi;
                $saved = $this->master->update('kelas_materi', $data, 'id_materi', $id_materi);

                $result['status'] = $saved;
                $result['message'] = 'Materi berhasil diupdate';
                $this->logging->saveLog(4, 'mengedit materi');
            } else {
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['updated_on'] = date('Y-m-d H:i:s');
                $saved =  $this->master->create('kelas_materi', $data);

                $result['status'] = $saved;
                $result['message'] = 'Materi berhasil dibuat';
                $this->logging->saveLog(3, 'membuat materi');
            }
        }

        $this->output_json($result);
    }

    public function delMateri() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $id = $this->input->post('id_materi', true);
        if ($this->master->delete('kelas_materi', $id, 'id_materi')) {
            if ($this->master->delete('kelas_jadwal_materi', $id, 'id_materi')) {
                $this->logging->saveLog(5, 'menghapus materi');
                $this->output_json(['status' => true]);
            }
        }
    }

    public function aktifkanMateri() {
        $this->load->model('Log_model', 'logging');
        $method = $this->input->post('method', true);
        $id = $this->input->post('id_materi', true);
        $stat = $method == '1' ? '0' : '1';

        $this->db->set('status', $stat);
        $this->db->where('id_materi', $id);
        $this->db->update('kelas_materi');

        $this->logging->saveLog(3, 'mengaktifkan materi');
        $this->output_json(['status' => true]);
    }

    public function dataAddKelas($guru) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->elearning->getGuruMapelKelas($guru, $tp->id_tp, $smt->id_smt);
        $kelas = $this->maybe_unserialize($guru->mapel_kelas);
        $this->output_json($kelas);
    }

    public function dataAddJadwal() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $id_kelas = $this->input->get('kelas');
        $id_mapel = $this->input->get('mapel');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $mapel = $this->elearning->getJadwalMapelByMapel($tp->id_tp, $smt->id_smt, $id_mapel, $id_kelas );
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';
        foreach ($mapel as $ind=>$jad) {
            if ($latestMpl !== '0' && $jad->id_mapel === $latestMpl && $jad->id_hari === $lastDay) {
                $mapel[$lastInd]->sampai = $jad->sampai;
                unset($mapel[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }

        $jadwal_terisi = $this->elearning->getJadwalTerisi('kelas_jadwal_materi', $id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
        $this->output_json(['mapel'=>array_values($mapel), 'terisi'=>$jadwal_terisi]);
    }

    public function status()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'     => 'Nilai Harian Siswa',
            'subjudul'  => 'Nilai',
            'setting'	=> $this->dashboard->getSetting()
        ];
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $guru = $this->elearning->getAllGuru();
            $data['gurus'] = $guru;
            $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['mapels'] = $this->elearning->getAllMapel();

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/siswa_status.php');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['guru'] = $guru;
            $data['gurus'] = $nguru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = json_decode(json_encode($this->maybe_unserialize($mapel_guru->mapel_kelas ?? '')));

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$kls->kelas] = $this->elearning->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas);
                    }
                }
            }

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    $arrId[] = $id_mapel->kelas;
                }
            }

            $data['mapel'] = $mapel;
            $data['mapels'] = $arrMapel;
            $data['kelas'] = $arrKelas;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/siswa_status.php');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function getMateriMapel() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $id_mapel = $this->input->get('id', true);
        $id_guru = $this->input->get('id_guru', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $materi = $this->elearning->getKodeMateriMapel($tp->id_tp, $smt->id_smt, $id_mapel, $id_guru);
        $arrKelasMateri = [];
        $arrKelasTugas = [];
        $arrKelas = [];
        foreach ($materi as $m) {
            $kode_mapel = $m->kode_mapel == null ? '--' : $m->kode_mapel;
            if ($m->jenis == '1') {
                $arrMateri = [
                    'id_materi'=> $m->id_materi,
                    'id_kjm'=> $m->id_kjm,
                    'jadwal'=> $m->jadwal_materi,
                    'kode'=>$m->kode_materi,
                    'mapel'=>$kode_mapel,
                    'guru'=>$m->nama_guru,
                    'jenis'=>$m->jenis
                ];

                if (!isset($arrKelasMateri[$m->id_kelas])) {
                    $arrKelasMateri[$m->id_kelas] = [];
                }
                $arrKelasMateri[$m->id_kelas][] = $arrMateri;
            } else {
                $arrTugas = [
                    'id_materi'=> $m->id_materi,
                    'id_kjm'=> $m->id_kjm,
                    'jadwal'=> $m->jadwal_materi,
                    'kode'=>$m->kode_materi,
                    'mapel'=>$kode_mapel,
                    'guru'=>$m->nama_guru,
                    'jenis'=>$m->jenis
                ];

                if (!isset($arrKelasTugas[$m->id_kelas])) {
                    $arrKelasTugas[$m->id_kelas] = [];
                }
                $arrKelasTugas[$m->id_kelas][] = $arrTugas;
            }

            if (isset($arrKelas[$m->jenis])) {
                if (!in_array($m->id_kelas, $arrKelas[$m->jenis], true)) {
                    $arrKelas[$m->jenis][] = $m->id_kelas;
                }
            } else {
                $arrKelas[$m->jenis] = [];
                $arrKelas[$m->jenis][] = $m->id_kelas;
            }
        }

        $this->output_json(array(
            'materi'=>$arrKelasMateri,
            'tugas'=>$arrKelasTugas,
            'kelas' => $arrKelas,
        ));
    }

    public function loadStatus() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $label = $this->input->post('label', true);
        $id_kelas = $this->input->post('id_kelas', true);
        $id_kjm = $this->input->post('id_kjm', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;
        $jenis = $label === "Materi" ? '1' : '2';

        $siswa = $this->elearning->getKelasSiswa($id_kelas, $id_tp, $id_smt);
        $logs = $this->elearning->getStatusMateriSiswa($id_kjm);

        $info = $this->elearning->getJadwalKbm($id_tp, $id_smt, $id_kelas);
        if ($info != null) {
            $info->istirahat = $this->maybe_unserialize($info->istirahat ?? '');
        }
        $materi = $this->elearning->getMateriKelasSiswa($id_kjm, $jenis);
        $detail = [];
        $jam_materi = [];
        if ($materi) {
            $kelas_materi = $this->elearning->getNamaKelasById([$id_kelas]);

            $numday = date('N', strtotime($materi->jadwal_materi));
            $jadwals = $this->elearning->loadJadwalSiswaHariIni($id_tp, $id_smt, $id_kelas, $numday);
            $key = array_search($materi->id_mapel, array_column($jadwals, 'id_mapel'));
            $jadwal = $jadwals[$key];

            $jam_materi = [
                "dari"=> $jadwal->dari,
                "sampai"=> $jadwal->sampai,
                "tgl"=>$materi->jadwal_materi
            ];

            $detail = [
                "mapel" => $materi->nama_mapel,
                "judul" => $materi->judul_materi,
                "guru" => $materi->nama_guru,
                "kelas" => $kelas_materi[$id_kelas],
                "waktu" => $jam_materi,
            ];
        }

        $log = [];
        foreach ($siswa as $s) {
            $mulai = isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->log_time : null;
            $selesai = isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->finish_time : null;

            $diff = null;
            if ($selesai) {
                $jam_jadwal = new DateTime(date('Y-m-d H:i:s', strtotime($materi->jadwal_materi . ' ' .$jam_materi['sampai'])));
                $jam_siswa = new DateTime(date('Y-m-d H:i:s', strtotime($mulai)));
                $interval = $jam_siswa->diff($jam_jadwal);
                $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                $time_jadwal = strtotime($materi->jadwal_materi . ' ' .$jam_materi['sampai']);
                $time_siswa = strtotime($mulai);
                $diff = [
                    'days'  => $interval->days,
                    'hari'  => $interval->d,
                    'jam'   => $interval->h,
                    'menit' => $interval->i,
                    'detik' => $interval->s,
                    'total' => $minutes,
                    'interval' => (int)$interval->format( '%r%H:%i:%s' ),
                    'terlambat' => ($time_siswa - $time_jadwal) > 0
                ];
            }

            $log[$s->id_siswa] = [
                'nama' =>$s->nama,
                'nis' =>$s->nis,
                'kelas' =>$s->nama_kelas,
                'login' => $this->elearning->getLoginSiswa($s->username),
                'mulai' => $mulai,
                'selesai' => $selesai,
                'text' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->text : '',
                'nilai' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->nilai : '',
                'catatan' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->catatan : '',
                'jadwal_materi' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->jadwal_materi : null,
                'file' => isset($logs[$s->id_siswa]) && $logs[$s->id_siswa]->file != null ? $this->maybe_unserialize($logs[$s->id_siswa]->file ?? '') : [],
                'diff' => $diff,
                'j_materi' => $jam_materi['sampai']
            ];
        }
        $this->output_json([
            "log"=>$log,
            "jadwal"=>$info,
            "materi"=>$materi,
            "detail"=>$detail,
            //"kelas"=>$kelas_materi,
            //"mapel"=>$mapel
        ]);
    }

    public function saveNilai() {
        $id_siswa = $this->input->post('id_siswa', true);
        $id_materi = $this->input->post('id_materi', true);
        $nilai = $this->input->post('nilai', true);
        $catatan = $this->input->post('catatan', true);

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_materi', $id_materi);
        $q = $this->db->get('log_materi');

        if ($q->num_rows() > 0) {
            $insert = [
                'nilai'		=> $nilai,
                'catatan'	=> $catatan,
            ];

            $this->db->where('id_siswa', $id_siswa);
            $this->db->where('id_materi', $id_materi);
            $update = $this->db->update('log_materi', $insert);
        } else {
            $insert = [
                'id_siswa'  => $id_siswa,
                'id_materi'  => $id_materi,
                'nilai'		=> $nilai,
                'catatan'	=> $catatan,
            ];

            $update = $this->db->insert('log_materi', $insert);
        }
        $this->output_json($update);
    }

    public function harian()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user'      => $user,
            'judul'     => 'Kehadiran Harian Siswa',
            'subjudul'  => 'Data Kehadiran Siswa',
            'setting'	=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;

        $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['mapel'] = $this->elearning->getAllMapel();

        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $data['guru'] = $this->elearning->getAllGuru();

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/siswa_harian.php');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/siswa_harian.php');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function loadAbsensi() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $id_kelas = $this->input->post('kelas', true);
        $tahun = $this->input->post('thn', true);
        $bulan = $this->input->post('bln', true);
        $tanggal = $this->input->post('tgl', true);
        $hari = $this->input->post('hari', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;

        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $tanggal = str_pad($tanggal, 2, '0', STR_PAD_LEFT);

        $info = $this->elearning->getJadwalKbm($id_tp, $id_smt, $id_kelas);
        unset($info->istirahat);

        $jadwal = $this->elearning->loadJadwalHariIni($id_tp, $id_smt, $id_kelas, $hari);
        $arrIdMapel = [];
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';

        foreach ($jadwal as $ind=>$jad) {
            $arrIdMapel[] = $jad->id_mapel;
            if ($latestMpl !== '0' && $jad->id_mapel === $latestMpl && $jad->id_hari === $lastDay) {
                $jadwal[$lastInd]->sampai = $jad->sampai;
                unset($jadwal[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }
        $jadwal = array_values($jadwal);

        $jadwal_materi = [];
        if (count($arrIdMapel)>0) {
            $jadwal_materi = $this->elearning->getAllMateriByTgl($id_kelas, $tahun.'-'.$bulan.'-'.$tanggal, $arrIdMapel);
        }

        $arrIdKjm = [];
        foreach ($jadwal_materi as $jmtr) {
            $arrIdKjm[] = $jmtr->id_kjm;
        }

        $siswa = $this->elearning->getKelasSiswa($id_kelas, $id_tp, $id_smt);
        $logs = $this->elearning->getRekapMateriKelas($id_kelas, $arrIdKjm);

        $log = [];
        if ($info != null) {
            foreach ($siswa as $s) {
                $log[$s->id_siswa] = [
                    'nama' => $s->nama,
                    'nis' => $s->nis,
                    'kelas' => $s->nama_kelas,
                    'status' => $logs[$s->id_siswa] ?? [],
                    //'test' => $logSiswa
                ];
            }
        }

        $this->output_json(array(
                //'test'=> [$id_kelas, $tahun.'-'.$bulan.'-'.$tanggal, $arrIdMapel],
                'log'=>$log,
                'info'=>$info,
                'jadwal'=>$jadwal,
                'materi'=>$jadwal_materi,
            )
        );
    }

    public function bulanan()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Daftar Hadir Bulanan',
            'subjudul' => 'Daftar Hadir Bulanan Siswa',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['bulan'] = $this->elearning->getBulan();

        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['guru'] = $this->elearning->getAllGuru();
            $data['mapel'] = $this->elearning->getAllMapel();

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/siswa_bulanan.php');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/siswa_bulanan.php');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function loadAbsensiMapel() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Elearning_model',  'elearning');

        $id_kelas = $this->input->post('kelas', true);
        $id_mapel = $this->input->post('mapel', true);
        $tahun = $this->input->post('thn', true);
        $bulan = $this->input->post('bln', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;

        $jadwal = $this->elearning->getJadwalKbm($id_tp, $id_smt, $id_kelas);
        $arrTgl = [];
        if ($jadwal != null) {
            unset($jadwal->istirahat);
            $tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $b = $bulan < 10 ? '0'.($bulan) : $bulan;
                $arrTgl[] = $tahun.'-'.$b.'-'.$t;
            }
            $jadwal_materi = $this->elearning->getAllMateriByArrTgl($id_kelas, $arrTgl, $id_mapel);

            $materi_perbulan = $this->elearning->getRekapBulananSiswa($id_mapel, $id_kelas, $tahun, $bulan);
            $log = [];

            $siswa = $this->elearning->getKelasSiswa($id_kelas, $id_tp, $id_smt);
            foreach ($siswa as $s) {
                $arrMateri = [];
                for ($i=0;$i<$tgl;$i++) {
                    $t = ($i+1) < 10 ? '0'.($i+1) : $i+1;
                    $b = $bulan < 10 ? '0'.($bulan) : $bulan;
                    $arrMateri[1][] = $materi_perbulan !== null && isset($materi_perbulan[$s->id_siswa][1]) && isset($materi_perbulan[$s->id_siswa][1][$tahun . '-' . $b . '-' . $t]) ?
                        $materi_perbulan[$s->id_siswa][1][$tahun.'-'.$b.'-'.$t] : null;

                    $arrMateri[2][] = $materi_perbulan !== null && isset($materi_perbulan[$s->id_siswa][2]) && isset($materi_perbulan[$s->id_siswa][2][$tahun . '-' . $b . '-' . $t]) ?
                        $materi_perbulan[$s->id_siswa][2][$tahun.'-'.$b.'-'.$t] : null;

                }

                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'materi' => $arrMateri[1],
                    'tugas' => $arrMateri[2]
                ];
            }

            $mapel_bulan_ini = [];
            $infos = $this->elearning->getJadwalMapelByMapel($id_tp, $id_smt, $id_mapel, $id_kelas);
            $latestMpl = '0';
            $lastInd = -1;
            $lastDay = '';

            foreach ($infos as $ind=>$info) {
                if ($latestMpl !== '0' && $info->id_mapel === $latestMpl && $info->id_hari === $lastDay) {
                    if (isset($infos[$lastInd])) {
                        $infos[$lastInd]->sampai = $info->sampai;
                        unset($infos[$ind]);
                    }
                } else {
                    $lastInd = $ind;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';

                    $dates = $this->getDateFromWeekday($info->id_hari, $bulan, $tahun);
                    foreach ($dates as $date) {
                        $d = explode('-', $date ?? '');
                        $mapel_bulan_ini[$d[2]] = $date;
                    }
                }
            }

            $this->output_json([
                "log"=>$log,
                "jadwal"=>$jadwal,
                "materi"=>$jadwal_materi,
                "mapels"=>$mapel_bulan_ini,
                "info" => array_values($infos)
            ]);
        } else {
            $this->output_json(["jadwal"=>$jadwal]);
        }
    }

    public function nilai()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Rekapitulasi Nilai Siswa',
            'subjudul' => 'Nilai dalam satu semester',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $data['mapel'] = $this->elearning->getAllMapel();
            $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/siswa_nilai.php');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = json_decode(json_encode($this->maybe_unserialize($mapel_guru->mapel_kelas ?? '')));

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$m->id_mapel][] = [
                            'id_kelas' => $kls->kelas,
                            'nama_kelas' => $this->elearning->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas)];
                    }
                }
            }

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    $arrId[] = $id_mapel->kelas;
                }
            }

            $data['mapel'] = $arrMapel;
            $data['arrkelas'] = $arrKelas;
            $data['kelas'] = count($arrId) > 0 ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/siswa_nilai.php');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function loadNilaiMapel() {
        $this->load->model('Elearning_model',  'elearning');

        $kelas = $this->input->get('kelas');
        $mapel = $this->input->get('mapel');
        $tahun = $this->input->get('tahun');
        $smt = $this->input->get('smt');
        $stahun = $this->input->get('stahun');

        $siswa = $this->elearning->getKelasSiswa($kelas, $tahun, $smt);

        if ($smt=='1') {
            $arrBulan = ['07','08','09','10','11','12'];
        } else {
            $arrBulan = ['01','02','03','04','05','06'];
        }

        //$namaBulan = ["", "Januari", "Februari","Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember"];
        //$namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

        $infos = $this->elearning->getJadwalMapelByMapel($tahun, $smt, $mapel, $kelas);
        $log_siswa = $this->elearning->getRekapMateriSemester($kelas);

        $jadwal_per_bulan = [];
        //$cols = 0;

        $arrTgl = [];
        $dates = [];
        foreach ($arrBulan as $bulan) {
            $latestMpl = '0';
            $lastDay = '';
            foreach ($infos as $info) {
                $dates = array_merge($dates, $this->getDateFromWeekday($info->id_hari, $bulan, $stahun));
                if ($latestMpl !== '0' && $info->id_mapel === $latestMpl && $info->id_hari === $lastDay) {
                    $jadwal_per_bulan[$bulan][$info->id_hari]->sampai = $info->sampai;
                } else {
                    $jadwal_per_bulan[$bulan][$info->id_hari] = $info;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';
                }
            }

            $tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $stahun);
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $arrTgl[] = $stahun.'-'.$bulan.'-'.$t;
            }
        }
        $all_jadwal =  $this->elearning->getAllMateriByArrTgl($kelas, $arrTgl, $mapel);
        $jadwal_materi = [];
        foreach ($dates as $date) {
            $d = explode('-', $date ?? '');
            $b = $d[1];
            $t = $d[2];
            $jadwal_materi[$b][$t] = $all_jadwal[$date] ?? null;
        }

        $log = [];
        if (count($siswa)>0 && count($jadwal_per_bulan)>0) {
            foreach ($siswa as $s) {
                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'nilai' => $log_siswa[$s->id_siswa] ?? [],
                ];
            }

            $data = [
                'log' => $log,
                'materi'=>$jadwal_materi,
                'bulans'=>$arrBulan,
                'mapels'=>$jadwal_per_bulan,
                'nilai'=> $log_siswa,
                'tahun'=>$stahun
                //'cols'=>$cols,
                //'all'=>$all_jadwal,
                //'dates'=>$dates
            ];
        } else {
            $data['mapels'] = [];
        }

        $this->output_json($data);
    }

    public function nilaiSemester()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Rekapitulasi Nilai Siswa',
            'subjudul' => 'Nilai dalam satu semester',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;

        if ($smt->id_smt=='1') {
            $arrBulan = [
                '00'    => "Satu Semeter",
                '07'    => "Juli",
                '08'    => "Agustus",
                '09'    => "September",
                '10'    => "Oktober",
                '11'    => "Nopember",
                '12'    => "Desember"
            ];
        } else {
            $arrBulan = [
                '00'    => "Satu Semeter",
                '01'    => "Januari",
                '02'    => "Februari",
                '03'    => "Maret",
                '04'    => "April",
                '05'    => "Mei",
                '06'    => "Juni"
            ];
        }
        $data['bulans'] = $arrBulan;

        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $data['mapel'] = $this->elearning->getAllMapel();
            $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('elearning/siswa_semester.php');
            $this->load->view('_templates/dashboard/_footer');
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = json_decode(json_encode($this->maybe_unserialize($mapel_guru->mapel_kelas ?? '')));

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$m->id_mapel][] = [
                            'id_kelas' => $kls->kelas,
                            'nama_kelas' => $this->elearning->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas)];
                    }
                }
            }

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    $arrId[] = $id_mapel->kelas;
                }
            }

            $data['mapel'] = $arrMapel;
            $data['arrkelas'] = $arrKelas;
            $data['kelas'] = count($arrId) > 0 ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('elearning/siswa_semester.php');
            $this->load->view('members/guru/templates/footer');
        }
    }

    public function loadNilaiSemester() {
        $this->load->model('Elearning_model',  'elearning');

        $kelas = $this->input->get('kelas');
        $mapel = $this->input->get('mapel');
        $tahun = $this->input->get('tahun');
        $smt = $this->input->get('smt');
        $stahun = $this->input->get('stahun');

        $siswa = $this->elearning->getKelasSiswa($kelas, $tahun, $smt);

        if ($smt=='1') {
            $arrBulan = ['07','08','09','10','11','12'];
        } else {
            $arrBulan = ['01','02','03','04','05','06'];
        }

        //$namaBulan = ["", "Januari", "Februari","Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember"];
        //$namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

        $infos = $this->elearning->getJadwalMapelByMapel($tahun, $smt, $mapel, $kelas);
        $log_siswa = $this->elearning->getRekapMateriSemester($kelas);

        $jadwal_per_bulan = [];
        //$cols = 0;

        $arrTgl = [];
        $dates = [];
        foreach ($arrBulan as $bulan) {
            $latestMpl = '0';
            $lastDay = '';
            foreach ($infos as $info) {
                $dates = array_merge($dates, $this->getDateFromWeekday($info->id_hari, $bulan, $stahun));
                if ($latestMpl !== '0' && $info->id_mapel === $latestMpl && $info->id_hari === $lastDay) {
                    $jadwal_per_bulan[$bulan][$info->id_hari]->sampai = $info->sampai;
                } else {
                    $jadwal_per_bulan[$bulan][$info->id_hari] = $info;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';
                }
            }

            $tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $stahun);
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $arrTgl[] = $stahun.'-'.$bulan.'-'.$t;
            }
        }
        $all_jadwal =  $this->elearning->getAllMateriByArrTgl($kelas, $arrTgl, $mapel);
        $jadwal_materi = [];
        foreach ($dates as $date) {
            $d = explode('-', $date ?? '');
            $b = $d[1];
            $t = $d[2];
            $jadwal_materi[$b][$t] = $all_jadwal[$date] ?? null;
        }

        $log = [];
        if (count($siswa)>0 && count($jadwal_per_bulan)>0) {
            foreach ($siswa as $s) {
                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'nilai' => $log_siswa[$s->id_siswa] ?? null,
                ];
            }

            $data = [
                'log' => $log,
                'materi'=>$jadwal_materi,
                'bulans'=>$arrBulan,
                'mapels'=>$jadwal_per_bulan,
                'nilai'=> $log_siswa,
                'tahun'=>$stahun,
                //'tp'=> $tahun,
                //'smt'=> $smt
                //'cols'=>$cols,
                //'all'=>$all_jadwal,
                //'dates'=>$dates
            ];
        } else {
            $data['mapels'] = [];
        }

        $this->output_json($data);
    }

    function getDateFromWeekday($dayOfWeek, $month, $year) {
        $dates = [];
        $date = new DateTime("$year-$month-01");

        $firstDayOfWeek = $date->format('N');

        $dayOffset = $dayOfWeek - $firstDayOfWeek;

        if ($dayOffset < 0) {
            $dayOffset += 7;
        }

        $date->modify("+$dayOffset day");

        while ($date->format('m') == $month) {
            $dates[] = $date->format('Y-m-d');
            $date->modify('+7 days');
        }

        return $dates;
    }

    function uploadFile(){
        $this->load->library('upload');
        $max_size = $this->input->post('max-size', true);
        if(isset($_FILES["file_uploads"]["name"])){
            $config['upload_path'] = './uploads/materi/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4|avi|doc|docx|xls|xlsx|ppt|pptx|csv|pdf|rtf|txt';
            //$config['encrypt_name'] = TRUE;
            $config['max_size'] = $max_size;
            $config['overwrite'] = TRUE;

            $this->upload->initialize($config);
            if(!$this->upload->do_upload('file_uploads')){
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            }else{
                $result = $this->upload->data();
                $data['src'] = 'uploads/materi/'.$result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;
            }

            $data['type'] = $_FILES['file_uploads']['type'];
            $data['size'] = $_FILES['file_uploads']['size'];
        }
        $this->output_json($data);
    }

    function deleteFile() {
        $src = $this->input->post('src');
        if (unlink($src)) {
            echo 'File Delete Successfully';
        } else {
            echo 'Gagal';
        }
    }

}
