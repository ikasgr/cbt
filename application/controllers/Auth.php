<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * 
 * Handles authentication-related functionality including login, logout, 
 * user management, password changes, and role-based access control.
 * Supports roles: admin, guru, siswa, and pengawas.
 */
class Auth extends CI_Controller
{
    private $data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['form_validation', 'ion_auth']);
        $this->load->helper(['url', 'language']);
        $this->form_validation->set_error_delimiters(
            $this->config->item('error_start_delimiter', 'ion_auth'),
            $this->config->item('error_end_delimiter', 'ion_auth')
        );
        $this->lang->load('auth');
    }

    /**
     * Output data as JSON
     * 
     * @param array $data Data to be encoded as JSON
     */
    private function output_json($data)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    /**
     * Display login page or redirect to dashboard if logged in
     */
    public function index()
    {
        $this->load->model('Settings_model', 'settings');
        
        // Check if database tables exist
        if (count($this->db->list_tables()) == 0 || !$this->settings->getSetting()) {
            redirect('install');
        }

        // Redirect to dashboard if already logged in
        if ($this->ion_auth->logged_in()) {
            redirect('dashboard');
        }

        $this->data['setting'] = $this->settings->getSetting();
        $this->data['identity'] = [
            'name' => 'identity',
            'id' => 'identity',
            'type' => 'text',
            'placeholder' => 'Username',
            'autofocus' => 'autofocus',
            'class' => 'form-control',
            'autocomplete' => 'off'
        ];
        $this->data['password'] = [
            'name' => 'password',
            'id' => 'password',
            'type' => 'password',
            'placeholder' => 'Password',
            'class' => 'form-control',
        ];
        $this->data['message'] = validation_errors() ?: $this->session->flashdata('message');

        $this->load->view('_templates/auth/_header', $this->data);
        $this->load->view('auth/login');
        $this->load->view('_templates/auth/_footer');
    }

    /**
     * Process login attempt
     */
    public function cek_login()
    {
        $this->form_validation->set_rules('identity', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        if ($this->form_validation->run()) {
            $remember = (bool) $this->input->post('remember');
            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                $this->cek_akses();
            } else {
                $identity = $this->input->post('identity');
                $data = [
                    'status' => false,
                    'akses' => $this->ion_auth->is_max_login_attempts_exceeded($identity) ? 'attempts' : 'no attempts',
                    'failed' => $this->ion_auth->is_max_login_attempts_exceeded($identity)
                        ? 'Anda sudah 3x melakukan percobaan login, silakan hubungi Pengawas/Proktor'
                        : 'Username atau Password yang Anda masukan SALAH!'
                ];
                $this->output_json($data);
            }
        } else {
            $this->output_json([
                'status' => false,
                'invalid' => [
                    'identity' => form_error('identity'),
                    'password' => form_error('password')
                ],
                'akses' => 'no valid'
            ]);
        }
    }

    /**
     * Check user access and role
     */
    public function cek_akses()
    {
        if (!$this->ion_auth->logged_in()) {
            $data = ['status' => false, 'url' => 'auth'];
        } else {
            $this->load->model('Log_model', 'logging');
            $this->logging->saveLog(1, 'Login');

            // Determine user role
            $role = $this->ion_auth->is_admin() ? 'admin' :
                ($this->ion_auth->in_group('guru') ? 'guru' :
                ($this->ion_auth->in_group('siswa') ? 'siswa' :
                ($this->ion_auth->in_group('pengawas') ? 'pengawas' : 'unknown')));

            $data = [
                'status' => true,
                'url' => 'dashboard',
                'role' => $role
            ];
        }
        $this->output_json($data);
    }

    /**
     * Log out user
     */
    public function logout()
    {
        $this->ion_auth->logout();
        redirect('login', 'refresh');
    }

    /**
     * Change user password
     */
    public function change_password()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        $this->form_validation->set_rules('old', 'Old Password', 'required');
        $this->form_validation->set_rules('new', 'New Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');

        $user = $this->ion_auth->user()->row();

        if ($this->form_validation->run() === FALSE) {
            $this->data['message'] = validation_errors() ?: $this->session->flashdata('message');
            $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
            $this->data['old_password'] = ['name' => 'old', 'id' => 'old', 'type' => 'password'];
            $this->data['new_password'] = [
                'name' => 'new',
                'id' => 'new',
                'type' => 'password',
                'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$'
            ];
            $this->data['new_password_confirm'] = [
                'name' => 'new_confirm',
                'id' => 'new_confirm',
                'type' => 'password',
                'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$'
            ];
            $this->data['user_id'] = ['name' => 'user_id', 'id' => 'user_id', 'type' => 'hidden', 'value' => $user->id];

            $this->_render_page('auth/change_password', $this->data);
        } else {
            $identity = $this->session->userdata('identity');
            if ($this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'))) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('auth/change_password', 'refresh');
            }
        }
    }

    /**
     * Handle forgot password functionality
     */
    public function forgot_password()
    {
        $this->data['title'] = $this->lang->line('forgot_password_heading');
        $identity_column = $this->config->item('identity', 'ion_auth');

        $this->form_validation->set_rules(
            'identity',
            $identity_column != 'email' ? 'Username' : 'Email',
            $identity_column != 'email' ? 'required' : 'required|valid_email'
        );

        if ($this->form_validation->run() === FALSE) {
            $this->data['type'] = $identity_column;
            $this->data['identity'] = [
                'name' => 'identity',
                'id' => 'identity',
                'class' => 'form-control',
                'autocomplete' => 'off',
                'autofocus' => 'autofocus'
            ];
            $this->data['identity_label'] = $identity_column != 'email'
                ? $this->lang->line('forgot_password_identity_label')
                : $this->lang->line('forgot_password_email_identity_label');
            $this->data['message'] = validation_errors() ?: $this->session->flashdata('message');

            $this->load->view('_templates/auth/_header', $this->data);
            $this->load->view('auth/forgot_password');
            $this->load->view('_templates/auth/_footer');
        } else {
            $identity = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();
            if (empty($identity)) {
                $this->ion_auth->set_error($identity_column != 'email' ? 'forgot_password_identity_not_found' : 'forgot_password_email_not_found');
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('auth/forgot_password', 'refresh');
            }

            if ($this->ion_auth->forgotten_password($identity->{$identity_column})) {
                $this->session->set_flashdata('success', $this->ion_auth->messages());
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
            }
            redirect('auth/forgot_password', 'refresh');
        }
    }

    /**
     * Reset password using reset code
     * 
     * @param string|null $code Reset code
     */
    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_404();
        }

        $this->data['title'] = $this->lang->line('reset_password_heading');
        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            $this->form_validation->set_rules('new', 'New Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');

            if ($this->form_validation->run() === FALSE) {
                $this->data['message'] = validation_errors() ?: $this->session->flashdata('message');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = [
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$'
                ];
                $this->data['new_password_confirm'] = [
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$'
                ];
                $this->data['user_id'] = ['name' => 'user_id', 'id' => 'user_id', 'type' => 'hidden', 'value' => $user->id];
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;

                $this->load->view('_templates/auth/_header');
                $this->load->view('auth/reset_password', $this->data);
                $this->load->view('_templates/auth/_footer');
            } else {
                $identity = $user->{$this->config->item('identity', 'ion_auth')};
                if ($this->_valid_csrf_nonce() && $user->id == $this->input->post('user_id')) {
                    if ($this->ion_auth->reset_password($identity, $this->input->post('new'))) {
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        redirect('auth/login', 'refresh');
                    } else {
                        $this->session->set_flashdata('message', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code, 'refresh');
                    }
                } else {
                    $this->ion_auth->clear_forgotten_password_code($identity);
                    show_error($this->lang->line('error_csrf'));
                }
            }
        } else {
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect('auth/forgot_password', 'refresh');
        }
    }

    /**
     * Activate user account
     * 
     * @param int $id User ID
     * @param string|bool $code Activation code
     */
    public function activate($id, $code = FALSE)
    {
        $activation = $code ? $this->ion_auth->activate($id, $code) : ($this->ion_auth->is_admin() ? $this->ion_auth->activate($id) : FALSE);

        if ($activation) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect('auth', 'refresh');
        } else {
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect('auth/forgot_password', 'refresh');
        }
    }

    /**
     * Deactivate user account
     * 
     * @param int|string|null $id User ID
     */
    public function deactivate($id = NULL)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            show_error('You must be an administrator to view this page.');
        }

        $id = (int) $id;
        $this->form_validation->set_rules('confirm', 'Confirmation', 'required');
        $this->form_validation->set_rules('id', 'User ID', 'required|alpha_numeric');

        if ($this->form_validation->run() === FALSE) {
            $this->data['csrf'] = $this->_get_csrf_nonce();
            $this->data['user'] = $this->ion_auth->user($id)->row();
            $this->_render_page('auth/deactivate_user', $this->data);
        } else {
            if ($this->input->post('confirm') == 'yes' && $this->_valid_csrf_nonce() && $id == $this->input->post('id')) {
                $this->ion_auth->deactivate($id);
            }
            redirect('auth', 'refresh');
        }
    }

    /**
     * Create new user
     */
    public function create_user()
    {
        $this->data['title'] = $this->lang->line('create_user_heading');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $tables = $this->config->item('tables', 'ion_auth');
        $identity_column = $this->config->item('identity', 'ion_auth');
        $this->data['identity_column'] = $identity_column;

        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone', 'trim');
        $this->form_validation->set_rules('company', 'Company', 'trim');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required');

        if ($identity_column !== 'email') {
            $this->form_validation->set_rules('identity', 'Username', 'trim|required|is_unique[' . $tables['users'] . '.' . $identity_column . ']');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        } else {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[' . $tables['users'] . '.email]');
        }

        if ($this->form_validation->run()) {
            $email = strtolower($this->input->post('email'));
            $identity = $identity_column === 'email' ? $email : $this->input->post('identity');
            $password = $this->input->post('password');

            $additional_data = [
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone')
            ];

            if ($this->ion_auth->register($identity, $password, $email, $additional_data)) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('auth', 'refresh');
            }
        }

        $this->data['message'] = validation_errors() ?: ($this->ion_auth->errors() ?: $this->session->flashdata('message'));
        $this->data['first_name'] = ['name' => 'first_name', 'id' => 'first_name', 'type' => 'text', 'value' => $this->form_validation->set_value('first_name')];
        $this->data['last_name'] = ['name' => 'last_name', 'id' => 'last_name', 'type' => 'text', 'value' => $this->form_validation->set_value('last_name')];
        $this->data['identity'] = ['name' => 'identity', 'id' => 'identity', 'type' => 'text', 'value' => $this->form_validation->set_value('identity')];
        $this->data['email'] = ['name' => 'email', 'id' => 'email', 'type' => 'text', 'value' => $this->form_validation->set_value('email')];
        $this->data['company'] = ['name' => 'company', 'id' => 'company', 'type' => 'text', 'value' => $this->form_validation->set_value('company')];
        $this->data['phone'] = ['name' => 'phone', 'id' => 'phone', 'type' => 'text', 'value' => $this->form_validation->set_value('phone')];
        $this->data['password'] = ['name' => 'password', 'id' => 'password', 'type' => 'password', 'value' => $this->form_validation->set_value('password')];
        $this->data['password_confirm'] = ['name' => 'password_confirm', 'id' => 'password_confirm', 'type' => 'password', 'value' => $this->form_validation->set_value('password_confirm')];

        $this->_render_page('auth/create_user', $this->data);
    }

    /**
     * Redirect user based on admin status
     */
    public function redirectUser()
    {
        redirect($this->ion_auth->is_admin() ? 'auth' : '/', 'refresh');
    }

    /**
     * Edit user details
     * 
     * @param int|string $id User ID
     */
    public function edit_user($id)
    {
        $this->data['title'] = $this->lang->line('edit_user_heading');

        if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && $this->ion_auth->user()->row()->id != $id)) {
            redirect('auth', 'refresh');
        }

        $user = $this->ion_auth->user($id)->row();
        $groups = $this->ion_auth->groups()->result_array();
        $currentGroups = $this->ion_auth->get_users_groups($id)->result();

        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone', 'trim');
        $this->form_validation->set_rules('company', 'Company', 'trim');

        if (!empty($_POST)) {
            if ($this->_valid_csrf_nonce() && $id == $this->input->post('id')) {
                if ($this->input->post('password')) {
                    $this->form_validation->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
                    $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required');
                }

                if ($this->form_validation->run()) {
                    $data = [
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone')
                    ];

                    if ($this->input->post('password')) {
                        $data['password'] = $this->input->post('password');
                    }

                    if ($this->ion_auth->is_admin()) {
                        $this->ion_auth->remove_from_group('', $id);
                        $groupData = $this->input->post('groups');
                        if (!empty($groupData)) {
                            foreach ($groupData as $grp) {
                                $this->ion_auth->add_to_group($grp, $id);
                            }
                        }
                    }

                    if ($this->ion_auth->update($user->id, $data)) {
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                    } else {
                        $this->session->set_flashdata('message', $this->ion_auth->errors());
                    }
                    $this->redirectUser();
                }
            } else {
                show_error($this->lang->line('error_csrf'));
            }
        }

        $this->data['csrf'] = $this->_get_csrf_nonce();
        $this->data['message'] = validation_errors() ?: ($this->ion_auth->errors() ?: $this->session->flashdata('message'));
        $this->data['user'] = $user;
        $this->data['groups'] = $groups;
        $this->data['currentGroups'] = $currentGroups;

        $this->data['first_name'] = ['name' => 'first_name', 'id' => 'first_name', 'type' => 'text', 'value' => $this->form_validation->set_value('first_name', $user->first_name)];
        $this->data['last_name'] = ['name' => 'last_name', 'id' => 'last_name', 'type' => 'text', 'value' => $this->form_validation->set_value('last_name', $user->last_name)];
        $this->data['company'] = ['name' => 'company', 'id' => 'company', 'type' => 'text', 'value' => $this->form_validation->set_value('company', $user->company)];
        $this->data['phone'] = ['name' => 'phone', 'id' => 'phone', 'type' => 'text', 'value' => $this->form_validation->set_value('phone', $user->phone)];
        $this->data['password'] = ['name' => 'password', 'id' => 'password', 'type' => 'password'];
        $this->data['password_confirm'] = ['name' => 'password_confirm', 'id' => 'password_confirm', 'type' => 'password'];

        $this->_render_page('auth/edit_user', $this->data);
    }

    /**
     * Create new group
     */
    public function create_group()
    {
        $this->data['title'] = $this->lang->line('create_group_title');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $this->form_validation->set_rules('group_name', 'Group Name', 'trim|required|alpha_dash');

        if ($this->form_validation->run() && $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'))) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect('auth', 'refresh');
        }

        $this->data['message'] = validation_errors() ?: ($this->ion_auth->errors() ?: $this->session->flashdata('message'));
        $this->data['group_name'] = ['name' => 'group_name', 'id' => 'group_name', 'type' => 'text', 'value' => $this->form_validation->set_value('group_name')];
        $this->data['description'] = ['name' => 'description', 'id' => 'description', 'type' => 'text', 'value' => $this->form_validation->set_value('description')];

        $this->_render_page('auth/create_group', $this->data);
    }

    /**
     * Edit existing group
     * 
     * @param int|string $id Group ID
     */
    public function edit_group($id)
    {
        if (!$id) {
            redirect('auth', 'refresh');
        }

        $this->data['title'] = $this->lang->line('edit_group_title');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $group = $this->ion_auth->group($id)->row();
        $this->form_validation->set_rules('group_name', 'Group Name', 'trim|required|alpha_dash');

        if (!empty($_POST) && $this->form_validation->run()) {
            if ($this->ion_auth->update_group($id, $_POST['group_name'], ['description' => $_POST['group_description']])) {
                $this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
            }
            redirect('auth', 'refresh');
        }

        $this->data['message'] = validation_errors() ?: ($this->ion_auth->errors() ?: $this->session->flashdata('message'));
        $this->data['group'] = $group;
        $this->data['group_name'] = [
            'name' => 'group_name',
            'id' => 'group_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_name', $group->name),
            'readonly' => $this->config->item('admin_group', 'ion_auth') === $group->name ? 'readonly' : null
        ];
        $this->data['group_description'] = [
            'name' => 'group_description',
            'id' => 'group_description',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_description', $group->description)
        ];

        $this->_render_page('auth/edit_group', $this->data);
    }

    /**
     * Generate CSRF nonce
     * 
     * @return array CSRF key-value pair
     */
    private function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return [$key => $value];
    }

    /**
     * Validate CSRF nonce
     * 
     * @return bool Whether the posted CSRF token matches
     */
    private function _valid_csrf_nonce()
    {
        $csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
        return $csrfkey && $csrfkey === $this->session->flashdata('csrfvalue');
    }

    /**
     * Render view page
     * 
     * @param string $view View name
     * @param array|null $data Data to pass to view
     * @param bool $returnhtml Return HTML instead of rendering
     * @return mixed
     */
    private function _render_page($view, $data = NULL, $returnhtml = FALSE)
    {
        $viewdata = empty($data) ? $this->data : $data;
        return $this->load->view($view, $viewdata, $returnhtml);
    }
}