<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Created by IntelliJ IDEA.
 * User: ServerMts
 * Date: 18/12/2021
 * Time: 15:45
 */

class Migrate extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        } else if (!$this->ion_auth->is_admin()) {
            show_error('Hanya Admin yang boleh mengakses halaman ini', 403, 'Akses dilarang');
        }
    }

    public function output_json($data, $encode = true)
    {
        if($encode) $data = json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index(){
        //echo 'Controller file index method run.';
        /*
        $this->load->library('migration');

        if ($this->migration->current() === FALSE) {
            show_error($this->migration->error_string());
        }
        */
        $this->load->view('install/header');
        $this->load->view('setting/data');
        $this->load->view('install/footer');
    }

    public function updateApp(){
        $this->load->dbutil();
        $this->dbutil->optimize_database();

        $prefs = [
            'tables' => $this->db->list_tables(),
            'ignore' => array(),           // List of tables to omit from the backup
            'format' => 'zip',             // gzip, zip, txt
            'filename' => 'backup.sql',    // File name - NEEDED ONLY WITH ZIP FILES
            'add_drop' => TRUE,              // Whether to add DROP TABLE statements to backup file
            'add_insert' => TRUE,              // Whether to add INSERT data to backup file
            'newline' => "\n"               // Newline character used in backup file
        ];

        $backup = $this->dbutil->backup($prefs);
        $this->load->helper('file');
        write_file('./backups/backup-db-' . date("Y-m-d-H-i-s") . '.sql.zip', $backup);

        $this->load->library('migration');
        if ($this->migration->current() === FALSE) {
            $data['success'] = false;
            $data['message'] = $this->migration->error_string();
            //show_error($this->migration->error_string());
        } else {
            $data['success'] = true;
            $data['message'] = 'The migration file has executed successfully.';
        }
        $this->output_json($data);
        /*
        if(isset($version) && ($this->migration->version($version) === FALSE)){
            show_error($this->migration->error_string());
        } elseif(is_null($version) && $this->migration->latest() === FALSE){
            show_error($this->migration->error_string());
        } else {
            echo 'The migration file has executed successfully.';
        }*/
    }

    public function undoMigration($version = NULL){
        $this->load->library('migration');
        $migrations = $this->migration->find_migrations();
        $migrationKeys = array();
        foreach($migrations as $key => $migration){
            $migrationKeys[] = $key;
        }
        if(isset($version) && array_key_exists($version,$migrations) && $this->migration->version($version)){
            echo 'The migration was undo';
            exit;
        } elseif(isset($version) && !array_key_exists($version,$migrations)){
            echo 'The migration with selected version doesn’t exist.';
        } else {
            $penultimate = (sizeof($migrationKeys)==1) ? 0 : $migrationKeys[sizeof($migrationKeys) - 2];
            if($this->migration->version($penultimate)){
                echo 'The migration has been reverted successfully.';
                exit;
            } else {
                echo 'Couldn\’t roll back the migration.';
                exit;
            }
        }
    }

    public function resetMigration(){
        $this->load->library('migration');
        if($this->migration->current()!== FALSE){
            echo 'The migration was revert to the version set in the config file.';
            return TRUE;
        } else {
            echo 'Couldn\’t reset migration.';
            show_error($this->migration->error_string());
            exit;
        }
    }

    function make_base(){

        $this->load->library('ci_migrations_generator/Sqltoci');

        // All Tables:
        //$this->sqltoci->generate();

        //Single Table:
        $this->sqltoci->generate('kelas_jadwal_kbm');

    }
}