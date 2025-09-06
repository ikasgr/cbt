<div class="content-wrapper bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <button class="btn btn-default btn-sm ml-auto btn-toggle" 
                            data-toggle="modal" 
                            data-target="#infoModal">
                        <i class="fas fa-info-circle mr-1"></i> Info Error
                    </button>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-8 col-md-4 mb-3">
                            <label for="jadwal">Jadwal</label>
                            <?= form_dropdown('jadwal', $jadwal, null, 'id="jadwal" class="form-control"'); ?>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <label for="printby">Filter By</label>
                            <select name="printby" id="printby" class="form-control">
                                <option value="1" selected>Ruang</option>
                                <option value="2">Kelas</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 mb-3 d-none" id="by-kelas">
                            <label for="kelas">Kelas</label>
                            <select name="kelas" id="kelas" class="form-control">
                                <option value="" selected>Pilih Kelas</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-3 by-ruang">
                            <label for="ruang">Ruang</label>
                            <select name="ruang" id="ruang" class="form-control">
                                <option value="" selected>Pilih Ruang</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-3 by-ruang">
                            <label for="sesi">Sesi</label>
                            <select name="sesi" id="sesi" class="form-control">
                                <option value="" selected>Pilih Sesi</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="row d-none" id="info">
                        <div class="col-md-4">
                            <div class="card card-widget widget-user-2">
                                <div class="widget-user-header bg-warning">
                                    <h5 class="text-bold text-center">INFO UJIAN</h5>
                                </div>
                                <div class="card-footer p-0">
                                    <div class="col-12 mt-1 mb-1">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">TOKEN</span>
                                            </div>
                                            <input id="token-input" 
                                                   class="form-control text-bold text-center" 
                                                   readonly="readonly">
                                            <div class="input-group-append" id="kolom-kanan">
                                                <span id="interval" 
                                                      class="input-group-text text-xs d-none">
                                                    -- : -- : --
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="info-ujian"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card card-widget widget-user-2">
                                <div class="widget-user-header bg-info">
                                    <h5 class="text-bold text-center">
                                        <i class="fa fa-warning mr-1"></i> PANDUAN
                                    </h5>
                                </div>
                                <div class="card-footer p-0">
                                    <div class="col-12 mt-1 mb-1">
                                        <div id="info-penggunaan">
                                            <ul>
                                                <li>
                                                    Gunakan tombol 
                                                    <span class="badge badge-success pt-1 pb-1">
                                                        <i class="fa fa-sync mr-1"></i> Refresh
                                                    </span> 
                                                    untuk merefresh halaman
                                                </li>
                                                <li>
                                                    <b>RESET WAKTU.</b> Jika siswa logout sebelum selesai 
                                                    dan tidak melanjutkan sampai waktu ujian habis maka 
                                                    akan ditolak, jika ingin melanjutkan maka harus reset waktu.
                                                </li>
                                                <li>
                                                    Aksi <b>RESET IZIN</b> untuk mengizinkan siswa 
                                                    mengerjakan ujian di perangkat berbeda.
                                                </li>
                                                <li>
                                                    Aksi <b>PAKSA SELESAI</b> untuk memaksa siswa 
                                                    menyelesaikan ujian.
                                                </li>
                                                <li>
                                                    Aksi <b>ULANG</b> untuk mengulang ujian siswa dari awal.
                                                </li>
                                                <li>
                                                    <span class="badge badge-success">
                                                        <i class="fa fa-check mr-1"></i> Terapkan Aksi
                                                    </span> 
                                                    untuk menerapkan aksi terpilih ke setiap siswa yang dipilih
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="button" 
                                    class="btn btn-success mb-2" 
                                    onclick="refreshStatus()" 
                                    data-toggle="tooltip" 
                                    title="Refresh">
                                <i class="fa fa-sync mr-1"></i> Refresh
                            </button>
                            <div class="float-right mb-2">
                                <label>
                                    <input type="search" 
                                           id="cari-status-siswa" 
                                           class="form-control form-control-sm" 
                                           placeholder="Cari">
                                </label>
                                <button type="button" 
                                        class="btn btn-success ml-2 <?= $this->ion_auth->is_admin() ? '' : 'd-none' ?>" 
                                        onclick="terapkanAksi()" 
                                        data-toggle="tooltip" 
                                        title="Terapkan Aksi pada siswa terpilih">
                                    <i class="fa fa-check mr-1"></i> Terapkan Aksi
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-nowrap" id="table-status"></table>
                    </div>
                </div>

                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= form_open('', ['id' => 'reset']) ?>
