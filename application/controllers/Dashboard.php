<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller
 * 
 * Manages the main dashboard for different user roles (admin, guru, siswa, pengawas).
 * Displays relevant information and statistics based on user role.
 * 
 * @author AbangAzmi
 * @date 02/06/2020
 */
class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Redirect to auth if not logged in
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }

        // Load required models
        $this->load->model([
            'Master_model' => 'master',
            'Dashboard_model' => 'dashboard',
            'Log_model' => 'logging',
            'Dropdown_model' => 'dropdown',
            'Cbt_model' => 'cbt'
        ]);
    }

    /**
     * Generate admin dashboard info boxes
     * 
     * @param object $setting School settings
     * @param string $tp Active academic year ID
     * @param string $smt Active semester ID
     * @return array Array of info box objects
     */
    private function admin_box($setting, $tp, $smt)
    {
        $where = $this->get_jenjang_where($setting->jenjang);

        return json_decode(json_encode([
            [
                'box' => 'blue',
                'total' => $this->dashboard->total('master_siswa'),
                'title' => 'Siswa',
                'url' => 'datasiswa',
                'icon' => 'users'
            ],
            [
                'box' => 'cyan',
                'total' => $this->dashboard->total('master_kelas', "id_tp=$tp AND id_smt=$smt"),
                'title' => 'Rombel',
                'url' => 'datakelas',
                'icon' => 'bell'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboard->total('master_guru'),
                'title' => 'Guru',
                'url' => 'dataguru',
                'icon' => 'user'
            ],
            [
                'box' => 'fuchsia',
                'total' => $this->dashboard->totalWaliKelas($tp, $smt),
                'title' => 'Wali Kelas',
                'url' => 'dataguru',
                'icon' => 'user'
            ],
            [
                'box' => 'success',
                'total' => $this->dashboard->total('master_mapel', $where),
                'title' => 'Mapel',
                'url' => 'datamapel',
                'icon' => 'book'
            ],
            [
                'box' => 'yellow',
                'total' => $this->dashboard->total('master_ekstra'),
                'title' => 'Ekstrakurikuler',
                'url' => 'dataekstra',
                'icon' => 'book'
            ]
        ]), false);
    }

    /**
     * Generate guru dashboard info boxes
     * 
     * @param object $setting School settings
     * @return array Array of info box objects
     */
    private function guru_box($setting)
    {
        $where = $this->get_jenjang_where($setting->jenjang);

        return json_decode(json_encode([
            [
                'box' => 'teal',
                'total' => $this->dashboard->total('master_kelas'),
                'title' => 'Rombel',
                'icon' => 'user'
            ],
            [
                'box' => 'blue',
                'total' => $this->dashboard->total('master_siswa'),
                'title' => 'Siswa',
                'icon' => 'users'
            ],
            [
                'box' => 'fuchsia',
                'total' => $this->dashboard->total('master_guru'),
                'title' => 'Guru',
                'icon' => 'user'
            ],
            [
                'box' => 'success',
                'total' => $this->dashboard->total('master_mapel', $where),
                'title' => 'Mapel',
                'icon' => 'book'
            ]
        ]), false);
    }

    /**
     * Generate pengawas dashboard info boxes
     * 
     * @param string $tp Active academic year ID
     * @param string $smt Active semester ID
     * @return array Array of info box objects
     */
    private function pengawas_box($tp, $smt)
    {
        return json_decode(json_encode([
            [
                'box' => 'indigo',
                'total' => $this->dashboard->total('cbt_ruang'),
                'title' => 'Ruang Ujian',
                'url' => 'cbtruang',
                'icon' => 'school'
            ],
            [
                'box' => 'maroon',
                'total' => $this->dashboard->total('cbt_sesi'),
                'title' => 'Sesi',
                'url' => 'cbtsesi',
                'icon' => 'clock'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboard->totalPengawas($tp, $smt),
                'title' => 'Pengawas',
                'url' => 'cbtpengawas',
                'icon' => 'user'
            ],
            [
                'box' => 'green',
                'total' => $this->dashboard->totalJadwal($tp, $smt),
                'title' => 'Jadwal Ujian',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ]
        ]), false);
    }

    /**
     * Generate ujian dashboard info boxes
     * 
     * @param string $tp Active academic year ID
     * @param string $smt Active semester ID
     * @return array Array of info box objects
     */
    private function ujian_box($tp, $smt)
    {
        return json_decode(json_encode([
            [
                'box' => 'indigo',
                'total' => $this->dashboard->total('cbt_ruang'),
                'title' => 'Ruang Ujian',
                'url' => 'cbtruang',
                'icon' => 'school'
            ],
            [
                'box' => 'maroon',
                'total' => $this->dashboard->total('cbt_sesi'),
                'title' => 'Sesi',
                'url' => 'cbtsesi',
                'icon' => 'clock'
            ],
            [
                'box' => 'green',
                'total' => $this->dashboard->total('cbt_bank_soal'),
                'title' => 'Bank Soal',
                'url' => 'cbtbanksoal',
                'icon' => 'folder'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboard->totalPengawas($tp, $smt),
                'title' => 'Pengawas',
                'url' => 'cbtpengawas',
                'icon' => 'user'
            ],
            [
                'box' => 'yellow',
                'total' => $this->dashboard->totalJadwal($tp, $smt),
                'title' => 'Jadwal',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ]
        ]), false);
    }

    /**
     * Generate siswa menu boxes
     * 
     * @return array Array of menu box objects
     */
    private function menu_siswa_box()
    {
        return json_decode(json_encode([
            [
                'title' => 'Jadwal Pelajaran',
                'icon' => 'ic_online.png',
                'link' => 'siswa/jadwalpelajaran'
            ],
            [
                'title' => 'Materi',
                'icon' => 'ic_elearning.png',
                'link' => 'siswa/materi'
            ],
            [
                'title' => 'Tugas',
                'icon' => 'ic_questions.png',
                'link' => 'siswa/tugas'
            ],
            [
                'title' => 'Ujian / Ulangan',
                'icon' => 'ic_question.png',
                'link' => 'siswa/cbt'
            ],
            [
                'title' => 'Nilai Hasil',
                'icon' => 'ic_exam.png',
                'link' => 'siswa/hasil'
            ],
            [
                'title' => 'Absensi',
                'icon' => 'ic_clipboard.png',
                'link' => 'siswa/kehadiran'
            ],
            [
                'title' => 'Catatan Guru',
                'icon' => 'ic_student.png',
                'link' => 'siswa/catatan'
            ]
        ]), false);
    }

    /**
     * Get jenjang where clause based on setting
     * 
     * @param string $jenjang School level
     * @return string Where clause for database query
     */
    private function get_jenjang_where($jenjang)
    {
        return $jenjang == '1' ? 'jenjang=0 OR jenjang=1' : ($jenjang == '2' ? 'jenjang=2 OR jenjang=1' : '');
    }

    /**
     * Main dashboard index
     */
    public function index()
    {
        $setting = $this->dashboard->getSetting();
        $user = $this->ion_auth->user()->row();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Beranda',
            'subjudul' => 'Halaman Utama',
            'setting' => $setting,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'kelases' => $tp ? $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt) : []
        ];

        // Load daily schedule and KBM
        $day = date('N');
        $jadwal = $this->dashboard->loadJadwalHariIni($tp->id_tp, $smt->id_smt, null, $day);
        $kbms = $this->dashboard->getJadwalKbm($tp->id_tp, $smt->id_smt);
        foreach ($kbms as $kbm) {
            $kbm->istirahat = $this->maybe_unserialize($kbm->istirahat);
        }

        $arrJadwalKelas = [];
        foreach ($jadwal as $item) {
            $arrJadwalKelas[$item->id_kelas][$item->jam_ke] = $item;
        }

        $arrKbm = [];
        foreach ($kbms as $item) {
            $arrKbm[$item->id_kelas] = $item;
        }

        // Role-based dashboard rendering
        if ($this->ion_auth->in_group('siswa')) {
            $this->render_siswa_dashboard($data, $user, $tp, $smt, $arrJadwalKelas, $arrKbm);
        } elseif ($this->ion_auth->in_group('pengawas')) {
            $this->render_pengawas_dashboard($data, $user, $tp, $smt, $setting);
        } else {
            $this->render_admin_guru_dashboard($data, $user, $tp, $smt, $setting, $arrJadwalKelas, $arrKbm);
        }
    }

    /**
     * Render dashboard for siswa
     * 
     * @param array $data Base data
     * @param object $user User data
     * @param object $tp Active academic year
     * @param object $smt Active semester
     * @param array $arrJadwalKelas Schedule array
     * @param array $arrKbm KBM array
     */
    private function render_siswa_dashboard($data, $user, $tp, $smt, $arrJadwalKelas, $arrKbm)
    {
        $siswa = $this->dashboard->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        if (!$siswa) {
            $this->load->view('disable_login', $data);
            return;
        }

        $data['login'] = $siswa;
        $data['siswa'] = $siswa;
        $data['menu'] = $this->menu_siswa_box();
        $data['kbms'] = $arrKbm[$siswa->id_kelas] ?? null;
        $data['jadwals'] = $arrJadwalKelas[$siswa->id_kelas] ?? [];
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/dashboard');
        $this->load->view('members/siswa/templates/footer');
    }

    /**
     * Render dashboard for pengawas
     * 
     * @param array $data Base data
     * @param object $user User data
     * @param object $tp Active academic year
     * @param object $smt Active semester
     * @param object $setting School settings
     */
    private function render_pengawas_dashboard($data, $user, $tp, $smt, $setting)
    {
        $pengawas = $this->dashboard->getDataPengawasByUserId($user->id, $tp->id_tp, $smt->id_smt);
        if (!$pengawas) {
            $this->load->view('disable_login', $data);
            return;
        }

        $data['info_box'] = $this->pengawas_box($tp->id_tp, $smt->id_smt);
        $data['pengawas'] = $pengawas;
        $data['ada_ujian'] = $this->cbt->getDataJadwalByTgl(date('Y-m-d'));
        $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, []);
        $data['token'] = $this->get_token_data();

        $this->load->view('members/pengawas/templates/header', $data);
        $this->load->view('members/pengawas/dashboard');
        $this->load->view('members/pengawas/templates/footer');
    }

    /**
     * Render dashboard for admin or guru
     * 
     * @param array $data Base data
     * @param object $user User data
     * @param object $tp Active academic year
     * @param object $smt Active semester
     * @param object $setting School settings
     * @param array $arrJadwalKelas Schedule array
     * @param array $arrKbm KBM array
     */
    private function render_admin_guru_dashboard($data, $user, $tp, $smt, $setting, $arrJadwalKelas, $arrKbm)
    {
        $data['token'] = $this->get_token_data();
        $data['ada_ujian'] = $this->cbt->getDataJadwalByTgl(date('Y-m-d'));
        $data['jadwals'] = $arrJadwalKelas;
        $data['kbms'] = $arrKbm;
        $data['mapels'] = $this->master->getAllMapel();
        $data['jadwals_ujian'] = $this->get_jadwals_ujian($tp->id_tp, $smt->id_smt);
        $data['pengawas'] = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt, null, null);
        $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, []);
        $data['gurus'] = $this->dropdown->getAllGuru();

        if ($this->ion_auth->is_admin()) {
            $data['info_box'] = $this->admin_box($setting, $tp->id_tp, $smt->id_smt);
            $data['ujian_box'] = $this->ujian_box($tp->id_tp, $smt->id_smt);
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);

            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('dashboard');
            $this->load->view('_templates/dashboard/_footer');
        } elseif ($this->ion_auth->in_group('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if (!$guru) {
                $this->load->view('disable_login', $data);
                return;
            }

            $data['info_box'] = $this->admin_box($setting, $tp->id_tp, $smt->id_smt);
            $data['ujian_box'] = $this->ujian_box($tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;

            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('members/guru/dashboard');
            $this->load->view('members/guru/templates/footer');
        }
    }

    /**
     * Get exam schedules with peserta
     * 
     * @param string $tp_id Academic year ID
     * @param string $smt_id Semester ID
     * @return array Exam schedules
     */
    private function get_jadwals_ujian($tp_id, $smt_id)
    {
        $tglJadwals = $this->cbt->getAllJadwalByJenis(null, $tp_id, $smt_id);
        foreach ($tglJadwals as $tgl => $jadwalss) {
            foreach ($jadwalss as $mpl => $jadwals) {
                foreach ($jadwals as $jadwal) {
                    $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas);
                    foreach ($jadwal->bank_kelas as $kb) {
                        if ($kb['kelas_id'] != '') {
                            $jadwal->peserta[] = $this->cbt->getKelasUjian($kb['kelas_id']);
                        }
                    }
                }
            }
        }
        return $tglJadwals;
    }

    /**
     * Get token data
     * 
     * @return object Token data
     */
    private function get_token_data()
    {
        $token = $this->cbt->getToken();
        $default = ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00'];
        return $token ?: json_decode(json_encode($default));
    }

    /**
     * Check token and schedule
     */
    public function checkTokenJadwal()
    {
        $token = $this->cbt->getToken();
        $token->now = date('d-m-Y H:i:s');
        $this->output_json([
            'ada_ujian' => $this->cbt->getDataJadwalByTgl(date('Y-m-d')),
            'token' => $token
        ]);
    }

    /**
     * Output JSON response
     * 
     * @param mixed $data Data to output
     * @param bool $encode Whether to encode as JSON
     */
    public function output_json($data, $encode = true)
    {
        $this->output->set_content_type('application/json')
                     ->set_output($encode ? json_encode($data) : $data);
    }

    /**
     * Update active academic year
     */
    public function gantiTahun()
    {
        $aktif = $this->input->post('active', true);
        $tahuns = $this->input->post('tahun', true);
        $update = [];

        foreach ($tahuns as $i => $tahun) {
            $id_tp = $this->input->post("id_tp[$i]", true);
            $update[] = [
                'id_tp' => $id_tp,
                'tahun' => $tahun,
                'active' => ($id_tp === $aktif) ? 1 : 0
            ];
        }

        $this->dashboard->update('master_tp', $update, 'id_tp', null, true);
        $this->logging->saveLog(4, 'mengganti tahun ajaran aktif');
        $this->output_json(['update' => $update, 'status' => true]);
    }

    /**
     * Update active semester
     */
    public function gantiSemester()
    {
        $aktif = $this->input->post('active', true);
        $smts = $this->input->post('smt', true);
        $update = [];

        foreach ($smts as $i => $smt) {
            $id_smt = $this->input->post("id_smt[$i]", true);
            $update[] = [
                'id_smt' => $id_smt,
                'smt' => $smt,
                'active' => ($id_smt === $aktif) ? 1 : 0
            ];
        }

        $this->dashboard->update('master_smt', $update, 'id_smt', null, true);
        $this->logging->saveLog(4, 'mengganti semester aktif');
        $this->output_json(['update' => $update, 'status' => true]);
    }

    /**
     * Get notifications (placeholder)
     */
    public function getNotifikasi()
    {
        // Implement notification logic here
    }

    /**
     * Get activity logs
     * 
     * @param int $limit Number of logs to retrieve
     */
    public function getLog($limit)
    {
        $this->output_json($this->logging->loadAktifitas($limit));
    }

    /**
     * Delete all logs
     */
    public function hapusLog()
    {
        $this->db->trans_start();
        $result = $this->db->empty_table('log');
        $this->db->trans_complete();

        $this->output_json([
            'status' => $result,
            'message' => $result ? 'berhasil' : 'gagal'
        ]);
    }

    /**
     * Get student activity logs
     * 
     * @param int $limit Number of logs to retrieve
     */
    public function getLogSiswa($limit)
    {
        $this->output_json($this->logging->loadAktifitasSiswa($limit));
    }

    /**
     * Get announcements
     * 
     * @param string $for Target audience
     */
    public function getPengumuman($for)
    {
        $this->output_json($this->dashboard->loadPengumuman($for));
    }

    /**
     * Get daily schedule
     * 
     * @param string $id_kelas Class ID
     * @param int $id_hari Day ID
     */
    public function getJadwalHariIni($id_kelas, $id_hari)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $this->output_json($this->dashboard->loadJadwalHariIni($tp->id_tp, $smt->id_smt, $id_kelas, $id_hari));
    }

    /**
     * Get KBM schedule
     * 
     * @param string $id_kelas Class ID
     */
    public function getJadwalKbm($id_kelas)
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $jadwal = $this->dashboard->getJadwalKbm($tp->id_tp, $smt->id_smt, $id_kelas);
        $istirahat = $this->maybe_unserialize($jadwal->istirahat);

        $this->output_json(['jadwal' => $jadwal, 'istirahat' => $istirahat]);
    }
}