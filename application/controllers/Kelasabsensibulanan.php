<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 14:10
 */

class Kelasabsensibulanan extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->ion_auth->logged_in()) {
			redirect('auth');
		} else if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Dibatasi');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Dropdown_model', 'dropdown');
		$this->load->model('Kelas_model', 'kelas');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function output_json($data, $encode = true) {
		if ($encode) $data = json_encode($data);
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function index() {
		$user = $this->ion_auth->user()->row();
		$data = [
			'user' => $user,
			'judul' => 'Daftar Hadir Bulanan',
			'subjudul' => 'Daftar Hadir Bulanan Siswa',
            'setting'		=> $this->dashboard->getSetting()
		];

		$tp = $this->master->getTahunActive();
		$smt = $this->master->getSemesterActive();

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;
		$data['bulan'] = $this->dropdown->getBulan();

		if ($this->ion_auth->is_admin()) {
            $data['profile'] = $this->dashboard->getProfileAdmin($user->id);
			$data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
			$data['guru'] = $this->dropdown->getAllGuru();
			$data['mapel'] = $this->dropdown->getAllMapel();

			$this->load->view('_templates/dashboard/_header', $data);
			$this->load->view('kelas/absenbulanan/data');
			$this->load->view('_templates/dashboard/_footer');
		} else {
			$guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
			$nguru[$guru->id_guru] = $guru->nama_guru;
			$data['guru'] = $guru;
			$data['id_guru'] = $guru->id_guru;

			$mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
			$mapel = json_decode(json_encode($this->maybe_unserialize($mapel_guru->mapel_kelas)));

			$arrMapel = [];
			$arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$m->id_mapel][] = [
                            'id_kelas' => $kls->kelas,
                            'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas)];
                    }
                }
            }

			$arrId = [];
            if ($mapel != null) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    array_push($arrId, $id_mapel->kelas);
                }
            }

			$data['mapel'] = $arrMapel;
			$data['arrkelas'] = $arrKelas;
			$data['kelas'] = count($arrId)>0 ? $this->dropdown->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

			$this->load->view('members/guru/templates/header', $data);
            $this->load->view('kelas/absenbulanan/data');
			$this->load->view('members/guru/templates/footer');
		}
	}

	public function loadAbsensiMapel() {
		$id_kelas = $this->input->post('kelas', true);
		$id_mapel = $this->input->post('mapel', true);
		$tahun = $this->input->post('thn', true);
		$bulan = $this->input->post('bln', true);

		$id_tp = $this->master->getTahunActive()->id_tp;
		$id_smt = $this->master->getSemesterActive()->id_smt;

        $jadwal = $this->dashboard->getJadwalKbm($id_tp, $id_smt, $id_kelas);
        if ($jadwal != null) {
            $jadwal->istirahat = $this->maybe_unserialize($jadwal->istirahat);

            $tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            $jadwal_materi = [];
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $b = $bulan < 10 ? '0'.($bulan) : $bulan;
                $jadwal_materi[$t] = (array)$this->kelas->getAllMateriByTgl($id_kelas, $tahun.'-'.$b.'-'.$t, [$id_mapel]);
            }

            /* hitung hari
            $myTime = strtotime('1/'.$bulan.'/'.$tahun);
            $daysInMonth = $tgl;
            $libur = 0;
            while($daysInMonth > 0) {
                $day = date("D", $myTime);
                if($day == "Sun") $libur++;
                $daysInMonth--;
                $myTime=strtotime('+1 day', $myTime);
            }
            */

            $materi_perbulan = $this->kelas->getRekapBulananSiswa($id_mapel, $id_kelas, $tahun, $bulan);
            $log = [];

            $siswa = $this->kelas->getKelasSiswa($id_kelas, $id_tp, $id_smt);
            foreach ($siswa as $s) {
                $arrMateri = [];
                for ($i=0;$i<$tgl;$i++) {
                    $t = ($i+1) < 10 ? '0'.($i+1) : $i+1;
                    $b = $bulan < 10 ? '0'.($bulan) : $bulan;
                    $arrMateri[1][] = isset($materi_perbulan[$s->id_siswa][1]) && $materi_perbulan !== null && isset($materi_perbulan[$s->id_siswa][1][$tahun . '-' . $b . '-' . $t]) ?
                        $materi_perbulan[$s->id_siswa][1][$tahun.'-'.$b.'-'.$t] : null;

                    $arrMateri[2][] = isset($materi_perbulan[$s->id_siswa][2]) && $materi_perbulan !== null && isset($materi_perbulan[$s->id_siswa][2][$tahun . '-' . $b . '-' . $t]) ?
                        $materi_perbulan[$s->id_siswa][2][$tahun.'-'.$b.'-'.$t] : null;

                }

                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'materi' => $arrMateri[1],
                    'tugas' => $arrMateri[2]
                ];
            }

            $mapel_bulan_ini = [];
            $infos = $this->kelas->getJadwalMapelByMapel($id_kelas, $id_mapel, $id_tp, $id_smt);
            foreach ($infos as $info) {
                $dates = $this->getDateFromWeekday($info->id_hari, $bulan, $tahun);
                foreach ($dates as $date) {
                    $d = explode('-', $date ?? '');
                    $mapel_bulan_ini[$d[2]][$info->jam_ke] = $date;
                }
            }

            $this->output_json([
                "log"=>$log,
                "jadwal"=>$jadwal,
                "materi"=>$jadwal_materi,
                "mapels"=>$mapel_bulan_ini,
                "info" => $infos
            ]);
        } else {
            $this->output_json(["jadwal"=>$jadwal]);
        }
	}

    function getDateFromWeekday($dayOfWeek, $month, $year) {
        $dates = [];
        $date = new DateTime("$year-$month-01");

        $firstDayOfWeek = $date->format('N');

        $dayOffset = $dayOfWeek - $firstDayOfWeek;

        if ($dayOffset < 0) {
            $dayOffset += 7;
        }

        $date->modify("+$dayOffset day");

        while ($date->format('m') == $month) {
            $dates[] = $date->format('Y-m-d');
            $date->modify('+7 days');
        }

        return $dates;
    }

    function total_hari($id_day, $bulan, $taun) {
        $days = 0;
        $dates = [];
        $total_days = cal_days_in_month(CAL_GREGORIAN, $bulan, $taun);
        $idday = $id_day === '7' ? 0 : $id_day;
        for($i=1;$i<$total_days;$i++) {
            if (date('N', strtotime($taun.'-'.$bulan.'-'.$i)) == $idday) {
                $days ++;
                $dates[] = date('Y-m-d', strtotime($taun . '-' . $bulan . '-' . $i));
            }
        }
        return $dates;//array('days' =>$days, 'dates'=>$dates);
    }

}