<div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetLabel">Reset Waktu Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="icheck-success">
                        <input class="radio" type="radio" name="reset" id="reset1" value="1">
                        <label for="reset1">Reset Waktu dari awal</label>
                    </div>
                    <div class="icheck-success">
                        <input class="radio" type="radio" name="reset" id="reset2" value="2">
                        <label for="reset2">Lanjutkan sisa waktu</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">OK</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoLabel">Kode Error Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table w-100">
                    <tr>
                        <td class="text-bold">001</td>
                        <td>:</td>
                        <td>Token salah atau token belum digenerate</td>
                    </tr>
                    <tr>
                        <td class="text-bold">002</td>
                        <td>:</td>
                        <td>Harus reset izin</td>
                    </tr>
                    <tr>
                        <td class="text-bold">003</td>
                        <td>:</td>
                        <td>Harus reset waktu</td>
                    </tr>
                    <tr>
                        <td class="text-bold">004</td>
                        <td>:</td>
                        <td>Soal belum dibuat atau belum dipilih</td>
                    </tr>
                    <tr>
                        <td class="text-bold">005</td>
                        <td>:</td>
                        <td>Siswa menggunakan browser yang tidak mendukung</td>
                    </tr>
                    <tr>
                        <td class="text-bold">006</td>
                        <td>:</td>
                        <td>Internet down atau error dari database/server</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    const ruangs = JSON.parse('<?=json_encode($ruang)?>');
    const arrRuang = JSON.parse('<?=json_encode($ruangs)?>');
    var dnone = '<?= $this->ion_auth->is_admin() ? "" : "d-none" ?>';
    var printBy = '1';
    var url = '';

    var kelas;
    var jadwal, ruang, sesi;

    function terapkanAksi() {
        const $rows = $('#table-status').find('tr'), headers = $rows.splice(0, 2);
        let item = {};
        item ["reset"] = [];
        //item ["id_logs"] = [];
        item ["force"] = [];
        item ["log"] = [];
        item ["ulang"] = [];
        item ["hapus"] = [];
        $rows.each((i, row) => {
            var siswa_id = $(row).attr("data-id");

            const $colReset = $(row).find('.input-reset');
            const $colForce = $(row).find('.input-force');
            const $colUlang = $(row).find('.input-ulang');
            if ($colReset.prop("checked") === true) {
                item ["reset"].push(siswa_id + '0' + jadwal + '1');
                //item ["id_logs"].push(siswa_id+''+jadwal);
            }
            if ($colForce.prop("checked") === true) {
                item ["force"].push(siswa_id + '0' + jadwal);
                item ["log"].push(siswa_id);
            }
            if ($colUlang.prop("checked") === true) {
                item ["ulang"].push(siswa_id);
                item ["hapus"].push(siswa_id + '0' + jadwal);
            }
        });

        var dataSiswa = $('#reset').serialize() + '&jadwal=' + jadwal + "&aksi=" + JSON.stringify(item);
        console.log(dataSiswa);

        var jmlReset = item.reset.length === 0 ? '' : '<b>' + item.reset.length + '</b> siswa akan direset<br>';
        var jmlForce = item.force.length === 0 ? '' : '<b>' + item.force.length + '</b> siswa akan dipaksa selesai<br>';
        var jmlUlang = item.ulang.length === 0 ? '' : '<b>' + item.ulang.length + '</b> siswa akan mengulang ujian';

        if (item.reset.length === 0 && item.force.length === 0 && item.ulang.length === 0) {
            showWarningToast('Silahkan pilih siswa terlebih dulu');
            return;
        }

        swal.fire({
            title: "Terapkan Aksi",
            html: jmlReset + jmlForce + jmlUlang,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Apply"
        }).then(result => {
            if (result.value) {
                $.ajax({
                    url: base_url + "siswa/applyaction",
                    type: 'POST',
                    data: dataSiswa,
                    success: function (data) {
                        console.log(data);
                        if (printBy === '1') {
                            url = base_url + "cbtstatus/getsiswaruang?ruang=" + ruang + '&sesi=' + sesi + '&jadwal=' + jadwal;
                        } else {
                            url = base_url + "cbtstatus/getsiswakelas?kelas=" + kelas + '&jadwal=' + jadwal;
                        }
                        refreshStatus();
                    }, error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }
        });
    }

    function refreshStatus() {
        $('#cari-status-siswa').val('')
        $('#loading').removeClass('d-none');
        setTimeout(function () {
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    console.log(response);
                    createPreview(response)
                }
            });
        }, 500);
    }

    function createPreview(data) {
        var tbody = '<thead class="alert-light">' +
            '<tr>' +
            '<th rowspan="2" class="text-center align-middle" width="40">No.</th>' +
            '<th rowspan="2" class="text-center align-middle" width="100">No. Peserta</th>' +
            '<th rowspan="2" class="text-center align-middle">NAMA PESERTA </br></br>(Pastikan status siswa sudah <span class="badge badge-info ml-1"><i class="fa fa-check-circle mr-1"></i> Selesai </span>)</th>' +
            '<th rowspan="2" class="text-center align-middle">Kelas</th>' +
            '<th rowspan="2" class="text-center align-middle">Sesi</th>' +
            '<th rowspan="2" class="text-center align-middle">Ruang</th>' +
            '<th colspan="2" class="text-center align-middle">Status</th>' +
            '<th rowspan="2" class="text-center align-middle ' + dnone + '">Reset<br>Waktu</th>' +
            '<th colspan="3" class="text-center align-middle ' + dnone + '">Aksi</th>' +
            '</tr>' +
            '<tr>' +
            '<th class="text-center align-middle p-1">Mulai</th>' +
            '<th class="text-center align-middle">Durasi</th>' +
            '<th class="text-center align-middle ' + dnone + '">Reset<br>Izin<br><input id="input-reset-all" class="check" type="checkbox"></th>' +
            '<th class="text-center align-middle ' + dnone + '">Paksa<br>Selesai<br><input id="input-force-all" class="check" type="checkbox"></th>' +
            '<th class="text-center align-middle ' + dnone + '">Ulang<br><input id="input-ulang-all" class="check" type="checkbox"></th>' +
            '</tr></thead><tbody>';

        if (data.siswa.length > 0) {
            for (let i = 0; i < data.siswa.length; i++) {
                var idSiswa = data.siswa[i].id_siswa;
                var durasi = data.durasi[idSiswa].dur != null ? data.durasi[idSiswa].dur.lama_ujian : ' - -';
                var adaWaktu = data.durasi[idSiswa].dur != null ? data.durasi[idSiswa].dur.ada_waktu : true;

                var logging = data.durasi[idSiswa].log;
                var mulai = '- -  :  - -';
                var selesai = '- -  :  - -';
                var reset = null;
                for (let k = 0; k < logging.length; k++) {
                    if (logging[k].log_type === '1') {
                        if (logging[k] != null) {
                            reset = logging[k].reset;
                            var t = logging[k].log_time.split(/[- :]/);
                            //var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
                            mulai = t[3] + ':' + t[4];
                        }
                    } else {
                        if (logging[k] != null) {
                            var ti = logging[k].log_time.split(/[- :]/);
                            selesai = ti[3] + ':' + ti[4];
                        }
                    }
                }

                var belumUjian = data.durasi[idSiswa].dur == null;
                var sudahSelesai = !belumUjian && data.durasi[idSiswa].dur.selesai != null;
                var loading = belumUjian ? '' : (sudahSelesai ? '<i class="fa fa-check-circle text-green mr-2"></i>' : '<i class="fa fa-spinner fa-spin text-blue mr-2"></i>');
                var statusku = belumUjian ? '<i class="fa fa-warning text-warning ml-2 mr-2"></i>' : (sudahSelesai ? ' <span class="badge badge-info ml-2"><i class="fa fa-check-circle mr-2"></i> Selesai </span>' : ' <span class="badge badge-success ml-2"><i class="fa fa-spinner fa-spin mr-2"></i> Sedang Ujian </span>');

                var disabledResetWaktu = !sudahSelesai && !adaWaktu ? '' : 'disabled';
                var disabledReset = !sudahSelesai && reset != null && reset == '0' ? '' : 'disabled';
                var disabledSelesai = !sudahSelesai && !belumUjian ? '' : 'disabled';
                var disabledUlang = belumUjian ? 'disabled' : (sudahSelesai ? '' : 'disabled');

                // jika ingin selalu aktif
                // var disabledResetWaktu = '';     // reset waktu selalu aktif
                // var disabledReset = '';          // reset izin selalu aktif
                // var disabledSelesai = '';        // paksa selesai selalu aktif
                // var disabledUlang = '';         // ulangi selalu aktif

                var sesi = data.siswa[i].kode_sesi;
                var ruang = data.siswa[i].kode_ruang;
                var kelas = data.siswa[i].kode_kelas;

                tbody += '<tr data-id="' + idSiswa + '">' +
                    '<td class="text-center align-middle">' + (i + 1) + '</td>' +
                    '<td class="text-center align-middle">' + data.siswa[i].nomor_peserta + '</td>' +
                    '<td class="align-middle">' + data.siswa[i].nama + statusku + '</td>' +
                    '<td class="text-center align-middle">' + kelas + '</td>' +
                    '<td class="text-center align-middle">' + sesi + '</td>' +
                    '<td class="text-center align-middle">' + ruang + '</td>' +
                    '<td class="text-center align-middle">' + mulai + '</td>' +
                    '<td class="text-center align-middle">' + loading + durasi + '</td>' +
                    '<td class="text-center align-middle '+dnone+'">' +
                    '	<button type="button" class="btn btn-default" ' +
                    'data-siswa="' + idSiswa + '" data-jadwal="' + data.info.id_jadwal + '" ' +
                    'data-toggle="modal" data-target="#resetModal" ' + disabledReset + '><i class="fa fa-refresh"></i></button>' +
                    '</td>' +
                    '<td class="text-center text-success align-middle ' + dnone + '">' +
                    '<input class="check input-reset" type="checkbox" ' + disabledReset + '>' +
                    '</td>' +
                    '<td class="text-center text-danger align-middle ' + dnone + '">' +
                    '<input class="check input-force" type="checkbox" ' + disabledSelesai + '>' +
                    '</td>' +
                    '<td class="text-center text-danger align-middle ' + dnone + '">' +
                    '<input class="check input-ulang" type="checkbox" ' + disabledUlang + '>' +
                    '</td>' +
                    '</tr>';
            }
        } else {
            tbody += '<tr><td colspan="12" class="text-center">Tidak ada siswa tergabung disini!</td></tr>'
        }

        tbody += '</tbody>';
        $('#table-status').html(tbody);
        $('#info').removeClass('d-none');

        var infoJadwal =   '<ul class="nav flex-column">' +
                              '<li class="nav-item">' +
                                '<a class="nav-link"> Mata Pelajaran <span class="float-right badge bg-primary">' + data.info.nama_mapel + '</span> </a>' +
                             '</li>' +
                              '<li class="nav-item">' +
                                ' <a class="nav-link"> Nama Guru <span class="float-right badge bg-info">' + data.info.nama_guru + '</span> </a>' +
                             '</li>' +
                              '<li class="nav-item">' +
                                ' <a class="nav-link"> Jenis Ujian <span class="float-right badge bg-success">' + data.info.kode_jenis + '</span> </a>' +
                             '</li>' +  
                              '<li class="nav-item">' +
                                ' <a class="nav-link"> Jumlah Soal <span class="float-right badge bg-danger">' 
                                + (parseInt(data.info.tampil_pg) + parseInt(data.info.tampil_kompleks) +
                                parseInt(data.info.tampil_jodohkan) + parseInt(data.info.tampil_isian) +
                                parseInt(data.info.tampil_esai)) + '</span> </a>' +
                             '</li>' +                              
                            '</ul>' ;    
            
            

        $('#info-ujian').html(infoJadwal);
        $('#loading').addClass('d-none');

        $("#input-reset-all").on("click", function () {
            if (this.checked) {
                $(".input-reset").each(function () {
                    if (!$(this).prop('disabled')) {
                        this.checked = true;
                    }
                    $("#input-reset-all").prop("checked", true);
                });
            } else {
                $(".input-reset").each(function () {
                    if (!$(this).prop('disabled')) {
                        this.checked = false;
                    }
                    $("#input-reset-all").prop("checked", false);
                });
            }
        });

        $("#input-force-all").on("click", function () {
            if (this.checked) {
                $(".input-force").each(function () {
                    if (!$(this).prop('disabled')) {
                        this.checked = true;
                    }
                    $("#input-force-all").prop("checked", true);
                });
            } else {
                $(".input-force").each(function () {
                    if (!$(this).prop('disabled')) {
                        this.checked = false;
                    }
                    $("#input-force-all").prop("checked", false);
                });
            }
        });

        $("#input-ulang-all").on("click", function () {
            if (this.checked) {
                $(".input-ulang").each(function () {
                    if (!$(this).prop('disabled')) {
                        this.checked = true;
                    }
                    $("#input-ulang-all").prop("checked", true);
                });
            } else {
                $(".input-ulang").each(function () {
                    if (!$(this).prop('disabled')) {
                        this.checked = false;
                    }
                    $("#input-ulang-all").prop("checked", false);
                });
            }
        });

        $('#cari-status-siswa').quicksearch('#table-status tbody tr');
    }

    function getDetailJadwal(idJadwal) {
        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/getjadwalujianbyjadwal?id_jadwal=" + idJadwal,
            cache: false,
            success: function (response) {
                console.log(response);
                var selKelas = $('#kelas');
                selKelas.html('');
                selKelas.append('<option value="">Pilih Kelas</option>');
                $.each(response, function (k, v) {
                    if (v != null) {
                        selKelas.append('<option value="' + k + '">' + v + '</option>');
                    }
                });
            }
        });
    }

    $(document).ready(function () {
        ajaxcsrf();

        var opsiPrintBy = $("#printby");
        var opsiJadwal = $("#jadwal");
        var opsiRuang = $("#ruang");
        var opsiSesi = $("#sesi");
        var opsiKelas = $("#kelas");

        opsiJadwal.prepend("<option value='' selected='selected'>Pilih Jadwal</option>");
        opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
        opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");
        opsiKelas.prepend("<option value='' selected='selected'>Pilih Kelas</option>");

        function loadSiswaRuang(ruang, sesi, jadwal) {
            var empty = ruang === '' || sesi === '' || jadwal === '';
            if (!empty) {
                url = base_url + "cbtstatus/getsiswaruang?ruang=" + ruang + '&sesi=' + sesi + '&jadwal=' + jadwal;
                refreshStatus();
            } else {
                console.log('empty')
            }
        }

        function loadSiswaKelas(kelas, jadwal) {
            var empty = kelas === '' || jadwal === '';
            if (!empty) {
                url = base_url + "cbtstatus/getsiswakelas?kelas=" + kelas + '&jadwal=' + jadwal;
                refreshStatus();
            } else {
                console.log('empty')
            }
        }

        opsiPrintBy.change(function () {
            printBy = $(this).val();
            if (printBy === '1') {
                $('#by-kelas').addClass('d-none');
                $('.by-ruang').removeClass('d-none');
                if (ruang && sesi && jadwal) loadSiswaRuang(ruang, sesi, jadwal)
            } else {
                $('#by-kelas').removeClass('d-none');
                $('.by-ruang').addClass('d-none');
                if (kelas && jadwal) loadSiswaKelas(kelas, jadwal)
            }
        });

        opsiJadwal.change(function () {
            getDetailJadwal($(this).val());
            opsiRuang.html("<option value='' selected='selected'>Pilih Ruang</option>");
            if ($(this).val()) {
                $.each(arrRuang, function (k, v) {
                    opsiRuang.append("<option value='"+k+"'>"+ruangs[k]+"</option>");
                })
            }
        });

        opsiKelas.change(function () {
            kelas = $(this).val();
            jadwal = opsiJadwal.val();
            loadSiswaKelas(kelas, jadwal)
        });

        opsiRuang.change(function () {
            opsiSesi.html("<option value='' selected='selected'>Pilih Sesi</option>");
            if ($(this).val()) {
                $.each(arrRuang[$(this).val()], function (k, v) {
                    opsiSesi.append("<option value='"+k+"'>"+v.nama_sesi+"</option>");
                })
            }
        });

        opsiSesi.change(function () {
            sesi = $(this).val();
            ruang = opsiRuang.val();
            jadwal = opsiJadwal.val();
            loadSiswaRuang(ruang, $(this).val(), jadwal)
        })

        var idSiswa = '';
        var idJadwal = '';
        $('#resetModal').on('show.bs.modal', function (e) {
            idSiswa = $(e.relatedTarget).data('siswa');
            idJadwal = $(e.relatedTarget).data('jadwal');

            console.log('siswa:' + idSiswa, 'jadwal:' + idJadwal);
        });

        $('#reset').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $('#resetModal').modal('hide').data('bs.modal', null);
            $('#resetModal').on('hidden', function () {
                $(this).data('modal', null);
            });

            $.ajax({
                url: base_url + "siswa/resettimer",
                type: 'POST',
                data: $(this).serialize() + '&id_durasi=' + idSiswa + '0' + idJadwal,
                success: function (data) {
                    console.log(data.status);
                    if (data.status) refreshStatus();
                    else showDangerToast('tidak bisa mereset waktu');
                }, error: function (xhr, status, error) {
                    console.log('error');
                }
            });
        });

        opsiKelas.select2({theme: 'bootstrap4'});
        opsiRuang.select2({theme: 'bootstrap4'});
        opsiSesi.select2({theme: 'bootstrap4'});
        opsiJadwal.select2({theme: 'bootstrap4'});

    });

</script>
