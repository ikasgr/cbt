<?php
/**
 * Created by IntelliJ IDEA.
 * User: AbangAzmi
 * Date: 27/06/2020
 * Time: 08.51
 */

class Elearning_model extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

    public function getBulan() {
        $result = $this->db->get('bulan')->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_bln] = $row->nama_bln;
            }
        }
        return $ret;
    }

    public function getLoginSiswa($username){
        $this->db->select('a.id, b.*');
        $this->db->from('users a');
        $this->db->join('log b', 'a.id=b.id_user', 'left');
        $this->db->where("a.username", $username);
        $this->db->order_by('b.log_time', 'DESC');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row()->log_time;
        }
        return null;
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

    public function getAllMapel() {
        $this->db->select('id_mapel,nama_mapel,urutan_tampil');
        $this->db->order_by('urutan_tampil');
        $this->db->where('status', '1');
        $result = $this->db->get('master_mapel')->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_mapel] = $row->nama_mapel;
            }
        }
        return $ret;
    }

    public function getAllGuru() {
        $this->db->select('a.id_guru, a.nama_guru');
        $this->db->from('master_guru a');
        $this->db->join('users e', 'a.username=e.username');
        $result = $this->db->get()->result();
        $ret ['0'] = 'Pilih Guru :';
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_guru] = $row->nama_guru;
            }
        }
        return $ret;
    }

    public function getGuruMapelKelas($id_guru, $tp, $smt) {
        $this->db->select('a.id_guru, a.nama_guru, a.kode_guru, b.mapel_kelas, b.ekstra_kelas, d.nama_kelas');
        $this->db->from('master_guru a');
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp='.$tp.' AND b.id_smt='.$smt.'', 'left');
        $this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
        $this->db->join('master_kelas d', 'b.id_kelas=d.id_kelas AND d.id_tp='.$tp.' AND d.id_smt='.$smt.'', 'left');
        $this->db->where('a.id_guru', $id_guru);
        return $this->db->get()->row();
    }

    public function getAllKodeMapel() {
        $this->db->order_by('urutan_tampil');
        $this->db->where('status', '1');
        $result = $this->db->get('master_mapel')->result();
        $ret[''] = 'Tidak ada';
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_mapel] = $row->kode;
            }
        }
        return $ret;
    }

    public function getAllLevel($jenjang) {
        $levels = [];
        if ($jenjang == "1") {
            $levels = ["1" => "1", "2"=> "2", "3" => "3", "4"=>"4", "5"=>"5", "6"=>"6"];
        } elseif ($jenjang == "2") {
            $levels = ["7" => "7", "8"=> "8", "9" => "9"];
        } elseif ($jenjang == "3") {
            $levels = ["10" => "10", "11"=> "11", "12" => "12"];
        }

        return $levels;
    }

    public function getAllKelas($tp, $smt, $level = null) {
        $this->db->select('*');
        $this->db->from('master_kelas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->order_by('level_id', 'ASC');
        $this->db->order_by('nama_kelas', 'ASC');
        if ($level != null) {
            $this->db->where('level_id'.$level);
        }

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_kelas] = $row->nama_kelas;
            }
        }
        return $ret;
    }

    public function getDataKelas() {
        $this->db->select("id_kelas,id_smt,id_tp,kode_kelas,level_id,nama_kelas");
        $this->db->from('master_kelas');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_tp][$row->id_smt][$row->id_kelas] = $row;
            }
        }
        return $ret;
    }

    public function getNamaKelasById($arr_id) {
        $this->db->select('id_kelas, nama_kelas');
        $this->db->where_in('id_kelas', $arr_id);
        $result = $this->db->get('master_kelas')->result();

        $ret = null;
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_kelas] = $row->nama_kelas;
            }
        }
        return $ret;
    }

    public function getNamaKelasByKode($arr_kode) {
        $this->db->select('id_kelas, nama_kelas');
        $this->db->where_in('kode_kelas', $arr_kode);
        $result = $this->db->get('master_kelas')->result();

        $ret = null;
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_kelas] = $row->nama_kelas;
            }
        }
        return $ret;
    }

    public function getKelasSiswa($id_kelas, $id_tp, $id_smt){
        $this->db->select('a.*, b.nama, b.nis, b.nisn, b.username, b.jenis_kelamin, c.nama_kelas, c.level_id');
        $this->db->from('kelas_siswa a');
        $this->db->join('master_siswa b', 'a.id_siswa=b.id_siswa');
        $this->db->join('master_kelas c', 'a.id_kelas=c.id_kelas');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');

        $this->db->where("a.id_kelas", $id_kelas);
        $this->db->where("a.id_tp", $id_tp);
        $this->db->where("a.id_smt", $id_smt);
        $this->db->order_by('b.nama', 'ASC');
        return $this->db->get()->result();
    }

    public function getAllKelasByArrayId($tp, $smt, $arrId) {
        $this->db->select('*');
        $this->db->from('master_kelas');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->where_in('id_kelas', $arrId);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret [$row->id_kelas] = $row->nama_kelas;
            }
        }
        return $ret;
    }

    public function getJadwalKbm($tp, $smt, $kelas = null){
        $this->db->select('*');
        $this->db->from('kelas_jadwal_kbm');
        $this->db->where("id_tp", $tp);
        $this->db->where("id_smt", $smt);
        if ($kelas) {
            $this->db->where("id_kelas", $kelas);
            return $this->db->get()->row();
        }

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->id_kelas] = $row;
            }
        }
        return $ret;
    }

    public function getAllJadwalHarian(){
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.jam_ke, a.dari, a.sampai, a.id_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_tp][$row->id_smt][$row->id_hari][$row->id_kelas][] = $row;
            }
        }
        return $ret;
    }

    public function getJadwalHarian($tp, $smt, $hari, $kelas=null){
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.dari, a.sampai, a.id_mapel, '.
            'b.kode, b.nama_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'a.id_mapel=b.id_mapel', 'left');
        $this->db->where("id_tp", $tp, FALSE);
        $this->db->where("id_smt", $smt, FALSE);
        $this->db->where("id_hari", $hari, FALSE);
        if ($kelas) {
            $this->db->where("id_kelas", $kelas);
        }

        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_kelas][] = $row;
            }
        }
        return $ret;
    }

    public function getJadwalPerkelas($tp, $smt, $kelas){
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.dari, a.sampai, a.id_mapel, '.
            'b.kode, b.nama_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'b.id_mapel=a.id_mapel', 'left');
        $this->db->where("id_tp", $tp, FALSE);
        $this->db->where("id_smt", $smt, FALSE);
        $this->db->where("id_kelas", $kelas, FALSE);
        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->id_hari][] = $row;
            }
        }
        return $ret;
    }

    public function loadJadwalHariIni($id_tp, $id_smt, $id_kelas = null, $id_hari=null) {
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.dari, a.sampai, a.id_mapel, '.
            'b.kode, b.nama_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'b.id_mapel=a.id_mapel', 'left');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_mapel > 0');
        if ($id_kelas != null) {
            $this->db->where('a.id_kelas', $id_kelas);
        }
        if ($id_hari!=null) {
            $this->db->where('a.id_hari', $id_hari);
        }

        return $this->db->get()->result();
    }

    public function getJadwalMapel($tp, $smt){
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.dari, a.sampai, a.id_mapel, b.kode, b.nama_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'a.id_mapel=b.id_mapel', 'left');
        $this->db->where("a.id_tp", $tp);
        $this->db->where("a.id_smt", $smt);
        $this->db->where("a.dari IS NOT NULL AND a.id_mapel != 0 AND a.id_mapel !=''");
        return $this->db->get()->result();
    }

    public function getJadwalByMateri($id, $jenis, $tp, $smt) {
        $this->db->select('id_kjm, id_kelas, jadwal_materi, (SELECT COUNT(id_materi) FROM log_materi WHERE kelas_jadwal_materi.id_kjm=log_materi.id_materi) AS jml_siswa');
        $this->db->where('id_materi', $id);
        $this->db->where('jenis', $jenis);
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $result = $this->db->get('kelas_jadwal_materi')->result();

        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                if (isset($ret[$row->id_kelas])) {
                    $ret[$row->id_kelas][] = $row;
                } else {
                    $ret[$row->id_kelas] = [];
                    $ret[$row->id_kelas][] = $row;
                }
            }
        }
        return $ret;
    }

    public function getJadwalMapelByMapel($tp, $smt, $mapel=null, $kelas=null){
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.dari, a.sampai, a.id_mapel, b.kode, b.nama_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'a.id_mapel=b.id_mapel', 'left');
        $this->db->where("a.id_tp", $tp, FALSE);
        $this->db->where("a.id_smt", $smt, FALSE);
        if ($mapel) $this->db->where("a.id_mapel", $mapel, FALSE);
        if ($kelas) $this->db->where_in('a.id_kelas', $kelas);
        return $this->db->get()->result();
    }

    public function getJadwalTerisi($table, $kelas, $mapel, $tp, $smt){
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where("id_tp", $tp, FALSE);
        $this->db->where("id_smt", $smt, FALSE);
        $this->db->where("id_mapel", $mapel, FALSE);
        $this->db->where_in('id_kelas', $kelas);
        return $this->db->get()->result();
    }

    public function loadJadwalSiswaHariIni($id_tp, $id_smt, $id_kelas, $id_hari) {
        $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_kelas, a.id_hari, a.dari, a.sampai, a.id_mapel, b.kode, b.nama_mapel');
        $this->db->from('kelas_jadwal_mapel a');
        $this->db->join('master_mapel b', 'b.id_mapel=a.id_mapel', 'left');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_kelas', $id_kelas);
        $this->db->where('a.id_hari', $id_hari);
        return $this->db->get()->result();
    }

    public function getAllMateriByTgl($id_kelas, $tgl, $arr_mapel = []) {
        $this->db->select('a.*, b.id_materi, b.kode_materi, b.materi_kelas, b.tgl_mulai, c.nama_guru, d.kode, d.nama_mapel');
        $this->db->from('kelas_jadwal_materi a');
        $this->db->join('kelas_materi b', 'a.id_materi=b.id_materi AND b.status=1');
        $this->db->join('master_guru c', 'b.id_guru=c.id_guru', 'left');
        $this->db->join('master_mapel d', 'b.id_mapel=d.id_mapel', 'left');

        $this->db->where("a.id_kelas", $id_kelas);
        $this->db->where("a.jadwal_materi", $tgl);
        if (count($arr_mapel) > 0) {
            $this->db->where_in("a.id_mapel", $arr_mapel);
        }

        return $this->db->get()->result();
    }

    public function getAllMateriByArrTgl($id_kelas, $arr_tgl, $mapel) {
        $this->db->select('a.id_kjm, a.id_materi, a.jadwal_materi, a.jenis, b.id_materi, b.kode_materi, b.tgl_mulai, c.nama_guru, d.kode, d.nama_mapel');
        $this->db->from('kelas_jadwal_materi a');
        $this->db->join('kelas_materi b', 'a.id_materi=b.id_materi AND b.status=1');
        $this->db->join('master_guru c', 'b.id_guru=c.id_guru', 'left');
        $this->db->join('master_mapel d', 'b.id_mapel=d.id_mapel', 'left');

        $this->db->where_in("a.jadwal_materi", $arr_tgl);
        $this->db->where("a.id_mapel", $mapel);
        $this->db->where("a.id_kelas", $id_kelas);
        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->jadwal_materi][$row->jenis][] = $row;
            }
        }
        return $ret;
    }

    public function getAllMateriKelas($id_guru, $jenis) {
        $this->db->select('a.id_materi, a.kode_materi, a.kode_mapel, a.judul_materi, a.materi_kelas, f.nama_smt, e.tahun, f.smt,'.
            ' a.id_mapel, a.created_on, a.updated_on, a.file, a.status, a.id_tp, a.id_smt, b.nama_guru, d.nama_mapel, d.kode');
        $this->db->from('kelas_materi a');
        $this->db->join('master_guru b', 'a.id_guru=b.id_guru', 'left');
        $this->db->join('master_mapel d', 'a.id_mapel=d.id_mapel OR a.kode_mapel=d.kode', 'left');
        $this->db->join('master_tp e', 'a.id_tp=e.id_tp', 'left');
        $this->db->join('master_smt f', 'a.id_smt=f.id_smt', 'left');
        $this->db->where('a.jenis', $jenis);
        if ($id_guru != '0') $this->db->where('a.id_guru', $id_guru);
        $this->db->order_by('a.created_on', 'DESC');
        return $this->db->get()->result();
    }

    public function getMateriKelasById($id_materi, $jenis) {
        $this->db->select('a.*, b.nama_guru, b.foto, d.id_mapel, d.nama_mapel, c.mapel_kelas as kelas_guru');
        $this->db->from('kelas_materi a');
        $this->db->join('master_guru b', 'a.id_guru=b.id_guru', 'left');
        $this->db->join('jabatan_guru c', 'a.id_guru=c.id_guru', 'left');
        $this->db->join('master_mapel d', 'a.id_mapel=d.id_mapel', 'left');
        $this->db->where('a.id_materi', $id_materi);
        $this->db->where('a.jenis', $jenis);
        return $this->db->get()->row();
    }

    public function getKodeMateriMapel($id_tp, $id_smt, $id_mapel, $id_guru = null) {
        $this->db->select('a.id_mapel, a.id_materi, a.jenis, a.kode_materi, a.materi_kelas, a.id_guru, b.kode as kode_mapel, c.id_kjm, c.jadwal_materi, c.id_kelas, d.nama_guru');
        $this->db->from('kelas_materi a');
        $this->db->join('master_mapel b', 'b.id_mapel=a.id_mapel', 'left');
        $this->db->join('kelas_jadwal_materi c', 'a.id_materi=c.id_materi');
        $this->db->join('master_guru d', 'a.id_guru=d.id_guru');
        if ($id_guru != null) $this->db->where('a.id_guru', $id_guru);
        $this->db->where('a.id_mapel', $id_mapel);
        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        return $this->db->get()->result();
    }

    public function getStatusMateriSiswa($id_kjm = null){
        $this->db->select('a.*, b.jadwal_materi');
        $this->db->from('log_materi a');
        $this->db->join('kelas_jadwal_materi b', 'b.id_kjm=a.id_materi');
        if ($id_kjm !=null) {
            $this->db->where("a.id_materi", $id_kjm);
        }
        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_siswa] = $row;
            }
        }
        return $ret;
    }

    public function getMateriKelasSiswa($id_kjm, $jenis) {
        $this->db->select('a.id_kjm, a.id_materi, a.jadwal_materi, b.*, c.nama_guru, c.foto, e.id_mapel, e.nama_mapel, d.mapel_kelas as kelas_guru');
        $this->db->from('kelas_jadwal_materi a');
        $this->db->join('kelas_materi b', 'a.id_materi=b.id_materi');
        $this->db->join('master_guru c', 'b.id_guru=c.id_guru');
        $this->db->join('jabatan_guru d', 'b.id_guru=d.id_guru');
        $this->db->join('master_mapel e', 'b.id_mapel=e.id_mapel');
        $this->db->where('a.jenis', $jenis);
        $this->db->where('a.id_kjm', $id_kjm);
        return $this->db->get()->row();
    }

    public function getRekapStatusMateri($id_siswa, $arr_id_kjm) {
        $this->db->select('a.id_materi, a.log_time, a.finish_time, c.jenis, DAYOFMONTH(a.log_time) as tanggal, MONTH(a.log_time) as bulan, YEAR(a.log_time) as tahun, TIME_FORMAT(a.log_time, "%H:%i") as jam, d.nama_mapel, d.kode, d.id_mapel');
        $this->db->from('log_materi a');
        $this->db->join('kelas_jadwal_materi b', 'a.id_materi=b.id_kjm', 'left');
        $this->db->join('kelas_materi c', 'b.id_materi=c.id_materi', 'left');
        $this->db->join('master_mapel d', 'c.id_mapel=d.id_mapel', 'left');
        $this->db->where('a.id_siswa', $id_siswa);
        $this->db->where_in('a.id_materi', $arr_id_kjm);
        return $this->db->get()->result();
    }

    public function getRekapMateriKelas($id_kelas, $arr_id_kjm) {
        $this->db->select('a.id_siswa, a.id_materi, a.log_time, a.finish_time, c.jenis, DAYOFMONTH(a.log_time) as tanggal, MONTH(a.log_time) as bulan, YEAR(a.log_time) as tahun, TIME_FORMAT(a.log_time, "%H:%i") as jam, d.nama_mapel, d.kode, d.id_mapel');
        $this->db->from('log_materi a');
        $this->db->join('kelas_jadwal_materi b', 'a.id_materi=b.id_kjm', 'left');
        $this->db->join('kelas_materi c', 'b.id_materi=c.id_materi', 'left');
        $this->db->join('master_mapel d', 'c.id_mapel=d.id_mapel', 'left');
        $this->db->where_in('a.id_materi', $arr_id_kjm);
        $this->db->where('b.id_kelas', $id_kelas);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->id_siswa][] = $row;
            }
        }
        return $ret;
    }

    public function getRekapBulananSiswa($id_mapel, $id_kelas, $tahun, $bulan) {
        $this->db->select('a.*, b.log_time, b.finish_time, b.id_siswa, b.jam_ke, DAYOFMONTH(b.log_time) as tanggal, MONTH(b.log_time) as bulan, YEAR(b.log_time) as tahun, TIME_FORMAT(b.log_time, "%H:%i") as jam');
        $this->db->from('kelas_jadwal_materi a');
        $this->db->join('log_materi b', 'b.id_materi=a.id_kjm');
        $this->db->where('a.id_kelas', $id_kelas);
        if ($id_mapel != null) $this->db->where('a.id_mapel', $id_mapel);
        $this->db->where('MONTH(a.jadwal_materi)', $bulan)->where('YEAR(a.jadwal_materi)', $tahun);

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_siswa][$row->jenis][$row->jadwal_materi][] = $row;
            }
        }
        return $ret;
    }

    public function getRekapMateriSemester($id_kelas, $id_materi = null) {
        $this->db->select('a.id_siswa, a.id_log, a.log_time, a.finish_time, a.id_materi,'.
            ' DAYOFMONTH(a.log_time) as tanggal,'.
            ' MONTH(a.log_time) as bulan,'.
            ' YEAR(a.log_time) as tahun,'.
            ' TIME_FORMAT(a.log_time, "%H:%i") as jam,'.
            ' a.nilai, b.id_kelas, b.jenis');

        $this->db->from('log_materi a');
        $this->db->join('kelas_jadwal_materi b', 'a.id_materi=b.id_kjm');
        $this->db->where('b.id_kelas', $id_kelas);
        if ($id_materi != null) $this->db->where('a.id_materi', $id_materi);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $row) {
                $ret[$row->id_siswa][$row->jenis][] = $row;
            }
        }
        return $ret;
    }

    public function getDummyMateri() {
        return array(
            'id_materi' => '',
            'kode_materi' => '',
            'id_guru' => '',
            'id_mapel' => '',
            'id_jadwal' => '',
            'materi_kelas' => serialize([]),
            'kelas_guru' => serialize([]),
            'judul_materi' => '',
            'isi_materi' => '',
            'file' => '',
            'link_file' => '',
            'tgl_mulai' => '',
            'created_on' => '',
            'updated_on' => '',
        );
    }

}

