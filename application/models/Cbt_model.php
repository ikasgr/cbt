<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 17:34
 */

class Cbt_model extends CI_Model {

	public function get_where($table, $pk, $id, $join = null, $order = null) {
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($pk, $id);

		if($join !== null){
			foreach($join as $table => $field){
				$this->db->join($table, $field);
			}
		}

		if($order !== null){
			foreach($order as $field => $sort){
				$this->db->order_by($field, $sort);
			}
		}

		$query = $this->db->get();
		return $query;
	}

    /**
     * @param $id_siswa
     * @param $id_jadwal
     * @param $type
     * @param $desc
     * @return bool
     */
    public function saveLog($id_siswa, $id_jadwal, $type, $desc) {
		if ($this->agent->is_browser()){
			$agent = $this->agent->browser().' '.$this->agent->version();
		} elseif ($this->agent->is_mobile()){
			$agent = $this->agent->mobile();
		}else{
			return false;
		}

        $os = $this->agent->platform();
        $ip = $this->input->ip_address();
        return $this->insertLog($id_siswa, $id_jadwal, $type, $desc, $agent, $os, $ip);
    }

	/*
	 * SEMUA
	 * 1 = memulai ujian
	 * 2 = selesai ujian
	 */

    /**
     * @param $id_siswa
     * @param $id_jadwal
     * @param $type
     * @param $desc
     * @param $agent
     * @param $os
     * @param $ip
     * @return bool
     */
    private function insertLog($id_siswa, $id_jadwal, $type, $desc, $agent, $os, $ip) {
        $log = $this->db->where('id_log', $id_siswa.'0'.$id_jadwal.$type)->get('log_ujian')->row();
        if ($log != null) {
            $this->db->set('log_type', $type);
            $this->db->set('log_desc', $desc);
            $this->db->where('id_log', $id_siswa .'0'. $id_jadwal . $type);
            $insert = $this->db->update('log_ujian');
        } else {
            $data = array(
                'id_log' => $id_siswa.'0'.$id_jadwal.$type,
                'id_siswa' => $id_siswa,
                'id_jadwal' => $id_jadwal,
                'log_type' => $type,
                'log_desc' => $desc,
                'address' => $ip,
                'agent' => $agent,
                'device' => $os,
            );

            $insert = $this->db->insert('log_ujian', $data);
        }
		return $insert;
	}

