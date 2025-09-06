<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_logs extends CI_Migration {

	public function up() {
        $fields = [
            'id_log' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
        ];
        $this->dbforge->modify_column('log_materi', $fields);
	 }
}