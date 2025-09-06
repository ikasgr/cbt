<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dataalumni extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
		} else if (!$this->ion_auth->is_admin()) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
        $this->load->library('upload');
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Kelas_model', 'kelas');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Rapor_model', 'rapor');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function output_json($data, $encode = true) {
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

    public function index() {
        $tahun = $this->input->get('tahun', true);
        $kelas_akhir = $this->input->get('kelas', true);

        $user = $this->ion_auth->user()->row();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Data Kelulusan & Alumni',
            'subjudul' => 'Data Alumni',
            'setting'		=> $setting,
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $allTp = $this->dashboard->getTahun();
        $data['tp'] = $allTp;
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
        $data['tahun_lulus'] = $this->master->getDistinctTahunLulus();
        $data['kelas_akhir'] = $this->master->getDistinctKelasAkhir();
        $data['tahun_selected'] = $tahun;
        $data['kelas_selected'] = $kelas_akhir;

        $level = $setting->jenjang == '1' ? '6' : ($setting->jenjang == '2' ? '9' : ($setting->jenjang == '1' ? '3' : '12'));
        $jumlah_lulus = $this->rapor->getJumlahLulus(($tp->id_tp) - 1, '2', $level);

        $idSearch = array_search(($tp->id_tp) - 1, array_column($allTp, 'id_tp'));
        $tpBefore = $allTp[$idSearch]->tahun;
        $splitTahun = explode('/', $tpBefore ?? '');
        $alumnis = $this->master->getAlumniByTahun($splitTahun[1]);
        if ($jumlah_lulus > count($alumnis)) {
            $data['jumlah_lulus'] = $jumlah_lulus;
        } else {
            $data['jumlah_lulus'] = 0;
        }

        if ($tahun == null) {
            $count_siswa = $this->db->count_all('master_siswa');
            $count_induk = $this->db->count_all('buku_induk');

            if ($count_siswa > $count_induk) {
                $uids = $this->db->select('id_siswa, uid')->from('master_siswa')->get()->result();

                foreach ($uids as $uid) {
                    $check = $this->db->select('id_siswa')->from('buku_induk')->where('id_siswa', $uid->id_siswa);
                    if ($check->get()->num_rows() == 0) {
                        $this->db->insert('buku_induk', $uid);
                    }
                }
            }
        } elseif ($tahun != null && $tahun !='') {
            $data['alumnis'] = $this->master->getAlumniByTahun($tahun, $kelas_akhir);
        }

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/alumni/data');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function generateAlumni() {
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $allTp = $this->dashboard->getTahun();
        $searchId = array_search('1', array_column($allTp, 'active'));

        $idBefore = $allTp[($searchId) - 1]->id_tp;
        $tpBefore = $allTp[($searchId) - 1]->tahun;
        $splitTahun = explode('/', $tpBefore ?? '');

        $level = $setting->jenjang == '1' ? '6' : ($setting->jenjang == '2' ? '9' : ($setting->jenjang == '1' ? '3' : '12'));
        $siswas = $this->rapor->getSiswaLulus(($tp->id_tp) - 1, '2', $level);

        $ids = [];
        $this->db->trans_start();
        foreach ($siswas as $siswa) {
            if ($siswa->naik != null && $siswa->naik == '0') {
                //
            } else {
                $ids[] = $siswa->id_siswa;
                /*
                $data = [];
                foreach($siswa as $key=>$val) {
                    $data[$key] = $val;
                }
                $foto = $data['foto'];
                $def   = 'siswa';
                $pos = strpos($foto, $def);
                if ($pos !== false) {
                    $data['foto'] = 'uploads/profiles/'.$data['nis'].'.jpg';
                }
                $data['tahun_lulus'] = $splitTahun[1];
                $data['no_ijazah'] = '- -';
                unset($data["id_siswa"]);
                unset($data["username"]);
                unset($data["password"]);
                unset($data["naik"]);
                $this->db->insert('master_siswa', $data);
                */

                $this->db->where('id_siswa', $siswa->id_siswa);
                $this->db->set('status', '2');
                $this->db->set('tahun_lulus', $splitTahun[1]);
                $this->db->set('no_ijazah','- -');
                $this->db->set('kelas_akhir', $siswa->kelas_akhir);
                $this->db->update('buku_induk');

            }
        }

        /*
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $gurus = $this->master->getAllWaliKelas();
        $mapels = $this->master->getAllMapel();

        $all_nilai = [];
        $kelas_ekstra = $this->rapor->getAllEkstra();
        $setting_rapor = $this->rapor->getAllRaporSetting();
        $kkms = $this->rapor->getAllKkm();

        $nilai_rapor = $this->rapor->getAllNilaiRapor($ids);
        $nilai_extra = $this->rapor->getAllNilaiEkstra($ids);
        $nilai_sikap = $this->rapor->getAllNilaiSikap($ids);
        $rapor_fisik = $this->rapor->getAllFisik($ids);

        $nilai_hph = [];
        $nilai_hpts = [];
        $nilai_hpas = [];
        $nilai_nr = [];
        $nilai_ekstra = [];

        foreach ($nilai_rapor as $nilai) { //id_siswa
            $kkm_tunggal = $setting_rapor[$nilai->id_tp][$nilai->id_smt]->kkm_tunggal == "1";

            $kkm_mapel = null;
            $all_kkm = [];
            if (isset($kkms[$nilai->id_tp]) && isset($kkms[$nilai->id_tp][$nilai->id_smt]) &&
                isset($kkms[$nilai->id_tp][$nilai->id_smt][$nilai->id_kelas])) {
                $all_kkm = $kkms[$nilai->id_tp][$nilai->id_smt][$nilai->id_kelas];
                $kkm_mapel = isset($all_kkm[1]) && isset($all_kkm[1][$nilai->id_mapel]) ? $all_kkm[1][$nilai->id_mapel] : null;
            }

            foreach ($mapels as $mapel) {
                if ($mapel->id_mapel == $nilai->id_mapel) {
                    $nilai_hph[$nilai->id_siswa][] = [
                        'id_mapel' => $nilai->id_mapel,
                        'mapel' => $nilai->mapel,
                        'kkm' => $kkm_tunggal ? $setting_rapor[$nilai->id_tp][$nilai->id_smt]->kkm : ($kkm_mapel == null ? "" : $kkm_mapel->kkm),
                        'p_nilai' => $nilai->p_rata_rata,
                        'p_pred' => $nilai->p_predikat,
                        'p_desk' => $nilai->p_deskripsi,
                        'k_nilai' => $nilai->k_rata_rata,
                        'k_pred' => $nilai->k_predikat,
                        'k_desk' => $nilai->k_deskripsi
                    ];
                    $nilai_hpts[$nilai->id_siswa][] = [
                        'id_mapel' => $nilai->id_mapel,
                        'mapel' => $nilai->mapel,
                        'kkm' => $kkm_tunggal ? $setting_rapor[$nilai->id_tp][$nilai->id_smt]->kkm : ($kkm_mapel == null ? "" : $kkm_mapel->kkm),
                        'nilai' => $nilai->nilai_pts,
                        'pred' => $nilai->pts_predikat
                    ];
                    $nilai_hpas[$nilai->id_siswa][] = [
                        'id_mapel' => $nilai->id_mapel,
                        'mapel' => $nilai->mapel,
                        'kkm' => $kkm_tunggal ? $setting_rapor[$nilai->id_tp][$nilai->id_smt]->kkm : ($kkm_mapel == null ? "" : $kkm_mapel->kkm),
                        'nilai' => $nilai->nilai_pas,
                    ];
                    $nilai_nr[$nilai->id_siswa][] = [
                        'id_mapel' => $nilai->id_mapel,
                        'mapel' => $nilai->mapel,
                        'kkm' => $kkm_tunggal ? $setting_rapor[$nilai->id_tp][$nilai->id_smt]->kkm : ($kkm_mapel == null ? "" : $kkm_mapel->kkm),
                        'nilai' => $nilai->nilai_rapor,
                        'pred' => $nilai->rapor_predikat
                    ];
                }
            }

            $nilai_ekstra = [];
            if (isset($nilai_extra[$nilai->id_tp]) && isset($nilai_extra[$nilai->id_tp][$nilai->id_smt]) &&
                isset($nilai_extra[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa])) {

                foreach ($nilai_extra[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa] as $ekstra) {
                    $kkm_ekstra = "";
                    if (isset($all_kkm[2]) && isset($all_kkm[2][$ekstra->id_ekstra])) {
                        $kkm_ekstra = $all_kkm[2][$ekstra->id_ekstra]->kkm;
                    }
                    $nilai_ekstra[$nilai->id_siswa][] = [
                        'mapel' => $ekstra->kode_ekstra,
                        'id_ekstra' => $ekstra->id_ekstra,
                        'nama_ekstra' => $ekstra->nama_ekstra,
                        'kkm' => $kkm_tunggal ? $setting_rapor[$nilai->id_tp][$nilai->id_smt]->kkm : $kkm_ekstra,
                        'nilai' => $ekstra->nilai,
                        'pred' => $ekstra->predikat,
                        'desk' => $ekstra->deskripsi,
                    ];
                }
            }

            $spiritual = null;
            $sosial = null;
            if (isset($nilai_sikap[$nilai->id_tp]) &&
                isset($nilai_sikap[$nilai->id_tp][$nilai->id_smt]) &&
                isset($nilai_sikap[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa])) {
                $spiritual = isset($nilai_sikap[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa][1]) ?
                    $nilai_sikap[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa][1] : null;
                $sosial = isset($nilai_sikap[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa][2]) ?
                    $nilai_sikap[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa][2] : null;

            }

            $fisik = [];
            if (isset($rapor_fisik[$nilai->id_siswa])) {
                $fisik[] = $rapor_fisik[$nilai->id_siswa][$nilai->id_tp][$nilai->id_smt];
            }

            $all_nilai[$nilai->id_tp][$nilai->id_smt][$nilai->id_siswa] = [
                'uid' => $nilai->uid,
                'id_siswa' => $nilai->id_siswa,
                'tp' => $nilai->tahun,
                'smt' => $nilai->nama_smt,
                'kelas' => $nilai->nama_kelas,
                'level' => $nilai->level_id,
                'wali_kelas' => $nilai->nama_guru,
                'jurusan' => $nilai->nama_jurusan,
                'hph' => serialize(isset($nilai_hph[$nilai->id_siswa]) ? $nilai_hph[$nilai->id_siswa] : []),
                'hpts' => serialize(isset($nilai_hpts[$nilai->id_siswa]) ? $nilai_hpts[$nilai->id_siswa] : []),
                'hpas' => serialize(isset($nilai_hpas[$nilai->id_siswa]) ? $nilai_hpas[$nilai->id_siswa] : []),
                'nilai_rapor' => serialize(isset($nilai_nr[$nilai->id_siswa]) ? $nilai_nr[$nilai->id_siswa] : []),
                'ekstra' => serialize(isset($nilai_ekstra[$nilai->id_siswa]) ? $nilai_ekstra[$nilai->id_siswa] : ""),
                'spritual' => $spiritual == null ? serialize([]) : serialize([
                    'desk' => $spiritual->deskripsi,
                    'nilai' => $this->maybe_unserialize($spiritual->nilai)['predikat']
                ]),
                'sosial' => $sosial == null ? serialize([]) : serialize([
                    'desk' => $sosial->deskripsi,
                    'nilai' => $this->maybe_unserialize($sosial->nilai)['predikat']
                ]),
                'rank' => serialize([
                    'rank' => $nilai->ranking,
                    'saran' => $nilai->rank_deskripsi,
                ]),
                'prestasi' => serialize([
                    ['nilai' => $nilai->p1, 'desk' => $nilai->p1_desk],
                    ['nilai' => $nilai->p2, 'desk' => $nilai->p2_desk],
                    ['nilai' => $nilai->p3, 'desk' => $nilai->p3_desk]
                ]),
                'absen' => $nilai->absen != null ? $nilai->absen : serialize([]),
                'saran' => $nilai->saran != null ? $nilai->saran : "-",
                'fisik' => serialize($fisik),
                'naik' => $nilai->naik != null ? $nilai->naik : '1',
                'setting_rapor' => serialize((array)$setting_rapor[$nilai->id_tp][$nilai->id_smt]),
                'setting_mapel' => serialize((array)$mapels)
            ];
        }

        $insert = [];
        foreach ($tps as $tp) {
            foreach ($smts as $smt) {
                if (isset($all_nilai[$tp->id_tp]) && isset($all_nilai[$tp->id_tp][$smt->id_smt])) {
                    foreach ($all_nilai[$tp->id_tp][$smt->id_smt] as $nilai) {
                        if (!$this->rapor->exists($nilai['uid'], $nilai['tp'], $nilai['smt'], $nilai['kelas'])) {
                            $insert[] = $nilai;
                        }
                    }
                }
            }
        }

        if (count($insert)>0) {
            $this->db->insert_batch('buku_nilai', $insert);
        }
        */

        $this->db->trans_complete();

        $this->output_json($ids);
    }

    public function luluskan() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $posts = json_decode($this->input->post('kelas', true));
        $mode = $this->input->post('mode', true);

        $idkelases = [];
        $alumnikelas = [];
        foreach ($posts as $d) {
            $idkelases[] = $d->kelas_baru;
            $alumnikelas[$d->kelas_baru][] = ['id' => $d->id_siswa];
        }

        $idkelases = array_unique($idkelases);

        $res = [];
        $idks = [];
        foreach ($idkelases as $ik) {
            $kelas = $this->kelas->get_one($ik, ($tp->id_tp)-1, '2');
            //$kelasBaru = $this->kelas->get_one($ik, $tp->id_tp, $smt->id_smt);
            $kelas_baru = $this->kelas->getKelasByNama($kelas->nama_kelas, $tp->id_tp, $smt->id_smt);

            if ($kelas_baru == null) {
                $jumlah = serialize($alumnikelas[$ik]);
                $data = array(
                    'nama_kelas' => $kelas->nama_kelas,
                    'kode_kelas' => $kelas->kode_kelas,
                    'jurusan_id' => $kelas->jurusan_id,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => $kelas->level_id,
                    'guru_id' => $kelas->guru_id,
                    'alumni_id' => $kelas->alumni_id,
                    'jumlah_alumni' => $jumlah
                );
                $this->db->insert('master_kelas', $data);
                //$idk = $this->db->insert_id();
                array_push($idks, $this->db->insert_id());
            } else {
                if ($mode == 'peralumni') {
                    $jmlLama = $this->maybe_unserialize($kelas_baru->jumlah_alumni ?? '');
                    foreach ($alumnikelas[$ik] as $s) {
                        foreach ($jmlLama as $lama) {
                            if ($lama['id'] != $s['id']) {
                                array_push($jmlLama, ['id' => $s['id']]);
                                array_push($idks, $kelas_baru->id_kelas);
                            }
                        }
                    }
                    $jumlah = serialize($jmlLama);
                } else {
                    $jumlah = serialize($alumnikelas[$ik]);
                    array_push($idks, $kelas_baru->id_kelas);
                }

                $data = array(
                    'nama_kelas' => $kelas->nama_kelas,
                    'kode_kelas' => $kelas->kode_kelas,
                    'jurusan_id' => $kelas->jurusan_id,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => $kelas->level_id,
                    'guru_id' => $kelas->guru_id,
                    'alumni_id' => $kelas->alumni_id,
                    'jumlah_alumni' => $jumlah
                );

                $this->db->where('id_kelas', $kelas_baru->id_kelas);
                $this->db->update('master_kelas', $data);
            }

            foreach ($idks as $idk) {
                foreach ($alumnikelas[$ik] as $s) {
                    $insert = [
                        'id_kelas_alumni' => $tp->id_tp . $smt->id_smt . $s['id'],
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'id_kelas' => $idk,
                        'id_siswa' => $s['id'],
                    ];
                    $res[] = $this->db->replace('kelas_alumni', $insert);
                }
            }
        }

        $data['res'] = $alumnikelas;
        $this->output_json($data);
    }

    public function detail($id) {
        $alumni = $this->master->getAlumniById($id);
        $inputData = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'value' => $alumni->nama,
                'icon' => 'far fa-user',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'NIS',
                'name' => 'nis',
                'value' => $alumni->nis,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'number'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'value' => $alumni->nisn,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $alumni->jenis_kelamin,
                'icon' => 'fas fa-venus-mars',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'kelas_awal',
                'label' => 'Diterima di kelas',
                'value' => $alumni->kelas_awal,
                'icon' => 'fas fa-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tahun_masuk',
                'label' => 'Tgl diterima',
                'value' => $alumni->tahun_masuk,
                'icon' => 'tahun far fa-calendar-alt',
                'class' => 'tahun',
                'type' => 'text'
            ]
        ];
        $inputBio = [
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $alumni->tempat_lahir,
                'icon' => 'far fa-map',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $alumni->tanggal_lahir,
                'icon' => 'far fa-calendar',
                'class' => 'tahun',
                'type' => 'text'
            ],
            ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $alumni->agama, 'icon' => 'far fa-calendar', 'type' => 'text'],
            ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $alumni->alamat, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $alumni->rt, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $alumni->rw, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $alumni->kelurahan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $alumni->kecamatan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $alumni->kabupaten, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $alumni->kode_pos, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $alumni->hp, 'icon' => 'far fa-user', 'type' => 'text']
        ];

        $inputOrtu = [['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $alumni->nama_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pendidikan_ayah', 'label' => 'Pendidikan Ayah', 'value' => $alumni->pendidikan_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $alumni->pekerjaan_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $alumni->nohp_ayah, 'icon' => 'far fa-user', 'type' => 'number'], ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $alumni->alamat_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $alumni->nama_ibu, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pendidikan_ibu', 'label' => 'Pendidikan Ibu', 'value' => $alumni->pendidikan_ibu, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $alumni->pekerjaan_ibu, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $alumni->nohp_ibu, 'icon' => 'far fa-user', 'type' => 'number'], ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $alumni->alamat_ibu, 'icon' => 'far fa-user', 'type' => 'text']];

        $inputWali = [['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $alumni->nama_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pendidikan_wali', 'label' => 'Pendidikan Wali', 'value' => $alumni->pendidikan_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $alumni->pekerjaan_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $alumni->alamat_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $alumni->nohp_wali, 'icon' => 'far fa-user', 'type' => 'number']];
        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Alumni', 'subjudul' => 'Edit Data Alumni', 'alumni' => $alumni, 'setting' => $this->dashboard->getSetting()];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['input_data'] = json_decode(json_encode($inputData), FALSE);
        $data['input_bio'] = json_decode(json_encode($inputBio), FALSE);
        $data['input_ortu'] = json_decode(json_encode($inputOrtu), FALSE);
        $data['input_wali'] = json_decode(json_encode($inputWali), FALSE);
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/alumni/edit');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function add() {
        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Alumni', 'subjudul' => 'Tambah Data Alumni', 'setting' => $this->dashboard->getSetting()];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
        $data['tipe'] = 'add';

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/alumni/add');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function create() {
        $nis           = $this->input->post('nis', true);
        $nisn          = $this->input->post('nisn', true);

        $u_nis = '|is_unique[master_siswa.nis]';
        $u_nisn = '|is_unique[master_siswa.nisn]';

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[6]|max_length[20]'. $u_nisn);

        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['text'] = 'Data Sudah ada, Pastikan NIS, NISN dan Username belum digunakan alumni lain';
        } else {
            $insert = [
                "nama"          => $this->input->post('nama_alumni', true),
                "nis"           => $nis,
                "nisn"          => $nisn,
                "jenis_kelamin" => $this->input->post('jenis_kelamin', true),
                "foto"          => 'uploads/foto_siswa/'.$nis.'jpg',
            ];

            $this->db->set('uid','UUID()',FALSE);
            $this->db->insert('master_siswa', $insert);
            $last_id = $this->db->insert_id();
            $uid = $this->db->select('uid')->from('master_siswa')->where('id_siswa', $last_id)->get()->row();

            $induk = [
                "id_siswa"      => $last_id,
                "uid"           => $uid->uid,
                "kelas_akhir"    => $this->input->post('kelas_akhir', true),
                "tahun_lulus"   => $this->input->post('tahun_lulus', true),
                "no_ijazah"      => $this->input->post('no_ijazah', true),
                "status"        => 2
            ];

            $data['insert'] = $this->db->insert('buku_induk', $induk);
            $data['text'] = 'Alumni berhasil ditambahkan';
        }
        $this->output_json($data);
    }

    public function edit() {
        $id = $this->input->get('id', true);
        $alumni = $this->master->getAlumniById($id);
        $inputData = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'value' => $alumni->nama,
                'icon' => 'far fa-user',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'NIS',
                'name' => 'nis',
                'value' => $alumni->nis,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'number'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'value' => $alumni->nisn,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $alumni->jenis_kelamin,
                'icon' => 'fas fa-venus-mars',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'kelas_awal',
                'label' => 'Diterima di kelas',
                'value' => $alumni->kelas_awal,
                'icon' => 'fas fa-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tahun_masuk',
                'label' => 'Tgl diterima',
                'value' => $alumni->tahun_masuk,
                'icon' => 'tahun far fa-calendar-alt',
                'class' => 'tahun',
                'type' => 'text'
            ]
        ];
        $inputBio = [
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $alumni->tempat_lahir,
                'icon' => 'far fa-map',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $alumni->tanggal_lahir,
                'icon' => 'far fa-calendar',
                'class' => 'tahun',
                'type' => 'text'
            ],
            ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $alumni->agama, 'icon' => 'far fa-calendar', 'type' => 'text'],
            ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $alumni->alamat, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $alumni->rt, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $alumni->rw, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $alumni->kelurahan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $alumni->kecamatan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $alumni->kabupaten, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $alumni->kode_pos, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $alumni->hp, 'icon' => 'far fa-user', 'type' => 'text']
        ];

        $inputOrtu = [['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $alumni->nama_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pendidikan_ayah', 'label' => 'Pendidikan Ayah', 'value' => $alumni->pendidikan_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $alumni->pekerjaan_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $alumni->nohp_ayah, 'icon' => 'far fa-user', 'type' => 'number'], ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $alumni->alamat_ayah, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $alumni->nama_ibu, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pendidikan_ibu', 'label' => 'Pendidikan Ibu', 'value' => $alumni->pendidikan_ibu, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $alumni->pekerjaan_ibu, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $alumni->nohp_ibu, 'icon' => 'far fa-user', 'type' => 'number'], ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $alumni->alamat_ibu, 'icon' => 'far fa-user', 'type' => 'text']];

        $inputWali = [['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $alumni->nama_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pendidikan_wali', 'label' => 'Pendidikan Wali', 'value' => $alumni->pendidikan_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $alumni->pekerjaan_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $alumni->alamat_wali, 'icon' => 'far fa-user', 'type' => 'text'], ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $alumni->nohp_wali, 'icon' => 'far fa-user', 'type' => 'number']];
        $user = $this->ion_auth->user()->row();
        $data = ['user' => $user, 'judul' => 'Alumni', 'subjudul' => 'Edit Data Alumni', 'alumni' => $alumni, 'setting' => $this->dashboard->getSetting()];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['input_data'] = json_decode(json_encode($inputData), FALSE);
        $data['input_bio'] = json_decode(json_encode($inputBio), FALSE);
        $data['input_ortu'] = json_decode(json_encode($inputOrtu), FALSE);
        $data['input_wali'] = json_decode(json_encode($inputWali), FALSE);
        $data['profile'] = $this->dashboard->getProfileAdmin($user->id);

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/alumni/edit');
        $this->load->view('_templates/dashboard/_footer');
    }

    public function updateData() {
        $id_siswa = $this->input->post('id_siswa', true);
        $nis           = $this->input->post('nis', true);
        $nisn          = $this->input->post('nisn', true);

        $alumni = $this->master->getAlumniById($id_siswa);
        $u_nis = $alumni->nis === $nis ? "" : "|is_unique[mater_alumni.nis]";
        $u_nisn = $alumni->nisn === $nisn ? "" : "|is_unique[mater_alumni.nisn]";

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[6]|max_length[20]'. $u_nisn);


        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['text'] = 'Data Sudah ada, Pastikan NIS, dan NISN belum digunakan alumni lain';
        } else {
            /*
            $alumni->nisn            = $this->input->post('nisn', true);
            $alumni->nis             = $this->input->post('nis', true);
            $alumni->nama            = $this->input->post('nama', true);
            $alumni->jenis_kelamin   = $this->input->post('jenis_kelamin', true);
            $alumni->tempat_lahir    = $this->input->post('tempat_lahir', true);
            $alumni->tanggal_lahir   = $this->input->post('tanggal_lahir', true);
            $alumni->agama           = $this->input->post('agama', true);
            $alumni->status_keluarga = $this->input->post('status_keluarga', true);
            $alumni->anak_ke         = $this->input->post('anak_ke', true);
            $alumni->alamat	        = $this->input->post('alamat', true);
            $alumni->rt	=	$this->input->post('rt', true);
            $alumni->rw	=	$this->input->post('rw', true);
            $alumni->kelurahan	= $this->input->post('kelurahan', true);
            $alumni->kecamatan	= $this->input->post('kecamatan', true);
            $alumni->kabupaten	= $this->input->post('kabupaten', true);
            $alumni->provinsi	=	$this->input->post('provinsi', true);
            $alumni->kode_pos	=	$this->input->post('kode_pos', true);
            $alumni->hp	=	$this->input->post('hp', true);
            $alumni->nama_ayah	=	$this->input->post('nama_ayah', true);
            $alumni->nohp_ayah	=	$this->input->post('nohp_ayah', true);
            $alumni->pendidikan_ayah	=	$this->input->post('pendidikan_ayah', true);
            $alumni->pekerjaan_ayah	=	$this->input->post('pekerjaan_ayah', true);
            $alumni->alamat_ayah	=	$this->input->post('alamat_ayah = ', true);
            $alumni->nama_ibu	=	$this->input->post('nama_ibu = ', true);
            $alumni->nohp_ibu	=	$this->input->post('nohp_ibu', true);
            $alumni->pendidikan_ibu	=	$this->input->post('pendidikan_ibu', true);
            $alumni->pekerjaan_ibu	=	$this->input->post('pekerjaan_ibu', true);
            $alumni->alamat_ibu	=	$this->input->post('alamat_ibu', true);
            $alumni->nama_wali	=	$this->input->post('nama_wali', true);
            $alumni->pendidikan_wali	=	$this->input->post('pendidikan_wali', true);
            $alumni->pekerjaan_wali	=	$this->input->post('pekerjaan_wali', true);
            $alumni->nohp_wali	=	$this->input->post('nohp_wali', true);
            $alumni->alamat_wali	=	$this->input->post('alamat_wali', true);
            $alumni->tahun_masuk	=	$this->input->post('tahun_masuk', true);
            $alumni->kelas_awal	=	$this->input->post('kelas_awal', true);
            $alumni->tgl_lahir_ayah	=	$this->input->post('tgl_lahir_ayah', true);
            $alumni->tgl_lahir_ibu	=	$this->input->post('tgl_lahir_ibu', true);
            $alumni->tgl_lahir_wali	=	$this->input->post('tgl_lahir_wali', true);
            $alumni->sekolah_asal	=	$this->input->post('sekolah_asal', true);
            $alumni->foto	=	'uploads/foto_siswa/'.$nis.'.jpg';
            */
            $input = [
                'nisn'   => $this->input->post('nisn', true),
                'nis'     => $this->input->post('nis', true),
                'nama'   => $this->input->post('nama', true),
                'jenis_kelamin'   => $this->input->post('jenis_kelamin', true),
                'tempat_lahir'   => $this->input->post('tempat_lahir', true),
                'tanggal_lahir'   => $this->input->post('tanggal_lahir', true),
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
                'tahun_masuk'   =>	$this->input->post('tahun_masuk', true),
                'kelas_awal'   =>	$this->input->post('kelas_awal', true),
                'tgl_lahir_ayah'   =>	$this->input->post('tgl_lahir_ayah', true),
                'tgl_lahir_ibu'   =>	$this->input->post('tgl_lahir_ibu', true),
                'tgl_lahir_wali'   =>	$this->input->post('tgl_lahir_wali', true),
                'sekolah_asal'   =>	$this->input->post('sekolah_asal', true),
                'foto'   =>	'uploads/foto_siswa/'.$nis.'.jpg'
            ];

            $action = $this->master->update('master_siswa', $input, 'id_siswa', $id_siswa);
            $data['insert'] = $input;
            $data['text'] = 'Alumni berhasil diperbaharui';
        }

        $this->output_json($data);
    }

    function uploadFile($id_siswa){
        $alumni = $this->master->getAlumniById($id_siswa);
        if(isset($_FILES["foto"]["name"])){

            $config['upload_path'] = './uploads/foto_siswa/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
            $config['overwrite'] = true;
            //$config['encrypt_name'] = TRUE;
            $config['file_name'] = $alumni->nis;

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

    function deleteFoto() {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        unlink($file_name);
        echo 'File Delete Successfully';
    }

    public function delete() {
        $chk = $this->input->post('checked', true);
        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            if ($this->master->delete('master_siswa', $chk, 'id_siswa')) {
                $this->output_json(['status' => true, 'total' => count($chk)]);
            }
        }
    }

    public function do_import() {
        $input = json_decode($this->input->post('alumni', true));
        $this->db->trans_start();
        foreach ($input as $key1=>$val1) {
            $data = [];
            foreach(((array)$input)[$key1] as $key=>$val) {
                $data[$key] = $val;
            }
            $data['foto'] = 'uploads/foto_siswa/'.$data['nis'].'.jpg';
            $save = $this->db->insert('master_siswa', $data);
        }
        $this->db->trans_complete();
        $this->output->set_content_type('application/json')->set_output($save);
    }
    /*
     *              * 1	NO
             * 2	NISN
             * 3	NIS
             * 4	NAMA SISWA
             * 5	JENIS KELAMIN (L/P)
             * 6	TAHUN LULUS
             * 7	KELAS AKHIR
             * 8	NOMOR IJAZAH
             * 9	TEMPAT LAHIR
             * 10	TANGGAL LAHIR
             * 11	AGAMA
             * 12	NOMOR TELEPON
13	EMAIL
14	ALAMAT
15	RT
16	RW
17	DESA/KELURAHAN
18	KECAMATAN
19	KABUPATEN/KOTA
20	PROVINSI
21	KODE POS
22	NAMA AYAH
23	TANGGAL LAHIR AYAH
24	PENDIDIKAN AYAH
25	PEKERJAAN AYAH
26	NOMOR TELEPON AYAH
27	ALAMAT AYAH
28	TANGGAL LAHIR IBU
29	PENDIDIKAN IBU
30	PEKERJAAN IBU
31	NOMOR TELEPON IBU
32	ALAMAT IBU
33	NAMA WALI
34	TANGGAL LAHIR WALI
35	PENDIDIKAN WALI
36	PEKERJAAN WALI
37	NOMOR TELEPON WALI
38	ALAMAT WALI
     */

    /*
    public function do_import()
    {
        $this->load->model('Master_model', 'master');
        $input = $this->input->post('guru', true);
        $errors = [];
        foreach ($input as $guru) {

            $this->form_validation->set_data($guru);
            $this->form_validation->set_rules('2', 'Nama Guru', 'required|trim|min_length[1]|max_length[50]');
            $this->form_validation->set_rules('3', 'NIP', 'required|trim|min_length[6]|max_length[30]|is_unique[master_guru.nip]');
            $this->form_validation->set_rules('5', 'Username', 'required|trim|min_length[3]|max_length[30]|is_unique[master_guru.username]');
            $this->form_validation->set_rules('6', 'Password', 'required|trim|min_length[5]|max_length[30]');

            if ($this->form_validation->run() == FALSE) {
                $errors[] = [
                    'nama' => form_error('2'),
                    'nip' => form_error('3'),
                    'username' => form_error('5'),
                    'password' => form_error('6'),
                ];
            }
        }

        if (count($errors) > 0) {
            $data = [
                'status'	=> false,
                'errors'	=> $errors,
            ];
        } else {
            $data_insert = [];
            foreach ($input as $guru) {
                $foto = 'uploads/foto_siswa/' . trim($guru['3'] ?? '00') . '.jpg';
                if (isset($guru['7'])) {
                    $base64_image_string = $guru['7'];
                    $extension = $guru['8'];
                    if ($extension == 'jpeg') $extension = 'jpg';
                    $output_file = trim($guru['3'] ?? '00') . '.' . $extension;
                    file_put_contents('./uploads/foto_siswa/' . $output_file, base64_decode($base64_image_string));
                    $foto = 'uploads/foto_siswa/'.$output_file;
                }
                $data_insert[] = [
                    'nama_guru' => trim($guru['2'] ?? ''),
                    'nip' => trim($guru['3'] ?? ''),
                    'kode_guru' => trim($guru['4'] ?? ''),
                    'username' => trim($guru['5'] ?? ''),
                    'password' => trim($guru['6'] ?? ''),
                    'foto' => $foto
                ];
            }

            $save = $this->master->create('master_siswa', $data_insert, true);
            $data = [
                'status'	=> true,
                'data'	=> $save,
                'insert' => $data_insert
            ];
        }
        $this->output_json($data);
    }
    */


    public function editKelulusan() {
        $id_siswa 	= $this->input->post('id_siswa', true);
        $thn 	= $this->input->post('tahun_lulus', true);
        $no_ijazah 	= $this->input->post('no_ijazah', true);
        $kelas_akhir 	= $this->input->post('kelas_akhir', true);

        $this->db->set('kelas_akhir', $kelas_akhir);
        $this->db->set('tahun_lulus', $thn);
        $this->db->set('no_ijazah', $no_ijazah);
        $this->db->where('id_siswa', $id_siswa);
        $status = $this->db->update('master_siswa');

        $data['status'] = $status;
        $this->output_json($data);
    }

}