    public function getDataSiswa($username, $id_tp, $id_smt) {
        $this->db->select('a.id_siswa, a.nisn, a.nis, a.nama, a.jenis_kelamin, a.username, a.password, a.agama, a.foto,'.
            ' b.id_kelas_siswa, b.id_tp, b.id_smt, b.id_siswa, b.id_kelas,'.
            ' c.nama_kelas, c.kode_kelas, c.level_id, '.
            ' d.kelas_id, d.ruang_id, d.sesi_id');
        $this->db->from('master_siswa a');
        $this->db->join('kelas_siswa b', 'a.id_siswa=b.id_siswa AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
        $this->db->join('master_kelas c', 'b.id_kelas=c.id_kelas AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt, 'left');
        $this->db->join('cbt_sesi_siswa d', 'a.id_siswa=d.siswa_id', 'left');
        $this->db->where('username', $username);
        $query = $this->db->get()->row();
        return $query;
    }

    public function getDataSiswaById($id_tp, $id_smt, $idSiswa) {
        $this->db->select('b.id_siswa, b.nama, b.jenis_kelamin, b.nis, b.nisn, b.username, b.password,'.
            ' b.foto, c.sesi_id, d.kode_ruang, e.kode_sesi, f.nama_kelas, g.nomor_peserta,'.
            ' h.set_siswa, i.kode_ruang as ruang_kelas, j.kode_sesi as sesi_kelas');
        $this->db->from('kelas_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'left');
        $this->db->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=c.ruang_id', 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=c.sesi_id', 'left');
        $this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp, 'left');

        $this->db->join('cbt_kelas_ruang h', 'h.id_kelas=a.id_kelas', 'left');
        $this->db->join('cbt_ruang i', 'i.id_ruang=h.id_ruang', 'left');
        $this->db->join('cbt_sesi j', 'j.id_sesi=h.id_sesi', 'left');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_siswa', $idSiswa);

        return $this->db->get()->row();
    }

    public function getWaktuSesiById($id_sesi) {
		$this->db->select('*');
		$this->db->where('id_sesi', $id_sesi);
		$result = $this->db->get('cbt_sesi')->row();
		return $result;
	}

	public function getAllRuang() {
        $this->db->select('id_ruang, nama_ruang, kode_ruang');
		$result = $this->db->get('cbt_ruang')->result();
		$ret = [];
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_ruang] = $row->kode_ruang;
			}
		}
		return $ret;
	}

	public function getKelasByLevel($level, $arrKelas) {
		$this->db->select('id_kelas, kode_kelas');
		$this->db->where('level_id', $level);
		$this->db->where_in('id_kelas', $arrKelas);
		$result = $this->db->get('master_kelas')->result();
		return $result;
	}

	public function getAllJurusan() {
		$result = $this->db->get('master_jurusan')->result();
        $ret = [];
        if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_jurusan] = $row->kode_jurusan;
			}
		}
		return $ret;
	}

    public function getPengawas($id_pengawas) {
        $this->db->select('id_pengawas, id_jadwal, id_tp, id_smt, id_ruang, id_sesi, id_guru');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_pengawas', $id_pengawas);
        return $this->db->get()->row();
    }

    public function getPengawasByGuru($tp, $smt, $id_guru) {
        $this->db->select('a.id_pengawas, a.id_jadwal, a.id_tp, a.id_smt, a.id_ruang, a.id_sesi, a.id_guru,'.
            ' b.id_jadwal, b.tgl_mulai, b.tgl_selesai, c.bank_kode, d.kode_jenis');
        $this->db->from('cbt_pengawas a');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->like('a.id_guru', $id_guru);
        $this->db->join('cbt_jadwal b', 'b.id_jadwal=a.id_jadwal');
        $this->db->join('cbt_bank_soal c', 'b.id_bank=c.id_bank');
        $this->db->join('cbt_jenis d', 'd.id_jenis=b.id_jenis', "left");
        return $this->db->get()->result();
    }

    public function getPengawasByJadwal($tp, $smt, $id_jadwal, $sesi=null, $ruang=null) {
        $this->db->select('id_pengawas, id_guru');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->where('id_jadwal', $id_jadwal);
        if ($sesi != null) {
            $this->db->where('id_sesi', $sesi);
        }
        if ($ruang != null) {
            $this->db->where('id_ruang', $ruang);
        }
        return $this->db->get()->result();
    }

    public function getAllPengawas($tp, $smt, $ruang=null, $sesi=null) {
        $this->db->select('id_pengawas, id_jadwal, id_ruang, id_sesi, id_guru');
        $this->db->from('cbt_pengawas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        if ($ruang!=null) $this->db->where('id_ruang', $ruang);
        if ($sesi!=null) $this->db->where('id_sesi', $sesi);
		$result = $this->db->get()->result();
		$ret = [];
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_jadwal][$row->id_ruang][$row->id_sesi] = $row;
			}
		}
		return $ret;
	}

    public function getDistinctRuang($tp, $smt, $arrKelas) {
        $this->db->distinct('a.ruang_id');
        $this->db->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        if (count($arrKelas)>0) $this->db->where_in('kelas_id', $arrKelas);
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->ruang_id][$row->sesi_id] = $row;
            }
        }
        return $ret;
    }

    public function getKelasUjian($kelas_id) {
        //$this->db->distinct('kelas_id');
        $this->db->select('kelas_id, ruang_id, sesi_id');
        $this->db->from('cbt_sesi_siswa');
        $this->db->where('kelas_id', $kelas_id);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->ruang_id][$row->sesi_id][] = $row->kelas_id;
            }
        }
        return $ret;
    }

    public function getDistinctKelasLevel($tp, $smt, $arrLevel) {
        $this->db->select('id_kelas, level_id');
        $this->db->distinct();
        $this->db->from('master_kelas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->where_in('level_id', $arrLevel);
        $result = $this->db->get()->result();
        return $result;
    }
    /*
    public function getAllDataPengawas($jenis, $dari=null, $sampai=null) {
        $this->db->select('a.*,'.
            ' e.id_tp, e.tahun, f.id_smt, f.nama_smt, g.level, b.bank_kode, b.bank_level,'.
            ' b.bank_kelas, c.kode_jenis, d.kode, d.nama_mapel,'.
            ' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, b.bank_guru_id,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        $this->db->join('master_tp e', 'a.id_tp=e.id_tp');
        $this->db->join('master_smt f', 'a.id_smt=f.id_smt');
        $this->db->join('level_kelas g', 'b.bank_level=g.id_level');

        $this->db->where('a.id_jenis', $jenis);
        if ($dari != null) $this->db->where('a.tgl_mulai >=', $dari);
        if ($sampai != null) $this->db->where('a.tgl_mulai <=', $sampai);

        $this->db->order_by('b.bank_level', 'ASC');
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $query = $this->db->get()->result();
        return $query;
    }
    */

    public function getAllJenisUjian() {
        $this->db->select('id_jenis, nama_jenis, kode_jenis');
		$result = $this->db->get('cbt_jenis')->result();
		$ret [''] = 'Jenis Penilaian :';
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_jenis] = $row->kode_jenis;
			}
		}
		return $ret;
	}

    public function getAllJenisUjianByArrJenis($arrJenis) {
        $this->db->where_in('id_jenis', $arrJenis);
        $result = $this->db->get('cbt_jenis')->result();
        $ret [''] = 'Jenis Penilaian :';
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_jenis] = $row->kode_jenis;
            }
        }
        return $ret;
    }

    /*
    public function getJadwalHariIni($tgl) {
        $this->db->select('a.*, c.bank_kode, c.bank_level, c.bank_kelas, b.kode_jenis, b.nama_jenis, d.kode, d.id_mapel, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->where('a.tgl_mulai', $tgl);
        $this->db->where('a.status', '1');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        $this->db->order_by('c.bank_level', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_mapel][] = $row;
            }
        }
        return $ret;
    }
    */

    public function getPengawasHariIni($tgl) {
        $this->db->from('cbt_jadwal a');
        $this->db->where("a.tgl_mulai <= '$tgl' AND a.tgl_selesai >= '$tgl'");
        $this->db->join('cbt_pengawas b', 'b.id_jadwal=a.id_jadwal');
        $this->db->where('status', '1');
        return $this->db->get()->result();
    }

    /*
    public function getAllInfoJadwal() {
        $this->db->select('a.*, c.bank_kode, c.bank_level, c.bank_kelas, b.kode_jenis, b.nama_jenis, d.kode, d.id_mapel, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        //$this->db->where('a.tgl_mulai', $tgl);
        $this->db->where('a.status', '1');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        $this->db->order_by('a.jam_ke', 'ASC');
        $this->db->order_by('c.bank_level', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->tgl_mulai][$row->id_mapel][] = $row;
            }
        }
        return $ret;
    }
    */

    public function getJadwalGuru($tp, $smt, $guru) {
        $this->db->select('a.id_jadwal, a.tgl_mulai, b.bank_kode, b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank AND b.bank_guru_id='.$guru);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        return $this->db->get()->result();
    }

    public function getJadwalKelas($tp, $smt) {
        $this->db->select('a.id_jadwal, a.tgl_mulai, b.bank_kode, b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        return $this->db->get()->result();
    }

    public function getJadwalByJenis($jenis, $level, $dari, $sampai) {
        $this->db->select('a.id_jadwal, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.jam_ke,'.
            ' c.bank_kode, c.bank_level, c.bank_kelas, b.kode_jenis, b.nama_jenis, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        $this->db->where('a.id_jenis', $jenis);
        if ($level != '0') $this->db->where('c.bank_level', $level);
        if ($dari != null) $this->db->where('a.tgl_mulai >=', $dari);
        if ($sampai != null) $this->db->where('a.tgl_mulai <=', $sampai);
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $this->db->order_by('a.jam_ke', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllJadwalByJenis($jenis, $tp, $smt) {
        $this->db->select('a.id_jadwal, a.id_jenis, a.tgl_mulai, '.
            'c.bank_kode, c.bank_level, c.bank_kelas, b.kode_jenis, b.nama_jenis, d.id_mapel, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        if ($jenis != null) $this->db->where('a.id_jenis', $jenis);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $this->db->order_by('a.jam_ke', 'ASC');
        $this->db->order_by('c.bank_level', 'ASC');

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->tgl_mulai][$row->id_mapel][] = $row;
            }
        }
        return $ret;
    }

    public function getAllBankSoal($guru=null) {
		$this->db->select('id_bank, bank_kode');
		if ($guru !== null) {
			$this->db->where('bank_guru_id', $guru);
		}
		$result = $this->db->get('cbt_bank_soal')->result();
		$ret ['0'] = 'Pilih Bank Soal :';
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_bank] = $row->bank_kode;
			}
		}
		return $ret;
	}

    public function getAllBankSoalByTp($id_tp, $id_smt, $guru=null) {
        $this->db->select('id_bank, bank_kode, bank_mapel_id, tampil_pg, tampil_kompleks, tampil_jodohkan, tampil_isian, tampil_esai');
        $this->db->where('id_tp', $id_tp);
        $this->db->where('id_smt', $id_smt);
        $this->db->where('status', '1');
        $this->db->where('status_soal', '1');
        if ($guru !== null) {
            $this->db->where('bank_guru_id', $guru);
        }
        $result = $this->db->get('cbt_bank_soal')->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_bank] = $row;
            }
        }
        return $ret;
        //if (count($ret) > 1) return $ret;
        //else return $this->getAllBankSoal($guru);
    }

    public function getAllBankSoalByMapel($id_tp, $id_smt, $mapel) {
        $this->db->select('id_bank, bank_kode, bank_mapel_id, tampil_pg, tampil_kompleks, tampil_jodohkan, tampil_isian, tampil_esai, status');
        $this->db->from('cbt_bank_soal');
        $this->db->where('id_tp', $id_tp);
        $this->db->where('id_smt', $id_smt);
        $this->db->where('bank_mapel_id', $mapel);
        $this->db->where('status', '1');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_bank] = $row;
            }
        }
        return $ret;
    }

    public function getJumlahJenisSoal($id_bank) {
        $this->db->select('id_soal, jenis');
        $this->db->from('cbt_soal');
        $this->db->where('bank_id', $id_bank);
        $this->db->where('tampilkan', '1');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret [$row->jenis][] = $row;
            }
        }
        return $ret;
    }

    public function getJenis() {
        $this->datatables->select('*');
        $this->datatables->from('cbt_jenis');
        return $this->datatables->generate();
    }

    public function getJenisById($id) {
		$this->db->select('id_jenis, nama_jenis, kode_jenis');
		$this->db->from('cbt_jenis');
		$this->db->where(['id_jenis' => $id]);
		return $this->db->get()->row();
	}

	function updateJenis() {
		$id = $this->input->post('id_jenis');
		$name = $this->input->post('nama_jenis', true);
		$kode = $this->input->post('kode_jenis', true);

		$this->db->set('nama_jenis', $name);
		$this->db->set('kode_jenis', $kode);
		$this->db->where('id_jenis', $id);
		return $this->db->update('cbt_jenis');
	}

    public function getRuang() {
        $this->datatables->select('*, (SELECT COUNT(id_sesi) FROM cbt_sesi) AS jum_sesi');
        $this->datatables->from('cbt_ruang');
        return $this->datatables->generate();
    }

    public function getRuangById($id) {
		$this->db->select('id_ruang, nama_ruang, kode_ruang');
		$this->db->from('cbt_ruang');
		$this->db->where(['id_ruang' => $id]);
		return $this->db->get()->row();
	}

    public function getRuangSesi($tp, $smt) {
        $this->db->select('a.siswa_id, a.sesi_id, a.ruang_id, a.kelas_id, '.
            'b.nama_ruang, b.kode_ruang, c.nama_sesi, c.kode_sesi, d.nama_kelas');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        $this->db->join('master_kelas d', 'd.id_kelas=a.kelas_id');
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->sesi_id][$row->ruang_id][$row->kelas_id] = $row->nama_kelas;
            }
        }
        return $ret;
    }

	function updateRuang() {
		$id = $this->input->post('id_ruang');
		$name = $this->input->post('nama_ruang', true);
		$kode = $this->input->post('kode_ruang', true);

		$this->db->set('nama_ruang', $name);
		$this->db->set('kode_ruang', $kode);
		$this->db->where('id_ruang', $id);
		return $this->db->update('cbt_ruang');
	}

    public function getSesi() {
        $this->datatables->select('*');
        $this->datatables->from('cbt_sesi c');
        return $this->datatables->generate();
    }

    public function getAllKodeSesi() {
        $this->db->select('id_sesi, nama_sesi, kode_sesi, waktu_mulai, waktu_akhir');
        $this->db->from('cbt_sesi');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret [$row->kode_sesi] = $row;
            }
        }
        return $ret;
    }

	public function getSesiById($id) {
		$this->db->select('id_sesi, nama_sesi, kode_sesi, waktu_mulai, waktu_akhir');
		$this->db->from('cbt_sesi');
		$this->db->where(['id_sesi' => $id]);
		return $this->db->get()->row();
	}

	public function getSesiBySiswa($siswa_id) {
		$this->db->where('siswa_id', $siswa_id);
		$query = $this->db->get('siswa_sesi')->result();
		return $query;
	}

	function updateSesi() {
		$id = $this->input->post('id_sesi');
		$name = $this->input->post('nama_sesi', true);
		$kode = $this->input->post('kode_sesi', true);
		$mulai = $this->input->post('waktu_mulai', true);
		$akhir = $this->input->post('waktu_akhir', true);

		$this->db->set('nama_sesi', $name);
		$this->db->set('kode_sesi', $kode);
		$this->db->set('waktu_mulai', $mulai);
		$this->db->set('waktu_akhir', $akhir);
		$this->db->set('aktif', 1);
		$this->db->where('id_sesi', $id);
		return $this->db->update('cbt_sesi');
	}

    public function getSiswaCbtInfo($id_siswa, $id_tp, $id_smt) {
        $this->db->select('a.id_kelas_siswa, a.id_tp, a.id_smt, a.id_siswa, a.id_kelas,'.
            ' b.siswa_id, b.kelas_id, b.ruang_id, b.sesi_id,'.
            ' rk.id_ruang, rk.nama_ruang, rk.kode_ruang,'.
            ' sk.id_sesi, sk.nama_sesi, sk.kode_sesi, sk.waktu_mulai, sk.waktu_akhir');
        $this->db->from('kelas_siswa a');
        $this->db->join('cbt_sesi_siswa b', 'a.id_siswa=b.siswa_id', 'left');
        $this->db->join('cbt_ruang rk', 'b.ruang_id=rk.id_ruang', 'left');
        $this->db->join('cbt_sesi sk', 'b.sesi_id=sk.id_sesi', 'left');
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->where("a.id_tp", $id_tp);
        $this->db->where("a.id_smt", $id_smt);

        return $this->db->get()->row();
    }

    public function getRuangSesiSiswa($id_kelas, $id_tp, $id_smt){
		$this->db->select('a.id_siswa, a.id_kelas,'.
            ' b.nama, b.nis, b.username,'.
            ' c.nama_kelas, c.kode_kelas,'.
			' e.sesi_id, e.ruang_id,'.
			' rk.id_ruang, rk.kode_ruang,'.
			' sk.id_sesi, sk.kode_sesi');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'a.id_siswa=b.id_siswa', 'left');
		$this->db->join('master_kelas c', 'a.id_kelas=c.id_kelas', 'left');
        $this->db->join('cbt_sesi_siswa e', 'a.id_siswa=e.siswa_id', 'left');
		//$this->db->join('cbt_kelas_ruang d', 'a.id_kelas=d.id_kelas', 'left');
		$this->db->join('cbt_ruang rk', 'e.ruang_id=rk.id_ruang', 'left');
		$this->db->join('cbt_sesi sk', 'e.sesi_id=sk.id_sesi', 'left');
        $this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');

		$this->db->where("a.id_kelas", $id_kelas);
		$this->db->where("a.id_tp", $id_tp);
		$this->db->where("a.id_smt", $id_smt);
        $this->db->order_by('b.nama', 'ASC');

		return $this->db->get()->result();
	}

	public function getSiswaByKelas($id_tp, $id_smt, $id_kelas) {
		$this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.agama,'.
			' b.foto, d.kode_ruang, e.kode_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'left');
        $this->db->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa', 'left');
		$this->db->join('cbt_ruang d', 'd.id_ruang=c.ruang_id', 'left');
		$this->db->join('cbt_sesi e', 'e.id_sesi=c.sesi_id', 'left');
		$this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas', 'left');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp, 'left');
        $this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_siswa is NOT NULL', NULL, FALSE);
        $this->db->where('b.id_siswa is NOT NULL', NULL, FALSE);
        $this->db->where('c.siswa_id is NOT NULL', NULL, FALSE);
        $this->db->where('f.siswa_id is NOT NULL', NULL, FALSE);
        $this->db->where('g.id_siswa is NOT NULL', NULL, FALSE);

		if (is_array($id_kelas)) {
            $this->db->where_in('a.id_kelas', $id_kelas);
        } else {
            $this->db->where('a.id_kelas', $id_kelas);
        }
        $this->db->order_by('b.nama', 'ASC');

		return $this->db->get()->result();
	}

    public function getSiswaById($id_tp, $id_smt, $idSiswa) {
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password,'.
            ' b.foto, d.kode_ruang, e.kode_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta,'.
            ' h.set_siswa, i.kode_ruang as ruang_kelas, j.kode_sesi as sesi_kelas');
        $this->db->from('kelas_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'left');
        $this->db->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=c.ruang_id', 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=c.sesi_id', 'left');
        $this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp, 'left');

        $this->db->join('cbt_kelas_ruang h', 'h.id_kelas=a.id_kelas', 'left');
        $this->db->join('cbt_ruang i', 'i.id_ruang=h.id_ruang', 'left');
        $this->db->join('cbt_sesi j', 'j.id_sesi=h.id_sesi', 'left');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_siswa', $idSiswa);

        return $this->db->get()->row();
    }

    /*
    public function getKelasRuang($id_tp, $id_smt, $kelas) {
        $this->db->select('*');
        $this->db->from('cbt_kelas_ruang');
        $this->db->where('id_kelas', $kelas);
        //$this->db->where('id_sesi', $sesi);
        $this->db->where('id_tp', $id_tp);
        $this->db->where('id_smt', $id_smt);

        return $this->db->get()->result();
    }
    */

    public function getAllPesertaByRuang($id_tp, $id_smt) {
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto, f.level_id,'.
            ' f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
            ' e.kode_sesi, e.nama_sesi,'.
            ' g.nomor_peserta');

        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp, 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND =i.status=1');

        $this->db->order_by('d.kode_ruang');
        $this->db->order_by('e.kode_sesi');
        $this->db->order_by('f.level_id');
        $this->db->order_by('f.kode_kelas');
        $this->db->order_by('b.nama');

        $result = $this->db->get()->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_ruang][$row->kode_sesi][] = $row;
        }
        return $ret;

    }

    public function getAllPesertaByKelas($id_tp, $id_smt) {
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto,'.
            ' f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
            ' e.kode_sesi, e.nama_sesi,'.
            ' g.nomor_peserta');

        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp, 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND =i.status=1');

        $this->db->order_by('f.level_id');
        $this->db->order_by('f.kode_kelas');
        //$this->db->order_by('d.kode_ruang');
        //$this->db->order_by('e.kode_sesi');
        $this->db->order_by('b.nama');

        $result = $this->db->get()->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_kelas][] = $row;
        }
        return $ret;

    }

    public function getSiswaByRuang($id_tp, $id_smt, $id_ruang, $sesi, $level = null) {
		$this->db->select('a.ruang_id, a.sesi_id, b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto, b.agama,'.
			' f.id_kelas, f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
			' e.kode_sesi, e.nama_sesi,'.
            ' (SELECT COUNT(id) FROM users WHERE users.username = b.username) AS aktif,'.
            ' (SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = b.username) AS reset,'.
			' g.nomor_peserta');

		$this->db->from('cbt_sesi_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
		$this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp, 'left');
		$this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        if ($level === null) {
            $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        } else {
            $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas'.' AND f.level_id='.$level.'');
        }
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND =i.status=1');

		$this->db->where('a.ruang_id', $id_ruang);
		$this->db->where('a.sesi_id', $sesi);
		$this->db->order_by('b.nama');

		return $this->db->get()->result();
	}

	public function getRuangSiswaByKelas($id_tp, $id_smt, $kelas, $sesi) {
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.username, b.password, b.foto,'.
            ' f.nama_kelas, f.kode_kelas,'.
            ' d.nama_ruang, d.kode_ruang,'.
            ' e.kode_sesi, e.nama_sesi,'.
            ' g.nomor_peserta');

        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
        $this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id AND g.id_tp='.$id_tp, 'left');
        $this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');

        $this->db->join('kelas_siswa c', 'c.id_siswa=b.id_siswa AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt.'');
        $this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas');
        $this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND =i.status=1');

        $this->db->where_in('a.kelas_id', $kelas);
        if ($sesi!=null) $this->db->where('a.sesi_id', $sesi);
        $this->db->order_by('b.nama');

        return $this->db->get()->result();
	}

	public function getSiswaByKelasArray($id_tp, $id_smt, $arr_kelas) {
		$this->db->select('a.id_siswa, a.id_kelas,'.
            ' b.nama, b.nis, b.nisn, b.username, b.password, b.agama,'.
            ' f.nama_kelas, f.kode_kelas, l.level, g.nomor_peserta');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');
		$this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas');
		$this->db->join('level_kelas l', 'l.id_level=f.level_id');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp, 'left');
        $this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
		if (!in_array('Semua', $arr_kelas)) {
			$this->db->where_in('a.id_kelas', $arr_kelas);
		}
		$this->db->where('a.id_tp', $id_tp);
		$this->db->where('a.id_smt', $id_smt);
		$this->db->order_by('l.level', 'ASC');
        $this->db->order_by('f.kode_kelas', 'ASC');
        $this->db->order_by('b.nama', 'ASC');

		return $this->db->get()->result();
	}

	public function getKelasList($tp, $smt) {
		$this->db->select('a.id_kelas, a.nama_kelas, a.kode_kelas, c.nama_jurusan, b.id_ruang, b.id_sesi, b.set_siswa');
		$this->db->from('master_kelas a');
		$this->db->join('cbt_kelas_ruang b', 'a.id_kelas=b.id_kelas', 'left');
		$this->db->join('master_jurusan c', 'c.id_jurusan=a.jurusan_id', 'left');
		$this->db->join('level_kelas d', 'd.id_level=a.level_id', 'left');
		//$this->db->join('cbt_ruang e', 'e.id_ruang=b.id_ruang', 'left');
		$this->db->where('a.id_tp', $tp);
		$this->db->where('a.id_smt', $smt);
		$this->db->order_by('a.level_id', 'ASC');
        $this->db->order_by('a.nama_kelas', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}

	public function getKelas($tp = null, $smt = null) {
		$this->db->select('a.id_kelas, a.nama_kelas, a.kode_kelas, b.level');
		$this->db->from('master_kelas a');
        $this->db->join('level_kelas b', 'b.id_level=a.level_id', 'left');

        if ($tp != null) $this->db->where('a.id_tp', $tp);
        if ($smt != null) $this->db->where('a.id_smt', $smt);
		$this->db->order_by('a.nama_kelas', 'ASC');
		return $this->db->get()->result();
	}

	/*
	public function getPeserta($id_tp, $id_smt) {
		$this->datatables->select('a.id_siswa, b.nis, b.nama, b.username, b.password, c.nama_kelas, c.kode_kelas,'.
			' (SELECT COUNT(id) FROM users WHERE users.username = b.username) AS aktif,'.
			' d.nomor_peserta, f.nama_ruang, f.kode_ruang, g.kode_sesi, g.nama_sesi,'.
			' i.nama_ruang as ruang_kelas, i.kode_ruang as kode_ruang_kelas,'.
			' h.set_siswa, j.nama_sesi as sesi_kelas, j.kode_sesi as kode_sesi_kelas');
		$this->datatables->from('kelas_siswa a');

		$this->datatables->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'left');
		$this->datatables->join('master_kelas c', 'c.id_kelas=a.id_kelas', 'left');
		$this->datatables->join('cbt_nomor_peserta d', 'd.id_siswa=b.id_siswa AND d.id_tp='.$id_tp. ' AND d.id_smt='.$id_smt, 'left');

		$this->datatables->join('cbt_sesi_siswa e', 'e.siswa_id=a.id_siswa', 'left');
		$this->datatables->join('cbt_ruang f', 'f.id_ruang=e.ruang_id', 'left');
		$this->datatables->join('cbt_sesi g', 'g.id_sesi=e.sesi_id', 'left');

		$this->datatables->join('cbt_kelas_ruang h', 'h.id_kelas=a.id_kelas', 'left');
		$this->datatables->join('cbt_ruang i', 'i.id_ruang=h.id_ruang', 'left');
		$this->datatables->join('cbt_sesi j', 'j.id_sesi=h.id_sesi', 'left');
        //$this->datatables->join('buku_induk k', 'k.id_siswa=a.id_siswa AND =k.status=1');

		$this->datatables->where('a.id_tp', $id_tp);
		$this->datatables->where('a.id_smt', $id_smt);

		return $this->datatables->generate();
	}
	*/

    public function getDataTableBank($guru = null) {
        $this->datatables->select('a.id_bank, a.bank_kode, a.bank_level, a.tampil_pg, a.tampil_esai, a.status, b.nama_mapel, c.nama_guru');
        $this->datatables->from('cbt_bank_soal a');
        $this->datatables->join('master_mapel b', 'b.id_mapel=a.bank_mapel_id', "left");
        $this->datatables->join('master_guru c', 'c.id_guru=a.bank_guru_id', "left");
        $this->datatables->join('master_jurusan d', 'd.id_jurusan=a.bank_jurusan_id', "left");
        $this->datatables->join('cbt_jenis e', 'e.id_jenis=a.bank_jenis_id', "left");
        if ($guru !== null) {
            $this->datatables->where('a.bank_guru_id', $guru);
        }
        //$query = $this->db->get();
        return $this->datatables->generate();
    }

	public function getDataBank($guru = null, $mapel=null, $level=null) {
		$this->db->select('a.id_bank, a.id_tp, a.id_smt, a.bank_kode, a.bank_level, a.bank_kelas, a.date, a.status,'.
            ' a.tampil_pg, a.tampil_kompleks, a.tampil_jodohkan, a.tampil_isian, a.tampil_esai, a.bank_guru_id,'.
            ' b.nama_mapel, c.id_guru, c.nama_guru,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal,'.
            ' (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan');
		$this->db->from('cbt_bank_soal a');
		$this->db->join('master_mapel b', 'b.id_mapel=a.bank_mapel_id', "left");
		$this->db->join('master_guru c', 'c.id_guru=a.bank_guru_id', "left");
		//$this->db->join('master_jurusan d', 'd.id_jurusan=a.bank_jurusan_id', "left");
		//$this->db->join('cbt_jenis e', 'e.id_jenis=a.bank_jenis_id', "left");
		if ($guru !== null) {
			$this->db->where('a.bank_guru_id', $guru);
		}
        if ($mapel !== null) {
            $this->db->where('a.bank_mapel_id', $mapel);
        }
        if ($level !== null) {
            $this->db->where('a.bank_level', $level);
        }
        $this->db->order_by('a.bank_level', 'ASC');
        //$this->db->order_by('a.date', 'ASC');
		$result = $this->db->get()->result();
		//return $query->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_tp][$row->id_smt][] = $row;
        }
        return $ret;

    }

	public function getDataBankById($id) {
		$this->db->select('a.*, b.nama_mapel, b.kode, c.nama_guru, d.nama_jurusan, d.kode_jurusan,'.
            ' (SELECT COUNT(id_jadwal) FROM cbt_jadwal WHERE cbt_jadwal.id_bank = a.id_bank AND cbt_jadwal.status="1") AS digunakan');
		$this->db->from('cbt_bank_soal a');
		$this->db->join('master_mapel b', 'b.id_mapel=a.bank_mapel_id', "left");
		$this->db->join('master_guru c', 'c.id_guru=a.bank_guru_id', "left");
		$this->db->join('master_jurusan d', 'd.id_jurusan=a.bank_jurusan_id', "left");
		//$this->db->join('cbt_soal e', 'e.bank_id=a.id_bank', "left");
		$this->db->where('a.id_bank', $id);
		return $this->db->get()->row();
	}

    public function getTotalSoal($id_bank, $jenis = null) {
        $this->db->where('bank_id', $id_bank);
        if ($jenis !=null) $this->db->where('jenis', $jenis);
        return $this->db->get('cbt_soal')->num_rows();
    }

    public function getNomorSoalById($id_soal) {
        $this->db->select('nomor_soal, jenis, bank_id');
        $this->db->where('id_soal', $id_soal);
        return $this->db->get('cbt_soal')->row();
    }

    public function getFileSoalById($id_soal) {
		$this->db->select('file');
        $this->db->where('id_soal', $id_soal);
		return $this->db->get('cbt_soal')->row();
	}

    public function getSoalByBank($id_bank) {
        $this->db->select('id_soal, bank_id, mapel_id, jenis, nomor_soal, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban');
        $this->db->from('cbt_soal');
        $this->db->where('bank_id', $id_bank);
        $this->db->order_by('jenis');
        $this->db->order_by('nomor_soal');
        $result = $this->db->get()->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->jenis][$row->nomor_soal] = $row;
        }
        return $ret;
    }

    public function getAllSoalByBank($id_bank, $jenis=null) {
        $this->db->select('id_soal, bank_id, mapel_id, jenis, nomor_soal, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan');
        $this->db->where('bank_id', $id_bank);
        if ($jenis!=null) {
            $this->db->where('jenis', $jenis);
        }
        return $this->db->get('cbt_soal')->result();
    }

	public function getSoalByNomor($id_bank, $nomor, $jenis) {
		$this->db->select('*');
		$this->db->where('bank_id', $id_bank);
		$this->db->where('nomor_soal', $nomor);
		$this->db->where('jenis', $jenis);
		return $this->db->get('cbt_soal')->row();
	}

    public function getNomorSoalByBankJenis($id_bank, $jenis) {
        $this->db->select('id_soal, jenis, nomor_soal');
        $this->db->where('bank_id', $id_bank);
        $this->db->where('jenis', $jenis);
        $result = $this->db->get('cbt_soal')->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->nomor_soal] = $row;
        }
        return $ret;
    }

    public function getNomorSoalByBank($id_bank, $jenis=null) {
        $this->db->select('id_soal, jenis, nomor_soal, jawaban');
        $this->db->where('bank_id', $id_bank);
        $this->db->where('tampilkan', '1');
        if ($jenis!=null) {
            $this->db->where('jenis', $jenis);
        }
        $result = $this->db->get('cbt_soal')->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_soal] = $row;
        }
        return $ret;
    }

    public function getNomorSoalByArrIdBank($arr_id_bank, $jenis=null) {
        $this->db->select('id_soal, jenis, nomor_soal, jawaban');
        $this->db->where_in('bank_id', $arr_id_bank);
        if ($jenis!=null) {
            $this->db->where('jenis', $jenis);
        }
        return $this->db->get('cbt_soal')->result();
    }

	public function cekSoalAda($id_bank, $jenis) {
        $this->db->select('id_soal, bank_id, jenis, nomor_soal');
		$this->db->where('bank_id', $id_bank);
        $this->db->where('jenis', $jenis);
		return $this->db->get('cbt_soal')->result();
	}

    public function cekSoalKomplit($id_bank, $jenjang) {
        $this->db->select('id_soal, bank_id, jenis, nomor_soal');
        $this->db->where('bank_id', $id_bank)
            ->where('soal NOT NULL')
            ->or_where('opsi_a NOT NULL')
            ->or_where('opsi_b NOT NULL')
            ->or_where('opsi_c NOT NULL')
            ->or_where('opsi_d NOT NULL')
            ->or_where('jawaban NOT NULL');
        if ($jenjang == '3') {
            $this->db->or_where('opsi_e NOT NULL');
        }
        return $this->db->get('cbt_soal')->result();
    }

    public function cekSoalBelumKomplit($jenis, $opsi_ganda) {
        $this->db->select('id_soal, bank_id, jenis, nomor_soal, mapel_id');
        $this->db->from('cbt_soal');
        $this->db->where('jenis', $jenis);
        $this->db->where('soal IS NULL')->or_where('soal =""');

        if ($jenis == "1") {
            $this->db->where('opsi_a IS NULL')->or_where('opsi_a =""');
            $this->db->where('opsi_b IS NULL')->or_where('opsi_b =""');
            $this->db->where('opsi_c IS NULL')->or_where('opsi_c =""');
            if ($opsi_ganda == '4') {
                $this->db->where('opsi_d IS NULL')->or_where('opsi_d =""');
            }
            if ($opsi_ganda == '5') {
                $this->db->where('opsi_d IS NULL')->or_where('opsi_d =""');
                $this->db->where('opsi_e IS NULL')->or_where('opsi_e =""');
            }
        }

        if ($jenis == "2") {
            $this->db->where('opsi_a IS NULL')->or_where('opsi_a =""');
        }

        $this->db->where('jawaban IS NULL')->or_where('jawaban =""');
        $ret = [];
        $result = $this->db->get()->result();
        foreach ($result as $key => $row) {
            $ret[$row->bank_id][] = $row;
        }
        return $ret;
    }

    public function getNomorSoalTerbesar($id_bank, $jenis) {
        $this->db->select('nomor_soal');
        $this->db->where('bank_id', $id_bank)->where('jenis', $jenis);
        $this->db->order_by('nomor_soal', 'DESC');
        return $this->db->get('cbt_soal')->row();
    }

	public function dummy($jenjang) {
		$data = array(
			'id_bank' => '',
			'bank_jenis_id' => '',
			'bank_kode' => '',
			'bank_mapel_id' => '',
			'bank_level' => '',
			'bank_kelas' => serialize([]),
			'bank_guru_id' => '',
			'jml_soal' => '0',
			'bobot_pg' => '0',
			'tampil_pg' => '0',
			'opsi' => $jenjang == '1' ? '3' : ($jenjang == '2' ? '4' : ($jenjang == '3' ? '5' : '')),

            'jml_kompleks' => '0',
            'tampil_kompleks' => '0',
            'bobot_kompleks' => '0',

            'jml_jodohkan' => '0',
            'tampil_jodohkan' => '0',
            'bobot_jodohkan' => '0',

            'jml_isian' => '0',
            'tampil_isian' => '0',
            'bobot_isian' => '0',

            'jml_esai' => '0',
			'bobot_esai' => '0',
			'tampil_esai' => '0',
			'kkm' => '',
            'soal_agama' => '-',
			'status' => '1'
		);
		return $data;
	}

    public function saveBankSoal($tp, $smt) {
		$id = $this->input->post('id_bank', true);
		$rows = count($this->input->post('kelas', true));
		//$jml_pg = strip_tags($this->input->post('jml_soal', TRUE));
		//$jml_esai = strip_tags($this->input->post('jml_esai', TRUE));

		$kelas = [];
		for ($i = 0; $i <= $rows; $i++) {
			$kelas[] = [
				'kelas_id' => $this->input->post('kelas[' . $i . ']', true)
			];
		}
		$jumlah = serialize($kelas);
		$data = array(
		    'id_tp' => $tp,
			'id_smt' => $smt,
			'bank_kode' => strip_tags($this->input->post('kode', TRUE) ?? ''),
			'bank_jenis_id' => strip_tags($this->input->post('jenis', TRUE) ?? ''),
			'bank_mapel_id' => strip_tags($this->input->post('mapel', TRUE) ?? ''),
			'bank_kelas' => $jumlah,
			'bank_level' => $this->input->post('level', TRUE),
			'bank_guru_id' => strip_tags($this->input->post('guru', TRUE) ?? ''),
			'jml_soal' => strip_tags($this->input->post('tampil_pg', TRUE) ?? ''),
            'tampil_pg' => strip_tags($this->input->post('tampil_pg', TRUE) ?? ''),
			'bobot_pg' => strip_tags($this->input->post('bobot_pg', TRUE) ?? ''),
			'opsi' => strip_tags($this->input->post('opsi', TRUE) ?? ''),

            'jml_kompleks' => strip_tags($this->input->post('tampil_kompleks', TRUE) ?? ''),
            'tampil_kompleks' => strip_tags($this->input->post('tampil_kompleks', TRUE) ?? ''),
            'bobot_kompleks' => strip_tags($this->input->post('bobot_kompleks', TRUE) ?? ''),

            'jml_jodohkan' => strip_tags($this->input->post('tampil_jodohkan', TRUE) ?? ''),
            'tampil_jodohkan' => strip_tags($this->input->post('tampil_jodohkan', TRUE) ?? ''),
            'bobot_jodohkan' => strip_tags($this->input->post('bobot_jodohkan', TRUE) ?? ''),

            'jml_isian' => strip_tags($this->input->post('tampil_isian', TRUE) ?? ''),
            'tampil_isian' => strip_tags($this->input->post('tampil_isian', TRUE) ?? ''),
            'bobot_isian' => strip_tags($this->input->post('bobot_isian', TRUE) ?? ''),

			'jml_esai' => strip_tags($this->input->post('bobot_esai', TRUE) ?? ''),
			'bobot_esai' => strip_tags($this->input->post('bobot_esai', TRUE) ?? ''),
			'tampil_esai' => strip_tags($this->input->post('tampil_esai', TRUE) ?? ''),
			//'kkm' => strip_tags($this->input->post('kkm', TRUE)),
			'status' => strip_tags($this->input->post('status', TRUE) ?? ''),
            'soal_agama' => strip_tags($this->input->post('soal_agama', TRUE) ?? ''),
		);

		if (!$id) {
			$this->db->insert('cbt_bank_soal', $data);
			$insert_id = $this->db->insert_id();
			/*
			$soal = [];
			for ($i = 0; $i < $jml_pg; $i++) {
				$soal[] = [
					'bank_id' => $insert_id,
					'jenis' => '1'
				];
			}

			for ($i = 0; $i < $jml_esai; $i++) {
				$soal[] = [
					'bank_id' => $insert_id,
					'jenis' => '2'
				];
			}
			$this->db->insert_batch('cbt_soal', $soal);
			*/
			return $insert_id;
		} else {
			$this->db->where('id_bank', $id);
			return $this->db->update('cbt_bank_soal', $data);
		}
	}

    /*
    public function getJadwalMapel($tp, $smt){
        $this->db->select('*');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'a.id_mapel=b.id_mapel', 'left');
        $this->db->where("id_tp", $tp, FALSE);
        $this->db->where("id_smt", $smt, FALSE);
        $result = $this->db->get()->result();
        return $result;
    }

    public function updateBankSoal($id) {
        $rows = count($this->input->post('kelas', true));
        $kelas = [];
        for ($i = 0; $i <= $rows; $i++) {
            $kelas[] = [
                'kelas_id' => $this->input->post('kelas['.$i.']', true)
            ];
        }
        $jumlah = serialize($kelas);
        $data = array(
            'bank_kode' 		=> strip_tags($this->input->post('kode', TRUE)),
            'bank_jenis_id'		=> strip_tags($this->input->post('jenis', TRUE)),
            'bank_mapel_id' 	=> strip_tags($this->input->post('mapel', TRUE)),
            'bank_kelas' 		=> $jumlah,
            'bank_guru_id' 		=> strip_tags($this->input->post('guru', TRUE)),
            'jml_soal' 			=> strip_tags($this->input->post('jml_soal', TRUE)),
            'bobot_pg' 			=> strip_tags($this->input->post('bobot_pg', TRUE)),
            'tampil_pg' 		=> strip_tags($this->input->post('tampil_pg', TRUE)),
            'opsi' 				=> strip_tags($this->input->post('opsi', TRUE)),
            'jml_esai' 			=> strip_tags($this->input->post('jml_esai', TRUE)),
            'bobot_esai' 		=> strip_tags($this->input->post('bobot_esai', TRUE)),
            'tampil_esai' 		=> strip_tags($this->input->post('tampil_esai', TRUE)),
            'kkm' 				=> strip_tags($this->input->post('kkm', TRUE)),
            'status' 			=> strip_tags($this->input->post('status', TRUE)),
        );

        $this->db->where('id_kelas', $id);
        return $this->db->update('kelas', $data);
    }
    */

	public function dummyJadwal() {
        return array(
            'id_bank' => '',
            'id_jadwal' => '',
            'id_jenis' => '',
            'tgl_mulai' => '',
            'tgl_selesai' => '',
            'durasi_ujian' => '',
            'bank_kelas' => serialize([]),
            //'pengawas' => serialize([]),
            'acak_soal' => '',
            'acak_opsi' => '',
            'hasil_tampil' => '',
            'token' => '',
            'status' => '',
            'ulang' => '',
'jarak' => '',
            'reset_login' => ''
        );
	}

    public function getDistinctJenisJadwal($tp, $smt) {
        $this->db->select('id_jenis');
        $this->db->distinct();
        $this->db->from('cbt_jadwal');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get()->result();
    }

	public function getDataJadwal($tp, $smt, $guru = null, $rekap=null) {
		$this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai,'.
            ' a.tgl_selesai, a.status, a.ulang, a.reset_login, a.rekap, a.jam_ke,'.
            ' e.id_tp, e.tahun, f.id_smt, f.nama_smt, g.level, b.bank_kode, b.bank_level, b.bank_kelas,'.
            ' c.kode_jenis, d.kode, d.nama_mapel,'.
            ' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, b.bank_guru_id,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal');
		$this->db->from('cbt_jadwal a');
		$this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
		$this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
		$this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        $this->db->join('master_tp e', 'a.id_tp=e.id_tp');
        $this->db->join('master_smt f', 'a.id_smt=f.id_smt');
        $this->db->join('level_kelas g', 'b.bank_level=g.id_level');

        if ($guru !== null) {
            $this->db->where('b.bank_guru_id', $guru);
        }
        if ($rekap !== null) {
            $this->db->where('a.rekap', $rekap);
        }
        $this->db->order_by('a.tgl_mulai', 'DESC');
        $this->db->order_by('b.bank_level', 'ASC');
		$query = $this->db->get()->result();
		return $query;
	}

    public function getAllDataJadwal($guru = null, $mapel=null, $level=null) {
        $this->db->select('a.id_jadwal, a.tgl_mulai, a.tgl_selesai, a.status, a.durasi_ujian, a.acak_soal,'.
            ' a.acak_opsi, a.id_bank, a.id_jenis, a.hasil_tampil, a.status, a.ulang, a.reset_login, a.rekap,'.
            ' a.jam_ke, a.token, e.tahun, f.nama_smt, g.level, b.bank_kode, b.bank_level, b.bank_kelas, c.kode_jenis, d.kode, d.nama_mapel,'.
            ' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai, b.bank_guru_id,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        $this->db->join('master_tp e', 'a.id_tp=e.id_tp');
        $this->db->join('master_smt f', 'a.id_smt=f.id_smt');
        $this->db->join('level_kelas g', 'b.bank_level=g.id_level');

        if ($guru !== null) {
            $this->db->where('b.bank_guru_id', $guru);
        }
        if ($mapel !== null) {
            $this->db->where('b.bank_mapel_id', $mapel);
        }
        if ($level !== null) {
            $this->db->where('b.bank_level', $level);
        }
        //$this->db->order_by('a.tgl_mulai', 'ASC');
        $this->db->order_by('b.bank_level', 'ASC');
        $this->db->order_by('a.id_tp', 'DESC');
        $this->db->order_by('a.id_smt', 'DESC');
        $this->db->order_by('a.tgl_mulai', 'ASC');
        $query = $this->db->get()->result();
        $ret = [];
        foreach ($query as $key => $row) {
            $ret['<b>'.$row->kode_jenis . '</b>  ' .$row->tahun. ' smt ' .$row->nama_smt][$row->level][] = $row;
        }
        return $ret;
        //return $query;
    }

    public function getJadwalTerpakai($id_jadwal = null) {
        $this->db->select('id_bank,id_jadwal,id_siswa');
        $this->db->from('cbt_soal_siswa');
        if ($id_jadwal != null) {
            $this->db->where('id_jadwal', $id_jadwal);
        }
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_jadwal][$row->id_siswa] = $row;
        }
        return $ret;
    }

    public function getBankTerpakai($id_banks = null) {
        $this->db->select('id_bank,id_soal,id_siswa');
        $this->db->from('cbt_soal_siswa');
        if ($id_banks != null) $this->db->where_in('id_bank', $id_banks);
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_bank][$row->id_siswa] = $row;
        }
        return $ret;
    }

    public function getCountBankTerpakai($id_bank = null) {
        $this->db->select('id_bank,COUNT(id_siswa) as siswa');
        $this->db->from('cbt_soal_siswa');
        if ($id_bank != null) $this->db->where('id_bank', $id_bank);
        $this->db->group_by('id_bank');
        return $this->db->get()->result();
    }

    public function getRekapByJadwalKelas($jadwal, $guru = null) {
        $this->db->from('cbt_rekap');
        $this->db->where('id_jadwal', $jadwal);
        //if ($kelas !='0') $this->db->where("bank_kelas LIKE '%".$kelas."%'");
        if ($guru !== null) {
            $this->db->where('id_guru', $guru);
        }
        return $this->db->get()->row();
    }

    public function getRekapJadwal($guru = null) {
        $this->db->select('*');
        $this->db->from('cbt_rekap');
        //$this->db->where_not_in('id_jadwal', $arrIdJadwal);
        if ($guru !== null) {
            $this->db->where('id_guru', $guru);
        }
        $this->db->order_by('tgl_mulai', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getAllRekapByJenis($tp, $smt, $jenis, $level, $mapel, $jadwal = null, $guru = null) {
        $this->db->from('cbt_rekap');
        if ($mapel != '0') {
            $this->db->where('id_mapel', $mapel);
        }
        if ($jadwal != null) {
            $this->db->where('id_jadwal', $jadwal);
        }
        if ($guru != null) {
            $this->db->where('id_guru', $guru);
        }
        $this->db->where('tp', $tp);
        $this->db->where('smt', $smt);
        $this->db->where('kode_jenis', $jenis);
        $this->db->where('bank_level', $level);
        $this->db->order_by('id_mapel', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllRekapByJadwal($tp, $smt, $jenis, $level, $jadwal, $guru = null) {
        $this->db->from('cbt_rekap');
        if ($jadwal != '0') {
            $this->db->where('id_jadwal', $jadwal);
        }
        if ($guru != null) {
            $this->db->where('id_guru', $guru);
        }
        $this->db->where('tp', $tp);
        $this->db->where('smt', $smt);
        $this->db->where('kode_jenis', $jenis);
        $this->db->where('bank_level', $level);
        $this->db->order_by('id_mapel', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllNilaiRekapByJenis($tp, $smt, $kode_jenis, $id_kelas, $id_mapel, $id_jadwal = null, $id_guru = null) {
	    $this->db->select('a.*, b.nomor_peserta, c.nama');
        $this->db->from('cbt_rekap_nilai a');
        $this->db->join('cbt_nomor_peserta b', 'b.id_siswa=a.id_siswa AND b.id_tp=a.id_tp', 'left');
        $this->db->join('master_siswa c', 'c.id_siswa=a.id_siswa', 'left');
        $this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
        //$this->db->join('cbt_nilai d', 'd.id_siswa=c.id_siswa AND d.id_jadwal='.$jadwal, 'left');
        if ($id_mapel != '0') {
            $this->db->where('a.id_mapel', $id_mapel);
        }
        if ($id_jadwal != null) {
            $this->db->where('a.id_jadwal', $id_jadwal);
        }
        if ($id_guru != null) {
            $this->db->where('a.id_guru', $id_guru);
        }
        $this->db->where('a.id_kelas', $id_kelas);
        $this->db->where('a.tp', $tp);
        $this->db->where('a.smt', $smt);
        $this->db->where('a.kode_jenis', $kode_jenis);
        $this->db->order_by('c.nama', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllNilaiRekapByJadwal($tp, $smt, $kode_jenis, $id_kelas, $id_jadwal, $id_guru = null) {
        $this->db->select('a.*, b.nomor_peserta, c.nama');
        $this->db->from('cbt_rekap_nilai a');
        $this->db->join('cbt_nomor_peserta b', 'b.id_siswa=a.id_siswa AND b.id_tp=a.id_tp', 'left');
        $this->db->join('master_siswa c', 'c.id_siswa=a.id_siswa', 'left');
        $this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
        if ($id_jadwal != '0') {
            $this->db->where('a.id_jadwal', $id_jadwal);
        }
        if ($id_guru != null) {
            $this->db->where('a.id_guru', $id_guru);
        }
        $this->db->where('a.id_kelas', $id_kelas);
        $this->db->where('a.tp', $tp);
        $this->db->where('a.smt', $smt);
        $this->db->where('a.kode_jenis', $kode_jenis);
        $this->db->order_by('c.nama', 'ASC');

        return $this->db->get()->result();
    }

    public function getAllRekap($guru = null) {
        $this->db->select('id_rekap, id_tp, tp, id_smt, smt, id_jadwal, id_jenis, kode_jenis, id_bank, bank_kelas, nama_kelas, bank_kode, bank_level, id_mapel, nama_mapel, kode, tgl_mulai, tgl_selesai, id_guru, nama_guru');
        $this->db->from('cbt_rekap');
        if ($guru != null) {
            $this->db->where('id_guru', $guru);
        }

        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_jadwal] = $row;
        }
        return $ret;
    }

    /*
    public function getAllNilaiRekap($guru = null) {
        $this->db->select('a.*, b.nomor_peserta, c.nama');
        $this->db->from('cbt_rekap_nilai a');
        $this->db->join('cbt_nomor_peserta b', 'b.id_siswa=a.id_siswa AND b.id_tp=a.id_tp', 'left');
        $this->db->join('master_siswa c', 'c.id_siswa=a.id_siswa', 'left');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
        if ($guru != null) {
            $this->db->where('a.id_guru', $guru);
        }

        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_jadwal][] = $row;
        }
        return $ret;
    }
    */

    public function getJadwalById($id_jadwal, $sesi = null) {
        $this->db->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            .' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            .' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            .' b.id_bank, b.bank_guru_id, b.soal_agama, c.kode_jenis, c.nama_jenis,'
            .' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
		$this->db->from('cbt_jadwal a');
		$this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
		$this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
		$this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
		if ($sesi != null) {
			$this->db->join('cbt_sesi e', 'e.id_sesi='.$sesi, "left");
		}
        $this->db->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
		$this->db->where('a.id_jadwal', $id_jadwal);

        return $this->db->get()->row();
	}

    public function getJadwalByIdBank($id_bank) {
        $this->db->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            .' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            .' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            .' b.id_bank, b.bank_guru_id, c.kode_jenis, c.nama_jenis,'
            .' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        $this->db->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
        $this->db->where('a.id_bank', $id_bank);

        return $this->db->get()->row();
    }

    public function getAllJadwal($tp, $smt, $id_guru=null) {
        $this->db->select('a.bank_kode, a.bank_kelas, b.id_jadwal');
        $this->db->from('cbt_bank_soal a');
        $this->db->join('cbt_jadwal b', 'b.id_bank=a.id_bank');
        if ($id_guru != null) $this->db->where('a.bank_guru_id', $id_guru);
        $this->db->where('b.id_tp', $tp);
        $this->db->where('b.id_smt', $smt);

        return $this->db->get()->result();
    }

    public function getJadwalByArrId($arr_id_jadwal, $sesi = null) {
        //$this->db->select('*');
        $this->db->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            .' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            .' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            .' b.id_bank, b.bank_guru_id, b.soal_agama, c.kode_jenis, c.nama_jenis,'
            .' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        if ($sesi != null) {
            $this->db->join('cbt_sesi e', 'e.id_sesi='.$sesi, "left");
        }
        $this->db->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
        $this->db->where_in('a.id_jadwal', $arr_id_jadwal);

        return $this->db->get()->result();
    }

	public function cekJadwalBankSoal($id_bank) {
	    $this->db->select('id_bank');
	    $this->db->from('cbt_jadwal');
	    if (is_array($id_bank)) $this->db->where_in('id_bank', $id_bank);
	    else $this->db->where('id_bank', $id_bank);
        return $this->db->get()->num_rows();
	}

	public function cekJadwalSudahMulai($id_jadwal) {
        return $this->get_where('cbt_durasi_siswa', 'id_jadwal', $id_jadwal)->num_rows();
	}

	public function saveJadwalUjian($id_tp, $id_smt) {
		$id = $this->input->post('id_jadwal', true);
		$acak_soal = $this->input->post('acak_soal', TRUE);
		$acak_opsi = $this->input->post('acak_opsi', TRUE);
		$hasil_tampil = $this->input->post('hasil_tampil', TRUE);
		$token = $this->input->post('token', TRUE);
		$status = $this->input->post('status', TRUE);
		$reset_login = $this->input->post('reset_login', TRUE);

        $bank_id = strip_tags($this->input->post('bank_id', TRUE) ?? '');
        $jenis_id = strip_tags($this->input->post('jenis_id', TRUE) ?? '');
        $mulai = strip_tags($this->input->post('tgl_mulai', TRUE) ?? '');
        $selesai = strip_tags($this->input->post('tgl_selesai', TRUE) ?? '');
        $durasi = strip_tags($this->input->post('durasi_ujian', TRUE) ?? '');
        $jarak = strip_tags($this->input->post('jarak', TRUE) ?? '');

        $check = $this->db->where('id_bank', $bank_id)
            ->where('id_jenis', $jenis_id)
            ->get('cbt_jadwal')->row();

        //hapus ruang dan sesi
		/*
		$row_ruang = count($this->input->post('ruang', true));
		$row_sesi = count($this->input->post('sesi', true));

		$ruang = [];
		for ($i = 0; $i <= $row_ruang; $i++) {
			$ruang[] = [
				'ruang' => $this->input->post('ruang[' . $i . ']', true)
			];
		}
		$jumlah_ruang = serialize($ruang);

		$sesi = [];
		for ($k = 0; $k <= $row_sesi; $k++) {
			$sesi[] = [
				'sesi' => $this->input->post('sesi[' . $k . ']', true)
			];
		}
		$jumlah_sesi = serialize($sesi);
		*/

		$data = array(
			//'kode_jadwal' => strip_tags($this->input->post('kode_jadwal', TRUE)),
			'id_tp' => $id_tp,
			'id_smt' => $id_smt,
			'id_bank' => $bank_id,
			'id_jenis' => $jenis_id,
			'tgl_mulai' => $mulai,
			'tgl_selesai' => $selesai,
			'durasi_ujian' => $durasi,
            'jarak' => $jarak,
			//'sesi' => $jumlah_sesi,
			//'ruang' => $jumlah_ruang,
			//'pengawas' => $jumlah_guru,
			'acak_soal' => !$acak_soal ? '0' : $acak_soal,
			'acak_opsi' => !$acak_opsi ? '0' : $acak_opsi,
			'hasil_tampil' => !$hasil_tampil ? '0' : $hasil_tampil,
			'token' => !$token ? '0' : $token,
			'status' => !$status ? '0' : $status,
			'reset_login' => !$reset_login ? '0' : $reset_login,
		);

        if ($id=='') {
            if ($check != null) {
                return false;
            } else {
                $this->db->insert('cbt_jadwal', $data);
                $insert_id = $this->db->insert_id();
                return $insert_id;
            }
        } else {
            if ($check != null && $check->id_jadwal != $id) {
                return false;
            } else {
                $this->db->where('id_jadwal', $id);
                return $this->db->update('cbt_jadwal', $data);
            }
        }
	}

	public function getJadwalTgl($guru = null) {
		$this->db->distinct();
		$this->db->select('tgl_mulai');
		$this->db->from('cbt_jadwal');
		$query = $this->db->get();
		return $query->result();
	}

	public function getDataJadwalByTgl($tgl) {
		$this->db->distinct();
		$this->db->select('tgl_mulai, tgl_selesai');
		$this->db->from('cbt_jadwal');
        $this->db->where("tgl_mulai <= '$tgl' AND tgl_selesai >= '$tgl'");
		$query = $this->db->get();
		return $query->result();
	}

	public function getDataGuru() {
		$this->db->select('a.id_guru, a.nama_guru, b.id_pengawas, b.id_jadwal');
		$this->db->from('master_guru a');
		$this->db->join('cbt_pengawas b', 'b.id_guru = a.id_guru', "left");
		//$this->db->join('cbt_jadwal c', 'c.id_jadwal=b.id_jadwal', "left");
        return $this->db->get()->result();
	}

	public function saveToken($post_token) {
        $id = isset($post_token->id_token) ? $post_token->id_token : false;
        $tkn = $post_token->token;
        $auto = $post_token->auto;
        $jarak = $post_token->jarak;
        $data = array(
            'token'     => $tkn,
            'auto'      => $auto,
            'jarak'     => $jarak,
            'updated'   => $post_token->updated
        );
		if (!$id) {
			$this->db->insert('cbt_token', $data);
			$insert_id = $this->db->insert_id();
			return $insert_id;
		} else {
			$this->db->where('id_token', $id);
			return $this->db->update('cbt_token', $data);
		}
	}

	public function updateToken($token, $auto) {
		$this->db->set('auto', $auto, FALSE);
		$this->db->where('token', $token);
		$this->db->update('cbt_token');

        return $this->db->get('cbt_token')->row();

	}

	public function getToken() {
        return $this->db->get('cbt_token')->row();
	}

    public function getJadwalCbtKelas($id_tp, $id_smt) {
        $this->db->select('a.id_jadwal, b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);

        return $this->db->get()->result();
    }

    public function getInfoJadwal($id_bank) {
        $this->db->select('a.id_bank, b.acak_soal, b.acak_opsi, a.opsi,'.
            ' a.tampil_pg, a.tampil_kompleks, a.tampil_jodohkan, a.tampil_isian, a.tampil_esai,'.
            ' a.bobot_pg,  a.bobot_kompleks,  a.bobot_jodohkan,  a.bobot_isian,  a.bobot_esai');
        $this->db->from('cbt_bank_soal a');
        $this->db->join('cbt_jadwal b', 'a.id_bank=b.id_bank');
        $this->db->where('a.id_bank', $id_bank);
        return $this->db->get()->row();
    }

    public function getAllIdSoal($id_bank) {
        $this->db->select('id_soal, jenis, jawaban');
        $this->db->from('cbt_soal');
        $this->db->where('tampilkan', '1');
        $this->db->where('bank_id', $id_bank);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret [$row->jenis][] = $row;
            }
        }
        return $ret;
    }

    /*
    public function getJadwalByJenisUjian($id_jenis) {
        $this->db->select('a.id_jadwal, a.pengawas, b.bank_kelas');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);

        return $this->db->get()->result();
    }
    /*
    public function getJadwalCbtByKelas($bank_kelas) {
        $this->db->select('a.bank_kode, a.bank_level, a.bank_kelas, b.kode_jenis, b.nama_jenis, c.*, d.kode');
        $this->db->from('cbt_bank_soal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_jadwal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');
        $this->db->where('a.id_smt', $id_smt);

        return $this->db->get()->row();
    }
    */

    public function getJadwalCbt($id_tp, $id_smt, $level) {
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai,'.
            ' a.durasi_ujian, a.pengawas, a.acak_soal, a.acak_opsi, a.hasil_tampil, a.token, a.status, a.ulang,'.
            ' a.reset_login, a.rekap, a.jam_ke, a.jarak,'.
            ' c.bank_kode, c.bank_level, c.bank_kelas, c.tampil_pg, c.tampil_kompleks, c.tampil_jodohkan,'.
            ' c.tampil_isian, c.tampil_esai, c.soal_agama, '.
            ' c.bobot_pg, c.bobot_kompleks, c.bobot_jodohkan, c.bobot_isian, c.bobot_esai, b.kode_jenis,'.
            ' b.nama_jenis, d.kode, d.nama_mapel');
		$this->db->from('cbt_jadwal a');
		$this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
		$this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
		$this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');

		$this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.status', '1');
		$this->db->where('a.id_smt', $id_smt);
        $this->db->where('c.status', '1');
        $this->db->where('c.status_soal', '1');
        $this->db->where('c.bank_level', $level);
        $this->db->order_by('a.jam_ke');

		$result = $this->db->get()->result();
		$retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal] = $row;
        }
        return $retur;
    }

	public function getCbt($id_jadwal) {
		$this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai,'.
            ' a.durasi_ujian, a.pengawas, a.acak_soal, a.acak_opsi, a.hasil_tampil, a.token, a.status, a.ulang,'.
            ' a.reset_login, a.rekap, a.jam_ke, a.jarak,'.
            ' b.nama_jenis, b.kode_jenis,'.
            ' c.bank_kode, c.bank_level, c.bank_kelas, c.bank_mapel_id, c.bank_jurusan_id,'.
            ' c.bank_guru_id, c.bank_nama, c.jml_soal, c.jml_esai, c.tampil_pg, c.tampil_esai, c.bobot_pg,'.
            ' c.bobot_esai, c.opsi, c.date, c.status, c.soal_agama, c.id_tp, c.id_smt, c.deskripsi, c.jml_kompleks,'.
            ' c.tampil_kompleks, c.bobot_kompleks, c.jml_jodohkan, c.tampil_jodohkan, c.bobot_jodohkan, c.jml_isian,'.
            ' c.tampil_isian, c.bobot_isian, c.status_soal,'.
            ' d.id_mapel, d.nama_mapel, d.kode,'.
            ' e.id_guru, e.nama_guru,'.
            ' f.id_jurusan, f.nama_jurusan, f.kode_jurusan,'.
            ' g.tahun,'.
            ' h.smt, h.nama_smt,'.
            ' (SELECT COUNT(id_soal) FROM cbt_soal WHERE cbt_soal.bank_id = a.id_bank) AS total_soal');
		$this->db->from('cbt_jadwal a');
		$this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis', 'left');
		$this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank', 'left');
		$this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id', 'left');
		$this->db->join('master_guru e', 'e.id_guru=c.bank_guru_id', "left");
		$this->db->join('master_jurusan f', 'f.id_jurusan=c.bank_jurusan_id', "left");
		$this->db->join('master_tp g', 'g.id_tp=a.id_tp', "left");
		$this->db->join('master_smt h', 'h.id_smt=a.id_smt', "left");

		$this->db->where('a.id_jadwal', $id_jadwal);

		return $this->db->get()->row();

	}

	public function getCbtById($id_jadwal) {
		$this->db->select('*');
		$this->db->from('cbt_jadwal');
		$this->db->where('id_jadwal', $id_jadwal);

		return $this->db->get()->row();
	}

	public function getIdRuangById($array) {
		$this->db->select('nama_ruang');
		$this->db->from('cbt_ruang');
		$this->db->where('id_ruang', $array);
		$result = $this->db->get()->result();
        $ret = [];
		if ($result) {
			foreach ($result as $key => $row) {
				$ret [$row->id_ruang] = $row->kode_ruang;
			}
		}
		return $ret;
	}

	public function getNamaRuangById($id) {
		$this->db->select('nama_ruang');
		$this->db->from('cbt_ruang');
		$this->db->where('id_ruang', $id);
		$result = $this->db->get()->row();
		if ($result) {
			return $result->nama_ruang;
		} else {
			return '';
		}
	}

	public function getNamaSesiById($id) {
		$this->db->select('nama_sesi');
		$this->db->from('cbt_sesi');
		$this->db->where(['id_sesi' => $id]);
		return $this->db->get()->row()->nama_sesi;
	}

	public function getNamaKelasById($id) {
		$this->db->select('nama_kelas');
		$this->db->from('master_kelas');
		$this->db->where(['id_kelas' => $id]);
		return $this->db->get()->row()->nama_kelas;
	}

	public function getNamaGuruById($id) {
		$this->db->select('nama_guru');
		$this->db->from('master_guru');
		$this->db->where('id_guru', $id);
		return $this->db->get()->row()->nama_guru;
	}

	public function getElapsed($id) {
		$this->db->select('id_durasi, id_siswa, id_jadwal, status, lama_ujian, mulai, selesai, reset');
		$this->db->from('cbt_durasi_siswa');
		$this->db->where('id_durasi', $id);
		return $this->db->get()->row();
	}

    public function getSoalSiswa($id_bank, $id_siswa) {
        $this->db->select('a.*, b.jenis, b.nomor_soal, b.jawaban');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal', 'left');
        $this->db->where('a.id_bank', $id_bank);
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->order_by('a.jenis_soal');
        $this->db->order_by('a.no_soal_alias');
        return $this->db->get()->result();
    }

    public function getJumlahSoalSiswa($id_bank, $id_siswa) {
        $this->db->select('id_soal_siswa');
        $this->db->from('cbt_soal_siswa');
        $this->db->where('id_bank', $id_bank);
        $this->db->where('id_siswa', $id_siswa);
        return $this->db->get()->num_rows();
    }

    public function getALLSoalSiswa($id_bank, $id_siswa) {
        $this->db->select('a.id_soal_siswa, a.id_bank, a.id_jadwal, a.id_soal, a.id_siswa, a.jenis_soal,'.
            ' a.no_soal_alias, a.opsi_alias_a, a.opsi_alias_b, a.opsi_alias_c, a.opsi_alias_d, a.opsi_alias_e,'.
            ' a.jawaban_alias, a.jawaban_siswa, a.jawaban_benar, a.point_essai, a.soal_end, a.point_soal,'.
            ' b.id_soal, b.nomor_soal, b.soal, b.jawaban, b.opsi_a, b.opsi_b, b.opsi_c, b.opsi_d,'.
            ' b.opsi_e, b.tampilkan');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal');
        $this->db->where('a.id_bank', $id_bank);
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->order_by('a.no_soal_alias');
        return $this->db->get()->result();
    }

    public function getJumlahJawaban($id_bank, $id_siswa) {
        $this->db->select('jawaban_siswa, id_siswa, id_bank');
        $this->db->from('cbt_soal_siswa');
        $this->db->where('id_bank', $id_bank);
        $this->db->where('id_siswa', $id_siswa);
        //$this->db->where('jawaban_siswa IS NOT NULL OR jawaban_siswa != ""');
        return $this->db->get()->result();
    }

    public function getSoalSiswaByJadwal($id_jadwal, $id_siswa) {
        $this->db->select('a.*, b.jenis, b.nomor_soal, b.soal, b.jawaban, b.opsi_a, b.opsi_b, b.opsi_c, b.opsi_d, b.opsi_e');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal');
        $this->db->where('a.id_jadwal', $id_jadwal);
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->where('b.tampilkan', '1');
        $this->db->order_by('a.jenis_soal');
        $this->db->order_by('b.nomor_soal');
        return $this->db->get()->result();
    }

    public function getSoalSiswaByNomor($id_soal_siswa) {
        $this->db->select('a.id_soal_siswa, a.id_bank, a.opsi_alias_a, a.opsi_alias_b, a.opsi_alias_c, a.opsi_alias_d,'.
            ' a.opsi_alias_e, a.no_soal_alias, a.jawaban_alias, a.soal_end, a.jawaban_siswa,'.
            ' b.id_soal, b.jenis, b.nomor_soal, b.soal, b.jawaban, b.opsi_a, b.opsi_b, b.opsi_c, b.opsi_d, b.opsi_e, b.tampilkan,'.
            ' c.tampil_pg, c.tampil_kompleks, c.tampil_jodohkan, c.tampil_isian, c.tampil_esai,');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal');
        $this->db->join('cbt_bank_soal c', 'b.id_bank=a.id_bank');
        $this->db->where('a.id_soal_siswa', $id_soal_siswa);
        $this->db->order_by('a.no_soal_alias');
        return $this->db->get()->row();
    }

	public function getSettingKartu() {
		$this->db->select('*');
		$this->db->from('cbt_kop_kartu');
		return $this->db->get()->row();
	}

	public function getSettingKopAbsensi() {
		$this->db->select('a.*, b.logo_kanan, b.logo_kiri, b.kepsek, b.tanda_tangan');
		$this->db->from('cbt_kop_absensi a');
		$this->db->join('setting b', 'b.id_setting=1', 'left');
		return $this->db->get()->row();
	}

	public function getSettingKopBeritaAcara() {
		$this->db->select('a.*, d.logo_kanan, d.logo_kiri, d.kepsek, d.nip, d.tanda_tangan, d.sekolah');
		$this->db->from('cbt_kop_berita a');
		//$this->db->join('cbt_kop_absensi b', 'b.id_kop=a.id_kop', 'left');
		$this->db->join('setting d', 'd.id_setting=1', 'left');
		return $this->db->get()->row();
	}

	public function getDurasiSiswa($id) {
		return $this->db->get_where('cbt_durasi_siswa', 'id_durasi='.$id)->row();
	}

    public function getFilterJawabanSiswa($jadwal, $arrIdSiswa) {
        $this->db->where('id_jadwal', $jadwal);
        $this->db->where_in('id_siswa', $arrIdSiswa);
        return $this->db->get('cbt_soal_siswa')->result();
    }

    public function getFilterDurasiSiswa($jadwal, $arrIdSiswa) {
        $this->db->where('id_jadwal', $jadwal);
        //$this->db->where_in('id_siswa', $arrIdSiswa);
        $result = $this->db->get_where('cbt_durasi_siswa')->result();

        $ret = [];
        foreach ($result as $key => $row) {
            $ret [$row->id_durasi] = $row;
        }
        return $ret;
    }

    public function getJadwalByKelas($id_tp, $id_smt, $kelas) {
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai,'.
            ' a.durasi_ujian, a.pengawas, a.acak_soal, a.acak_opsi, a.hasil_tampil, a.token, a.status, a.ulang,'.
            ' a.reset_login, a.rekap, a.jam_ke, a.jarak,'.
            ' c.bank_kode, c.bank_level, c.bank_kelas, c.tampil_pg, c.tampil_kompleks, c.tampil_jodohkan,'.
            ' c.tampil_isian, c.tampil_esai, c.soal_agama, '.
            ' c.bobot_pg, c.bobot_kompleks, c.bobot_jodohkan, c.bobot_isian, c.bobot_esai, b.kode_jenis,'.
            ' b.nama_jenis, d.kode, d.nama_mapel');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_jenis b', 'b.id_jenis=a.id_jenis');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=c.bank_mapel_id');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.status', '1');
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('c.status', '1');
        $this->db->where('c.status_soal', '1');
        $this->db->like('c.bank_kelas', $kelas);
        $this->db->order_by('a.jam_ke');

        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal] = $row;
        }
        return $retur;
    }

    public function getNilaiSiswaByArrJadwal($id_jadwal, $id_siswa) {
        $this->db->select('*');
        $this->db->from('cbt_nilai');
        $this->db->where_in('id_jadwal', $id_jadwal);
        $this->db->where('id_siswa', $id_siswa);
        //return $this->db->get()->row();
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal] = $row;
        }
        return $retur;
    }

    public function getJawabanSiswaByArrJadwal($id_jadwal, $id_siswa) {
        $this->db->select('a.*, b.jenis, b.nomor_soal, b.soal, b.jawaban, b.opsi_a, b.opsi_b, b.opsi_c, b.opsi_d, b.opsi_e, b.tampilkan');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal');
        $this->db->where_in('a.id_jadwal', $id_jadwal);
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->order_by('a.jenis_soal');
        $this->db->order_by('b.nomor_soal');
        $this->db->where('b.tampilkan', '1');
        //return $this->db->get()->result();
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal][] = $row;
        }
        return $retur;
    }

    public function getJawabanByBank($id_bank, $id_siswa = null) {
        $this->db->select('a.*, b.nomor_soal, b.jawaban, c.soal_agama');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal');
        $this->db->join('cbt_bank_soal c', 'c.id_bank=a.id_bank');
        if ($id_siswa!=null) {
            $this->db->where('a.id_siswa=', $id_siswa);
        }
        $this->db->where('a.id_bank=', $id_bank);
        return $this->db->get()->result();
    }

    public function getJawabanSiswa($id) {
        $this->db->select('id_soal_siswa, id_bank, id_jadwal, id_soal, id_siswa, jenis_soal, no_soal_alias, opsi_alias_a, opsi_alias_b, opsi_alias_c, opsi_alias_d, opsi_alias_e, jawaban_alias, jawaban_siswa, jawaban_benar, point_soal');
        $this->db->from('cbt_soal_siswa');
        $this->db->where('id_soal_siswa=', $id);
        return $this->db->get()->row();
	}

    public function getJawabanSiswaByJadwal($id_jadwal, $id_siswa = null) {
        $this->db->select('a.*, b.jenis, b.nomor_soal, b.soal, b.jawaban, b.opsi_a, b.opsi_b, b.opsi_c, b.opsi_d, b.opsi_e, b.tampilkan');
        $this->db->from('cbt_soal_siswa a');
        $this->db->join('cbt_soal b', 'b.id_soal=a.id_soal');
        if ($id_siswa != null) {
            if (is_array($id_siswa)) {
                $this->db->where_in('a.id_siswa', $id_siswa);
            } else {
                $this->db->where('a.id_siswa', $id_siswa);
            }
        }
        $this->db->where('a.id_jadwal=', $id_jadwal);
        $this->db->where('b.tampilkan', '1');
        $this->db->order_by('a.jenis_soal');
        $this->db->order_by('b.nomor_soal');
        return $this->db->get()->result();
    }

    public function getIdSiswaFromJawabanByJadwal($id_jadwal) {
        $result = $this->db->get_where('cbt_soal_siswa', 'id_jadwal='.$id_jadwal)->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_siswa][] = $row;
        }
        return $retur;
    }

    public function getDurasiSiswaByArrJadwal($id_jadwal, $id_siswa) {
        $this->db->select('id_durasi, id_siswa, id_jadwal, status, lama_ujian, mulai, selesai, reset');
        $this->db->from('cbt_durasi_siswa');
        $this->db->where_in('id_jadwal', $id_jadwal);
        $this->db->where('id_siswa', $id_siswa);
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal] = $row;
        }
        return $retur;
    }

    public function getDurasiSiswaByJadwal($id_jadwal, $id_siswa = null) {
        $this->db->select('id_durasi, id_siswa, id_jadwal, status, lama_ujian, mulai, selesai, reset');
        $this->db->from('cbt_durasi_siswa');
        $this->db->where('id_jadwal', $id_jadwal);
        if ($id_siswa != null) $this->db->where('id_siswa', $id_siswa);
        return $this->db->get()->result();
    }

    public function getIdSiswaFromDurasiByJadwal($id_jadwal) {
        $this->db->where('id_jadwal', $id_jadwal);
        $result = $this->db->get('cbt_durasi_siswa')->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_siswa] = $row;
        }
        return $retur;
    }

    public function getLogUjianByJadwal($id_jadwal) {
        $this->db->select('id_log, log_time, id_siswa, id_jadwal, log_type, log_desc, address, agent, device, reset');
        $this->db->from('log_ujian');
        $this->db->where('id_jadwal=', $id_jadwal);
        return $this->db->get()->result();
    }

    public function getIdSiswaFromLogUjianByJadwal($id_jadwal) {
        $result = $this->db->get_where('log_ujian', 'id_jadwal='.$id_jadwal)->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_siswa] = $row;
        }
        return $retur;
    }

    public function getNilaiSiswa($arr_jadwal, $id_siswa) {
        $this->db->select('*');
        $this->db->from('cbt_nilai');
        $this->db->where_in('id_jadwal', $arr_jadwal);
        $this->db->where('id_siswa', $id_siswa);
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_jadwal] = $row;
        }
        return $retur;
    }

    public function getNilaiSiswaByJadwal($id_jadwal, $id_siswa) {
        $this->db->select('*');
        $this->db->from('cbt_nilai');
        $this->db->where('id_jadwal', $id_jadwal);
        $this->db->where('id_siswa', $id_siswa);
        return $this->db->get()->row();
    }

    public function getNilaiAllSiswa($arr_jadwal, $arr_id_siswa) {
        $this->db->select('*');
        $this->db->from('cbt_nilai');
        $this->db->where_in('id_jadwal', $arr_jadwal);
        $this->db->where_in('id_siswa', $arr_id_siswa);
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_siswa] = $row;
        }
        return $retur;
    }

    public function getAllNilaiSiswa($id_jadwal) {
        $this->db->select('*');
        $this->db->from('cbt_nilai');
        $this->db->where('id_jadwal', $id_jadwal);
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            $retur[$row->id_siswa] = $row;
        }
        return $retur;
        //return $result;
    }

    public function getTotalKoreksi() {
        $this->db->select('id_jadwal, dikoreksi, id_siswa');
        $this->db->from('cbt_nilai');
        //$this->db->where_in('id_jadwal', $arr_jadwal);
        $result = $this->db->get()->result();
        $retur = [];
        foreach ($result as $row) {
            if ($row->id_siswa != null) $retur[$row->id_jadwal][$row->dikoreksi][] = $row->id_siswa;
        }
        return $retur;
        //return $result;
    }

    public function getNilaiAnalisis($id_jadwal) {
        return $this->db->get_where('cbt_nilai', 'id_jadwal='.$id_jadwal)->result();
    }

	public function getLogUjian($siswa_id, $id_jadwal) {
		return $this->db->get_where('log_ujian', 'id_siswa='.$siswa_id.' AND id_jadwal='.$id_jadwal)->result();
	}

	public function getNomorPeserta($id_siswa){
		return $this->db->get_where('cbt_nomor_peserta', 'id_siswa='.$id_siswa)->row();
	}

    public function getAllNomorPeserta(){
        $this->db->select('*');
        $result = $this->db->get('cbt_nomor_peserta')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_siswa] = $row;
        }
        return $ret;
    }

    /*
	public function getNilaiByKelas($id_tp, $id_smt, $id_kelas, $sesi = null) {
		$this->db->select('a.*, b.nama, d.kode_ruang, e.kode_sesi, e.nama_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta');
		$this->db->from('kelas_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');
		if ($sesi !=null) {
			$this->db->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa AND c.sesi_id='.$sesi);
		} else {
			$this->db->join('cbt_sesi_siswa c', 'c.siswa_id=a.id_siswa');
		}
		$this->db->join('cbt_ruang d', 'd.id_ruang=c.ruang_id');
		$this->db->join('cbt_sesi e', 'e.id_sesi=c.sesi_id');
		$this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
		$this->db->where('a.id_tp', $id_tp);
		$this->db->where('a.id_smt', $id_smt);
		if ($id_kelas!=null) {
			$this->db->where('a.id_kelas', $id_kelas);
		}

		return $this->db->get()->result();
	}

    public function getNilaiSiswaByKelasArray($id_tp, $id_smt, $jadwal, $arr_kelas) {
        $this->db->select('a.*, b.nama, b.nis, b.nisn, b.username, b.password, f.nama_kelas, f.kode_kelas, l.level, g.nomor_peserta, h.*');
        $this->db->from('kelas_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');
        $this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas');
        $this->db->join('level_kelas l', 'l.id_level=f.level_id');
        $this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.id_siswa AND g.id_tp='.$id_tp, 'left');
        $this->db->join('cbt_soal_siswa h', 'a.id_siswa=h.id_siswa AND h.id_jadwal='.$jadwal, 'left');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
        if (!in_array('Semua', $arr_kelas)) {
            $this->db->where_in('a.id_kelas', $arr_kelas);
        }
        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->order_by('l.level', 'ASC');

        return $this->db->get()->result();
    }

	public function getNilaiByRuang($id_tp, $id_smt, $id_ruang=null, $sesi=null) {
		$this->db->select('a.*, b.nama, d.nama_ruang, d.kode_ruang, e.kode_sesi, e.nama_sesi, f.nama_kelas, f.kode_kelas, g.nomor_peserta');
		$this->db->from('cbt_sesi_siswa a');
		$this->db->join('master_siswa b', 'b.id_siswa=a.siswa_id', 'left');
		$this->db->join('kelas_siswa c', 'c.id_siswa=a.siswa_id', 'left');
		$this->db->join('cbt_ruang d', 'd.id_ruang=a.ruang_id', 'left');
		$this->db->join('cbt_sesi e', 'e.id_sesi=a.sesi_id', 'left');
		$this->db->join('master_kelas f', 'f.id_kelas=c.id_kelas', 'left');
		$this->db->join('cbt_nomor_peserta g', 'g.id_siswa=a.siswa_id', 'left');
        //$this->db->join('buku_induk i', 'i.id_siswa=b.id_siswa AND =i.status=1');
		$this->db->where('a.tp_id', $id_tp);
		$this->db->where('a.smt_id', $id_smt);
		if ($id_ruang != null) {
			$this->db->where('a.ruang_id', $id_ruang);
		}
		if ($sesi !=null) {
			$this->db->where('a.sesi_id', $sesi);
		}

		return $this->db->get()->result();
	}
    */

    public function getDistinctTahun() {
        $this->db->select('tp');
        $this->db->distinct();
        $result = $this->db->get('cbt_rekap_nilai')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->tp] = $row->tp;
        }
        return $ret;
    }

    public function getDistinctSmt() {
        $this->db->select('smt');
        $this->db->distinct();
        $result = $this->db->get('cbt_rekap_nilai')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->smt] = $row->smt;
        }
        return $ret;
    }

    public function getDistinctJenisUjian() {
        $this->db->select('tp, smt, kode_jenis');
        $this->db->distinct();
        $result = $this->db->get('cbt_rekap_nilai')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->tp][$row->smt][$row->kode_jenis] = $row->kode_jenis;
        }
        return $ret;
    }

    public function getDistinctJenis() {
        $this->db->select('id_jenis, tp, smt, kode_jenis');
        $this->db->distinct();
        $result = $this->db->get('cbt_rekap_nilai')->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->tp][$row->smt][$row->id_jenis] = $row->kode_jenis;
        }
        return $ret;
    }

    public function getDistinctKelas($id_jadwal = null) {
        $this->db->select('a.tp, a.smt, a.kode_jenis, a.id_kelas, b.nama_kelas');
        $this->db->distinct();
        $this->db->from('cbt_rekap_nilai a');
        if ($id_jadwal !=null) {
            $this->db->where('id_jadwal', $id_jadwal);
        }
        $this->db->join('master_kelas b', 'b.id_kelas=a.id_kelas');
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $row) {
            if ($row->id_kelas !='') {
                $ret[$row->tp][$row->smt][$row->kode_jenis][$row->id_kelas] = $row->nama_kelas;
            }
        }
        return $ret;
    }

}
