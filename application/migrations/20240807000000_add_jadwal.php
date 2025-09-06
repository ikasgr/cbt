<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_jadwal extends CI_Migration {

	public function up() {
        $fields = [
            'dari' => ['type' => 'VARCHAR', 'constraint' => '10'],
            'sampai' => ['type' => 'VARCHAR', 'constraint' => '10']
        ];
        $this->dbforge->add_column('kelas_jadwal_mapel', $fields);
	 }
}