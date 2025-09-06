<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kbm extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->library('user_agent');
        $this->form_validation->set_error_delimiters('', '');
    }

	public function output_json($data, $encode = true)
	{
		if($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

    public function index()
    {

    }

     public function jadwal_pelajaran()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Jadwal Pelajaran',
            'subjudul' => 'Set Jadwal Pelajaran',
            'setting' => $setting
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $kelas = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $mapel = $this->elearning->getJadwalPerkelas($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        if ($kelas == null) {
            $data['jadwal_kbm'] = json_decode(json_encode([
                'id_tp' => $tp->tahun,
                'id_smt' => $smt->smt,
                'id_kelas' => $siswa->id_kelas,
                'kbm_jam_pel' => '',
                'kbm_jam_mulai' => '',
                'kbm_jml_mapel_hari' => '',
                'istirahat' => serialize([]),
                'ada' => false
            ]));
        } else {
            $minTime = "07:00";
            $maxTime = "09:00";
            $libur = '7';

            if ($kelas->kbm_jam_selesai > $maxTime) {
                $maxTime = $kelas->kbm_jam_selesai;
            }
            if ($kelas->kbm_jam_mulai < $minTime) {
                $minTime = $kelas->kbm_jam_mulai;
                $libur = $kelas->libur;
            }
            $details = [];
            $lastDari = '0:00';
            $lastSampai = '01:00';
            $latestMpl = '0';
            $lastDay = '';
            foreach ($mapel as $hari=>$jadwals) {
                $details[$hari] = [];
                foreach ($jadwals as $jadwal) {
                    $durasiMapel = round(abs(strtotime($jadwal->sampai ?? $lastSampai) - strtotime($jadwal->dari ?? $lastDari)) / 60, 2);
                    if ($latestMpl !== '0' && $jadwal->id_mapel === $latestMpl && $jadwal->id_hari === $lastDay) {
                        $details[$hari][$lastDari]['rows'] += (isset($jadwal->dari) ? $durasiMapel / 5 : 1);
                        $details[$hari][$lastDari]['sampai'] = $jadwal->sampai ?? $lastSampai;
                    } else {
                        $lastDay = $jadwal->id_hari ?? '0';
                        $latestMpl = $jadwal->id_mapel ?? '0';
                        $details[$hari][$jadwal->dari ?? $lastDari] = [
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
            }
            unset($kelas->istirahat);
            $kelas->detail = $details;

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
            // Akhir timeline
            $data['jadwal_kbm'] = $kelas;
        }
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/elearning/jadwal');
        $this->load->view('members/siswa/templates/footer');
    }

    public function kehadiran()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Absensi',
            'subjudul' => 'Kehadiran Siswa',
            'setting' => $this->dashboard->getSetting()
        ];
        $today = date('Y-m-d');
        $day = date('N', strtotime($today));
        //$day = date('N', strtotime('friday'));
        $kbm = $this->dashboard->getJadwalKbm($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $result = $this->dashboard->loadJadwalHariIni($tp->id_tp, $smt->id_smt, $siswa->id_kelas, null);
        $jadwals = [];
        foreach ($result as $row) {
            $jadwals[$row->id_hari][$row->jam_ke] = $row;
        }
        $mapels = $this->master->getAllMapel();
        $arrIdMapel = [];
        foreach ($mapels as $mpl) {
            $arrIdMapel[] = $mpl->id_mapel;
        }

        if ($kbm != null) {
            $bulan = date('m');
            $tahun = date('Y');
            //$b = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
            $tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

            $materi_sebulan = [];
            for ($i = 0; $i < $tgl; $i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $materi_sebulan[$t] = $this->kelas->getAllMateriByTgl($siswa->id_kelas, $tahun.'-'.$bulan.'-'.$t, $arrIdMapel);
            }

            $kbm->istirahat = $this->maybe_unserialize($kbm->istirahat ?? '');
            $logs = $this->kelas->getRekapBulananSiswa(null, $siswa->id_kelas, $tahun, $bulan);
            /*
            $mapel_bulan_ini = [];
            $infos = $this->kelas->getJadwalMapelByMapel($siswa->id_kelas, null, $tp->id_tp, $smt->id_smt);
            foreach ($infos as $info) {
                $dates = $this->total_hari($info->id_hari, $bulan, $tahun);
                foreach ($dates as $date) {
                    $d = explode('-', $date);
                    $mapel_bulan_ini[$info->id_mapel][$d[2]][$info->jam_ke] = $date;
                    $res = $this->kelas->getAllMateriByTgl($siswa->id_kelas, $date, $arrIdMapel);
                    $materi_sebulan[$date] = $res;
                }
            }
            */

            $data['sebulan'] = [
                "log"=> isset($logs[$siswa->id_siswa]) ? $logs[$siswa->id_siswa] : [],
                "materis"=>$materi_sebulan,
            ];
        } else {
            $data['sebulan'] = [
                "log"=> [],
                "materis"=> [],
            ];
        }

        $data['kbm'] = $kbm;
        $data['mapels'] = $mapels;
        $data['jadwals'] = $jadwals;
        $data['jadwal'] = isset($jadwals[$day]) && $day != 7 ? $jadwals[$day] : [];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/absensi/data');
        $this->load->view('members/siswa/templates/footer');
    }

    public function materi()
    {
        $this->getTugasMateri('1');
    }

    public function tugas()
    {
        $this->getTugasMateri('2');
    }

    private function getTugasMateri($jenis) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Elearning_model',  'elearning');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => $jenis == '1' ? 'Materi' : 'Tugas',
            'subjudul' => $jenis == '1' ? 'materi' : 'tugas',
            'setting' => $setting
        ];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        /*
        //$numday = date('N', strtotime(date("Y-m-d")));
        //$today = date("Y-m-d");
        $jadwal_seminggu = $this->kelas->loadJadwalSiswaSeminggu($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $materi_seminggu = $this->kelas->getMateriSiswaSeminggu($tp->id_tp, $smt->id_smt, $siswa->id_kelas, $jenis);
        $mapels = $this->dropdown->getAllMapel();

        $last_week = [
            date("Y-m-d", strtotime('-7 days')),
            date("Y-m-d", strtotime('-6 days')),
            date("Y-m-d", strtotime('-5 days')),
            date("Y-m-d", strtotime('-4 days')),
            date("Y-m-d", strtotime('-3 days')),
            date("Y-m-d", strtotime('-2 days')),
            date("Y-m-d", strtotime('-1 days')),
            date("Y-m-d")
        ];

        $materis = [];
        $logs = [];
        foreach ($last_week as $day) {
            $idhari = date('N', strtotime($day));
            $materis[$day] = [];
            if (isset($jadwal_seminggu[$idhari])) {
                foreach ($jadwal_seminggu[$idhari] as $kjam => $val) {
                    $dummy = new stdClass();
                    $dummy->id_mapel = $val->id_mapel;
                    $dummy->id_jadwal = $val->id_jadwal;
                    $dummy->nama_mapel = isset($mapels[$val->id_mapel]) ? $mapels[$val->id_mapel] : "";

                    $materis[$day][$kjam] = isset($materi_seminggu[$day]) && isset($materi_seminggu[$day][$kjam])
                        ? $materi_seminggu[$day][$kjam]
                        : $dummy;
                }

                $arrIdKjms = [];
                foreach ($materis[$day] as $mtr) {
                    if (isset($mtr->id_kjm)) $arrIdKjms[] = $mtr->id_kjm;
                }

                $log = [];
                if (count($arrIdKjms) > 0) $log = $this->kelas->getStatusMateriSiswaByJadwal($siswa->id_siswa, $arrIdKjms);
                $logs[$day] = $log;
            }
        }

        $data['week'] = $last_week;
        $data['jadwals'] = $jadwal_seminggu;
        $data['materis'] = $materis;
        $data['logs'] = $logs;
        */
        $data['jenis'] = $jenis;
        $data['jurusan'] = $this->dropdown->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['running_text'] = $this->dashboard->getRunningText();

        $now = new DateTime();
        $id_hari = $now->format('w');
        $id_hari = $id_hari == 0 ? 7 : $id_hari;

        $jadwal_mapel = $this->elearning->getJadwalHarian($tp->id_tp, $smt->id_smt, $id_hari, $siswa->id_kelas);
        $jadwal_materi = $this->elearning->getAllMateriByTgl($siswa->id_kelas, $now->format('Y-m-d'));
        $data['materis'] = $jadwal_materi;

        $jadwal_kbm = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt, $siswa->id_kelas);

        $details = [];
        $lastDari = '0:00';
        $lastSampai = '01:00';
        $latestMpl = '0';
        $lastDay = '';

        $mapels = $jadwal_mapel[$siswa->id_kelas] ?? [];
        $details = [];
        foreach ($mapels as $jadwal) {
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
                    'kode' => $jadwal->kode ?? '',
                    'materi' => array_filter($jadwal_materi, function($val) use ($jadwal) {
                        return ($val->id_mapel == $jadwal->id_mapel and $val->id_kelas == $jadwal->id_kelas);
                    })
                ];
            }
            $lastDari = $jadwal->dari ?? $lastDari;
            $lastSampai = $jadwal->sampai ?? $lastSampai;
        }

        unset($jadwal_kbm->istirahat);
        $jadwal_kbm->detail = $details;
        $data['jadwal_materi'] = $jadwal_kbm;

        $dates = [];
        $dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
        $monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

        $libur = $jadwal_kbm->libur == '7' ? 0 : $jadwal_kbm->libur;
        for ($i = -15; $i <= 15; $i++) {
            $date = clone $now;
            $date->modify("$i days");

            $dayName = $dayNames[$date->format('w')];
            $day = $date->format('j');
            $monthName = $monthNames[$date->format('n') - 1];
            $dateString = $date->format('Y-m-d');

            $today = $dateString === $now->format('Y-m-d');
            $is_libur = false;
            if ($date->format('w') == $libur) {
                $is_libur = true;
            }
            $dates[] = [
                'hari'  => $dayName,
                'tgl'   => $day,
                'bln'   => $monthName,
                'today' => $today,
                'libur' => $is_libur
            ];
        }
        $data['dates'] = $dates;

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/elearning/materi_jadwal');
        $this->load->view('members/siswa/templates/footer');
    }

    public function seminggu()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');

        $id_siswa = $this->input->get('id_siswa', true);
        $id_kelas = $this->input->get('id_kelas', true);
        $tgl = $this->input->get('tgl', true);
        $jenis = $this->input->get('jenis', true);

        $mapels = $this->dropdown->getAllMapel();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $today = date($tgl);
        $numday = date('N', strtotime($tgl));
        $jadwal = $this->kelas->loadJadwalSiswaHariIni($tp->id_tp, $smt->id_smt, $id_kelas, $numday);

        $materi_hari_ini = $this->kelas->getMateriSiswa($id_kelas, $today, $jenis);
        $materi = [];
        foreach ($jadwal as $key => $value) {
            $materi['materi'][$key] = $materi_hari_ini[$key] ?? [
                'id_mapel' => $value->id_mapel,
                'id_jadwal' => $value->id_jadwal,
                'nama_mapel' => $mapels[$value->id_mapel] ?? ""
            ];
        };

        $arrIdKjm = [];
        foreach ($materi['materi'] as $mtr) {
            if (isset($mtr->id_kjm)) $arrIdKjm[] = $mtr->id_kjm;
        }

        if (count($arrIdKjm) > 0)
            $materi['logs'] = (array)$this->kelas->getStatusMateriSiswaByJadwal($id_siswa, $arrIdKjm);

        $materi['jadwal'] = $jadwal;

        $kelas = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $id_kelas);
        $kelas->istirahat = $this->maybe_unserialize($kelas->istirahat ?? '');
        $materi['kbm'] = $kelas;
        $materi['seminggu'] = $this->kelas->loadJadwalSiswaSeminggu($tp->id_tp, $smt->id_smt, $id_kelas);

        $this->output_json($materi);
    }

    public function bukaMateri($id_kjm, $jamke)
    {
        $this->bukaTugasMateri($id_kjm, $jamke, '1');
    }

    public function bukaTugas($id_kjm, $jamke)
    {
        $this->bukaTugasMateri($id_kjm, $jamke, '2');
    }

    private function bukaTugasMateri($id_kjm, $jamke, $jenis) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => $jenis == '1' ? 'Materi' : 'Tugas',
            'subjudul' => 'Kerjakan',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['jamke'] = $jamke;
        $data['materi'] = $this->kelas->getMateriKelasSiswa($id_kjm, $jenis);
        $logs = $this->kelas->getStatusMateriSiswa($id_kjm);
        if (isset($logs[$siswa->id_siswa])) {
            $logs[$siswa->id_siswa]->file = $this->maybe_unserialize($logs[$siswa->id_siswa]->file ?? '');
        }

        $data['kjm'] = $id_kjm;
        $data['logs'] = isset($logs[$siswa->id_siswa]) ? $logs[$siswa->id_siswa] : null;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/elearning/materi_detail');
        $this->load->view('members/siswa/templates/footer');
    }

    public function saveLogMateri()
    {
        $this->load->model('Kelas_model', 'kelas');
        $id_siswa = $this->input->get('id_siswa', true);
        $id_kjm = $this->input->get('id_kjm', true);
        $jamke = $this->input->get('jamke', true);
        $mapel = $this->input->get('mapel', true);

        $this->output_json($this->kelas->saveLog('log_materi', $id_siswa, $id_kjm, $jamke, $mapel, 'Membuka materi'));
    }

    public function saveLogTugas()
    {
        $this->load->model('Kelas_model', 'kelas');
        $id_siswa = $this->input->get('id_siswa', true);
        $id_kjm = $this->input->get('id_kjm', true);
        $jamke = $this->input->get('jamke', true);
        $mapel = $this->input->get('mapel', true);

        $this->output_json($this->kelas->saveLog('log_materi', $id_siswa, $id_kjm, $jamke, $mapel, 'Membuka tugas'));
        //$data['log_mulai'] = $id_siswa.$id_kjm.$jamke.(1);
        //$this->output_json($data);
    }

    public function saveFileMateriSelesai()
    {
        $id_siswa = $this->input->post('id_siswa', true);
        $id_kjm = $this->input->post('id_kjm', true);
        $isi_materi = $this->input->post('isi_materi', true);
        $jamke = $this->input->post('jamke', true);
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

        $id_log = $id_siswa . $id_kjm;
        $insert = [
            'id_siswa' => $id_siswa,
            'id_materi' => $id_kjm,
            'finish_time' => date('Y-m-d H:i:s'),
            'jam_ke' => $jamke,
            'log_desc' => 'Menyelesaikan materi',
            'text' => $isi_materi,
            'file' => serialize($src_file)
        ];

        $this->db->where('id_log', $id_log);
        $q = $this->db->get('log_materi');

        if ($q->num_rows() > 0) {
            $this->db->where('id_log', $id_log);
            $update = $this->db->update('log_materi', $insert);
        } else {
            $this->db->set('id_log', $id_log);
            $update = $this->db->insert('log_materi', $insert);
        }

        $data['status'] = $update;
        $this->output_json($data);
    }

    public function saveFileTugasSelesai()
    {
        $id_siswa = $this->input->post('id_siswa', true);
        $id_kjm = $this->input->post('id_kjm', true);
        $isi_tugas = $this->input->post('isi_tugas', true);
        $jamke = $this->input->post('jamke', true);
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

        $id_log = $id_siswa . $id_kjm;
        $insert = [
            'id_siswa' => $id_siswa,
            'id_materi' => $id_kjm,
            'jam_ke' => $jamke,
            'log_desc' => 'Menyelesaikan tugas',
            'text' => $isi_tugas,
            'file' => serialize($src_file),
        ];

        $this->db->where('id_log', $id_log);
        $q = $this->db->get('log_tugas');

        if ($q->num_rows() > 0) {
            $this->db->where('id_log', $id_log);
            $update = $this->db->update('log_tugas', $insert);
        } else {
            $this->db->set('id_log', $id_log);
            $update = $this->db->insert('log_tugas', $insert);
        }

        $data['status'] = $update;
        $this->output_json($data);
    }

    function uploadFile()
    {
        $this->load->library('upload');
        $max_size = $this->input->post('max-size', true);
        if (isset($_FILES["file_uploads"]["name"])) {
            $config['upload_path'] = './uploads/file_siswa/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4|avi|doc|docx|xls|xlsx|ppt|pptx|csv|pdf|rtf|txt';
            //$config['encrypt_name'] = TRUE;
            $config['max_size'] = $max_size;
            $config['overwrite'] = FALSE;

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file_uploads')) {
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            } else {
                $result = $this->upload->data();
                $data['src'] = 'uploads/file_siswa/' . $result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;
            }

            $data['type'] = $_FILES['file_uploads']['type'];
            $data['size'] = $_FILES['file_uploads']['size'];
        }
        $this->output_json($data);
    }

    function deleteFile()
    {
        $src = $this->input->post('src');
        //$file_name = str_replace(base_url(), '', $src);
        if (unlink($src)) {
            echo 'File Delete Successfully';
        }
    }
}