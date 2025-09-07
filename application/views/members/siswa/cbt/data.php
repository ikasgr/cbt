<?php
// Initialize arrays and variables
$arrGuru = [];
foreach ($guru as $g) {
    $arrGuru[$g->id_guru] = htmlspecialchars($g->nama_guru); // Sanitize output
}

$jam_pertama = null;
$jadwal_selesai = [];
$cbt_setting = [];
?>

<main>

    <header class="masthead">
        <?php
        $cbt_setting = [];
        $this->load->view('members/siswa/templates/top'); ?>
    </header>
    <div class="container-fluid" style="margin-top: 10px;">
        <div class="main-content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="content logo-bg">
                            <!-- Breadcrumb Navigation -->
                            <div class="row">
                                <div class="col-12">
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
                                            <li class="breadcrumb-item active" aria-current="page">Daftar Asesmen</li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>

                            <!-- Assessment Header -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card-header">
                                        <div class="float-left">
                                            <a href="javascript:history.back()" class="btn btn-danger btn-sm"><i class="fa fa-arrow-left"></i></a>
                                        </div>
                                        <div class="float-right">
                                            <a class="btn btn-info text-white" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#token_notif"><i class="fa fa-spinner fa-spin"></i> Token</a>
                                        </div>
                                        <h5 class="text-center">ASESMEN HARI INI<br><?= buat_tanggal(date('D, d M Y')) ?></h5>
                                    </div>

                                    <!-- Modal Token -->
                                    <div class="modal fade" id="token_notif" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header" style="background: linear-gradient(141deg, #D6EAF8 0%, #ECF0F1 51%, #D5F5E3 75%); color: black;">
                                                    <h5 class="modal-title"><b><i class="fa fa-spin fa-spinner"></i> TOKEN UJIAN</b></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true"></span>
                                                    </button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <h1>
                                                        <label data-toggle="tooltip" data-placement="bottom" title="HARAP MENCATAT TOKEN INI SEBELUM KLIK MULAI UJIAN">
                                                            <?php if ($setting->tkn_siswa == 1): ?>
                                                                <span id="token-view" class="text-center">- - - - - -</span>
                                                            <?php else: ?>
                                                                <span class="text-center">Minta ke Pengawas</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    </h1>
                                                    <p><i class="fa fa-check-circle text-success"></i> Sebelum Klik Mulai Ujian Harap Mencatat Token Ini!</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                         <!-- Assessment Schedule -->           
                        <div class="card-body">
                            <div class="row" id="jadwal-content">
                                <?php
                                if ($cbt_info == null || count($cbt_setting) > 0) : ?>
                                    <div class="col-12 alert alert-default-warning">
                                        <div class="text-center">Tidak ada jadwal penilaian.<b>Tidak bisa mengerjakan
                                                ulangan/ujian.<br>Hubungi Proktor/Admin</div>
                                    </div>
                                <?php else:
                                    $jamSesi = $cbt_info == null ? '0' : (isset($cbt_info->sesi_id) ? $cbt_info->sesi_id : $cbt_info->id_sesi);
                                    if (isset($cbt_jadwal[date('Y-m-d')]) && count($cbt_jadwal[date('Y-m-d')]) > 0) :
                                        foreach ($cbt_jadwal[date('Y-m-d')] as $key => $jadwal)  :
                                            $kk = unserialize($jadwal->bank_kelas ?? '');
                                            $arrKelasCbt = [];
                                            foreach ($kk as $k) {
                                                array_push($arrKelasCbt, $k['kelas_id']);
                                            }

                                            $startDay = strtotime($jadwal->tgl_mulai);
                                            $endDay = strtotime($jadwal->tgl_selesai);
                                            $today = strtotime(date('Y-m-d'));

                                            //echo 'skrg='.$today . ' start=' . $startDay . ' end=' . $endDay;

                                            $hariMulai = new DateTime($jadwal->tgl_mulai);
                                            $hariSampai = new DateTime($jadwal->tgl_selesai);

                                            $sesiMulai = new DateTime($sesi[$jamSesi]['mulai']);
                                            $sesiSampai = new DateTime($sesi[$jamSesi]['akhir']);
                                            $now = strtotime(date('H:i'));

                                            $durasi = $elapsed[$jadwal->id_jadwal];
                                            $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke] = $durasi != null
                                                ? $durasi->status == '2'
                                                : false;

                                            if ($durasi != null) {
                                                $selesai = $durasi->selesai != null;
                                                $lanjutkan = $durasi->lama_ujian != null;
                                                $reset = $durasi->reset;
                                                if ($lanjutkan != null && !$selesai) $bg = 'bg-gradient-warning';
                                                elseif ($selesai) $bg = 'bg-success';
                                                else {
                                                    $bg = 'bg-gradient-danger';
                                                }
                                            } else {
                                                $selesai = false;
                                                $lanjutkan = false;
                                                $reset = 0;
                                                $bg = 'bg-gradient-danger';
                                            }
                                            $jam_ke = $jadwal->jam_ke == '0' ? '1' : $jadwal->jam_ke;
                                            ?>
                                            <div class="jadwal-cbt col-md-6 col-lg-4">
                                                <div class="card border">
                                                    <div class="card-header">
                                                        <div class="card-title">
                                                            <b>Jam ke: <?= $jam_ke ?></b>
                                                        </div>
                                                        <div class="card-tools">
                                                            <b><i class="fa fa-clock-o text-gray mr-1"></i><?= $jadwal->durasi_ujian ?>
                                                                mnt</b>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="small-box <?= $bg ?> mb-0">
                                                            <div class="ribbon-wrapper ribbon-lg">
                                                                <div class="ribbon bg-blue text-sm">
                                                                    <?= $jadwal->tampil_pg + $jadwal->tampil_kompleks + $jadwal->tampil_jodohkan + $jadwal->tampil_isian + $jadwal->tampil_esai ?> Soal
                                                                </div>
                                                            </div>                                                             
                                                            
                                                            <div class="inner">
                                                                <h6 class="crop-text-1">
                                                                    <b><?= $jadwal->nama_mapel ?></b></h6>
                                                                <h5><?= $jadwal->nama_jenis ?></h5>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="fas fa-book-open"></i>
                                                            </div>
                                                            <hr style="margin-top:0; margin-bottom: 0">
                                                            <?php
                                                            if (!$lanjutkan && $reset == 0 && !$selesai) : ?>
                                                                <?php if ($today < $startDay) : ?>
                                                                    <div id="<?= $jadwal->id_jadwal ?>"
                                                                         class="status small-box-footer p-2"
                                                                         data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                         data-jamke="<?= $jadwal->jam_ke ?>">
                                                                        <b>BELUM DIMULAI</b>
                                                                    </div>
                                                                <?php elseif ($today > $endDay) : ?>
                                                                    <div id="<?= $jadwal->id_jadwal ?>"
                                                                         class="status small-box-footer p-2"
                                                                         data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                         data-jamke="<?= $jadwal->jam_ke ?>">
                                                                        <b>SUDAH BERAKHIR</b>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <?php if ($now < strtotime($sesiMulai->format('H:i'))) : ?>
                                                                        <div id="<?= $jadwal->id_jadwal ?>"
                                                                             class="status small-box-footer p-2"
                                                                             data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                             data-jamke="<?= $jadwal->jam_ke ?>">
                                                                            <b><?= strtoupper($cbt_info->nama_sesi ?? '') ?>
                                                                                BELUM DIMULAI</b>
                                                                        </div>
                                                                    <?php elseif ($now > strtotime($sesiSampai->format('H:i'))) : ?>
                                                                        <div id="<?= $jadwal->id_jadwal ?>"
                                                                             class="status small-box-footer p-2"
                                                                             data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                             data-jamke="<?= $jadwal->jam_ke ?>">
                                                                            <b><?= strtoupper($cbt_info->nama_sesi ?? '') ?>
                                                                                SUDAH BERAKHIR</b>
                                                                        </div>
                                                                    <?php else : ?>
                                                                        <?php if (isset($jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1]) && $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1] == false) : ?>
                                                                            <button id="<?= $jadwal->id_jadwal ?>"
                                                                                    class="btn-block btn status text-white small-box-footer p-2 btn-disabled"
                                                                                    disabled>
                                                                                <b>MENUNGGU</b>
                                                                            </button>
                                                                        <?php else : ?>
                                                                            <button id="<?= $jadwal->id_jadwal ?>"
                                                                                    onclick="location.href='<?= base_url('siswa/konfirmasi/' . $jadwal->id_jadwal) ?>'"
                                                                                    class="btn btn-block status text-white small-box-footer p-2"
                                                                                    data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                                    data-jamke="<?= $jadwal->jam_ke ?>">
                                                                                <b>KERJAKAN</b><i
                                                                                        class="fas fa-arrow-circle-right ml-3"></i>
                                                                            </button>
                                                                        <?php endif; endif; endif; ?>
                                                            <?php elseif ($lanjutkan && !$selesai) : ?>
                                                                <button id="<?= $jadwal->id_jadwal ?>"
                                                                        class="btn-block btn status small-box-footer p-2 text-white"
                                                                        onclick="location.href='<?= base_url('siswa/konfirmasi/' . $jadwal->id_jadwal) ?>'"
                                                                        data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                        data-jamke="<?= $jadwal->jam_ke ?>">
                                                                    <b>LANJUTKAN</b><i
                                                                            class="fas fa-arrow-circle-right ml-3"></i>
                                                                </button>
                                                            <?php else : ?>
                                                                <div id="<?= $jadwal->id_jadwal ?>"
                                                                     class="btn status small-box-footer p-2"
                                                                     data-tgl="<?= $jadwal->tgl_mulai ?>"
                                                                     data-jamke="<?= $jadwal->jam_ke ?>">
                                                                    <b>SUDAH SELESAI</b>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endforeach;
                                    else: ?>
                                        <div class="col-12 alert alert-default-warning">
                                            <div class="text-center">Tidak ada jadwal penilaian hari ini.</div>
                                        </div>
                                    <?php
                                    endif;
                                endif;
                                ?>
                            </div>
                            <div class="alert bg-yellow text-center p-2">
                                <div class="text-lg">
                                    Jika waktu ujian sudah sesuai waktu namun belum muncul jadwal silahkan Klik <a href=''><span class="badge badge-success pt-1 pb-1"><i class="fa fa-sync ml-1 mr-1"></i> Refresh</span></a>
                                </div>
                        </div>                             
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <h5 class="text-center">
                                JADWAL PENILAIAN SEBELUMNYA
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row table-responsive">
                                <table class="table">
                                    <?php
                                    foreach ($cbt_jadwal as $tgl => $jadwals)  :
                                        if ($tgl != date('Y-m-d')) :?>
                                            <tr>
                                                <td colspan="4" class="tgl-ujian text-center bg-secondary"
                                                    data-tgl="<?= $tgl ?>">
                                                    <?= buat_tanggal(date('D, d M Y', strtotime($tgl))) ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-center">Jam ke</th>
                                                <th class="text-center align-middle">Mapel</th>
                                                <th class="align-middle d-none d-md-block">Jenis Penilaian</th>
                                                <th class="align-middle">Status</th>
                                            </tr>
                                            <?php
                                            foreach ($jadwals as $key => $jadwal)  :
                                                $jam_ke = $jadwal->jam_ke == '0' ? '1' : $jadwal->jam_ke;
                                                $kk = unserialize($jadwal->bank_kelas ?? '');
                                                $arrKelasCbt = [];
                                                foreach ($kk as $k) {
                                                    array_push($arrKelasCbt, $k['kelas_id']);
                                                }

                                                $startDay = strtotime($jadwal->tgl_mulai);
                                                $endDay = strtotime($jadwal->tgl_selesai);
                                                $today = strtotime(date('Y-m-d'));

                                                $hariMulai = new DateTime($jadwal->tgl_mulai);
                                                $hariSampai = new DateTime($jadwal->tgl_selesai);

                                                $sesiMulai = new DateTime($sesi[$jamSesi]['mulai']);
                                                $sesiSampai = new DateTime($sesi[$jamSesi]['akhir']);
                                                $now = strtotime(date('H:i'));

                                                $durasi = $elapsed[$jadwal->id_jadwal];
                                                $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke] = $durasi != null
                                                    ? $durasi->status == '2'
                                                    : false;

                                                if ($durasi != null) {
                                                    $selesai = $durasi->selesai != null;
                                                    $lanjutkan = $durasi->lama_ujian != null;
                                                    $reset = $durasi->reset;
                                                    if ($lanjutkan != null && !$selesai) $bg = 'btn-warning';
                                                    elseif ($selesai) $bg = 'btn-success';
                                                    else {
                                                        $bg = 'btn-danger';
                                                    }
                                                } else {
                                                    $selesai = false;
                                                    $lanjutkan = false;
                                                    $reset = 0;
                                                    $bg = 'btn-danger';
                                                }

                                                $status = '';
                                                if (!$lanjutkan && $reset == 0 && !$selesai) {
                                                    if ($today < $startDay) {
                                                        $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                            . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                            . ' <b>BELUM DIMULAI</b></button>';
                                                    } elseif ($today > $endDay) {
                                                        $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                            . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                            . ' <b>SUDAH BERAKHIR</b></button>';
                                                    } else {
                                                        if ($now < strtotime($sesiMulai->format('H:i'))) {
                                                            $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                                . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                                . ' <b>' . strtoupper($cbt_info->nama_sesi ?? '') . ' BELUM DIMULAI</b></button>';
                                                        } elseif ($now > strtotime($sesiSampai->format('H:i'))) {
                                                            $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                                . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                                . '<b>' . strtoupper($cbt_info->nama_sesi ?? '') . ' SUDAH BERAKHIR</b></button>';
                                                        } else {
                                                            if (isset($jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1]) && $jadwal_selesai[$jadwal->tgl_mulai][$jadwal->jam_ke - 1] == false) {
                                                                $status = '<button id="' . $jadwal->id_jadwal . '"'
                                                                    . ' class="status-table btn btn-disabled ' . $bg . '" disabled>'
                                                                    . ' <b>MENUNGGU</b></button>';
                                                            } else {
                                                                $status = '<button id="' . $jadwal->id_jadwal . '"'
                                                                    . ' onclick="location.href=\'' . base_url() . 'siswa/konfirmasi/' . $jadwal->id_jadwal . '\'"'
                                                                    . ' class="status-table btn ' . $bg . '"'
                                                                    . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                                    . ' <b>KERJAKAN</b></button>';
                                                            }
                                                        }
                                                    }
                                                } elseif ($lanjutkan && !$selesai) {
                                                    $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn ' . $bg . '"'
                                                        . ' onclick="location.href=\'' . base_url() . 'siswa/konfirmasi/' . $jadwal->id_jadwal . '\'"'
                                                        . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                        . ' <b>LANJUTKAN</b></button>';
                                                } else {
                                                    $status = '<button id="' . $jadwal->id_jadwal . '" class="status-table btn btn-disabled ' . $bg . '"'
                                                        . ' data-tgl="' . $jadwal->tgl_mulai . '" data-jamke="' . $jadwal->jam_ke . '">'
                                                        . ' <b>SUDAH SELESAI</b></button>';
                                                } ?>
                                                <tr>
                                                    <td class="text-center"><?= $jam_ke ?>
                                                        <br><?= $jadwal->durasi_ujian ?> mnt
                                                    </td>
                                                    <td class="text-center"><?= $jadwal->nama_mapel ?><br>
                                                        <small class="d-block d-md-none"><?= $jadwal->nama_jenis ?></small>
                                                    </td>
                                                    <td class="d-none d-md-block"><?= $jadwal->nama_jenis ?></td>
                                                    <td><?= $status ?></td>
                                                </tr>
                                            <?php
                                            endforeach;
                                        endif;
                                    endforeach; ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card bg-dark text-white shadow-lg rounded-lg">
                                    <img class="card-img" src="<?= base_url() ?>uploads/settings/banner2.jpg" alt="Card image">
                                    <div class="card-img-overlay">
                                        <h2 class="class-title"></h2>
                                        <p class="card-text">
                                        <h3 class="class-section-title"></h3>
                                        </p>
                                    </div>
                                </div>
                            </div>
                                <!-- Action Buttons -->
                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-center">
                                        <div class="col-4 col-md-3">
                                            <a href="<?= base_url() ?>dashboard" class="btn btn-success btn-block"><i class="fa fa-arrow-left"></i> KEMBALI</a>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <a href="#" class="btn btn-danger btn-block" onclick="logout()"><i class="fa fa-sign-out-alt"></i> LOGOUT</a>
                                        </div>
                                    </div>
                                </div>                           
                        </div>
                    </div>
                    </section>
                </div>

                <!-- Footer Section -->
                <footer>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="copyright">COPYRIGHT © <?= date('Y'); ?> | <?= htmlspecialchars($setting->sekolah); ?></div>
                            </div>
                        </div>
                    </div>
                </footer>
                <script>
                    let tokenResponse;
                    $(document).ready(function() {
                        function loadToken() {
                            $.ajax({
                                url: base_url + "siswa/getatoken/",
                                type: "GET",
                                success: function(response) {
                                    tokenResponse = response;
                                    console.log("load", tokenResponse);
                                    $('#token-view').html('<b>' + response.token + '</b>');
                                },
                                error: function(xhr, status, error) {
                                    console.log(xhr);
                                }
                            });
                        }

                        loadToken();


                    });
                </script>