<?php

class Siswa extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        $this->load->library('upload');
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->library('user_agent');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function output_json($data, $encode = true)
    {
        if ($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    private function sortArrays(&$array)
    {
        foreach ($array as &$subArray) {
            if ($subArray) {
                sort($subArray);
            }
        }
    }

    public function index()
    {
    }

    private function arrToUpper($val) {
        return strtoupper($val ?? '');
    }

    public function getPost()
    {
        $this->load->model('Post_model', 'post');
        $kode = $this->input->get('kelas', true);
        //$this->input->get()->getPostForUser("'%siswa%'")
        $post = $this->post->getPostForUser("'%siswa%'", "'%" . $kode . "%'");
        $this->output_json($post);
    }

    public function getComment($id_post, $page)
    {
        $perPage = 5;
        $offset = $page * $perPage;
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $this->db->from('post_comments a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_post', $id_post);
        $this->db->limit($perPage, $offset);
        $comment = $this->db->get()->result();

        $this->output_json($comment);
    }

    public function getReplies($id_comment, $page)
    {
        $perPage = 5;
        $offset = $page * $perPage;
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa');
        $this->db->from('post_reply a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_comment', $id_comment);
        $this->db->limit($perPage, $offset);
        $replies = $this->db->get()->result();

        $this->output_json($replies);
    }

    public function saveKomentar()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $dari = $siswa->id_siswa;
        $dari_group = 3;
        $data = [
            'type' => '1',
            'id_post' => $this->input->post('id_post'),
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->input->post('text'),
        ];

        $insert = $this->db->replace('post_comments', $data);

        $id = $this->db->insert_id();
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa, (SELECT COUNT(post_reply.id_reply) FROM post_reply WHERE a.id_comment = post_reply.id_comment) AS jml');
        $this->db->from('post_comments a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_comment', $id);
        $comment = $this->db->get()->result();

        $this->output_json($comment);
    }

    public function saveBalasan()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Post_model', 'post');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $dari = $siswa->id_siswa;
        $dari_group = 3;
        $data = [
            'id_comment' => $this->input->post('id_comment'),
            'dari' => $dari,
            'dari_group' => $dari_group,
            'text' => $this->input->post('text'),
        ];

        $insert = $this->db->replace('post_reply', $data);

        $id = $this->db->insert_id();
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, b.nama_guru, b.foto, c.nama as nama_siswa, c.foto as foto_siswa');
        $this->db->from('post_reply a');
        $this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
        $this->db->join('master_siswa c', 'a.dari=c.id_siswa', 'left');
        $this->db->order_by('a.tanggal', 'desc');
        $this->db->where('a.id_reply', $id);
        $replies = $this->db->get()->result();

        $this->output_json($replies);
    }

    public function jadwalPelajaran()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
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
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $jadk = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        if ($jadk == null) {
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
            $data['jadwal_kbm'] = $jadk;
        }

        $data['id_kelas'] = $siswa->id_kelas;
        $jadm = $this->kelas->getJadwalMapelGroupJam($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $jml_mapel = $jadk == null ? 1 : $jadk->kbm_jml_mapel_hari;
        if ($jadm == null) {
            for ($i = 0; $i < $jml_mapel; $i++) {
                $jadwal_mapel[] = [
                    'jadwal' => $this->kelas->getDummyJadwalMapel($tp->id_tp, $smt->id_smt, $i + 1, $siswa->id_kelas)
                ];
            }
            $data['method'] = 'add';
        } else {
            foreach ($jadm as $j) {
                $jadwal_mapel[] = [
                    'jadwal' => $this->kelas->getJadwalMapelByHari($tp->id_tp, $smt->id_smt, $j->jam_ke, $siswa->id_kelas)
                ];
            }
            $data['method'] = 'edit';
        }

        $data['jadwal_mapel'] = $jadwal_mapel;
        $data['mapels'] = $this->master->getAllMapel();
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/jadwal/data');
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

        //$numday = date('N', strtotime(date("Y-m-d")));
        $jenis == null ? '1' : '2';
        $today = date("Y-m-d");
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

        /*
        $week = [
            date("Y-m-d", strtotime('monday this week', strtotime($today))),
            date("Y-m-d", strtotime('tuesday this week', strtotime($today))),
            date("Y-m-d", strtotime('wednesday this week', strtotime($today))),
            date("Y-m-d", strtotime('thursday this week', strtotime($today))),
            date("Y-m-d", strtotime('friday this week', strtotime($today))),
            date("Y-m-d", strtotime('saturday this week', strtotime($today)))
        ];
        */

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
        $data['jenis'] = $jenis;

        $data['kbm'] = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['jurusan'] = $this->dropdown->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/materi/data');
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

        $jadk = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $id_kelas);
        $jadk->istirahat = $this->maybe_unserialize($jadk->istirahat ?? '');
        $materi['kbm'] = $jadk;
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
        $this->load->view('members/siswa/materi/view');
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

    public function leavecbt($id_jadwal, $id_siswa) {
        $this->db->set('agent', 'illegal agent');
        $this->db->set('device', 'illegal device');
        $this->db->set('reset', 0);
        $this->db->where('id_log', $id_siswa .'0'. $id_jadwal . '1');
        $this->db->update('log_ujian');
        redirect('logout', 'refresh');
    }

    public function cbt(){
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting()
        ];

        //$numday = date('N', strtotime(date("Y-m-d")));
        $today = strtotime(date('Y-m-d'));
        $cbt_info = $this->cbt->getSiswaCbtInfo($siswa->id_siswa, $tp->id_tp, $smt->id_smt);
        $cbt_info->no_peserta = $this->cbt->getNomorPeserta($siswa->id_siswa);

        $cbt_jadwal = $this->cbt->getJadwalCbt($tp->id_tp, $smt->id_smt, $siswa->level_id);
        $jadwal_ujian_aktif = [];
        $timer = [];
        foreach ($cbt_jadwal as $key => $jadwal) {
            $kk = $this->maybe_unserialize($jadwal->bank_kelas ?? '');
            $arrKelasCbt = [];
            foreach ($kk as $k) {
                $arrKelasCbt[] = $k['kelas_id'];
            }

            if ($cbt_info != null && in_array($cbt_info->id_kelas, $arrKelasCbt) && $jadwal->status === '1') {
                $mulai = strtotime($jadwal->tgl_mulai);
                $selesai = strtotime($jadwal->tgl_selesai);
                if ($today >= $mulai && $today <= $selesai) {
                    if ($jadwal->soal_agama == '-' || $jadwal->soal_agama == '0' || $jadwal->soal_agama == $siswa->agama) {
                        if (isset($jadwal_ujian_aktif[$jadwal->tgl_mulai])) {
                            $jadwal_ujian_aktif[$jadwal->tgl_mulai][] = $jadwal;
                        } else {
                            $jadwal_ujian_aktif[$jadwal->tgl_mulai] = [];
                            $jadwal_ujian_aktif[$jadwal->tgl_mulai][] = $jadwal;
                        }
                    }
                }
            }
            $timer[$jadwal->id_jadwal] = $this->cbt->getElapsed($siswa->id_siswa .'0'. $jadwal->id_jadwal);
        }

        //$data['cbt_test'] = $cbt_jadwal;
        $data['cbt_info'] = $cbt_info;
        $data['cbt_jadwal'] = $jadwal_ujian_aktif;
        $data['guru'] = $this->cbt->getDataGuru();
        //$data['kbm'] = $this->kelas->getJadwalKbm($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $data['sesi'] = $this->dropdown->getAllWaktuSesi();
        $data['elapsed'] = $timer;
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/cbt/data');
        $this->load->view('members/siswa/templates/footer');
    }

    public function konfirmasi($id_jadwal){
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $curr_address = $this->input->ip_address();
        if ($this->agent->is_browser()){
            $curr_agent = $this->agent->browser().' '.$this->agent->version();
        }elseif ($this->agent->is_mobile()){
            $curr_agent = $this->agent->mobile();
        }else{
            $curr_agent = 'unknown';
        }
        $curr_device = $this->agent->platform();
        $data['support'] = $curr_agent != 'unknown';

        $info = $this->cbt->getJadwalById($id_jadwal);
        if ($info->reset_login == '1') {
            //jika jadwal menggunakan reset izin
            $log = $this->db->where('id_log', $siswa->id_siswa.'0'.$id_jadwal.'1')->get('log_ujian')->row();
            if ($log != null) {
                //jika siswa sudah login ujian
                if ($log->reset == 1) {
                    //jika sudah direset izin
                    $this->db->set('address', $curr_address);
                    $this->db->set('agent', $curr_agent);
                    $this->db->set('device', $curr_device);
                    $this->db->set('reset', 0);
                    $this->db->where('id_log', $siswa->id_siswa .'0'. $id_jadwal . '1');
                    if ($this->db->update('log_ujian')) {
                        $log = $this->db->where('id_log', $siswa->id_siswa.'0'.$id_jadwal.'1')->get('log_ujian')->row();
                    }
                }
                $valid = $log->address == $curr_address && $log->agent == $curr_agent && $log->device == $curr_device;
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }

        //$data['log'] = $log;
        $data['valid'] = $valid;
        if ($valid) {
            $bank = $this->cbt->getCbt($id_jadwal);
            $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
            $guru = $this->cbt->getDataGuru();

            $cbt_info = $this->cbt->getSiswaCbtInfo($siswa->id_siswa, $tp->id_tp, $smt->id_smt);
            $pengawass = $this->cbt->getPengawas($tp->id_tp.$smt->id_smt.$id_jadwal.$cbt_info->id_ruang.$cbt_info->id_sesi);
            $pengawas = [];
            if ($pengawass != null && count(explode(",", $pengawass->id_guru ?? '')) > 0) {
                $pengawas = $this->master->getGuruByArrId(explode(",", $pengawass->id_guru ?? ''));
            }

            $data['bank'] = $bank;
            $data['guru'] = $guru;
            $data['pengawas'] = $pengawas;
            //$data['elapsed'] = $this->cbt->getElapsed($siswa->id_siswa .'0'. $id_jadwal);
        }

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/cbt/konfirmasi');
        $this->load->view('members/siswa/templates/footer');
    }

    public function validasiSiswa() {
        $id_jadwal = $this->input->post('jadwal');
        $id_siswa = $this->input->post('siswa');
        $id_bank = $this->input->post('bank');
        $token_siswa = $this->input->post('token');
        $this->load->model('Cbt_model', 'cbt');

        $this->db->trans_start();
        $info = $this->cbt->getJadwalById($id_jadwal);

        $token_valid = true;
        // cek token
        if ($info->token == '1') {
            $token = $this->cbt->getToken();
            if ($token == null) {
                $token_valid = false;
                $data['token_msg'] = 'Token tidak ada';
            } else {
                $token_valid = $token->token == $token_siswa ? true : false;
                $data['token_msg'] = $token_valid ? '' : 'Token salah';
            }
        }
        $data['token'] = $token_valid;

        if ($token_valid) {
            $curr_address = $this->input->ip_address();
            if ($this->agent->is_browser()){
                $curr_agent = $this->agent->browser().' '.$this->agent->version();
            }elseif ($this->agent->is_mobile()){
                $curr_agent = $this->agent->mobile();
            }else{
                $curr_agent = 'unknown';
            }
            $curr_device = $this->agent->platform();
            $support = $curr_agent != 'unknown';
            $data['support'] = $support;

            if ($support) {
                // cek log_ujian
                $mulai_baru = false;
                $cek_reset_waktu = false;
                $log = $this->db->where('id_log', $id_siswa.'0'.$id_jadwal.'1')->get('log_ujian')->row();
                if ($log == null) { // tidak ada log = mulai baru
                    $inserted = $this->cbt->saveLog($id_siswa, $id_jadwal, 1, 'Memulai Ujian');
                    if ($inserted) { // berhasil memulai
                        $log = $this->db->where('id_log', $id_siswa.'0'.$id_jadwal.'1')->get('log_ujian')->row();
                        $izinkan = true;
                        $mulai_baru = true;
                    } else { // tidak berhasil memulai
                        $izinkan = false;
                        $mulai_baru = false;
                    }
                } else { // ada log ujian
                    // cek reset izin
                    if ($info->reset_login == '1') { // jadwal ceklis reset izin
                        if ($log->address == $curr_address
                            && $log->agent == $curr_agent
                            && $log->device == $curr_device) { // tidak pindah komputer
                            $izinkan = true;
                            $mulai_baru = false;
                        } else { // pindah komputer, harus ada izin admin
                            if ($log->reset == '0') { // belum diizinkan
                                $izinkan = false;
                            } else { // sudah diizinkan ($log->reset == '1')
                                $this->db->set('address', $curr_address);
                                $this->db->set('agent', $curr_agent);
                                $this->db->set('device', $curr_device);
                                $this->db->set('reset', 0);
                                $this->db->where('id_log', $id_siswa .'0'. $id_jadwal . '1');
                                if ($this->db->update('log_ujian')) {
                                    $log = $this->db->where('id_log', $id_siswa.'0'.$id_jadwal.'1')->get('log_ujian')->row();
                                    $izinkan = true;
                                    $mulai_baru = false;
                                } else {
                                    $izinkan = false;
                                    $mulai_baru = false;
                                }
                                // cek apakah kehabisan waktu?
                                $cek_reset_waktu = true;
                            }
                        }
                    } else { // jadwal tidak ceklist reset izin
                        $izinkan = true;
                        $mulai_baru = false;
                    }
                }
                $data['izinkan'] = $izinkan;
                $data['log'] = $log;

                $mulai_baru_d = false;
                $ada_waktu = false;
                if ($izinkan || $cek_reset_waktu) {
                    // cek durasi ujian dan durasi siswa
                    $elapsed = $this->cbt->getElapsed($id_siswa .'0'. $id_jadwal);
                    if ($elapsed == null) {
                        $ada_waktu = true;
                        $mulai_baru_d = true;
                        $insert = [
                            'id_durasi' => $id_siswa .'0'. $id_jadwal,
                            'id_siswa' => $id_siswa,
                            'id_jadwal' => $id_jadwal,
                            'status' => 1,
                            'mulai' => date('Y-m-d H:i:s'),
                            'lama_ujian' => '00:00:00',
                            'reset' => 0,
                        ];
                        $this->db->insert('cbt_durasi_siswa', $insert);
                    } else {
                        $mulai_baru_d = $elapsed->reset == '3';
                        // cek reset waktu
                        // 0=tidak, 1=reset dari 0, 2=reset dari sisa waktu, 3=ulangi jadwal
                        if ($elapsed->reset == '1') {
                            $ada_waktu = true;
                            $this->db->set('lama_ujian', '00:00:00');
                            $this->db->set('mulai', date('Y-m-d H:i:s'));
                            $this->db->set('reset', 0);
                            $this->db->where('id_durasi', $id_siswa .'0'. $id_jadwal);
                            $data['update_reset'] = $this->db->update('cbt_durasi_siswa');
                        } elseif ($elapsed->reset == '2') {
                            $ada_waktu = true;
                            $dt = explode(':', $elapsed->lama_ujian ?? '');
                            $time = new DateTime();
                            $time->sub(new DateInterval('PT'.$dt[0] .'H'. $dt[1] .'M'.$dt[2].'S'));

                            $this->db->set('mulai', $time->format('Y-m-d H:i:s'));
                            $this->db->set('reset', 0);
                            $this->db->where('id_durasi', $id_siswa .'0'. $id_jadwal);
                            $data['update_reset'] = $this->db->update('cbt_durasi_siswa');
                        } elseif ($elapsed->reset == '3') {
                            $ada_waktu = true;
                            $this->db->set('lama_ujian', '00:00:00');
                            $this->db->set('mulai', date('Y-m-d H:i:s'));
                            $this->db->set('reset', 0);
                            $this->db->where('id_durasi', $id_siswa .'0'. $id_jadwal);
                            $data['update_reset'] = $this->db->update('cbt_durasi_siswa');
                        } else {
                            // reset waktu = 0 (belum reset)
                            $mulai = new DateTime($elapsed->mulai);
                            $interval = $mulai->diff(new DateTime());
                            $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                            $data['interval'] = [
                                'days'  => $interval->days,
                                'hari'  => $interval->d,
                                'jam'   => $interval->h,
                                'menit' => $interval->i,
                                'detik' => $interval->s,
                                'total' => $minutes
                            ];

                            // status
                            // 0 = ada waktu, 1 = habis waktu
                            $ada_waktu = $minutes < $info->durasi_ujian;
                            $data['warn'] = [
                                'durasi_ujian'  => $info->durasi_ujian,
                                'siswa_mulai'   => $elapsed->mulai,
                                'durasi_siswa'  => $elapsed->lama_ujian,
                                'timer_elapsed' => $minutes,
                                'terlampaui'    => $minutes - $info->durasi_ujian,
                                'status'        => $ada_waktu ? 0 : 1,
                                'msg'           => $ada_waktu ? '' : 'Waktu ujian sudah habis'
                            ];
                        }
                    }
                }
                $data['ada_waktu'] = $ada_waktu;
                $data['elapsed'] = $this->cbt->getElapsed($id_siswa .'0'. $id_jadwal);

                if ($ada_waktu) {
                    // cek soal siswa
                    $soal = $this->cbt->getJumlahSoalSiswa($id_bank, $id_siswa);
                    if ($soal > 0) {
                        if ($mulai_baru && $mulai_baru_d) {
                            $this->db->delete('cbt_soal_siswa', array('id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa, 'id_bank' => $id_bank));
                            $nomor_soal = $this->createQueueNumber($id_siswa, $id_bank, $id_jadwal);
                            if (count($nomor_soal) > 0) $this->db->insert_batch('cbt_soal_siswa', $nomor_soal);
                        }
                    } else {
                        $nomor_soal = $this->createQueueNumber($id_siswa, $id_bank, $id_jadwal);
                        if (count($nomor_soal) > 0) $this->db->insert_batch('cbt_soal_siswa', $nomor_soal);
                    }
                    $data['jml_soal'] = $this->cbt->getJumlahSoalSiswa($id_bank, $id_siswa);
                }
            }
        }
        $this->db->trans_complete();
        $this->output_json($data);
    }

    public function createQueueNumber($id_siswa, $id_bank, $id_jadwal) {
        $this->load->model('Cbt_model', 'cbt');

        $cek_soal = $this->cbt->getAllIdSoal($id_bank);
        $jadwal = $this->cbt->getInfoJadwal($id_bank);

        $num1 = isset($cek_soal['1']) ? count($cek_soal['1']) : 0;
        $num2 = isset($cek_soal['2']) ? count($cek_soal['2']) : 0;
        $num3 = isset($cek_soal['3']) ? count($cek_soal['3']) : 0;
        $num4 = isset($cek_soal['4']) ? count($cek_soal['4']) : 0;
        $num5 = isset($cek_soal['5']) ? count($cek_soal['5']) : 0;
        $total = $num1 + $num2 + $num3 + $num4 + $num5;

        $ada1 = $num1 == (int) $jadwal->tampil_pg;
        $ada2 = $num2 == (int) $jadwal->tampil_kompleks;
        $ada3 = $num3 == (int) $jadwal->tampil_jodohkan;
        $ada4 = $num4 == (int) $jadwal->tampil_isian;
        $ada5 = $num5 == (int) $jadwal->tampil_esai;

        if ($ada1 && $ada2 && $ada3 && $ada4 && $ada5) {
            $opsis = $jadwal->opsi;
            if ($opsis == '2') {
                $arrOpsi = ['A', 'B'];
            } elseif ($opsis == '3') {
                $arrOpsi = ['A', 'B', 'C'];
            } elseif ($opsis == '4') {
                $arrOpsi = ['A', 'B', 'C', 'D'];
            } else {
                $arrOpsi = ['A', 'B', 'C', 'D', 'E'];
            }

            $arrNum = range(1, $total);
            if ($jadwal->acak_soal == '1') shuffle($arrNum);

            $items = [];
            $j = 0;
            foreach ($cek_soal as $jenis=>$soals) {
                foreach ($soals as $soal) {
                    if ($jenis == '1') {
                        if ($jadwal->acak_opsi == '1') shuffle($arrOpsi);
                    }

                    $item_soal['id_soal_siswa'] = $id_siswa .'0'. $id_jadwal . $id_bank . $arrNum[$j];
                    $item_soal["id_bank"] = $id_bank;
                    $item_soal["id_jadwal"] = $id_jadwal;
                    $item_soal["id_soal"] = $soal->id_soal;
                    $item_soal["id_siswa"] = $id_siswa;
                    $item_soal["jenis_soal"] = $jenis;
                    $item_soal["no_soal_alias"] = $arrNum[$j];

                    if ($jenis == '1') {
                        $item_soal["opsi_alias_a"] = $arrOpsi[0];
                        $item_soal["opsi_alias_b"] = $arrOpsi[1];
                        $item_soal["opsi_alias_c"] = isset($arrOpsi[2]) ? $arrOpsi[2] : '';
                        $item_soal["opsi_alias_d"] = isset($arrOpsi[3]) ? $arrOpsi[3] : '';
                        $item_soal["opsi_alias_e"] = isset($arrOpsi[4]) ? $arrOpsi[4] : '';
                        $item_soal["point_soal"] = $jadwal->bobot_pg > 0 ? round($jadwal->bobot_pg / $jadwal->tampil_pg, 2) : 0;
                    } elseif ($jenis == '2') {
                        $item_soal["opsi_alias_a"] = 'A';
                        $item_soal["opsi_alias_b"] = '';
                        $item_soal["opsi_alias_c"] = '';
                        $item_soal["opsi_alias_d"] = '';
                        $item_soal["opsi_alias_e"] = '';
                        $item_soal["point_soal"] = $jadwal->bobot_kompleks > 0 ? round($jadwal->bobot_kompleks / $jadwal->tampil_kompleks, 2) : 0;
                    } elseif ($jenis == '3') {
                        $item_soal["point_soal"] = $jadwal->bobot_jodohkan > 0 ? round($jadwal->bobot_jodohkan / $jadwal->tampil_jodohkan, 2) : 0;
                    } elseif ($jenis == '4') {
                        $item_soal["point_soal"] = $jadwal->bobot_isian > 0 ? round($jadwal->bobot_isian / $jadwal->tampil_isian, 2) : 0;
                    } elseif ($jenis == '5') {
                        $item_soal["point_soal"] = $jadwal->bobot_esai > 0 ? round($jadwal->bobot_esai / $jadwal->tampil_esai, 2) : 0;
                    }

                    $item_soal["jawaban_benar"] = $soal->jawaban;
                    $item_soal["soal_end"] = $j + 1 === count($arrNum) ? '1' : '0';
                    $items[] = $item_soal;

                    $j ++;
                }
            }

            usort($items, static function ($a, $b) {
                return $a['no_soal_alias'] <=> $b['no_soal_alias'];
            });

            return $items;
        }

        return [];
    }

    public function penilaian($id_jadwal) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();
        $data['jadwal'] = $this->cbt->getCbt($id_jadwal);

        $id_durasi = $siswa->id_siswa .'0'. $id_jadwal;
        $durasi = $this->cbt->getElapsed($id_durasi);
        $mulai = new DateTime($durasi->mulai);
        $diff = $mulai->diff(new DateTime());
        $durasi->diff = [
            'days' => $diff->days,
            'hari' => $diff->d,
            'jam' => $diff->h,
            'menit' => $diff->i,
            'detik' => $diff->s,
            'format' => $diff->format( '%H:%I:%S' )
        ];
        if ($durasi == null || $durasi->selesai != null) {
            redirect('siswa/cbt');
        }
        $data['elapsed'] = $durasi;

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/cbt/ujian');
        $this->load->view('members/siswa/templates/footer');
    }

    public function checkTimer($id_siswa, $id_jadwal) {
        $this->load->model('Cbt_model', 'cbt');
        //$info = $this->cbt->getJadwalById($id_jadwal);
        //$elapsed = '00:00:00';
        $id_durasi = $id_siswa .'0'. $id_jadwal;
        $durasi = $this->cbt->getElapsed($id_durasi);
        if ($durasi != null) {
            $mulai = new DateTime($durasi->mulai);
            $diff = $mulai->diff(new DateTime());
            $elapsed = $diff->format( '%H:%I:%S' );
            if ($durasi->reset == '0') {
                // tidak ada reset
                $this->db->set('lama_ujian', $elapsed);
                $this->db->where('id_durasi', $id_durasi);
                $this->db->update('cbt_durasi_siswa');
                $durasi = $this->cbt->getElapsed($id_durasi);
            } elseif ($durasi->reset == '1') {
                // reset menit ke 00:00:00
                $this->db->set('lama_ujian', '00:00:00');
                $this->db->set('reset', 0);
                $this->db->where('id_durasi', $id_durasi);
                $this->db->update('cbt_durasi_siswa');
                $durasi = $this->cbt->getElapsed($id_durasi);
            } elseif ($durasi->reset == '3') {
                // ulangi siswa
                $durasi = false;
            } else {
                // reset dan lanjutkan
                $this->db->set('lama_ujian', $elapsed);
                $this->db->set('reset', 0);
                $this->db->where('id_durasi', $id_durasi);
                $this->db->update('cbt_durasi_siswa');
                $durasi = $this->cbt->getElapsed($id_durasi);
            }
        } else {
            $durasi = false;
        }
        return $durasi;
    }

    public function loadNomorSoal() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');
        $id_bank = $this->input->post('bank');
        $nomor = $this->input->post('nomor');
        $timer = $this->input->post('timer');

        $durasi = $this->checkTimer($id_siswa, $id_jadwal);

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswa = $this->cbt->getDataSiswaById($tp->id_tp, $smt->id_smt, $id_siswa);

        $soals = $this->cbt->getALLSoalSiswa($id_bank, $siswa->id_siswa);
        foreach ($soals as $sValue) {
            if ($sValue->jenis_soal == '3') {
                $sValue->jawaban = $this->maybe_unserialize($sValue->jawaban ?? '');
                $ada_jawab = $sValue->jawaban_siswa != null;
                if ($ada_jawab) {
                    $sValue->jawaban_siswa = $this->maybe_unserialize($sValue->jawaban_siswa ?? '');
                }
            }
        }
        $id_soal_siswa = $siswa->id_siswa .'0'. $id_jadwal . $id_bank . $nomor;
        $ind_soal = array_search($id_soal_siswa, array_column($soals, 'id_soal_siswa'));
        $item_soal = $soals[$ind_soal];
        $max_jawaban = [];
        if ($item_soal->jenis_soal == '1') {
            $jwbSiswa = $item_soal->jawaban_siswa != null ? strtoupper($item_soal->jawaban_siswa ?? '') : '';
            $opsis = [
                [
                    'valAlias' => $item_soal->opsi_alias_a,
                    'opsi' => $item_soal->opsi_a,
                    'value' => 'A',
                    'checked' => 'A' === $jwbSiswa ? 'checked' : ''
                ],
                [
                    'valAlias' => $item_soal->opsi_alias_b,
                    'opsi' => $item_soal->opsi_b,
                    'value' => 'B',
                    'checked' => 'B' === $jwbSiswa ? 'checked' : ''
                ],
                [
                    'valAlias' => $item_soal->opsi_alias_c,
                    'opsi' => $item_soal->opsi_c,
                    'value' => 'C',
                    'checked' => 'C' === $jwbSiswa ? 'checked' : ''
                ],
                [
                    'valAlias' => $item_soal->opsi_alias_d,
                    'opsi' => $item_soal->opsi_d,
                    'value' => 'D',
                    'checked' => 'D' === $jwbSiswa ? 'checked' : ''
                ],
                [
                    'valAlias' => $item_soal->opsi_alias_e,
                    'opsi' => $item_soal->opsi_e,
                    'value' => 'E',
                    'checked' => 'E' === $jwbSiswa ? 'checked' : ''
                ],
            ];

            usort($opsis, function ($a, $b) {
                return $a['valAlias'] <=> $b['valAlias'];
            });
        } elseif ($item_soal->jenis_soal == '2') {
            $max_jawaban = [count(array_filter(($this->maybe_unserialize($item_soal->jawaban ?? ''))))];
            $item_soal->opsi_a = $this->maybe_unserialize($item_soal->opsi_a ?? '');
            $item_soal->jawaban_siswa = $this->maybe_unserialize($item_soal->jawaban_siswa ?? '');
            $jwbSiswa = $item_soal->jawaban_siswa != null ? $item_soal->jawaban_siswa : [];
            $opsis = [];
            foreach ($item_soal->opsi_a as $key=>$opsi) {
                $item = [
                    'opsi' => $opsi,
                    'value' => $key,
                    'checked' => in_array(strtoupper($key ?? ''),  $jwbSiswa) ? 'checked="true"' : ''
                ];
                $opsis[] = $item;
            }

            usort($opsis, function ($a, $b) {
                return $a['value'] <=> $b['value'];
            });
        } elseif ($item_soal->jenis_soal == '3') {
            //$jwbs = unserialize($item_soal->jawaban);
            $jwbs = $item_soal->jawaban;
            if(isset($jwbs['jawaban'])) {
                foreach ($jwbs['jawaban'] as $jwb) {
                    $max_jawaban[$jwb[0]] = 0;
                    for ($i=1, $iMax = count($jwb); $i< $iMax; $i++) {
                        if ($jwb[$i] == '1') {
                            $max_jawaban[$jwb[0]] += 1;
                        }
                    }
                }
            }
            $ada_jawab = $item_soal->jawaban_siswa != null;

            $jawaban_siswa = $ada_jawab ? $item_soal->jawaban_siswa : json_decode(json_encode($item_soal->jawaban));

            $theader = [];
            $tbody = [];

            if (isset($jawaban_siswa->jawaban)) {
                foreach ($jawaban_siswa->jawaban as $key=>$jawaban) {
                    if ($key === 0) {
                        $theader = $jawaban;
                    } else {
                        if ($ada_jawab) {
                            $tbody[] = $jawaban;
                        } else {
                            $tbody[$key] = [];
                            foreach ($jawaban as $index=>$nbaris) {
                                if ($index === 0) {
                                    $tbody[$key][] = $nbaris;
                                } else {
                                    $tbody[$key][] = '';
                                }
                            }
                        }
                    }
                }
            }
            $opsis = [
                'tabel' => isset($jwbs['jawaban']) ? $jwbs['jawaban'] : [],
                'thead' => $theader,
                'tbody' => $tbody,
                'model'  => isset($item_soal->jawaban['model']) ? $item_soal->jawaban['model'] : '2',
                'type'  => $item_soal->jawaban['type']
            ];
        } else {
            $opsis = [];
        }

        $data['durasi'] = $durasi;
        $data['timer'] = $timer;
        $data['soal_id'] = $item_soal->id_soal;
        $data['soal_siswa_id'] = $item_soal->id_soal_siswa;
        $data['soal_nomor'] = $item_soal->no_soal_alias;
        $data['soal_nomor_asli'] = $item_soal->nomor_soal;
        $data['soal_jenis'] = $item_soal->jenis_soal;
        $data['soal_soal'] = $item_soal->soal;
        $data['soal_opsi'] = json_decode(json_encode($opsis));
        $data['soal_jawaban_siswa'] = $item_soal->jawaban_siswa;
        $data['max_jawaban'] = $max_jawaban;

        $arrJawaban = [];
        $modal = '<div class="d-flex flex-wrap justify-content-center grid-nomor-pg">';
        foreach ($soals as $key=>$soal) {
            if ($soal->jawaban_siswa != null) {
                if ($soal->jenis_soal === '3') {
                    $arrJawaban3 = [];
                    if (isset($soal->jawaban_siswa->jawaban)) {
                        foreach ($soal->jawaban_siswa->jawaban as $keyi=>$jwbn_siswa) {
                            if ($keyi > 0) {
                                foreach ($jwbn_siswa as $keyj=>$jwbn) {
                                    if ($keyj > 0) {
                                        $arrJawaban3[] = $jwbn;
                                    }
                                }
                            }
                        }
                        $terjawab = in_array('1', $arrJawaban3);
                    } else {
                        $terjawab = false;
                    }
                } else {
                    $terjawab = $soal->jawaban_siswa != "";
                }
            } else {
                $terjawab = false;
            }

            $color = !$terjawab ? 'outline-secondary' : 'primary';
            $selected = $nomor == $soal->no_soal_alias ? 'active' : "";
            $modal .= '<div class="mb-4">'.
                '<div id="box'. $soal->no_soal_alias .'" class="d-flex flex-column" style="width: 70px; height: 60px;">'.
                '<button id="btn'. $soal->no_soal_alias .'" class="btn btn-'. $color .' border border-dark '.$selected.'" '.
                'data-pos="'. ($key) .'" data-nomorsoal="'. $soal->no_soal_alias .'" '.
                'data-idsoal="'. $soal->id_soal. '" data-jenis="'.$soal->jenis_soal.'" '.
                'onclick="loadSoal(this)" '.
                'style="width: 50px; height: 50px;">'.
                '<span style="font-size: 14pt"><b>'. $soal->no_soal_alias .'</b></span>'.
                '</button>';

            if ($terjawab) {
                $txt_badge = $soal->jenis_soal == '1' ? $soal->jawaban_alias : '&check;';
                $arrJawaban[] = $soal->jawaban_alias;
                $modal .= '<div id="badge'. $soal->no_soal_alias .'" class="badge badge-pill badge-success border border-dark"'.
                    ' style="font-size:12pt; width: 30px; height: 30px; margin-top: -60px; margin-left: 30px;">'.
                    $txt_badge .'</div>';
            }
            $modal .= '</div></div>';
        }
        $modal .= '</div>';
        $data['soal_modal'] = $modal;
        $data['soal_total'] = count($soals);
        $data['soal_terjawab'] = count($arrJawaban);
        $data['soal_akhir'] = $modal;

        $this->output_json($data);
    }

    public function saveSoalSiswa()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');

        $shuffle = json_decode($this->input->post('shuffle', false));

        foreach ($shuffle as $s) {
            $id_siswa = $s->id_siswa;
            $id_jadwal = $s->id_jadwal;
            $id_bank = $s->id_bank;
            $jenis = $s->jenis;
            $nomor = $s->nomor_soal;
            $soal = $this->cbt->getSoalByNomor($id_bank, $nomor, $jenis);
            $id_soal = $soal->id_soal;

            $this->db->where('id_soal_siswa', $id_siswa .'0'. $id_jadwal . $id_bank . $jenis . $nomor);
            $jml = $this->db->get('cbt_soal_siswa')->num_rows();
            if ($jml > 0) {
                $insert = [
                    //'id_soal_siswa' => $id_siswa.'0'. $id_jadwal . $id_bank . $jenis . $nomor,
                    'id_bank' => $id_bank,
                    'id_jadwal' => $id_jadwal,
                    'id_soal' => $id_soal,
                    'id_siswa' => $id_siswa,
                    'jenis_soal' => $jenis,
                    'no_soal_alias' => $s->no_soal_alias,
                    'opsi_alias_a' => isset($s->opsi_alias_a) ? $s->opsi_alias_a : null,
                    'opsi_alias_b' => isset($s->opsi_alias_b) ? $s->opsi_alias_b : null,
                    'opsi_alias_c' => isset($s->opsi_alias_c) ? $s->opsi_alias_c : null,
                    'opsi_alias_d' => isset($s->opsi_alias_d) ? $s->opsi_alias_d : null,
                    'opsi_alias_e' => isset($s->opsi_alias_e) ? $s->opsi_alias_e : null,
                    'jawaban_benar' => $soal->jawaban,
                    'soal_end' => $s->soal_end
                ];
                //$action = $this->master->update('master_guru', $input, 'id_guru', $id_guru);
                $this->master->update('cbt_soal_siswa', $insert, 'id_soal_siswa', $id_siswa .'0'. $id_jadwal . $id_bank . $jenis . $nomor);
            } else {
                $insert = [
                    'id_soal_siswa' => $id_siswa .'0'. $id_jadwal . $id_bank . $jenis . $nomor,
                    'id_bank' => $id_bank,
                    'id_jadwal' => $id_jadwal,
                    'id_soal' => $id_soal,
                    'id_siswa' => $id_siswa,
                    'jenis_soal' => $jenis,
                    'no_soal_alias' => $s->no_soal_alias,
                    'opsi_alias_a' => isset($s->opsi_alias_a) ? $s->opsi_alias_a : null,
                    'opsi_alias_b' => isset($s->opsi_alias_b) ? $s->opsi_alias_b : null,
                    'opsi_alias_c' => isset($s->opsi_alias_c) ? $s->opsi_alias_c : null,
                    'opsi_alias_d' => isset($s->opsi_alias_d) ? $s->opsi_alias_d : null,
                    'opsi_alias_e' => isset($s->opsi_alias_e) ? $s->opsi_alias_e : null,
                    'jawaban_benar' => $soal->jawaban,
                    'soal_end' => $s->soal_end
                ];
                $this->master->create('cbt_soal_siswa', $insert, false);
                //$insert = $this->db->replace('cbt_jawaban', $insert);
            }
        }

        $id_siswa = $shuffle[0]->id_siswa;
        $id_bank = $shuffle[0]->id_bank;
        $data['soals'] = $this->cbt->getSoalSiswa($id_bank, $id_siswa);
        $this->output_json($data);
    }

    public function saveLogUjian($id_siswa, $id_jadwal)
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->saveLog($id_siswa, $id_jadwal, 1, 'Memulai Ujian'));
    }

    public function saveJawaban(){
        $this->load->model('Cbt_model', 'cbt');
        $id_bank = $this->input->post('bank', true);
        $timer = $this->input->post('waktu', true);
        $id_siswa = $this->input->post('siswa', true);
        $id_jadwal = $this->input->post('jadwal', true);
        $elapsed = $this->input->post('elapsed', true);

        $id_durasi = $id_siswa .'0'. $id_jadwal;
        if ($elapsed != '0') {
            $this->db->set('lama_ujian', $elapsed);
            $this->db->where('id_durasi', $id_durasi);
            $this->db->update('cbt_durasi_siswa');
        }

        $update = true;
        $jawab = $this->input->post('data', false);
        if ($jawab != null && isset($jawab['jenis'])) {
            if ($jawab['jenis'] == 1) {
                $this->db->set('jawaban_alias', $jawab['jawaban_alias']);
                $this->db->set('jawaban_siswa', $jawab['jawaban_siswa']);
            } elseif ($jawab['jenis'] == 2) {
                $this->db->set('jawaban_alias', '');
                $this->db->set('jawaban_siswa', serialize(json_decode($jawab['jawaban_siswa'])));
            } elseif ($jawab['jenis'] == 3) {
                $this->db->set('jawaban_alias', '');
                $this->db->set('jawaban_siswa', serialize(json_decode($jawab['jawaban_siswa'])));
            } elseif ($jawab['jenis'] == 4) {
                $jawab_isian = $this->input->post('jawaban', false);
                $this->db->set('jawaban_alias', '');
                $this->db->set('jawaban_siswa', $jawab_isian);
            } else {
                $this->db->set('jawaban_alias', '');
                $this->db->set('jawaban_siswa', $jawab['jawaban_siswa']);
            }
            $this->db->where('id_soal_siswa', $jawab['id_soal_siswa']);
            $update = $this->db->update('cbt_soal_siswa');
            //sleep(1);
        }
        $data['status'] = $update;

        if ($update && $id_bank != null) {
            $arrJawaban = [];
            $terjawab = $this->cbt->getJumlahJawaban($id_bank, $id_siswa);
            foreach ($terjawab as $jawab) {
                if ($jawab->jawaban_siswa != null && $jawab->jawaban_siswa != "") {
                    $arrJawaban[] = $jawab;
                }
            }
            $data['soal_terjawab'] = count($arrJawaban);
        }

        if ($update && $timer !=null) {
            $this->selesaiUjian();
        }

        $this->output_json($data);
    }

    public function selesaiUjian(){
        $this->load->model('Cbt_model', 'cbt');
        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');
        //$id_bank = $this->input->post('bank');

        //$data['status_nilai'] = $this->olahNilai($id_siswa, $id_jadwal);

        $this->db->set('selesai', date('Y-m-d H:i:s'));
        $this->db->set('status', 2);
        $this->db->where('id_durasi', $id_siswa .'0'. $id_jadwal);
        $update = $this->db->update('cbt_durasi_siswa');

        $this->cbt->saveLog($id_siswa, $id_jadwal, 2, 'Menyelesaikan Ujian');

        $data['status'] = $update;
        $this->output_json($data);
    }

    public function resetTimer()
    {
        $id_durasi = $this->input->post('id_durasi', true);
        $reset = $this->input->post('reset', true);
        if ($reset == '1') {
            $this->db->set('lama_ujian', '00:00:00');
        }
        $this->db->set('reset', $reset);
        $this->db->where('id_durasi', $id_durasi);
        $update = $this->db->update('cbt_durasi_siswa');

        $data['status'] = $update;
        $this->output_json($data);
    }

    public function ulangiUjian($id_durasi, $id_bank){
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');

        //$id_durasi = $this->input->post('id', true);
        //$id_bank = $this->input->post('id_bank', true);
        $soals = $this->cbt->getAllSoalByBank($id_bank);
        if ($this->master->delete('cbt_durasi_siswa', $id_durasi, 'id_durasi')) {
            for ($i = 0; $i < 2; $i++) {
                foreach ($soals as $soal) {
                    $this->db->where('id_soal_siswa', $id_durasi . $id_bank . ($i + 1) . $soal->nomor_soal);
                    $this->db->delete('cbt_soal_siswa');
                    //$this->master->delete('cbt_soal_siswa', $id_durasi . $id_bank . ($i+1) . $soal->nomor_soal, 'id_soal_siswa');
                }
            }
            $data['status'] = true;
        } else {
            $data['status'] = false;
        }
        $this->output_json($data);
    }

    public function applyAction() {
        $this->load->model('Cbt_model', 'cbt');
        $json = json_decode($this->input->post('aksi', true));
        $id_jadwal = $this->input->post('jadwal', true);

        $this->db->trans_start();
        // reset izin
        $data['update_reset'] = true;
        if (count($json->reset) > 0) {
            $data['reset'] = true;
            $this->db->set('reset', 1);
            $this->db->where_in('id_log', $json->reset);
            $this->db->update('log_ujian');
        }

        // selesai
        $data['update_selesai'] = true;
        if (count($json->force)>0) {
            $data['selesai'] = true;
            foreach ($json->log as $ids) {
                //$data['status_nilai'] = $this->olahNilai($ids, $id_jadwal);
                $this->cbt->saveLog($ids, $id_jadwal, 2, 'Menyelesaikan Ujian');
            }

            $this->db->set('selesai', date('Y-m-d H:i:s'));
            $this->db->set('status', 2);
            $this->db->set('reset', 3);
            $this->db->where_in('id_durasi', $json->force);
            $data['update_selesai'] = $this->db->update('cbt_durasi_siswa');
        }

        // ulang
        $data['update_ulangi'] = true;
        if (count($json->ulang) > 0) {
            $data['ulangi'] = true;
            $this->db->where_in('id_durasi', $json->hapus);
            if ($this->db->delete('cbt_durasi_siswa')) {
                $this->db->where('id_jadwal', $id_jadwal);
                $this->db->where_in('id_siswa', $json->ulang);
                if ($this->db->delete('log_ujian')) {
                    $this->db->where('id_jadwal', $id_jadwal);
                    $this->db->where_in('id_siswa', $json->ulang);
                    if ($this->db->delete('cbt_nilai')) {
                        $this->db->where('id_jadwal', $id_jadwal);
                        $this->db->where_in('id_siswa', $json->ulang);
                        $data['update_ulangi'] = $this->db->delete('cbt_soal_siswa');
                    }
                }
            }
        }

        $this->db->trans_complete();
        $this->output_json($data);
    }

    public function olahNilai($id_siswa, $id_jadwal){
        $this->load->model('Cbt_model', 'cbt');
        $info = $this->cbt->getJadwalById($id_jadwal);
        $jawabans = $this->cbt->getJawabanByBank($info->id_bank, $id_siswa);
        $unseriali_jawaban = $this->unserializeJawabanSiswa($jawabans, false);
        $jawabans_siswa = $unseriali_jawaban['jawaban'];

        $ada_jawaban_isian = isset($jawabans_siswa['4']);
        $ada_jawaban_essai = isset($jawabans_siswa['5']);

        $bagi_pg = $info->tampil_pg / 100;
        $bobot_pg = $info->bobot_pg / 100;
        $bagi_pg2 = $info->tampil_kompleks / 100;
        $bobot_pg2 = $info->bobot_kompleks / 100;
        $bagi_jodoh = $info->tampil_jodohkan / 100;
        $bobot_jodoh = $info->bobot_jodohkan / 100;
        $bagi_isian = $info->tampil_isian / 100;
        $bobot_isian = $info->bobot_isian / 100;
        $bagi_essai = $info->tampil_esai / 100;
        $bobot_essai = $info->bobot_esai / 100;

        // PG
        $jawaban_pg = isset($jawabans_siswa['1']) ? $jawabans_siswa['1'] : [];
        $benar_pg = 0;
        $salah_pg = 0;
        if ($info->tampil_pg > 0) {
            if (count($jawaban_pg)>0) {
                foreach ($jawaban_pg as $jwb_pg) {
                    if ($jwb_pg != null && $jwb_pg->jawaban_siswa != null) {
                        if (strtoupper($jwb_pg->jawaban_siswa ?? '') == strtoupper($jwb_pg->jawaban_benar ?? '')) {
                            $benar_pg += 1;
                        } else {
                            $salah_pg += 1;
                        }
                    }
                }
            }
        }
        $skor_pg = $bagi_pg == 0 ? 0 : ($benar_pg / $bagi_pg) * $bobot_pg;

        $jawaban_pg2 = isset($jawabans_siswa['2']) ? $jawabans_siswa['2'] : [];
        $benar_pg2 = 0;
        $skor_koreksi_pg2 = 0.0;
        $otomatis_pg2 = 0;
        if ($info->tampil_kompleks > 0) {
            if (count($jawaban_pg2)>0) {
                foreach ($jawaban_pg2 as $num=>$jawab_pg2) {
                    $otomatis_pg2 = $jawab_pg2->nilai_otomatis;
                    $skor_koreksi_pg2 += $jawab_pg2->nilai_koreksi;
                    $arr_benar = [];
                    if (is_array($jawab_pg2->jawaban_siswa)) {
                        foreach ($jawab_pg2->jawaban_siswa as $js) {
                            if (in_array($js, $jawab_pg2->jawaban_benar)) {
                                $arr_benar[] = true;
                            }
                        }
                    }
                    if (count($jawab_pg2->jawaban_benar) > 0) {
                        $benar_pg2 += (1 / count($jawab_pg2->jawaban_benar)) * count($arr_benar);
                    }
                }
            }
        }
        $s_pg2 = $bagi_pg2 == 0 ? 0 : ($benar_pg2 / $bagi_pg2) * $bobot_pg2;
        $skor_pg2 = $otomatis_pg2 == 0 ? $s_pg2 : $skor_koreksi_pg2;

        $jawaban_jodoh = isset($jawabans_siswa['3']) ? $jawabans_siswa['3'] : [];
        $benar_jod = 0;
        $skor_koreksi_jod = 0.0;
        $otomatis_jod = 0;

        if (($info->tampil_jodohkan > 0) && $jawaban_jodoh && count($jawaban_jodoh) > 0) {
            foreach ($jawaban_jodoh as $num => $jawab_jod) {
                $skor_koreksi_jod += $jawab_jod->nilai_koreksi;

                $item_benar = 0;
                $item_salah = 0;
                $item_kurang = 0;
                $items = 0;
                $arrBenar = []; // count same each subitem
                $point_benar = $info->bobot_jodohkan > 0 ? round($info->bobot_jodohkan / $info->tampil_jodohkan, 2) : 0;

                if (isset($jawab_jod->jawaban_siswa->links)) {
                    $array1 = (array)$jawab_jod->jawaban_benar->links;
                    $this->sortArrays($array1);
                    $array2 = (array)$jawab_jod->jawaban_siswa->links;
                    $this->sortArrays($array2);
                    //$point_item = $point_benar / count($array1);

                    $sameCount = 0;
                    $differentCount = 0;

                    foreach ($array1 as $key => $subArray1) {
                        //$point_subitem = $point_item / count($subArray1);

                        $arrBenar[$key] = new stdClass();
                        $arrBenar[$key]->benar = 0;
                        $arrBenar[$key]->salah = 0;
                        $arrBenar[$key]->kurang = 0;
                        $items += count($subArray1);
                        if (isset($array2[$key])) {
                            $subArray2 = $array2[$key];

                            // Count the same items
                            $sameItems = array_intersect($subArray1, $subArray2);
                            $item_benar += count($sameItems);
                            $arrBenar[$key]->benar += count($sameItems);

                            // Count the different items
                            $diffItems1 = array_diff($subArray1, $subArray2);
                            $diffItems2 = array_diff($subArray2, $subArray1);
                            $arrBenar[$key]->kurang += count($diffItems1);
                        } else {
                            // If the key doesn't exist in the second array, all items are different
                            $arrBenar[$key]->kurang += count($subArray1);
                        }
                    }
                }
                $point_soal = ((1 / $items) * $item_benar) * $point_benar;
                $benar_jod += (1 / $items) * $item_benar;
                $otomatis_jod = $jawab_jod->nilai_otomatis;
            }
        }
        $s_jod = $bagi_jodoh == 0 ? 0 : ($benar_jod / $bagi_jodoh) * $bobot_jodoh;
        $skor_jod = $otomatis_jod == 0 ? $s_jod : $skor_koreksi_jod;

        $jawaban_is = $ada_jawaban_isian ? $jawabans_siswa['4'] : [];
        $benar_is = 0;
        $skor_koreksi_is = 0.0;
        $otomatis_is = 0;
        if ($info->tampil_isian > 0) {
            if (count($jawaban_is)>0) {
                foreach ($jawaban_is as $num=>$jawab_is) {
                    $skor_koreksi_is += $jawab_is->nilai_koreksi;
                    $benar = $jawab_is != null && strtolower($jawab_is->jawaban_siswa ?? '') == strtolower($jawab_is->jawaban_benar ?? '');
                    if ($benar) {
                        $benar_is ++;
                    }
                    $otomatis_is = $jawab_is->nilai_otomatis;
                }
            }
        }
        $s_is = $bagi_isian == 0 ? 0 : ($benar_is / $bagi_isian) * $bobot_isian;
        $skor_is = $otomatis_is == 0 ? $s_is : $skor_koreksi_is;

        $jawaban_es = $ada_jawaban_essai ? $jawabans_siswa['5'] : [];
        $benar_es = 0;
        $skor_koreksi_es = 0.0;
        $otomatis_es = 0;
        if ($info->tampil_esai > 0) {
            if (count($jawaban_es)>0) {
                foreach ($jawaban_es as $num=>$jawab_es) {
                    $skor_koreksi_es += $jawab_es->nilai_koreksi;
                    $benar = $jawab_es != null && strtolower($jawab_es->jawaban_siswa ?? '') == strtolower($jawab_es->jawaban_benar ?? '');
                    if ($benar) {
                        $benar_es ++;
                    }
                    $otomatis_es = $jawab_es->nilai_otomatis;
                }
            }
        }
        $s_es = $bagi_essai == 0 ? 0 : ($benar_es / $bagi_essai) * $bobot_essai;
        $skor_es = $otomatis_es == 0 ? $s_es : $skor_koreksi_es;

        $total = $skor_pg + $skor_pg2 + $skor_jod + $skor_is + $skor_es;

        $insert = [
            'id_nilai' => $id_siswa .'0'. $id_jadwal,
            'id_siswa' => $id_siswa,
            'id_jadwal' => $id_jadwal,
            'pg_benar' => $benar_pg,
            'pg_nilai' => round($skor_pg, 2),
            'kompleks_nilai' => round($skor_pg2, 2),
            'jodohkan_nilai' => round($skor_jod, 2),
            'isian_nilai' => round($skor_is, 2),
            'essai_nilai' => round($skor_es, 2)
        ];
        return $this->db->replace('cbt_nilai', $insert);
        //return $info;
    }

    public function hasil()
    {
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
            'judul' => 'Nilai',
            'subjudul' => 'Nilai Hasil Belajar',
            'setting' => $this->dashboard->getSetting()
        ];

        $logs = $this->kelas->getNilaiMateriSiswa($siswa->id_siswa);
        $data['nilai_materi'] = $logs[1] ?? [];
        $data['nilai_tugas'] = $logs[2] ?? [];

        $arr_jadwal = [];
        $kelass_unset = [];
        $this->db->trans_start();
        $jadwals = $this->cbt->getJadwalByKelas($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        foreach ($jadwals as $kj=>$jadwal) {
            if ($jadwal->soal_agama != '-' && $jadwal->soal_agama != '0' && $jadwal->soal_agama !== $siswa->agama) {
                unset($jadwals[$kj]);
            } else {
                $arr_jadwal[] = $kj;
            }
            $kelass = $this->maybe_unserialize($jadwal->bank_kelas ?? '');
            $arr_kls_jadwal = [];
            foreach ($kelass as $kll) {
                foreach ($kll as $kl) {
                    if ($kl != null) {
                        $arr_kls_jadwal[] = $kl;
                    }
                }
            }

            if (!in_array($siswa->id_kelas, $arr_kls_jadwal)) {
                unset($jadwals[$kj]);
                $kelass_unset[] = $kj;
            } else {
                $jadwal->bank_kelas = $kelass;
            }
        }

        $durasies = $this->cbt->getDurasiSiswaByArrJadwal($arr_jadwal, $siswa->id_siswa);
        $nilai_inputs = $this->cbt->getNilaiSiswaByArrJadwal($arr_jadwal, $siswa->id_siswa);
        $temp_jawabans = $this->cbt->getJawabanSiswaByArrJadwal($arr_jadwal, $siswa->id_siswa);
        $all_jawabans_siswa = [];
        foreach ($temp_jawabans as $id_jdwl=>$jawaban) {
            $unseriali_jawaban = $this->unserializeJawabanSiswa($jawaban);
            $all_jawabans_siswa[$id_jdwl] = $unseriali_jawaban['jawaban'];
        }

        $skors = [];
        foreach ($jadwals as $kj=>$jadwal) {
            $jawabans_siswa = $all_jawabans_siswa[$kj];
            $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? '');
            $info = $jadwal;
            $bagi_pg = $info->tampil_pg / 100;
            $bobot_pg = $info->bobot_pg / 100;
            $bagi_pg2 = $info->tampil_kompleks / 100;
            $bobot_pg2 = $info->bobot_kompleks / 100;
            $bagi_jodoh = $info->tampil_jodohkan / 100;
            $bobot_jodoh = $info->bobot_jodohkan / 100;
            $bagi_isian = $info->tampil_isian / 100;
            $bobot_isian = $info->bobot_isian / 100;
            $bagi_essai = $info->tampil_esai / 100;
            $bobot_essai = $info->bobot_esai / 100;

            $ada_jawaban = isset($jawabans_siswa[$siswa->id_siswa]);
            $ada_jawaban_pg = $ada_jawaban && isset($jawabans_siswa[$siswa->id_siswa]['1']);
            $ada_jawaban_pg2 = $ada_jawaban && isset($jawabans_siswa[$siswa->id_siswa]['2']);
            $ada_jawaban_jodoh = $ada_jawaban && isset($jawabans_siswa[$siswa->id_siswa]['3']);
            $ada_jawaban_isian = $ada_jawaban && isset($jawabans_siswa[$siswa->id_siswa]['4']);
            $ada_jawaban_essai = $ada_jawaban && isset($jawabans_siswa[$siswa->id_siswa]['5']);

            $skor = new stdClass();
            $nilai_input = $nilai_inputs[$jadwal->id_jadwal] ?? [];
            if ($nilai_input != null) {
                $skor->dikoreksi = $nilai_input->dikoreksi;
            }

            // PG
            $jawaban_pg = $ada_jawaban_pg ? $jawabans_siswa[$siswa->id_siswa]['1'] : [];
            $benar_pg = 0;
            $salah_pg = 0;
            if ($info->tampil_pg > 0) {
                if (count($jawaban_pg) > 0) {
                    foreach ($jawaban_pg as $num => $jwb_pg) {
                        $benar = false;
                        if ($jwb_pg != null && $jwb_pg->jawaban_siswa != null) {
                            if (strtoupper($jwb_pg->jawaban_siswa ?? '') == strtoupper($jwb_pg->jawaban ?? '')) {
                                $benar_pg += 1;
                                $benar = true;
                            } else {
                                $salah_pg += 1;
                                $benar = false;
                            }
                        }
                    }
                }
            }
            $skor->skor_pg = $skor_pg = $bagi_pg == 0 ? 0 : round(($benar_pg / $bagi_pg) * $bobot_pg, 2);
            $skor->benar_pg = $benar_pg;

            // PG2
            $jawaban_pg2 = $ada_jawaban_pg2 ? $jawabans_siswa[$siswa->id_siswa]['2'] : [];
            $benar_pg2 = 0;
            $skor_koreksi_pg2 = 0.0;
            $otomatis_pg2 = 0;
            if ($info->tampil_kompleks > 0) {
                if (count($jawaban_pg2) > 0) {
                    foreach ($jawaban_pg2 as $num => $jawab_pg2) {
                        $skor_koreksi_pg2 += $jawab_pg2->nilai_koreksi;
                        $arr_benar = [];
                        if ($jawab_pg2->jawaban_siswa) {
                            foreach ($jawab_pg2->jawaban_siswa as $js) {
                                if (in_array($js, $jawab_pg2->jawaban)) {
                                    $arr_benar[] = true;
                                }
                            }
                        }
                        if (count($jawab_pg2->jawaban) > 0) {
                            $benar_pg2 += (1 / count($jawab_pg2->jawaban)) * count($arr_benar);
                        }
                        //$point_benar = $info->bobot_kompleks > 0 ? round($info->bobot_kompleks / $info->tampil_kompleks, 2) : 0;
                        //$point_item = count($jawab_pg2->jawaban) > 0 ? $point_benar / count($jawab_pg2->jawaban) : 0;
                        //$pk = $point_item * count($arr_benar);

                        $jml_benar = count($arr_benar);
                        $otomatis_pg2 = $jawab_pg2->nilai_otomatis;
                    }
                }
            }
            $s_pg2 = $bagi_pg2 == 0 ? 0 : ($benar_pg2 / $bagi_pg2) * $bobot_pg2;
            $input_pg2 = 0;
            if ($nilai_input != null && $nilai_input->kompleks_nilai != null) {
                $input_pg2 = $nilai_input->kompleks_nilai;
            }
            $skor_pg2 = $input_pg2 != 0 ? $input_pg2 : ($otomatis_pg2 == 0 ? $s_pg2 : $skor_koreksi_pg2);
            $skor->skor_kompleks = round($skor_pg2, 2);
            $skor->benar_kompleks = round($benar_pg2, 2);

            // JODOHKAN
            $jawaban_jodoh = $ada_jawaban_jodoh ? $jawabans_siswa[$siswa->id_siswa]['3'] : [];
            $benar_jod = 0;
            $skor_koreksi_jod = 0.0;
            $otomatis_jod = 0;

            if (($info->tampil_jodohkan > 0) && $jawaban_jodoh && count($jawaban_jodoh) > 0) {
                foreach ($jawaban_jodoh as $num => $jawab_jod) {
                    $skor_koreksi_jod += $jawab_jod->nilai_koreksi;

                    $item_benar = 0;
                    $item_salah = 0;
                    $item_kurang = 0;
                    $items = 0;
                    $arrBenar = []; // count same each subitem
                    $point_benar = $info->bobot_jodohkan > 0 ? round($info->bobot_jodohkan / $info->tampil_jodohkan, 2) : 0;

                    if (isset($jawab_jod->jawaban_siswa->links)) {
                        $array1 = (array)$jawab_jod->jawaban_benar->links;
                        $this->sortArrays($array1);
                        $array2 = (array)$jawab_jod->jawaban_siswa->links;
                        $this->sortArrays($array2);

                        foreach ($array1 as $key => $subArray1) {
                            $arrBenar[$key] = new stdClass();
                            $arrBenar[$key]->benar = 0;
                            $arrBenar[$key]->salah = 0;
                            $arrBenar[$key]->kurang = 0;
                            $items += count($subArray1);
                            if (isset($array2[$key])) {
                                $subArray2 = $array2[$key];

                                $sameItems = array_intersect($subArray1, $subArray2);
                                $item_benar += count($sameItems);
                                $arrBenar[$key]->benar += count($sameItems);

                                $diffItems1 = array_diff($subArray1, $subArray2);
                                $diffItems2 = array_diff($subArray2, $subArray1);
                                $arrBenar[$key]->kurang += count($diffItems1);
                            } else {
                                $arrBenar[$key]->kurang += count($subArray1);
                            }
                        }
                    }
                    $point_soal = ((1 / $items) * $item_benar) * $point_benar;
                    $benar_jod += (1 / $items) * $item_benar;
                    $otomatis_jod = $jawab_jod->nilai_otomatis;
                }
            }

            $s_jod = $bagi_jodoh == 0 ? 0 : ($benar_jod / $bagi_jodoh) * $bobot_jodoh;
            $input_jod = 0;
            if ($nilai_input != null && $nilai_input->jodohkan_nilai != null) {
                $input_jod = $nilai_input->jodohkan_nilai;
            }
            $skor_jod = $input_jod != 0 ? $input_jod : ($otomatis_jod == 0 ? $s_jod : $skor_koreksi_jod);
            $skor->skor_jodohkan = round($skor_jod, 2);
            $skor->benar_jodohkan = round($benar_jod, 2);

            // ISIAN
            $jawaban_is = $ada_jawaban_isian ? $jawabans_siswa[$siswa->id_siswa]['4'] : [];
            $benar_is = 0;
            $skor_koreksi_is = 0.0;
            $otomatis_is = 0;
            if ($info->tampil_isian > 0) {
                if (count($jawaban_is) > 0) {
                    foreach ($jawaban_is as $num => $jawab_is) {
                        $skor_koreksi_is += $jawab_is->nilai_koreksi;
                        $benar = $jawab_is != null && strtolower($jawab_is->jawaban_siswa ?? '') == strtolower($jawab_is->jawaban ?? '');
                        if ($benar) {
                            $benar_is++;
                        }
                        $otomatis_is = $jawab_is->nilai_otomatis;
                    }
                }
            }
            $s_is = $bagi_isian == 0 ? 0 : ($benar_is / $bagi_isian) * $bobot_isian;
            $input_is = 0;
            if ($nilai_input != null && $nilai_input->isian_nilai != null) {
                $input_is = $nilai_input->isian_nilai;
            }
            $skor_is = $input_is != 0 ? $input_is : ($otomatis_is == 0 ? $s_is : $skor_koreksi_is);
            $skor->skor_isian = round($skor_is, 2);
            $skor->benar_isian = $benar_is;

            // ESSAI
            $jawaban_es = $ada_jawaban_essai ? $jawabans_siswa[$siswa->id_siswa]['5'] : [];
            $benar_es = 0;
            $skor_koreksi_es = 0.0;
            $otomatis_es = 0;
            if ($info->tampil_esai > 0) {
                if (count($jawaban_es) > 0) {
                    foreach ($jawaban_es as $num => $jawab_es) {
                        $skor_koreksi_es += $jawab_es->nilai_koreksi;
                        $benar = $jawab_es != null && strtolower($jawab_es->jawaban_siswa ?? '') == strtolower($jawab_es->jawaban ?? '');
                        if ($benar) {
                            $benar_es++;
                        }
                        $otomatis_es = $jawab_es->nilai_otomatis;
                    }
                }
            }
            $s_es = $bagi_essai == 0 ? 0 : ($benar_es / $bagi_essai) * $bobot_essai;
            $input_es = 0;
            if ($nilai_input != null && $nilai_input->isian_nilai != null) {
                $input_es = $nilai_input->essai_nilai;
            }
            $skor_es = $input_es != 0 ? $input_es : ($otomatis_es == 0 ? $s_es : $skor_koreksi_es);
            $skor->skor_essai = round($skor_es, 2);
            $skor->benar_esai = $benar_es;

            $total = $skor_pg + $skor_pg2 + $skor_jod + $skor_is + $skor_es;
            $skor->skor_total = round($total, 2);

            $skors[$jadwal->id_jadwal] = $skor;
        }

        $this->db->trans_complete();

        $data['skor'] = $skors;
        $data['durasi'] = $durasies;

        $data['jadwal'] = $jadwals;
        //$data['jawaban'] = $jawabans;
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();
        $data['kelass'] = $kelass_unset;

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/nilai/data');
        $this->load->view('members/siswa/templates/footer');
    }

    public function catatan()
    {
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
            'judul' => 'Catatan',
            'subjudul' => 'Catatan Dari Guru',
            'setting' => $this->dashboard->getSetting()
        ];

        $catatan_mapel = $this->kelas->getCatatanMapelBySiswa($siswa->id_kelas, $tp->id_tp, $smt->id_smt);
        $catatan = [];
        foreach ($catatan_mapel as $cat) {
            if (($cat->type === '2' && $cat->id_siswa === $siswa->id_siswa) || ($cat->type === '1' && $cat->id_kelas === $siswa->id_kelas)) {
                $catatan[] = [
                    'id_catatan' => $cat->id_catatan,
                    'nama_guru' => $cat->nama_guru,
                    'foto_guru' => $cat->foto && file_exists($cat->foto) ? $cat->foto : 'uploads/profiles/'.$cat->nip. (file_exists('uploads/profiles/'.$cat->nip.'.jpg') ? '.jpg':'.png'),
                    'id_siswa' => $siswa->id_siswa,
                    'tgl' => $cat->tgl,
                    'table' => 'mapel',
                    'level' => $cat->level,
                    'type' => $cat->type,
                    'readed' => $cat->readed,
                    'reading' => $this->maybe_unserialize($cat->reading ?? '')
                ];
            }
        }

        $catatan_siswa = $this->kelas->getCatatanSiswaBySiswa($siswa->id_kelas, $tp->id_tp, $smt->id_smt);
        foreach ($catatan_siswa as $cat) {
            if (($cat->type === '2' && $cat->id_siswa === $siswa->id_siswa) || ($cat->type === '1' && $cat->id_kelas === $siswa->id_kelas)) {
                $catatan[] = [
                    'id_catatan' => $cat->id_catatan,
                    'nama_guru' => $cat->nama_guru,
                    'foto_guru' => $cat->foto && file_exists($cat->foto) ? $cat->foto : 'uploads/profiles/'.$cat->nip. (file_exists('uploads/profiles/'.$cat->nip.'.jpg') ? '.jpg':'.png'),
                    'id_siswa' => $siswa->id_siswa,
                    'tgl' => $cat->tgl,
                    'table' => 'wali',
                    'level' => $cat->level,
                    'readed' => $cat->readed,
                    'type' => $cat->type,
                    'reading' => $this->maybe_unserialize($cat->reading ?? '')
                ];
            }
        }

        rsort($catatan);
        $data['catatan'] = (array)json_decode(json_encode($catatan));
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/catatan/data');
        $this->load->view('members/siswa/templates/footer');
    }

    public function detailCatatan($table, $id_catatan)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        if ($siswa && $table === 'mapel') {
            $detail = $this->kelas->getCatatanMapelSiswaDetail($id_catatan);
        } else {
            $detail = $this->kelas->getCatatanKelasSiswaDetail($id_catatan);
        }
        $reading = [];
        if ($detail) {
            $detail->id_siswa = $siswa->id_siswa;
            $reading = $detail->reading != null ? $this->maybe_unserialize($detail->reading ?? '') : [];
        }
        $this->output_json(['reading' => $reading, 'detail' => $detail]);
    }

    public function readed($table, $id_catatan)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if ($table == 'mapel') {
            $tbl = 'kelas_catatan_mapel';
        } else {
            $tbl = 'kelas_catatan_wali';
        }
        $cat = $this->kelas->getReading($tbl, $id_catatan);
        $readed = $cat->readed == '0' ? date('Y-m-d H:i:s') : '0';
        if ($cat->type == '1') {
            $reading = $this->maybe_unserialize($cat->reading ?? '');
            if (!in_array($siswa->id_siswa, $reading)) {
                $reading[] = $siswa->id_siswa;
            }
            $this->db->set('reading', serialize($reading));
        } else {
            $this->db->set('readed', $readed);
        }
        $this->db->where('id_catatan', $id_catatan);
        $update = $this->db->update($tbl);

        $this->output_json($update);
    }

    public function getTimer($id_siswa, $id_jadwal)
    {
        $this->load->model('Cbt_model', 'cbt');
        $data['durasi'] = $this->cbt->getDurasiSiswa($id_siswa .'0'. $id_jadwal);
        $this->output_json($data);
    }

    function total_hari($id_day, $bulan, $taun) {
        $days = 0;
        $dates = [];
        $total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $taun);
        $idday = $id_day == '7' ? 0 : $id_day;
        for($i=1;$i<$total_days;$i++) {
            if (date('N', strtotime($taun.'-'.$bulan.'-'.$i)) == $idday) {
                $days ++;
                $dates[] = date('Y-m-d', strtotime($taun . '-' . $bulan . '-' . $i));
            }
        }
        return $dates;//array('days' =>$days, 'dates'=>$dates);
    }
}
