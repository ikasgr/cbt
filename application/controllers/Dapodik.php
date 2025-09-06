<?php
/*   ________________________________________
    |                 GarudaCBT              |
    |    https://github.com/garudacbt/cbt    |
    |________________________________________|
*/
defined("BASEPATH") or exit("No direct script access allowed");

class Dapodik extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect("auth");
            goto OdHIi;
        }
        if ($this->ion_auth->is_admin()) {
            goto YNpDy;
        }
        show_error("Hanya Admin yang boleh mengakses halaman ini", 403, "Akses dilarang");
        YNpDy:
        OdHIi:
        $this->load->library("upload");
        $this->load->model("Settings_model", "settings");
        $this->load->model("Master_model", "master");
        $this->load->model("Dashboard_model", "dashboard");
        $this->load->helper("directory");
    }

    public function tarikDataGtk() {
        $setting = $this->dashboard->getSetting();
        $npsn = $setting->npsn;

        $httpClient = $this->get_dapodik_httpclient(
            $this->config->item('DAPODIK_WEBSERVICE_URL'),
            $this->config->item('DAPODIK_WEBSERVICE_TOKEN'),
            $npsn,
        );

        $response = $httpClient->get('/WebService/getGtk');
        if (!$response) {
            $this->output_json([
                'error' => 'NPSN kemungkinan salah atau ada kesalahan yang tidak terduga',
            ]);
            return;
        }

        $json = json_decode((string) $response->getBody());
        $nama_fails = [];

        $this->db->trans_start();
        foreach($json->rows as $value) {
            $data = [
                'nip' => $value->nip,
                'nama_guru' => $value->nama,
                'username' => $value->nip,
                'password' => '123456' . $value->tahun_ajaran_id,
                "foto" => "uploads/profiles/" . $value->nip . ".jpg",
                "jenis_kelamin" => $value->jenis_kelamin,
                "agama" => $value->agama_id_str,
            ];

            if (!isset($value->nip) || strlen($value->nip) < 5) {
                array_push($nama_fails, 'MERR: ' . $value->nama);
            } else {
                // $this->ion_auth->register($username, $password, $email, $additional_data, $group);
                $userGuruRow = $this->db->select('id')->from('users')->where('username', $value->nip)->get()->row();
                if (isset($userGuruRow)) {
                    $data['id_user'] = $userGuruRow->id;
                }

                $rowGuru = $this->db->select('nip')->from('master_guru')->where('nip', $value->nip)->get()->num_rows();
                if ($rowGuru > 0) {
                    $act = $this->master->update('master_guru', $data, 'nip', $value->nip);
                } else {
                    $act = $this->master->create('master_guru', $data);
                }

                if (!$this->ion_auth->username_check($data['username'])) {
                    $nama = explode(" ", $value->nama);
                    $first_name = $nama[0];
                    $last_name = end($nama);
                    if (!$this->ion_auth->register($data['username'], $data['password'], $value->nip . '@guru.com', [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                    ], array('2'))) {
                        array_push($nama_fails, 'UERR: ' . $value->nama);
                    }
                }

                if (!$act) {
                    array_push($nama_fails, $value->nama);
                }
            }
        }
        $this->db->trans_complete();

        $this->output_json([
            'message' => 'Sukses menarik ' . strval(count($json->rows) - count($nama_fails)) . ' data GTK',
            'fails' => $nama_fails,
        ]);
        return;
    }

    public function tarikDataSiswa() {
        $setting = $this->dashboard->getSetting();
        $npsn = $setting->npsn;

        $httpClient = $this->get_dapodik_httpclient(
            $this->config->item('DAPODIK_WEBSERVICE_URL'),
            $this->config->item('DAPODIK_WEBSERVICE_TOKEN'),
            $npsn,
        );

        $response = $httpClient->get('/WebService/getPesertaDidik');

        if (!$response) {
            $this->output_json([
                'error' => 'NPSN kemungkinan salah atau ada kesalahan yang tidak terduga',
            ]);
            return;
        }

        $json = json_decode((string) $response->getBody());
        $nama_fails = [];

        // $this->db->trans_start();
        foreach ($json->rows as $value) {
            $data = [
                'nisn' => $value->nisn,
                'nis' => $value->nipd,
                'nama' => $value->nama,
                'jenis_kelamin' => $value->jenis_kelamin,
                'username' => $value->nisn,
                'password' => $value->nipd,
                'tahun_masuk' => explode('-', $value->tanggal_masuk_sekolah)[0],
                'sekolah_asal' => $value->sekolah_asal,
                'tempat_lahir' => $value->tempat_lahir,
                'tanggal_lahir' => $value->tanggal_lahir,
                'agama' => $value->agama_id_str,
                'hp' => $value->nomor_telepon_seluler,
                'email' => $value->email,
                'nama_ayah' => $value->nama_ayah,
                'pekerjaan_ayah' => $value->pekerjaan_ayah_id_str,
                'nama_ibu' => $value->nama_ibu,
                'pekerjaan_ibu' => $value->pekerjaan_ibu_id_str,
                'nik' => $value->nik,
                'warga_negara' => 'Indonesia',
                'foto' => "uploads/foto_siswa/" . $value->nipd . ".jpg",
            ];

            $levelKelas = 10;
            $kelas = explode(' ', $value->nama_rombel);
            if ($kelas[0] === 'X') {
                $levelKelas = 10;
            }

            if ($kelas[0] === 'XI') {
                $levelKelas = 11;
            }

            if ($kelas[0] === 'XII') {
                $levelKelas = 12;
            }


            $jurusanData = [
                'nama_jurusan' => $kelas[1],
                'kode_jurusan' => $kelas[1],
                'mapel_peminatan' => '',
            ];

            if (count($kelas) == 2) {
                $jurusanData['nama_jurusan'] = 'Non Jurusan';
                $jurusanData['kode_jurusan'] = 'NONJURUSAN';
            }

            $rowJurusan = $this->db->select('*')->from('master_jurusan')->where('kode_jurusan', $jurusanData['kode_jurusan'])->get()->row();
            if (!isset($rowJurusan)) {
                $actionJurusan = $this->master->create('master_jurusan', $jurusanData);
                $rowJurusan = $this->db->select('*')->from('master_jurusan')->where('kode_jurusan', $jurusanData['kode_jurusan'])->get()->row();
            }

            if (!$data['nis'] || !$data['nisn']) {
                array_push($nama_fails, '[N]: ' . $data['nama']);
            } else {
                $row = $this->db->select('nama, id_siswa')->from('master_siswa')->where('nisn', $value->nisn)->get()->row();

                if (isset($row)) {
                    $actionJurusan = $this->master->update("master_siswa", $data, 'nisn', $value->nisn);
                } else {
                    $this->db->set("uid", "UUID()", FALSE);
                    $actionJurusan = $this->master->create('master_siswa', $data);
                }

                if (!$actionJurusan) {
                    array_push($nama_fails, '[C]: ' . $data['nama']);
                }

                $rowKelas = $this->db->select('id_kelas, jumlah_siswa, siswa_id')->from('master_kelas')->where('kode_kelas', strtolower(preg_replace('/\s+/', '_', $value->nama_rombel)))->get()->row();
                $id_tp = $this->master->getTahunActive()->id_tp;
                $id_smt = $this->master->getSemesterActive()->id_smt;
                
                $insertDataKelas = [
                    'nama_kelas' => $value->nama_rombel,
                    'kode_kelas' => strtolower(preg_replace('/\s+/', '_', $value->nama_rombel)),
                    'jurusan_id' => $rowJurusan->id_jurusan,
                    'id_tp' => $id_tp,
                    'id_smt' => $id_smt,
                    'level_id' => $levelKelas,
                    'siswa_id' => $row->id_siswa,
                    // 'jumlah_siswa' => serialize(array_map(function($data) {
                    //     return ['id'=>$data->id_siswa];
                    // }, $uids)),
                    'guru_id' => 0,
                ];

                if ($rowKelas) {
                    $insertDataKelas['siswa_id'] = $rowKelas->siswa_id;
                    $currentKelasSiswa = $this->db->select('id_siswa')->from('kelas_siswa')->where('id_kelas', $rowKelas->id_kelas)->get()->result();

                    if (isset($currentKelasSiswa) && count($currentKelasSiswa)) {
                        $currentKelasSiswa = array_map(function ($data) {
                            return ['id' => $data->id_siswa];
                        }, $currentKelasSiswa);

                        array_push($currentKelasSiswa, ['id' => $row->id_siswa]);
                    }
                    // $unserializedJmlSiswa = json_decode(json_encode(unserialize($rowKelas->jumlah_siswa)));
                    // array_push($unserializedJmlSiswa, $row->id_siswa);

                    $insertDataKelas['jumlah_siswa'] = serialize($currentKelasSiswa);
                    $this->master->update('master_kelas', $insertDataKelas, 'id_kelas', $rowKelas->id_kelas);
                } else {
                    $insertDataKelas['jumlah_siswa'] = serialize([
                        [
                            'id' => $row->id_siswa,
                        ],
                    ]);
                    $this->master->create('master_kelas', $insertDataKelas);
                }

                $rowKelasSiswa = $this->db->select('id_siswa')->from('kelas_siswa')->where('id_siswa', $row->id_siswa)->get()->num_rows();
                $rowKelasData = [
                    'id_kelas_siswa' => $id_tp . $id_smt . $row->id_siswa,
                    'id_tp' => $id_tp,
                    'id_smt' => $id_smt,
                    'id_siswa' => $row->id_siswa,
                    'id_kelas' => $rowKelas->id_kelas,
                ];

                if ($rowKelasSiswa > 0) {
                    $this->master->update('kelas_siswa', $rowKelasData, 'id_siswa', $row->id_siswa);
                } else {
                    $this->master->create('kelas_siswa', $rowKelasData);
                }
            }
        }
        // $this->db->trans_complete();

        // $uids = $this->db->select("id_siswa, uid")->from("master_siswa")->get()->result();
        // $rowKelas = $this->db->select('id_kelas, jumlah_siswa')->from('master_kelas')->where('kode_kelas', strtolower(preg_replace('/\s+/', '_', $value->nama_rombel)))->get()->row();
        // $id_tp = $this->master->getTahunActive()->id_tp;
        // $id_smt = $this->master->getSemesterActive()->id_smt;
        
        // $insertDataKelas = [
        //     'nama_kelas' => $value->nama_rombel,
        //     'kode_kelas' => strtolower(preg_replace('/\s+/', '_', $value->nama_rombel)),
        //     'jurusan_id' => $rowJurusan->id_jurusan,
        //     'id_tp' => $id_tp,
        //     'id_smt' => $id_smt,
        //     'level_id' => $levelKelas,
        //     'siswa_id' => $uids[array_rand($uids)]->id_siswa,
        //     // 'jumlah_siswa' => serialize(array_map(function($data) {
        //     //     return ['id'=>$data->id_siswa];
        //     // }, $uids)),
        //     'guru_id' => 0,
        // ];

        // if ($rowKelas) {
        //     $this->master->update('master_kelas', $insertDataKelas, 'id_kelas', $rowKelas->id_kelas);
        // } else {
        //     $unserializedJmlSiswa = unserialize($rowKelas->jumlah_siswa);
        //     array_push($unserializedJmlSiswa, )
        //     $this->master->create('master_kelas', $insertDataKelas);
        // }

        // $this->db->trans_start();
        foreach ($uids as $uid) {
            $check = $this->db->select("id_siswa")->from("buku_induk")->where("id_siswa", $uid->id_siswa)->get()->num_rows();

            if ($check < 1) {
                $this->master->create("buku_induk", $uid);
            }
        }
        // $this->db->trans_complete();

        $uids = $this->db->select("id_siswa, uid")->from("master_siswa")->get()->result();
        foreach ($uids as $uid) {
            $check = $this->db->select("id_siswa")->from("buku_induk")->where("id_siswa", $uid->id_siswa)->get()->num_rows();

            if ($check < 1) {
                $this->master->create("buku_induk", $uid);
            }

            // $rowKelasSiswa = $this->db->select('id_siswa')->from('kelas_siswa')->where('id_siswa', $uid->id_siswa)->get()->num_rows();
            // $rowKelasData = [
            //     'id_kelas_siswa' => $id_tp . $id_smt . $uid->id_siswa,
            //     'id_tp' => $id_tp,
            //     'id_smt' => $id_smt,
            //     'id_siswa' => $uid->id_siswa,
            //     'id_kelas' => $rowKelas->id_kelas,
            // ];

            // if ($rowKelasSiswa > 0) {
            //     $this->master->update('kelas_siswa', $rowKelasData, 'id_siswa', $uid->id_siswa);
            // } else {
            //     $this->master->create('kelas_siswa', $rowKelasData);
            // }
        }

        $this->output_json([
            'message' => 'Telah terdaftar ' . strval(count($uids)) . ' siswa, dari perolehan ' . strval(count($json->results) . ' siswa'),
            'fails' => $nama_fails,
            // '_a' => $rowKelas,
        ]);
    }

    public function index() {
        $user = $this->ion_auth->user()->row();
        $setting = $this->dashboard->getSetting();
        $data = [
            'judul' => 'DAPODIK',
            'subjudul' => 'TARIK DATA DAPODIK',
            'user' => $user,
            'setting' => $setting,
            'profile' => $this->dashboard->getProfileAdmin($user->id),
        ];
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data["tp"] = $this->dashboard->getTahun();
        $data["tp_active"] = $tp;
        $data["smt"] = $this->dashboard->getSemester();
        $data["smt_active"] = $smt;

        $this->load->view("_templates/dashboard/_header", $data);
        $this->load->view('setting/dapodik',$data);
        $this->load->view("_templates/dashboard/_footer");
    }

    protected function get_dapodik_httpclient(string $service, string $token, string $npsn) {
        $httpClient = new \GuzzleHttp\Client([
            'base_uri' => $service,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'query' => [
                'npsn' => $npsn,
            ],
        ]);
    
        return $httpClient;
    }

    public function output_json($data, $encode = true)
    {
        if (!$encode) {
            goto AELLo;
        }
        $data = json_encode($data);
        AELLo:
        $this->output->set_content_type("application/json")->set_output($data);
    }
}