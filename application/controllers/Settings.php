<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Cek login dan hak akses admin
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        } elseif (!$this->ion_auth->is_admin()) {
            show_error('Hanya Admin yang boleh mengakses halaman ini', 403, 'Akses Dilarang');
        }

        // Load dependensi
        $this->load->library('upload');
        $this->load->model('Settings_model', 'settings');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->helper('directory');
        $this->load->helper('security');
    }

    // Fungsi untuk mengeluarkan output JSON
    public function output_json($data, $encode = true) {
        if ($encode) {
            $data = json_encode($data);
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output($data);
    }

    // Halaman utama pengaturan
    public function index() {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Profile Sekolah',
            'subjudul' => '',
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('setting/data', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    // Halaman backup dan restore database
    public function dbManager() {
        $data = [
            'user' => $this->ion_auth->user()->row(),
            'judul' => 'Backup dan Restore',
            'subjudul' => 'Backup dan Restore',
            'setting' => $this->settings->getSetting(),
            'list' => directory_map('./backups/'),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('setting/db', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    // Fungsi untuk upload file
    public function uploadFile($logo) {
        // Konfigurasi upload
        $config = [
            'upload_path' => './uploads/settings/',
            'allowed_types' => 'gif|jpg|png|jpeg',
            'overwrite' => true,
            'file_name' => $logo,
            'max_size' => 2048, // 2MB
            'file_ext_tolower' => true
        ];

        $this->upload->initialize($config);

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        if (!$this->upload->do_upload('logo')) {
            $this->output_json([
                'status' => false,
                'message' => $this->upload->display_errors('', '')
            ], true, 400);
            return;
        }

        $result = $this->upload->data();
        $this->output_json([
            'status' => true,
            'src' => base_url('uploads/settings/' . $result['file_name']),
            'filename' => $result['file_name'],
            'type' => $result['file_type'],
            'size' => $result['file_size'] * 1024 // Convert KB to bytes
        ]);
    }

    // Fungsi untuk menghapus file
    public function deleteFile() {
        $src = $this->input->post('src', true);
        $file_name = str_replace(base_url(), '', $src);

        if (!$file_name || !file_exists($file_name)) {
            $this->output_json([
                'status' => false,
                'message' => 'File tidak ditemukan'
            ], true, 404);
            return;
        }

        if (unlink($file_name)) {
            $this->output_json([
                'status' => true,
                'message' => 'File berhasil dihapus'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal menghapus file'
            ], true, 500);
        }
    }

    // Fungsi untuk menyimpan pengaturan
    public function saveSetting() {
        // Ambil input dengan XSS filtering
        $input = [
            'sekolah' => $this->input->post('nama_sekolah', true),
            'nss' => $this->input->post('nss', true),
            'npsn' => $this->input->post('npsn', true),
            'jenjang' => $this->input->post('jenjang', true),
            'satuan_pendidikan' => $this->input->post('satuan_pendidikan', true),
            'alamat' => $this->input->post('alamat', true),
            'kota' => $this->input->post('kota', true),
            'desa' => $this->input->post('desa', true),
            'kecamatan' => $this->input->post('kec', true),
            'provinsi' => $this->input->post('provinsi', true),
            'kode_pos' => $this->input->post('kode_pos', true),
            'telp' => $this->input->post('tlp', true),
            'web' => $this->input->post('web', true),
            'fax' => $this->input->post('fax', true),
            'email' => $this->input->post('email', true),
            'kepsek' => $this->input->post('kepsek', true),
            'nip' => $this->input->post('nip', true),
            'proktor' => $this->input->post('proktor', true),
            'nip_proktor' => $this->input->post('nip_proktor', true),
            'nama_aplikasi' => $this->input->post('nama_aplikasi', true),
            'ba_aktif' => $this->input->post('ba_aktif', true),
            'ba_waktu' => $this->input->post('ba_waktu', true),
            'tkn_siswa' => $this->input->post('tkn_siswa', true),
            'mode_app' => $this->input->post('mode_app', true),
            // Path file: hapus base_url dari input
            'logo_kanan' => str_replace(base_url(), '', $this->input->post('logo_kanan', true)),
            'logo_kiri' => str_replace(base_url(), '', $this->input->post('logo_kiri', true)),
            'stampel' => str_replace(base_url(), '', $this->input->post('stampel', true)),
            'ttdproktor' => str_replace(base_url(), '', $this->input->post('ttdproktor', true)),
            'kodescan' => str_replace(base_url(), '', $this->input->post('kodescan', true)),
            'kopsekolah' => str_replace(base_url(), '', $this->input->post('kopsekolah', true)),
            'banner1' => str_replace(base_url(), '', $this->input->post('banner1', true)),
            'banner2' => str_replace(base_url(), '', $this->input->post('banner2', true)),
            'banner3' => str_replace(base_url(), '', $this->input->post('banner3', true)),
            'tanda_tangan' => str_replace(base_url(), '', $this->input->post('tanda_tangan', true))
        ];

        // Validasi input wajib
        $required_fields = [
            'sekolah', 'jenjang', 'satuan_pendidikan', 'alamat', 'kota', 'desa',
            'kecamatan', 'provinsi', 'kepsek', 'proktor', 'nama_aplikasi'
        ];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                $this->output_json([
                    'status' => false,
                    'message' => 'Kolom ' . ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi'
                ], true, 400);
                return;
            }
        }

        // Validasi format email jika diisi
        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $this->output_json([
                'status' => false,
                'message' => 'Format email tidak valid'
            ], true, 400);
            return;
        }

        // Update data ke database
        $this->db->where('id_setting', 1);
        $update = $this->db->update('setting', $input);

        if ($update) {
            $this->output_json([
                'status' => true,
                'message' => 'Pengaturan berhasil disimpan'
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal menyimpan pengaturan'
            ], true, 500);
        }
    }
}