<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usersiswa extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->ion_auth->logged_in()){
			redirect('auth');
		}
		$this->load->library(['datatables', 'form_validation']);// Load Library Ignited-Datatables
		$this->load->model('Users_model', 'users');
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->form_validation->set_error_delimiters('','');
	}

	public function is_has_access() {
		$user_id = $this->ion_auth->user()->row()->id;
		$group = $this->ion_auth->get_users_groups($user_id)->row()->name;
		if (!$group === 'admin' or !$group === 'guru'){
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="'.base_url('dashboard').'">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
	}

	public function output_json($data, $encode = true) {
		if($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function data(){
		$this->is_has_access();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
		$this->output_json($this->users->getUserSiswa($tp->id_tp, $smt->id_smt), false);
	}

	public function index() {
		$this->is_has_access();
		$user = $this->ion_auth->user()->row();

		$data = [
			'user' => $user,
			'judul'	=> 'User Management',
			'subjudul'=> 'Data User Siswa',
			'profile'		=> $this->dashboard->getProfileAdmin($user->id),
			'setting'		=> $this->dashboard->getSetting()
		];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('users/siswa/data');
        $this->load->view('_templates/dashboard/_footer');
	}

    public function list() {
        $page = $this->input->post('page', true);
        $limit = $this->input->post('limit', true);
        $search = $this->input->post('search', true);
        //$sortBy = $this->input->post('sort', true);
        //$sortOrder = $this->input->post('order', true);

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $count_siswa = $this->users->getUserSiswaTotalPage($search);
        $lists = $this->users->getUserSiswaPage($tp->id_tp, $smt->id_smt, $offset, $limit, $search);
        $data = [
            'lists' => $lists,
            'total' => $count_siswa,
            'pages' => ceil($count_siswa / $limit),
            'search' => $search,
            'perpage' => $limit
        ];

        $this->output_json($data);
    }

	private function registerSiswa($username, $password, $email, $additional_data, $group) {
		$reg = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
		$data['status'] = true;
		$data['id'] = $reg;
		if ($reg == false) {
			$data['status'] = false;
		}
		return $data;
	}

    private function aktifkan($siswa) {
        $nama = explode(' ', $siswa->nama ?? '');

        $first_name = $nama[0];
        $last_name = end($nama);
        $username = trim($siswa->username ?? '');
        $password = trim($siswa->password ?? '');
        $email = $siswa->nis.'@siswa.com';
        $additional_data = [
            'first_name'	=> $first_name,
            'last_name'		=> $last_name
        ];
        $group = array('3');

        $user_siswa = $this->db->get_where('users', 'email="'.$email.'"')->row();
        $deleted = true;
        if ($user_siswa != null) {
            $deleted = $this->ion_auth->delete_user($user_siswa->id);
        }

        if ($deleted) {
            $reg = $this->registerSiswa($username, $password, $email, $additional_data, $group);
            $data = [
                'status'	=> $reg,
                'msg'	 => !$reg ? 'Akun '.$siswa->nama.' gagal diaktifkan.' : 'Akun '.$siswa->nama.' diaktifkan.'
            ];
        } else {
            $data = [
                'status' => false,
                'msg'	 => 'Akun siswa tidak tersedia (sudah digunakan).'
            ];
        }
        return $data;
    }

	public function activate($id) {
		$siswa = $this->users->getDataSiswa($id);
        $data = $this->aktifkan($siswa);
		$this->output_json($data);
	}

    public function aktifkanSemua() {
        $siswaAktif = $this->users->getSiswaAktif();
        $jum = 0;
        foreach ($siswaAktif as $siswa) {
            if ($siswa->aktif == 0) {
                $this->aktifkan($siswa);
                $jum +=1;
            }
        }

        $data = [
            'status' => true,
            'jumlah' => $jum,
            'msg'	 => $jum . ' siswa diaktifkan.'
        ];
        $this->output_json($data);
    }

    private function nonaktifkan($user, $nama) {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            $data = [
                'status'	=> false,
                'msg'		=> 'You must be an administrator to view this page.'
            ];
        } else {
            if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                $deleted = $this->ion_auth->delete_user($user->id);
                $data = [
                    'status'	=> $deleted,
                    'msg'	 => $deleted ? 'Siswa '.urldecode($nama).' dinonaktifkan.' : 'Siswa '.urldecode($nama).' gagal dinonaktifkan.'
                ];
            } else {
                $data = [
                    'status'	=> false,
                    'msg'	 => 'Anda bukan admin.'
                ];
            }
        }
        return $data;
    }

	public function deactivate($username, $nama) {
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
			$data = [
				'status'	=> false,
				'msg'		=> 'You must be an administrator to view this page.'
			];
		} else {
			$user = $this->users->getUsers($username);
            $data = $this->nonaktifkan($user, $nama);
		}

		$this->output_json($data, true);
	}

    public function reset_login($username, $nama) {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            $data = [
                'status'	=> false,
                'msg'		=> 'You must be an administrator to view this page.'
            ];
        } else {
            $this->db->where('login', $username);
            if ($this->db->delete('login_attempts')) {
                $data = [
                    'status'	=> true,
                    'msg'		=> 'User '.$nama.' berhasil direset'
                ];
            } else {
                $data = [
                    'status'	=> false,
                    'msg'		=> 'User '.$nama.' gagal direset'
                ];
            }
        }

        $this->output_json($data, true);
    }

	public function nonaktifkanSemua() {
		$siswaAktif = $this->users->getSiswaAktif();
		$jum = 0;
		foreach ($siswaAktif as $siswa) {
			if ($siswa->aktif > 0) {
                $del = $this->nonaktifkan($siswa, $siswa->nama);
                if ($del['status']) $jum +=1;
                else $this->output_json($del);
			}
		}
		$data = [
			'status' => true,
			'jumlah' => $jum,
			'msg'	 => $jum.' siswa dinonaktifkan.'
		];
		$this->output_json($data);
	}

	public function edit($id) {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswa = $this->master->getDataSiswaById($tp->id_tp, $smt->id_smt, $id);
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' 		=> $user,
            'judul'		=> 'User Management',
            'subjudul'	=> 'Edit Data User',
            'setting'		=> $this->dashboard->getSetting()
        ];
        $data['siswa'] = $siswa;
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        if($this->ion_auth->is_admin()){
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
            $this->load->view('_templates/dashboard/_header', $data);
            $this->load->view('users/siswa/edit');
            $this->load->view('_templates/dashboard/_footer');
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->load->view('members/guru/templates/header', $data);
            $this->load->view('users/siswa/edit');
            $this->load->view('members/guru/templates/footer');
        }
	}

	public function update() {
        $id_siswa = $this->input->post('id_siswa', true);
        $username = $this->input->post('username', true);
        $oldPass = $this->input->post('old', true);
        $newPass = $this->input->post('new', true);

        $this->form_validation->set_rules('username', 'Username', 'required|numeric|trim|min_length[6]|is_unique[master_siswa.username]');
        $this->form_validation->set_rules('old', 'Password Lama', 'required|numeric|trim|min_length[6]');
        $this->form_validation->set_rules('new', 'Password Baru', 'required|numeric|trim|min_length[6]');

        /*
        if($this->form_validation->run()===FALSE){
            //$this->session->set_flashdata('editsiswa', '<div id="flashdata" class="alert alert-default-danger align-content-center" role="alert"> test </div>');
            //redirect('usersiswa/edit/'.$id_siswa);
        } else {
            $insert = [
                "username"      => $username,
                "password"      => $this->input->post('password', true)];

            $data['insert'] = $this->master->create('master_siswa', $insert);
            $data['text'] = 'Siswa berhasil ditambahkan';
            $this->session->set_flashdata('editsiswa', '<div id="flashdata" class="alert alert-default-danger align-content-center" role="alert"> test </div>');
            redirect('usersiswa/edit/'.$id_siswa);
        }
        */
        //$this->output_json($siswa);
    }

	public function change_password() {
		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if ($this->form_validation->run() === FALSE){
			$data = [
				'status' => false,
				'errors' => [
					'old' => form_error('old'),
					'new' => form_error('new'),
					'new_confirm' => form_error('new_confirm')
				]
			];
		}else{
			$identity = $this->session->userdata('identity');
			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));
			if($change){
				$data['status'] = true;
			}
			else{
				$data = [
					'status' 	=> false,
					'msg'		=> $this->ion_auth->errors()
				];
			}
		}
		$this->output_json($data);
	}

	public function delete($id) {
		$this->is_has_access();
		$data['status'] = $this->ion_auth->delete_user($id) ? true : false;
		$this->output_json($data);
	}

    private function hash_password($password) {
        if (empty($password) || strpos($password, "\0") !== FALSE || strlen($password) > 4096) {
            return FALSE;
        }
        return password_hash($password, PASSWORD_BCRYPT);
    }

}
