<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userpengawas extends CI_Controller {

	public function __construct(){
		parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        } else if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('pengawas')) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
		$this->load->library(['datatables', 'form_validation']);// Load Library Ignited-Datatables
		$this->load->model('Users_model', 'users');
		$this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
		$this->form_validation->set_error_delimiters('','');
	}

	public function output_json($data, $encode = true) {
        if($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
	}

    public function data(){
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $this->output_json($this->users->getUserpengawas($tp->id_tp, $smt->id_smt), false);
    }

    public function index() {
		$user = $this->ion_auth->user()->row();
		$group = $this->ion_auth->get_users_groups($user->id)->row()->name;

		$data = [
			'user' => $user,
			'judul'	=> 'User Management',
			'subjudul'=> 'Data User pengawas',
            'profile'		=> $this->dashboard->getProfileAdmin($user->id),
            'setting'		=> $this->dashboard->getSetting()
        ];

		if ($group === 'admin') {
			$data['tp'] = $this->dashboard->getTahun();
			$data['tp_active'] = $this->dashboard->getTahunActive();
			$data['smt'] = $this->dashboard->getSemester();
			$data['smt_active'] = $this->dashboard->getSemesterActive();

			$this->load->view('_templates/dashboard/_header', $data);
			$this->load->view('users/pengawas/data');
			$this->load->view('_templates/dashboard/_footer');
		} else {
			$id = $this->users->getPengawasByUsername($user->username);
			$this->edit($id->id_pengawas);
		}
	}

	public function activate($id) {
		//$id = $this->input->get('id', true);
		$pengawas = $this->users->getDataPuru($id);
		$nama = explode(' ', $pengawas->nama_pengawas ?? '');

		$first_name = $nama[0];
		$last_name = count($nama)>2 ? $nama[1] : end($nama);
		$username = trim($pengawas->username ?? '');
		$password = trim($pengawas->password ?? '');
		$email = strtolower($pengawas->username ?? '').'@pengawas.com';
		$additional_data = [
			'first_name'	=> $first_name,
			'last_name'		=> $last_name
		];
		$group = array('2');

		if ($this->ion_auth->username_check($username)) {
			$data = [
				'status' => false,
				'msg'	 => 'Username '.$username.' tidak tersedia (sudah digunakan).'
			];
		} else if ($this->ion_auth->email_check($email)) {
			$data = [
				'status' => false,
				'msg'	 => 'Username '.$email.' tidak tersedia (sudah digunakan).'
			];
		} else {
			$id_user = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
			$data = [
				'status'	=> true,
				'msg'	 => 'Akun '.$pengawas->nama_pengawas.' diaktifkan.'
			];

			$this->db->set('id_user', $id_user);
			$this->db->where('id_pengawas', $id);
			$this->db->update('master_pengawas');
		}

		$data['pass'] = $password;
		$this->output_json($data);
	}

	public function deactivate($id = NULL) {
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
			$data = [
				'status'	=> false,
				'msg'		=> 'You must be an administrator to view this page.'
			];
		} else {
			$id = (int)$id;
			if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
				$deleted = $this->ion_auth->delete_user($id);

				$data = [
					'status'	=> $deleted,
					'msg'	 => 'telah dinonaktifkan.'
				];
			} else {
				$data = [
					'status'	=> false,
					'msg'	 => 'Anda bukan admin.'
				];
			}
		}

		$this->output_json($data);
	}

    public function aktifkanSemua() {
        $pengawasAktif = $this->users->getPengawasAktif();
        $jum = 0;
        foreach ($pengawasAktif as $pengawas) {
            if ($pengawas->aktif > 0) {
                continue;
            } else {
                $this->activate($pengawas->id_pengawas);
            }
            $jum +=1;
        }

        $data = [
            'status' => true,
            'jumlah' => $jum,
            'msg'	 => $jum . ' pengawas diaktifkan.'
        ];
        $this->output_json($data);
    }

    public function nonaktifkanSemua() {
        $pengawasAktif = $this->users->getPengawasAktif();
        $jum = 0;
        foreach ($pengawasAktif as $pengawas) {
            if ($pengawas->aktif > 0) {
                $del = $this->deactivate($pengawas->id, "");
                $this->output_json($del);
            } else {
                continue;
            }
            $jum +=1;
        }
        $data = [
            'status' => true,
            'jumlah' => $jum,
            'msg'	 => $jum.' pengawas dinonaktifkan.'
        ];
        $this->output_json($data);
    }

    public function edit($id) {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
		$pengawas = $this->users->getDetailPengawas($id);
		$users = $this->users->getUsers($pengawas->username);
		$user = $this->ion_auth->user()->row();
		$data = [
			'user' 		=> $user,
			'judul'		=> 'User Management',
			'subjudul'	=> 'Edit Data User',
            'setting'		=> $this->dashboard->getSetting()
		];
		$data['users'] = $users;
		$data['pengawas'] = $pengawas;
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;


        $group = $this->ion_auth->get_users_groups($user->id)->row()->name;
		if ($group === 'admin') {
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
			$data['groups'] = $this->ion_auth->groups()->result();

			$data['kelass'] = $this->users->getKelas($tp->id_tp, $smt->id_smt);
			$data['mapels'] = $this->users->getMapel();
			$data['levels'] = $this->users->getLevelPengawas();

			$this->load->view('_templates/dashboard/_header', $data);
			$this->load->view('users/pengawas/edit');
			$this->load->view('_templates/dashboard/_footer');
		} else {
			$this->load->view('members/pengawas/templates/header', $data);
			$this->load->view('users/pengawas/edit');
			$this->load->view('members/pengawas/templates/footer');
		}
	}

	public function editLogin() {
        $id_pengawas 	= $this->input->post('id_pengawas', true);
        $username 	= $this->input->post('username', true);
        $pass 	= $this->input->post('new', true);
        $pengawas_lain = $this->master->getUserIdPengawasByUsername($username);

		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

        if ($pengawas_lain && $pengawas_lain->id_pengawas != $id_pengawas) {
            $data = [
                //'pengawas' => $pengawas,
                'status' => false,
                'errors' => ['username' => 'Username sudah digunakan']
            ];

        } elseif ($this->form_validation->run() === FALSE){
			$data = [
				'status' => false,
				'errors' => [
                    //'username' => form_error('username'),
					'old' => form_error('old'),
					'new' => form_error('new'),
					'new_confirm' => form_error('new_confirm')
				]
			];
		} else {
            $pengawas = $this->db->get_where('master_pengawas', 'id_pengawas="'.$id_pengawas.'"')->row();
            $nama = explode(' ', $pengawas->nama_pengawas ?? '');
            $first_name = $nama[0];
            $last_name = end($nama);
            $username = trim($username ?? '');
            $password = trim($pass ?? '');
            $email = strtolower($username).'@pengawas.com';
            $additional_data = [
                'first_name'	=> $first_name,
                'last_name'		=> $last_name
            ];
            $group = array('2');
            $user_pengawas = $this->db->get_where('users', 'email="'.$email.'"')->row();
            $deleted = true;
            if ($user_pengawas != null) {
                $deleted = $this->ion_auth->delete_user((int)$user_pengawas->id);
            }
            if ($deleted) {
                $id_user = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
                $this->db->set('username', $username);
                $this->db->set('password', $password);
                $this->db->set('id_user', $id_user);
                $this->db->where('id_pengawas', $id_pengawas);
                $status = $this->db->update('master_pengawas');
                $msg	 = $status ? 'Update berhasil' : 'Gagal mengganti username/passsword';
            } else {
                $status = false;
                $msg	 = 'Gagal mengganti username/passsword';
            }

            $data['status'] = $status;
            $data['text'] = $msg;
        }
		$this->output_json($data);
	}

	function buangspasi($teks){
		$teks= trim($teks ?? '');
		$hasil=$teks;
		while( strpos($teks, ' ')){
			$remove[] = "'";
			$remove[] = ".";
			$remove[] = " ";
			$hasil= str_replace($remove, '', $teks ?? '');
		}
		return $hasil;
	}

    private function registerPengawas($username, $password, $email, $additional_data, $group) {
        $reg = $this->ion_auth->register($username, $password, $email, $additional_data, $group);
        $data['status'] = true;
        $data['id'] = $reg;
        if ($reg == false) {
            $data['status'] = false;
        }
        return $data;
    }

    public function reset_login() {
        $username 	= $this->input->get('username', true);
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
                    'msg'		=> ' berhasil direset'
                ];
            } else {
                $data = [
                    'status'	=> false,
                    'msg'		=> ' gagal direset'
                ];
            }
        }

        $this->output_json($data, true);
    }
}
