<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Linker extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function output_json($data, $encode = true) {
        if ($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index() {
        $this->load->view('linker');
        //$this->load->view('linker_old');
	}
}
