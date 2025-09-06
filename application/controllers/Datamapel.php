<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datamapel extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
		} else if (!$this->ion_auth->is_admin()) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
        $this->load->dbforge();
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function output_json($data, $encode = true)
	{
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

	private function updateUrutanTampil() {
        $mapels = $this->db->select('*')->from('master_mapel')->get()->result();
        $insert = [];
        foreach ($mapels as $mapel) {
            $insert = [
                "id_mapel"=> $mapel->id_mapel,
                "nama_mapel"=> $mapel->id_mapel,
                "kode"=> $mapel->id_mapel,
                "kelompok"=> $mapel->id_mapel,
                "bobot_p"=> $mapel->id_mapel,
                "bobot_k"=> $mapel->id_mapel,
                "jenjang"=> $mapel->id_mapel,
                "urutan"=> $mapel->id_mapel,
                "urutan_tampil"=> $mapel->id_mapel,
                "status"=> $mapel->id_mapel,
                "deletable"=> $mapel->id_mapel
            ];
        }
        if (count($insert)>0) $this->db->update_batch('master_mapel', $insert);
    }

	public function index(){
        if (!$this->db->field_exists('urutan_tampil', 'master_mapel')) {
            $fields = array(
                'urutan_tampil' => array('type' => 'int(3)', 'after' => 'urutan')
            );
            $this->dbforge->add_column('master_mapel', $fields);
        }
        //$cek = $this->db->select('urutan_tampil')->from('master_mapel')->where('id_mapel', '1')->get()->row();
        //$cek_urutan = $mapels = $this->db->select('kode')->from('master_mapel')->get()->result();

		$user = $this->ion_auth->user()->row();
        $setting = $this->dashboard->getSetting();
		$data = [
			'user' => $user,
			'judul'	=> 'Mata Pelajaran',
			'subjudul' => 'Daftar Mata Pelajaran',
			'profile'		=> $this->dashboard->getProfileAdmin($user->id),
			'setting'		=> $setting
		];
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();
        //$data['cek'] = $cek_urutan;

        $data['kategori'] = ['WAJIB', 'PAI (Kemenag)', 'PEMINATAN AKADEMIK', 'AKADEMIK KEJURUAN', 'LINTAS MINAT', 'MULOK'];
        $data['kelompok_mapel'] = $this->master->getDataKelompokMapel();
        $data['sub_kelompok_mapel'] = $this->master->getDataSubKelompokMapel();
        $data['kelompok'] = $this->dropdown->getDataKelompokMapel();
        $data['status'] = ['Nonaktif', 'Aktif'];
        $data['mapel_non_aktif'] = $this->master->getAllMapelNonAktif($setting->jenjang);
		$this->load->view('_templates/dashboard/_header', $data);
		$this->load->view('master/mapel/data');
		$this->load->view('_templates/dashboard/_footer');
	}

    public function addKelompokMapel() {
        $id = $this->input->post('id_kel_mapel');
        $insert = [
            "nama_kel_mapel" => $this->input->post('nama_kel_mapel', true),
            "kode_kel_mapel" => $this->input->post('kode_kel_mapel', true),
            "kategori" => $this->input->post('kategori', true),
            "id_parent" => $this->input->post('id_parent', true),
        ];

        if ($id != null) {
            $this->db->where('id_kel_mapel', $id);
            $data = $this->db->update('master_kelompok_mapel', $insert);
        } else {
            $data = $this->master->create('master_kelompok_mapel', $insert);
        }
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function hapusKelompok() {
        $id = $this->input->post('id_kel');
        $kode = $this->input->post('kode');
        $id_parent = $this->input->post('id_parent');
        $messages = [];

        $this->db->where_in('kelompok', $kode);
        $numm = $this->db->count_all_results('master_mapel');
        if ($numm > 0) array_push($messages, 'Mata Pelajaran');

        $this->db->where_in('id_parent', $id);
        $nums = $this->db->count_all_results('master_kelompok_mapel');
        if ($nums > 0) array_push($messages, 'Sub Kelompok');

        if (count($messages) > 0) {
            $this->output_json([
                'status' => false,
                'message' => 'Kelompok Mapel digunakan di '.count($messages).' tabel:<br>'.implode('<br>', $messages)]);
        } else {
            if ($this->master->delete('master_kelompok_mapel', $id, 'id_kel_mapel')) {
                $this->output_json(['status' => true, 'message' => 'berhasil']);
            }
        }
    }

    public function create() {
        $setting = $this->dashboard->getSetting();
		$insert = [
			"nama_mapel" => $this->input->post('nama_mapel', true),
			"kode" => $this->input->post('kode_mapel', true),
			"kelompok" => $this->input->post('kelompok', true),
            "urutan_tampil" => $this->input->post('urutan_tampil', true),
            "jenjang" => $setting->jenjang
		];
		$data = $this->master->create('master_mapel', $insert);
		$this->output->set_content_type('application/json')->set_output($data);
	}

    public function getDataKelompok() {
        $this->datatables->select('*');
        $this->datatables->from('master_kelompok_mapel');
        $this->datatables->where('id_parent', '0');
        $this->db->order_by('kode_kel_mapel');
        echo $this->datatables->generate();
    }

    public function getDataSubKelompok() {
        $this->datatables->select('*');
        $this->datatables->from('master_kelompok_mapel');
        $this->datatables->where('id_parent <> 0');
        $this->db->order_by('kode_kel_mapel');
        echo $this->datatables->generate();
    }

	public function read() {
        $setting = $this->dashboard->getSetting();
        $this->datatables->select('id_mapel, urutan_tampil, nama_mapel, kode, kelompok, deletable, status');
		$this->datatables->from('master_mapel');
		/*
        if ($setting->jenjang == "1") {
            $this->datatables->where('jenjang=0 OR jenjang=1');
        } elseif ($setting->jenjang == "2") {
            $this->datatables->where('jenjang=2 OR jenjang=1');
        }
		*/
        //$this->db->order_by('status', 'DESC');
        $this->db->order_by('kelompok');
        $this->db->order_by('urutan_tampil');
        //$this->db->order_by('id_mapel');
		echo $this->datatables->generate();
	}

	public function update() {
		$data = $this->master->updateMapel();
		$this->output->set_content_type('application/json')->set_output($data);
	}

    public function aktifkan($id) {
        $this->db->set('status', '1');
        $this->db->where('id_mapel', $id);
        $update = $this->db->update('master_mapel');
        $this->output_json($update);
    }

	public function delete() {
		$chk = $this->input->post('checked', true);
        if (!$chk) {
			$this->output_json(['status' => false, 'total' => 'Tidak ada data yang dipilih!']);
		} else {
            $messages = [];
            $tables = [];

            $tabless = $this->db->list_tables();
            foreach ($tabless as $table) {
                $fields = $this->db->field_data($table);
                foreach ($fields as $field) {
                    if ($field->name == 'id_mapel' || $field->name == 'mapel_id')
                        array_push($tables, $table);
                }
            }

            foreach ($tables as $table) {
                if ($table != 'master_mapel') {
                    if ($table == 'cbt_soal') {
                        $this->db->where_in('mapel_id', $chk);
                        $num = $this->db->count_all_results($table);
                    } else {
                        $this->db->where_in('id_mapel', $chk);
                        $num = $this->db->count_all_results($table);
                    }
                    if ($num > 0) array_push($messages, $table);
                }
            }

            if (count($messages) > 0) {
                $this->output_json([
                    'status' => false,
                    'total' => 'Mapel digunakan di '.count($messages).' tabel:<br>'.implode('<br>', $messages)]);
            } else {
                if ($this->master->delete('master_mapel', $chk, 'id_mapel')) {
                    $this->output_json(['status' => true, 'total' => count($chk)]);
                }
            }
		}
	}

	public function import($import_data = null)
	{
		$user = $this->ion_auth->user()->row();
		$data = [
			'user' => $user,
			'judul'	=> 'Mata Pelajaran',
			'subjudul' => 'Import Mata Pelajaran',
			'profile'		=> $this->dashboard->getProfileAdmin($user->id),
			'setting'		=> $this->dashboard->getSetting()
		];
		if ($import_data != null) $data['import'] = $import_data;

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();

		$this->load->view('_templates/dashboard/_header', $data);
		$this->load->view('master/mapel/import');
		$this->load->view('_templates/dashboard/_footer');
	}

	public function do_import() {
		$inputs = $this->input->post('mapel', true);
        /*
		$mapel = [];
		foreach ($data as $j) {
			$mapel[] = [
				'nama_mapel' => $j['nama'],
				'kode' => $j['kode']
			];
		}
        */
		$save = $this->master->create('master_mapel', $inputs, true);
		$this->output->set_content_type('application/json')->set_output($save);
	}
}
