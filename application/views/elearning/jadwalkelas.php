<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 17:20
 */

$arrHari = ["7"=>"Minggu", "1"=>"Senin", "2"=>"Selasa", "3"=>"Rabu", "4"=>"Kamis", "5"=>"Jum'at", "6"=>"Sabtu"];
?>
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
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" onclick="reload()" class="btn btn-sm btn-default">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button id="edit-jadwal" class="btn btn-sm btn-primary" data-toggle="modal" disabled data-target="#createJadwalModal">
                            <i class="fas fa-cog nav-icon"></i> <span class="d-none d-sm-inline-block ml-1">Jadwal</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($all_jadwal) && $all_jadwal['convert']) : ?>
                        <div class="col-lg-12 p-0 mt-3">
                            <div class="alert alert-default-info align-content-center" role="alert">
                                <div class="row py-1 justify-content-between">
                                    <div>
                                        Perlu konversi dari jadwal KBM lama ke database baru
                                    </div>
                                    <div>
                                        <button class="btn btn-primary" id="update-aplikasi">Konversi</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="col mb-3">
                            <div id="arr-kelas"></div>
                        </div>
                        <div class="col-12 p-0 d-none" id="kelas-empty">
                            <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                Belum ada data kelas untuk Tahun Pelajaran <b><?= $tp_active->tahun ?></b>
                                Semester:
                                <b><?= $smt_active->smt ?></b>
                            </div>
                        </div>
                        <?php if (isset($setting_kbm->ada)) : ?>
                            <div class="col-lg-12 p-0 mb-3">
                                <div class="alert alert-default-warning align-content-center" role="alert">
                                    <div class="row py-1 justify-content-between">
                                        <div>
                                            Jadwal Tahun Pelajaran
                                            <strong><?= $setting_kbm->id_tp ?>
                                                Smt <?= $setting_kbm->id_smt ?></strong> belum dibuat.
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" data-toggle="modal"
                                                    data-target="#createJadwalModal">Buat Jadwal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 alert alert-default-info align-content-center" id="alert" role="alert">
                                Silakan pilih hari
                            </div>
                            <div id="table-jadwal" class="d-none"></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= form_open('setJadwal', array('id' => 'setjadwal')); ?>
<div class="modal fade" id="createJadwalModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Buat Jadwal Kelas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="inputs">
                <div class="form-group row" id="hari_libur">
                    <label for="hari_libur" class="col-md-4 col-form-label">Hari Libur *</label>
                    <div class="col-md-8">
                        <?php
                        echo form_dropdown(
                            'hari_libur',
                            $arrHari,
                            '7',
                            'class="form-control select2" required'
                        ); ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="jam_mulai" class="col-md-4 col-form-label">Jam Mulai *</label>
                    <div class="col-md-8">
                        <input id="jam_mulai" type="text" name="jam_mulai" class="form-control jam-kbm"
                               value="" autocomplete="off"
                               placeholder="Jam Mulai" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="jam_selesai" class="col-md-4 col-form-label">Jam Selesai *</label>
                    <div class="col-md-8">
                        <input id="jam_selesai" type="text" name="jam_selesai" class="form-control jam-kbm"
                               value="" autocomplete="off"
                               placeholder="Jam Selesai" required>
                    </div>
                </div>
                <div><p>Jam Mulai dibatasi jika ada jadwal pada jam pertama</p></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= form_open('setMapel', array('id' => 'setMapel')); ?>
<div class="modal fade" id="hariJadwalModal" tabindex="-1" role="dialog" aria-labelledby="hariModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hariModalLabel">Jadwal Hari</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="konten-input"></div>
                <input type="hidden" id="kelas" name="kelas" value="">
                <input type="hidden" id="hari" name="hari" value="">
                <input type="hidden" id="jadwal" name="jadwal" value="">

                <input type="hidden" id="id_kbm" name="id_kbm" value="">
                <input type="hidden" id="kbm_mulai" name="kbm_mulai" value="">
                <input type="hidden" id="kbm_selesai" name="kbm_selesai" value="">
                <input type="hidden" id="kbm_libur" name="kbm_libur" value="">
                <div>
                    <ul>
                        <li><b>JAM dibatasi</b> jika jam sebelumnya dan sesudahnya sudah terisi</li>
                        <li>Jika ingin <b>menambahkan batas JAM</b>, hapus terlebih dahulu jadwal sebelumnya atau sesudahnya</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="hapus-jadwal">Hapus</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= form_open('convert', array('id' => 'convert')); ?>
