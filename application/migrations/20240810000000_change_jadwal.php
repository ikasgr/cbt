<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_jadwal extends CI_Migration {

	public function up() {
        $fields = [
            'id_jadwal' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
            'id_mapel' => [
                'type' => 'INT',
            ],
        ];
        $this->dbforge->modify_column('kelas_jadwal_mapel', $fields);
	 }
}