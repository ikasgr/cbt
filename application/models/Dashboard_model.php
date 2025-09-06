<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Model
 * 
 * Handles database operations for dashboard-related functionality.
 * Supports data retrieval for admin, guru, siswa, and pengawas roles.
 */
class Dashboard_model extends CI_Model
{
    /**
     * Get school settings
     * 
     * @return object|null Setting data
     */
    public function getSetting()
    {
        return $this->db->get('setting')->row();
    }

    /**
     * Get running text announcements
     * 
     * @return array Array of running text objects
     */
    public function getRunningText()
    {
        return $this->db->get('running_text')->result();
    }

    /**
     * Count total rows in a table
     * 
     * @param string $table Table name
     * @param string|null $where Where clause
     * @return int Number of rows
     */
    public function total($table, $where = null)
    {
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->get($table)->num_rows();
    }

    /**
     * Delete records from a table
     * 
     * @param string $table Table name
     * @param array $data Data to delete
     * @param string $pk Primary key column
     * @return bool Success status
     */
    public function hapus($table, $data, $pk)
    {
        $this->db->where_in($pk, $data);
        return $this->db->delete($table);
    }

    /**
     * Get admin profile
     * 
     * @param int $id_user User ID
     * @return object|null Admin profile data
     */
    public function getProfileAdmin($id_user)
    {
        $this->db->select('b.*')
                 ->from('users a')
                 ->join('users_profile b', 'a.id = b.id_user', 'left')
                 ->where('a.id', $id_user);
        return $this->db->get()->row();
    }

    /**
     * Count total homeroom teachers (wali kelas)
     * 
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return int Number of homeroom teachers
     */
    public function totalWaliKelas($id_tp, $id_smt)
    {
        $this->db->where([
            'id_jabatan' => '4',
            'id_tp' => $id_tp,
            'id_smt' => $id_smt
        ]);
        return $this->db->get('jabatan_guru')->num_rows();
    }

    /**
     * Count total students in a class
     * 
     * @param string $id_kelas Class ID
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return int Number of students
     */
    public function totalSiswaKelas($id_kelas, $id_tp, $id_smt)
    {
        $this->db->select('a.id_siswa')
                 ->from('kelas_siswa a')
                 ->where([
                     'a.id_tp' => $id_tp,
                     'a.id_smt' => $id_smt,
                     'a.id_kelas' => $id_kelas
                 ]);
        return $this->db->get()->num_rows();
    }

    /**
     * Count total exam supervisors (pengawas)
     * 
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return int Number of supervisors
     */
    public function totalPengawas($id_tp, $id_smt)
    {
        $this->db->where([
            'id_tp' => $id_tp,
            'id_smt' => $id_smt,
            'id_jadwal !=' => 'a:0:{}'
        ]);
        return $this->db->get('cbt_pengawas')->num_rows();
    }

    /**
     * Count total exam schedules
     * 
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return int Number of schedules
     */
    public function totalJadwal($id_tp, $id_smt)
    {
        $this->db->where([
            'id_tp' => $id_tp,
            'id_smt' => $id_smt
        ]);
        return $this->db->get('cbt_jadwal')->num_rows();
    }

    /**
     * Get academic years for datatables
     * 
     * @return string Datatables JSON
     */
    public function getDataTahun()
    {
        $this->datatables->select('id_tp, tahun, active')
                         ->from('master_tp');
        return $this->datatables->generate();
    }

    /**
     * Get all academic years
     * 
     * @return array Array of academic year objects
     */
    public function getTahun()
    {
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get('master_tp')->result();
    }

    /**
     * Get academic year by ID
     * 
     * @param string $id Academic year ID
     * @return object|null Academic year data
     */
    public function getTahunById($id)
    {
        return $this->db->get_where('master_tp', ['id_tp' => $id])->row();
    }

    /**
     * Get academic year by year name
     * 
     * @param string $tahun Year name
     * @return object|null Academic year data
     */
    public function getTahunByTahun($tahun)
    {
        return $this->db->get_where('master_tp', ['tahun' => $tahun])->row();
    }

    /**
     * Get active academic year
     * 
     * @return object|null Active academic year data
     */
    public function getTahunActive()
    {
        $this->db->select('id_tp, tahun')
                 ->from('master_tp')
                 ->where('active', 1);
        return $this->db->get()->row();
    }

    /**
     * Get all semesters
     * 
     * @return array Array of semester objects
     */
    public function getSemester()
    {
        $this->db->order_by('smt', 'ASC');
        return $this->db->get('master_smt')->result();
    }