<?= form_close() ?>

<script>
    const dataConvert = JSON.parse(JSON.stringify(<?= isset($all_jadwal) ? json_encode($all_jadwal) : '{}'?>));
    let settingKbm = JSON.parse(JSON.stringify(<?= isset($setting_kbm) ? json_encode($setting_kbm) : '{}'?>));
    let kelas = JSON.parse(JSON.stringify(<?= isset($kelas) ? json_encode($kelas) : '{}'?>));
    let arrMapel = {};
    let jadwalKbm = {};
    let times = [];
    let tp, smt = {}
    let idHari = [];

    let arrDataJadwal = []
    let arrKBM = []
    let idJadwal = '';

    $(document).ready(function ($) {
        createHari();

        $('.select2').select2({
            width: '100%',
            dropdownParent: $("#createJadwalModal")
        });

        $('#setjadwal').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let formData = new FormData($('#setjadwal')[0]);
            formData.append('id_smt', smt.id_smt)
            formData.append('id_tp', tp.id_tp)

            $('#createJadwalModal').modal('hide');

            swal.fire({
                title: "Simpan Jadwal",
                text: "Pengaturan KBM akan disimpan",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Simpan"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'elearning/setJadwal',
                        type: "POST",
                        processData: false,
                        contentType: false,
                        data: formData,
                        success: function (data) {
                            if (data.status) {
                                swal.fire({
                                    title: "Sukses",
                                    text: "Jadwal Pelajaran berhasil disimpan",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    reload();
                                });
                            } else {
                                swal.fire({
                                    title: "ERROR",
                                    text: "Data Tidak Tersimpan",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            swal.fire({
                                title: "ERROR",
                                text: "Data Tidak Tersimpan",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            })
        });

        $('#setMapel').submit('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let formData = new FormData($('#setMapel')[0]);
            formData.append('smt', smt.id_smt)
            formData.append('tp', tp.id_tp)

            $('#hariJadwalModal').modal('hide');

            $.ajax({
                url: base_url + 'elearning/setMapel',
                type: "POST",
                dataType: "JSON",
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    if (data.status) {
                        swal.fire({
                            title: "Sukses",
                            text: "Jadwal Pelajaran berhasil disimpan",
                            icon: "success",
                            showCancelButton: false,
                        }).then(result => {
                            reload();
                        });
                    } else {
                        swal.fire({
                            title: "ERROR",
                            text: "Data Tidak Tersimpan",
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    swal.fire({
                        title: "ERROR",
                        text: "Data Tidak Tersimpan",
                        icon: "error",
                        showCancelButton: false,
                    });
                }
            });
        });

        $('#hapus-jadwal').on('click', function () {
            $('#hariJadwalModal').modal('hide');
            setTimeout(function () {hapusJadwalMapel(idJadwal)}, 200)
        })

        $('#update-aplikasi').on('click', function () {
            convertData();
        })
    });

    function createHari() {
        if (Object.keys(kelas).length > 0) {
            $('#arr-kelas').html('')
            const arrHari = { "7": "Minggu", "1": "Senin", "2": "Selasa", "3": "Rabu", "4": "Kamis", "5": "Jum'at", "6": "Sabtu" };
            let newArrIdHari = [];
            const keys = Object.keys(arrHari);
            const idLibur = keys.indexOf(settingKbm.libur ?? '7');
            keys.slice(idLibur).forEach(value => {
                newArrIdHari.push(value);
            });
            keys.slice(0, idLibur).forEach(value => {
                newArrIdHari.push(value);
            });
            newArrIdHari.shift();
            for (const idhari of newArrIdHari) {
                const btnStyle = idHari[1] != undefined && idHari[1] === idhari ? 'btn-success' : 'btn-outline-success'
                $('#arr-kelas').append(`<button class="m-1 btn btn-kelas ${btnStyle}" data-id="btn-${idhari}">${arrHari[idhari]}</button>`)
            }

            $('.btn-kelas').on('click', function (e) {
                idHari = $(this).data('id').split('-')
                $('.btn-kelas').removeClass('btn-success')
                $('.btn-kelas').addClass('btn-outline-success')

                $(this).removeClass('btn-outline-success')
                $(this).addClass('btn-success')

                reload();
            })
        } else {
            $('#kelas-empty').removeClass('d-none')
        }
    }

    function hapusJadwalMapel(idJadwal) {
        swal.fire({
            title: "Hapus Mapel",
            text: "Mata Pelajaran akan dihapus",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f6052a",
            confirmButtonText: "Hapus"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                setTimeout(function () {
                    $.ajax({
                        url: base_url + 'elearning/delMapel/'+idJadwal,
                        success: function (data) {
                            if (data.status) {
                                swal.fire({
                                    title: "Sukses",
                                    text: "Jadwal Pelajaran berhasil disimpan",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    reload();
                                });
                            } else {
                                swal.fire({
                                    title: "ERROR",
                                    text: "Data Tidak Tersimpan",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            swal.fire({
                                title: "ERROR",
                                text: "Data Tidak Tersimpan",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }, 1000)
            }
        })
    }

    function createTable() {
        const tableContainer = document.createElement('div');
        tableContainer.className = 'table-responsive mb-3';

        const arrKelas = Object.keys(kelas)
        let arrStart = []
        if (arrKelas.length > 0) {
            const table = document.createElement('table');
            table.className = 'my-timetable w-100 border-bottom';
            table.id = 'tbl';

            // Membuat thead
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            headerRow.className = 'alert-primary';

            const headerTh1 = document.createElement('th');
            headerTh1.colSpan = 2;
            headerTh1.style.width = '80px';
            headerTh1.style.minWidth = '80px';
            headerTh1.style.maxWidth = '80px';
            headerTh1.className = 'text-center frozen-header text-white py-2';
            headerTh1.textContent = 'JAM';
            headerRow.appendChild(headerTh1);

            // Menambahkan kolom header untuk setiap kelas
            for (const kelasKey in kelas) {
                const value = kelas[kelasKey]
                const th = document.createElement('th');
                th.className = 'align-middle text-center';
                th.style.minWidth = '100px';
                th.textContent = value;
                headerRow.appendChild(th);
            }

            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Membuat tbody
            const tbody = document.createElement('tbody');
            let idRow = 0;
            const jadwalRows = [];

            times.forEach(jam => {
                const startTime = jam.start || '';
                arrStart.push(startTime)
                const rowSpanJam = jam.span || 12;

                if (idRow === 0) {
                    const firstRow = document.createElement('tr');
                    firstRow.className = `row${idRow}`;

                    const td0 = document.createElement('td');
                    td0.className = 'column-time text-center align-top text-xs text-bold times p-0 frozen-column';
                    td0.rowSpan = rowSpanJam;
                    td0.textContent = startTime;
                    firstRow.appendChild(td0);

                    const td1 = document.createElement('td');
                    td1.className = 'frozen-column-2';
                    td1.style.width = '30px';
                    td1.style.height = '10px';
                    firstRow.appendChild(td1);

                    for (let i = 2; i <= arrKelas.length + 1; i++) {
                        const td = document.createElement('td');
                        td.className = `row0`;
                        firstRow.appendChild(td);
                    }

                    tbody.appendChild(firstRow);
                } else {
                    const minute = startTime.split(':')[1];
                    const row = document.createElement('tr');
                    row.className = `row${idRow}`;
                    if (idRow % 2 !== 0) row.setAttribute('data-time', startTime);

                    if (minute % 30 === 0) {
                        const td0 = document.createElement('td');
                        td0.className = 'column-time text-center align-top text-xs text-bold times p-0 frozen-column';
                        td0.rowSpan = rowSpanJam;
                        td0.textContent = startTime;
                        row.appendChild(td0);
                    }

                    const td1 = document.createElement('td');
                    td1.className = 'frozen-column-2';
                    td1.style.width = '30px';
                    td1.style.height = '6px';
                    row.appendChild(td1);

                    tbody.appendChild(row);
                }

                // Tambahkan data jadwal di kolom
                const scheduleRow = document.createElement('tr');
                scheduleRow.className = `row${idRow + 1}`;
                if (idRow % 2 !== 0) scheduleRow.setAttribute('data-time', startTime);

                const td1 = document.createElement('td');
                td1.className = 'frozen-column-3';
                td1.style.width = '30px';
                td1.style.height = '6px';
                scheduleRow.appendChild(td1);

                for (const kelasKey in kelas) {
                    const kls = kelas[kelasKey]
                    const idk = kelasKey;
                    if (!jadwalRows[idk] || jadwalRows[idk] === 0) {
                        const kbm = jadwalKbm[idk] || {};
                        const arr = jadwalKbm[idk]?.detail?.[startTime] || null;
                        const rowSpan = arr ? arr.rows * 2 : 2;
                        jadwalRows[idk] = rowSpan;

                        const td = document.createElement('td');
                        td.className = `align-middle row${idRow + 1} column${idk} text-xs border-right`;
                        td.rowSpan = rowSpan;
                        td.setAttribute('data-dari', startTime)
                        td.setAttribute('data-pos', idRow)
                        td.setAttribute('data-kelas', idk)

                        const div = document.createElement('div');
                        div.className = 'h-100 pb-1 add-mapel';
                        div.style.padding = '0 .1rem';
                        div.setAttribute('data-pos', idRow)
                        div.setAttribute('data-mapel', '0')
                        div.setAttribute('data-kelas', idk)
                        div.setAttribute('data-dari', startTime)
                        div.setAttribute('data-hari', idHari[1])
                        div.setAttribute('data-idkbm', kbm.id_kbm || '')
                        div.setAttribute('data-kbmmulai', kbm.kbm_jam_mulai || settingKbm.mulai)
                        div.setAttribute('data-kbmselesai', kbm.kbm_jam_selesai || settingKbm.selesai)
                        div.setAttribute('data-kbmlibur', kbm.libur || settingKbm.libur)

                        if (arr) {
                            div.setAttribute('data-dari', arr.dari)
                            div.setAttribute('data-sampai', arr.sampai)
                            div.setAttribute('data-kelas', arr.id_kelas)
                            div.setAttribute('data-mapel', arr.id_mapel)
                            div.setAttribute('data-hari', arr.id_hari)
                            div.setAttribute('data-id', arr.id_jadwal)
                            const alertDiv = document.createElement('div');
                            const flex = rowSpan > 6 ? 'flex-column justify-content-center' : 'flex-row justify-content-between'
                            alertDiv.className = `alert alert-default-${arr.id_mapel === '-1' ? 'warning' : (arr.kode ? 'info' : 'secondary')} p-1 h-100 m-0 d-flex ${flex}`;

                            if (arr.id_mapel === '-1') {
                                alertDiv.innerHTML = `<div class="text-xs ml-1">Istirahat</div><div class="text-xs mx-1">${arr.rows * 5}m</div>`;
                            } else {
                                if (arr.kode) {
                                    alertDiv.innerHTML = `<div class="text-bold ml-1">${arr.kode}</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                                }
                            }
                            div.appendChild(alertDiv);
                        }

                        td.appendChild(div);
                        scheduleRow.appendChild(td);
                    }
                }

                tbody.appendChild(scheduleRow);

                // Update jumlah baris
                for (const idks in kelas) {
                    if (jadwalRows[idks]) jadwalRows[idks] -= 2;
                }

                idRow += 2;
            });

            table.appendChild(tbody);
            tableContainer.appendChild(table);
        } else {
            tableContainer.append(`<div class="alert alert-default-warning shadow align-content-center" role="alert">
                    Belum ada data kelas untuk Tahun Pelajaran <b>${tp.tahun}</b> Semester:<b>${smt.smt}</b>
                 </div>`);
        }

        const element = document.getElementById("table-jadwal");
        element.classList.remove("d-none");

        let oldH3Element =  document.querySelector('div.table-responsive')
        if(oldH3Element) oldH3Element.parentNode.removeChild(oldH3Element)
        element.appendChild(tableContainer);
        // Append the table container to the body or any desired container

        $('#loading').addClass('d-none')
        $('#edit-jadwal').removeAttr('disabled')
        $('#alert').addClass('d-none')

        // jadwal global
        $('#createJadwalModal').on('show.bs.modal', function (e) {
            if (settingKbm.libur === '0') settingKbm.libur = '7'
            $("#hari_libur select").val(settingKbm.libur).change();
            $("#jam_mulai").val(settingKbm.mulai);
            $("#jam_selesai").val(settingKbm.selesai);
            $('#jam_mulai').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 15,
                minTime: '05:00',
                maxTime: settingKbm.mulai
            });
            $('#jam_selesai').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 15,
                minTime: '07:00',
                maxTime: '18:00'
            });
        })

        // jadwal perkelas

        document.addEventListener('click', function (event) {
            if (event.target.closest('.add-mapel')) {
                const div = event.target.closest('.add-mapel');

                const kelasId = div.getAttribute('data-kelas');
                const dari = div.getAttribute('data-dari');
                const hari = div.getAttribute('data-hari');
                const sampai = div.getAttribute('data-sampai');
                const mpl = div.getAttribute('data-mapel');
                idJadwal = div.getAttribute('data-id');
                const divPos = parseInt(div.getAttribute('data-pos'), 10);
                const kbmId = div.getAttribute('data-idkbm')
                const kbmMulai = div.getAttribute('data-kbmmulai')
                const kbmSelesai = div.getAttribute('data-kbmselesai')
                const kbmLibur = div.getAttribute('data-kbmlibur')

                const tds = document.querySelectorAll(`td.column${kelasId}`);

                let storedTimes = [];
                let lastTime = null;
                const isStopped = Array.from(tds).some(td => {
                    const tdKelas = td.getAttribute('data-kelas');
                    if (tdKelas === kelasId) {
                        const tdPos = parseInt(td.getAttribute('data-pos'), 10);
                        if (tdPos <= divPos) {
                            const dataMulai = td.getAttribute('data-dari');
                            if (dataMulai) {
                                if (td.getAttribute('rowspan') === "2") {
                                    storedTimes.push(dataMulai)
                                } else {
                                    if (dataMulai !== dari) storedTimes = []
                                }
                            }
                        } else  { //if (tdPos > divPos) {
                            const dataMulai = td.getAttribute('data-dari');
                            if (dataMulai) {
                                const [jam, menit] = dataMulai.split(':').map(Number);
                                if (lastTime) {
                                    const [lastJam, lastMenit] = lastTime.split(':').map(Number);
                                    const timeDifference = (jam * 60 + menit) - (lastJam * 60 + lastMenit);
                                    if (timeDifference === 5) {
                                        storedTimes.push(dataMulai);
                                    } else if (timeDifference > 5) {
                                        return true;
                                    }
                                } else {
                                    storedTimes.push(dataMulai);
                                }
                                lastTime = dataMulai;
                            }
                        }
                        return false;
                    }
                });

                const filterNumbers = (min, max) => {
                    return function (a) { return a >= min && a <= max; };
                }
                let startEnds = arrStart.filter(filterNumbers(dari, sampai))
                for (const startEnd of startEnds) {
                    if (!storedTimes.includes(startEnd)) storedTimes.push(startEnd)
                }
                storedTimes.sort(function (a, b) {
                    return a.localeCompare(b);
                });

                const kontenInput = $('#konten-input')
                kontenInput.html('')
                let input = `<div class="card bg-light">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2" id="select-mapel">
                                Mapel:
                                <select name="mapel" class="form-control form-control-sm select2" data-placeholder="Pilih Mapel" required>
                                    <option value="-1">Istirahat</option>`;
                for (const id in arrMapel) {
                    input += id === '' ? `<option value="0">${arrMapel[id]}</option>` : `<option value="${id}">${arrMapel[id]}</option>`;
                }
                input += `</select>
                            </div>
                            <div class="col-6 col-md-3 mb-2">Dari:
                                <input type="text" name="dari" value="${dari}" class="form-control form-control-sm jam">
                            </div>
                            <div class="col-6 col-md-3 mb-2">Sampai:
                                <input type="text" name="sampai" value="${sampai||dari}" class="form-control form-control-sm jam">
                            </div>
                        </div>
                    </div>
                </div>`;
                kontenInput.prepend(input)

                $('#kelas').val(kelasId)
                $('#hari').val(hari)
                $('#jadwal').val(idJadwal)
                if (idJadwal) {
                    $('#hapus-jadwal').removeAttr('disabled')
                } else {
                    $('#hapus-jadwal').attr('disabled', 'disabled')
                }

                $('#id_kbm').val(kbmId)
                $('#kbm_mulai').val(kbmMulai)
                $('#kbm_selesai').val(kbmSelesai)
                $('#kbm_libur').val(kbmLibur)

                $('.select2').select2({
                    width: '100%',
                    dropdownParent: $("#hariJadwalModal")
                });
                $('#select-mapel select').val(mpl).change()
                $('.jam').datetimepicker({
                    datepicker: false,
                    format: 'H:i',
                    step: 5,
                    allowTimes: storedTimes,
                    minTime: '06:00',
                    maxTime: '17:00'
                });

                $('#hariJadwalModal').modal('show');
                isShow = true;
            }
        });
    }

    function convertData() {
        arrDataJadwal = [];
        arrKBM = [];
        if (dataConvert.convert) {
            if (Object.keys(dataConvert.jadwal).length > 0) {
                dataConvert.tps.forEach(tp => {
                    dataConvert.smts.forEach(smt => {
                        let kelass = dataConvert.kelas[tp.id_tp]?.[smt.id_smt] || [];
                        let jadk = dataConvert.jadwal[tp.id_tp]?.[smt.id_smt] || [];
                        let jadwal_mapel = dataConvert.mapel[tp.id_tp]?.[smt.id_smt] || [];
                        if (Object.keys(jadk).length > 0) {
                            const forResult = createNewJadwal(tp.id_tp, smt.id_smt, jadk, jadwal_mapel, kelass)
                            const flattenedData = flattenJSON(forResult.mapel);
                            arrDataJadwal = arrDataJadwal.concat(flattenedData)
                            for (const kbmKey in forResult.kbm) {
                                arrKBM.push(forResult.kbm[kbmKey])
                            }
                        }
                    });
                });
            }

            if (arrDataJadwal.length > 0) {
                swal.fire({
                    title: "Konversi Jadwal",
                    text: "Jadwal Pelajaran akan dikonversi untuk menyesuaikan tabel",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Konversi"
                }).then(result => {
                    if (result.value) {
                        partData(0)
                    }
                })
            } else {
                updateKBM()
            }
        }
    }

    function createNewJadwal(tp, smt, jadk, jadwal_mapels, all_kelas) {
        const jadwalDetail = [];
        for (let idKelas in jadk) {
            let kelas = jadk[idKelas];
            let istirahatData = kelas.istirahat;
            let istirahatDurasi = {};
            let istirahatJamKe = [];

            istirahatData.forEach(istirahat => {
                istirahatJamKe.push(istirahat.ist);
                istirahatDurasi[istirahat.ist] = istirahat.dur;
            });

            let jamMulai = new Date(`2000-01-01T${kelas.kbm_jam_mulai}:00Z`);
            let jamSampai = new Date(jamMulai.getTime());
            let jadwalMapel = {};
            let jadwalIstirahat = {};
            let kelasSampai = {};

            for (let i = 0; i <= 50; i++) {
                let jamKe = (i + 1).toString();
                if (istirahatJamKe.includes(jamKe)) {
                    try {
                        jamSampai.setMinutes(jamSampai.getMinutes() + Number(istirahatDurasi[jamKe]));
                        let formattedMulai = jamMulai.toISOString().substring(11, 16);

                        jadwalIstirahat[jamKe] = jamKe;
                        jadwalMapel[jamKe] = {
                            id_tp: tp,
                            id_smt: smt,
                            dari: formattedMulai,
                            sampai: jamSampai.toISOString().substring(11, 16),
                            id_mapel: '-1',
                            jam_ke: jamKe
                        };
                        kelasSampai[jamKe] = jamSampai.toISOString().substring(11, 16);
                        jamMulai.setMinutes(jamMulai.getMinutes() + Number(istirahatDurasi[jamKe]));
                    } catch (e) {}
                } else {
                    try {
                        jamSampai.setMinutes(jamSampai.getMinutes() + Number(kelas.kbm_jam_pel));
                        let formattedMulai = jamMulai.toISOString().substring(11, 16);

                        jadwalMapel[jamKe] = {
                            dari: formattedMulai,
                            sampai: jamSampai.toISOString().substring(11, 16)
                        };
                        kelasSampai[jamKe] = jamSampai.toISOString().substring(11, 16);
                        jamMulai.setMinutes(jamMulai.getMinutes() + Number(kelas.kbm_jam_pel));
                    } catch (e) {}
                }
            }
            let details = {};
            for (const idHari in jadwal_mapels) {
                const jadwal_mapel = jadwal_mapels[idHari]
                const kelasKeys = Object.keys(all_kelas);
                let jadwalKelas = {}
                for (const key of kelasKeys) {
                    jadwalKelas[key] = {}
                }

                for (const mapelKey in jadwal_mapel) {
                    const mapel = jadwal_mapel[mapelKey]
                    mapel.forEach(jadwal => {
                        if (jadwal.id_kelas === idKelas) {
                            if (jadwalMapel[jadwal.jam_ke]) {
                                let formattedMulai = jadwalMapel[jadwal.jam_ke]?.dari;
                                details[formattedMulai] = {
                                    id_jadwal: jadwal.id_jadwal,
                                    id_tp: tp,
                                    id_smt: smt,
                                    dari: jadwalMapel[jadwal.jam_ke]?.dari || '',
                                    sampai: jadwalMapel[jadwal.jam_ke]?.sampai || '',
                                    id_kelas: jadwal.id_kelas,
                                    id_hari: jadwal.id_hari,
                                    jam_ke: jadwal.jam_ke,
                                    id_mapel: jadwal.id_mapel
                                };
                            }
                        }
                    });
                }

                Object.keys(details).forEach(det => {
                    Object.values(jadwalIstirahat).forEach(ist => {
                        if (jadwalMapel[ist]) {
                            jadwalMapel[ist].id_hari = details[det].id_hari;
                            jadwalMapel[ist].id_kelas = details[det].id_kelas;
                            details[jadwalMapel[ist].dari] = jadwalMapel[ist];
                        }
                    });
                });

                jadwalKelas[idKelas] = JSON.parse(JSON.stringify(details))
                jadwalDetail.push(jadwalKelas)
            }
            kelas.kbm_jam_selesai = kelasSampai[(Number(kelas.kbm_jml_mapel_hari) + 2).toString()]
        }

        return {mapel: jadwalDetail, kbm: jadk};
    }

    function flattenJSON(data) {
        const result = [];

        data.forEach(dayData => {
            Object.keys(dayData).forEach(classId => {
                const classData = dayData[classId];
                Object.keys(classData).forEach(time => {
                    const timeData = classData[time];
                    result.push({
                        id_kelas: classId,
                        dari: time,
                        ...timeData
                    });
                });
            });
        });

        return result;
    }

    function partData(num) {
        const filter = arrDataJadwal.slice(num, num+100)
        prepareJadwal(num, filter)
    }

    function prepareJadwal(num, data) {
        const kalkulasi = Math.round(100 * (num / arrDataJadwal.length))
        const percent = kalkulasi === 0 ? '' : kalkulasi+'%'

        let formData = new FormData($('#convert')[0]);
        for (const detail of data) {
            formData.append('jadwal['+detail.id_jadwal+'_'+detail.id_tp+'_'+detail.id_smt+'_'+detail.id_kelas+'_'+detail.id_hari+'_'+detail.jam_ke+'][dari]', detail.dari)
            formData.append('jadwal['+detail.id_jadwal+'_'+detail.id_tp+'_'+detail.id_smt+'_'+detail.id_kelas+'_'+detail.id_hari+'_'+detail.jam_ke+'][id_mapel]', detail.id_mapel)
            formData.append('jadwal['+detail.id_jadwal+'_'+detail.id_tp+'_'+detail.id_smt+'_'+detail.id_kelas+'_'+detail.id_hari+'_'+detail.jam_ke+'][sampai]', detail.sampai)
        }
        postNow(formData, percent, num)
    }

    function postNow(formData, percent, num) {
        swal.fire({
            html: "Silahkan tunggu....<p class='text-xl'>"+percent+"</p>",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });

        setTimeout(function () {
            $.ajax({
                url: base_url + "elearning/convertMapel",
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    if (data.status) {
                        if (data.jadwal.length === 100) {
                            partData(num + 100)
                        } else {
                            updateKBM()
                        }
                    } else {
                        swal.fire({
                            "allowEscapeKey": false,
                            "allowOutsideClick": false,
                            "title": data.status ? "Berhasil" : "Gagal",
                            "text": data.status ? "Jadwal berhasil dibuat" : "Jadwal tidak dibuat",
                            "icon": data.status ? "success" : "error"
                        }).then(result => {
                            window.location.href = base_url + 'elearning';
                        });
                    }
                },
                error: function (xhr, status, error) {
                    showDangerToast(xhr.responseText);
                }
            });
        }, 1000)
    }

    function updateKBM() {
        let formData = new FormData($('#convert')[0]);
        for (const durasi of arrKBM) {
            formData.append('kbm['+durasi.id_kbm+'_'+durasi.id_tp+'_'+durasi.id_smt+'_'+durasi.id_kelas+'][selesai]', durasi.kbm_jam_selesai)
        }
        swal.fire({
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });
        setTimeout(function () {
            $.ajax({
                url: base_url + "elearning/convertJadwal",
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    swal.fire({
                        "title": data.status ? "Berhasil" : "Gagal",
                        "text": data.status ? "Jadwal berhasil dibuat" : "Jadwal tidak dibuat",
                        "icon": data.status ? "success" : "error"
                    }).then(result => {
                        if (data.status) {
                            window.location.href = base_url + 'elearning/jadwal';
                        }
                    });
                },
                error: function (xhr, status, error) {
                    showDangerToast(xhr.responseText);
                }
            });
        }, 1000)
    }

    function getMaxTime(data) {
        let maxTime = "00:00";

        Object.values(data).forEach(entry => {
            if (entry.sampai > maxTime) {
                maxTime = entry.sampai;
            }
        });

        return maxTime;
    }

    function reload() {
        if (idHari.length === 0) return
        $('#loading').removeClass('d-none')
        setTimeout(function () {
            $.ajax({
                type: "GET",
                url: base_url + "elearning/hari/" + idHari[1],
                success: function (response) {
                    arrMapel = response.mapels;
                    settingKbm = response.setting_kbm;
                    jadwalKbm = response.jadwal_kbm;
                    times = response.times;
                    kelas = response.kelas;
                    tp = response.tp_active;
                    smt = response.smt_active;
                    if (times.length > 0) {
                        createHari()
                        createTable();
                    }
                },
                error: function (xhr, status, error) {
                    $('#loading').addClass('d-none')
                    console.log("error", xhr.responseText);
                }
            });
        }, 300)
    }
</script>
