<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_kbm extends CI_Migration {

	public function up() {
        $fields = [
            'kbm_jam_selesai' => ['type' => 'VARCHAR', 'constraint' => '5', 'after' => 'kbm_jam_mulai'],
            'libur INT NOT NULL DEFAULT 0'
        ];
        $this->dbforge->add_column('kelas_jadwal_kbm', $fields);
	 }
}