    /**
     * Get semester by ID
     * 
     * @param string $id Semester ID
     * @return object|null Semester data
     */
    public function getSemesterById($id)
    {
        return $this->db->get_where('master_smt', ['id_smt' => $id])->row();
    }

    /**
     * Get semester by name
     * 
     * @param string $nama_smt Semester name
     * @return object|null Semester data
     */
    public function getSemesterByNama($nama_smt)
    {
        return $this->db->get_where('master_smt', ['nama_smt' => $nama_smt])->row();
    }

    /**
     * Get active semester
     * 
     * @return object|null Active semester data
     */
    public function getSemesterActive()
    {
        $this->db->select('id_smt, nama_smt, smt')
                 ->from('master_smt')
                 ->where('active', 1);
        return $this->db->get()->row();
    }

    /**
     * Get teacher data by user ID
     * 
     * @param int $id_user User ID
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return object|null Teacher data
     */
    public function getDataGuruByUserId($id_user, $id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, a.nip, a.id_user, a.foto, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level')
                 ->from('master_guru a')
                 ->join('jabatan_guru b', "a.id_guru = b.id_guru AND b.id_tp = '$id_tp' AND b.id_smt = '$id_smt'", 'left')
                 ->join('level_guru e', 'b.id_jabatan = e.id_level', 'left')
                 ->join('master_kelas f', "a.id_guru = f.guru_id AND f.id_tp = '$id_tp' AND f.id_smt = '$id_smt'", 'left')
                 ->join('level_kelas g', 'f.level_id = g.id_level', 'left')
                 ->where('a.id_user', $id_user);
        return $this->db->get()->row();
    }

    /**
     * Get teacher data by teacher ID
     * 
     * @param int $id_guru Teacher ID
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return object|null Teacher data
     */
    public function getDataGuruById($id_guru, $id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, a.nip, a.id_user, a.foto, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level')
                 ->from('master_guru a')
                 ->join('jabatan_guru b', "a.id_guru = b.id_guru AND b.id_tp = '$id_tp' AND b.id_smt = '$id_smt'", 'left')
                 ->join('level_guru e', 'b.id_jabatan = e.id_level', 'left')
                 ->join('master_kelas f', "a.id_guru = f.guru_id AND f.id_tp = '$id_tp' AND f.id_smt = '$id_smt'", 'left')
                 ->join('level_kelas g', 'f.level_id = g.id_level', 'left')
                 ->where('a.id_guru', $id_guru);
        return $this->db->get()->row();
    }

    /**
     * Get list of teachers by academic year and semester
     * 
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return array Array of teacher objects indexed by teacher ID
     */
    public function getListGuruByUserId($id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, a.id_user, a.foto, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level')
                 ->from('master_guru a')
                 ->join('jabatan_guru b', "a.id_guru = b.id_guru AND b.id_tp = '$id_tp' AND b.id_smt = '$id_smt'", 'left')
                 ->join('level_guru e', 'b.id_jabatan = e.id_level', 'left')
                 ->join('master_kelas f', "a.id_guru = f.guru_id AND f.id_tp = '$id_tp' AND f.id_smt = '$id_smt'", 'left')
                 ->join('level_kelas g', 'f.level_id = g.id_level', 'left');
        $query = $this->db->get()->result();
        return array_column($query, null, 'id_guru');
    }

    /**
     * Get detailed teacher data by user ID
     * 
     * @param int $id_user User ID
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return object|null Detailed teacher data
     */
    public function getDetailGuruByUserId($id_user, $id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('*')
                 ->from('master_guru a')
                 ->join('jabatan_guru b', "a.id_guru = b.id_guru AND b.id_tp = '$id_tp' AND b.id_smt = '$id_smt'", 'left')
                 ->join('level_guru e', 'b.id_jabatan = e.id_level', 'left')
                 ->join('master_kelas f', "a.id_guru = f.guru_id AND f.id_tp = '$id_tp' AND f.id_smt = '$id_smt'", 'left')
                 ->where('a.id_user', $id_user);
        return $this->db->get()->row();
    }

    /**
     * Get supervisor data by user ID
     * 
     * @param int $id_user User ID
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return object|null Supervisor data
     */
    public function getDataPengawasByUserId($id_user, $id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('p.*, u.username, u.first_name, u.last_name')
                 ->from('cbt_pengawas p')
                 ->join('users u', 'p.user_id = u.id', 'left')
                 ->where([
                     'p.user_id' => $id_user,
                     'p.id_tp' => $id_tp,
                     'p.id_smt' => $id_smt
                 ]);
        return $this->db->get()->row();
    }

