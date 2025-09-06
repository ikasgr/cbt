<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_materi extends CI_Migration {

	public function up() {
        $fields = [
            'id_kjm' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
        ];
        $this->dbforge->modify_column('kelas_jadwal_materi', $fields);
	 }
}