    /**
     * Get classes by subject
     * 
     * @param string|null $id_mapel Subject ID
     * @return object|null Class data
     */
    public function getKelasByMapel($id_mapel = null)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('*')
                 ->from('master_kelas a')
                 ->join('master_mapel b', 'a.mapel_id = b.id_mapel', 'left')
                 ->join('level_guru d', 'a.level_id = d.id_level', 'left');
        if ($id_mapel) {
            $this->db->where('b.id_mapel', $id_mapel);
        }
        return $this->db->get()->row();
    }

    /**
     * Get data with optional joins and ordering
     * 
     * @param string $table Table name
     * @param string $pk Primary key column
     * @param mixed $id Primary key value
     * @param array|null $join Join conditions
     * @param array|null $order Order conditions
     * @return CI_DB_result Query result
     */
    public function get_where($table, $pk, $id, $join = null, $order = null)
    {
        $this->db->select('*')
                 ->from($table)
                 ->where($pk, $id);
        
        if ($join) {
            foreach ($join as $table => $field) {
                $this->db->join($table, $field);
            }
        }
        
        if ($order) {
            foreach ($order as $field => $sort) {
                $this->db->order_by($field, $sort);
            }
        }
        
        return $this->db->get();
    }

    /**
     * Insert data into table
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return bool Success status
     */
    public function create($table, $data)
    {
        return $this->db->insert($table, $data);
    }

    /**
     * Update data in table
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param string $pk Primary key column
     * @param mixed|null $id Primary key value
     * @param bool $batch Whether to use batch update
     * @return bool Success status
     */
    public function update($table, $data, $pk, $id = null, $batch = false)
    {
        if ($batch) {
            return $this->db->update_batch($table, $data, $pk);
        }
        return $this->db->update($table, $data, [$pk => $id]);
    }

    /**
     * Get student data
     * 
     * @param string $username Student username
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @return object|null Student data
     */
    public function getDataSiswa($username, $id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('*')
                 ->from('master_siswa a')
                 ->join('kelas_siswa b', "a.id_siswa = b.id_siswa AND b.id_tp = '$id_tp' AND b.id_smt = '$id_smt'", 'left')
                 ->join('master_kelas c', "b.id_kelas = c.id_kelas AND c.id_tp = '$id_tp' AND c.id_smt = '$id_smt'", 'left')
                 ->join('cbt_sesi_siswa d', 'a.id_siswa = d.siswa_id', 'left')
                 ->where('username', $username);
        return $this->db->get()->row();
    }

    /**
     * Load announcements
     * 
     * @param string $id_for Target audience
     * @return array Array of announcement objects
     */
    public function loadPengumuman($id_for)
    {
        $this->db->select('a.*, b.nama_guru, b.foto')
                 ->from('pengumuman a')
                 ->join('master_guru b', 'a.dari = b.id_guru', 'left')
                 ->where('kepada', $id_for);
        return $this->db->get()->result();
    }

    /**
     * Load daily schedule
     * 
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @param string|null $id_kelas Class ID
     * @param int|null $id_hari Day ID
     * @return array Array of schedule objects
     */
    public function loadJadwalHariIni($id_tp, $id_smt, $id_kelas = null, $id_hari = null)
    {
        $this->db->select('*')
                 ->from('kelas_jadwal_mapel a')
                 ->join('master_mapel b', 'b.id_mapel = a.id_mapel', 'left')
                 ->where([
                     'a.id_tp' => $id_tp,
                     'a.id_smt' => $id_smt
                 ]);
        
        if ($id_kelas) {
            $this->db->where('a.id_kelas', $id_kelas);
        }
        if ($id_hari) {
            $this->db->where('a.id_hari', $id_hari);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Get KBM schedule
     * 
     * @param string $id_tp Academic year ID
     * @param string $id_smt Semester ID
     * @param string|null $id_kelas Class ID
     * @return object|array Single object or array of KBM objects
     */
    public function getJadwalKbm($id_tp, $id_smt, $id_kelas = null)
    {
        $this->db->select('*')
                 ->from('kelas_jadwal_kbm')
                 ->where([
                     'id_tp' => $id_tp,
                     'id_smt' => $id_smt
                 ]);
        
        if ($id_kelas) {
            $this->db->where('id_kelas', $id_kelas);
            return $this->db->get()->row();
        }
        
        return $this->db->get()->result();
    }
}