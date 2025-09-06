<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 17:20
 */

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
                        <button class="btn btn-sm btn-default" id="btn-reload">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-default" data-toggle="tooltip"
                                    title="Print" onclick="print()">
                                <i class="fas fa-print"></i> <span
                                        class="d-none d-sm-inline-block ml-1"> Print/PDF</span></button>
                            <button type="button" class="btn btn-sm btn-default" data-toggle="tooltip"
                                    title="Export As Word" onclick="exportWord()">
                                <i class="fa fa-file-word"></i> <span class="d-none d-sm-inline-block ml-1"> Word</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-default" data-toggle="tooltip"
                                    title="Export As Excel" onclick="exportExcel()">
                                <i class="fa fa-file-excel"></i> <span
                                        class="d-none d-sm-inline-block ml-1"> Excel</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open('', array('id' => 'formselect')) ?>
                    <?= form_close(); ?>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label>Kelas</label>
                            <?php
                            echo form_dropdown(
                                'kelas',
                                $kelas,
                                null,
                                'id="opsi-kelas" class="form-control"'
                            ); ?>
                        </div>
                        <div class='col-md-3 col-sm-6 mb-3'>
                            <label>Hari, Tanggal</label>
                            <input type='text' id="opsi-tgl" name='tanggal' class='tgl form-control'
                                   autocomplete='off' required/>
                        </div>
                    </div>
                    <hr>
                    <div id="konten-absensi">
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
            <div id="konten-copy" class="d-none"></div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/convertCss.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>

<script>
    var form;
    var hari = '';
    var tgl = '';
    var bln = '';
    var thn = '';
    var kls = '';
    var oldData = '';

    var arrhari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
    var bulans = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    var arrbulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    var docTitle = 'Kehadiran Harian';

    var styleCenterMiddle = ' style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0px;"';
    var styleLeftMiddle = ' style="border: 1px solid #c0c0c0; vertical-align: middle;margin: 0px;"';
    var styleFlexCenter = ' style="display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-pack:center;justify-content:center;height:100%;"';
    var styleKosong = ' style="border: 1px solid #c0c0c0;background-color: #eeeeee;"';

    // style excel
    var styleHead = ' data-fill-color="d3d3d3" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true"';
    var styleNormal = ' data-fill-color="ffffff" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="false"';
    var styleNama = ' data-fill-color="ffffff" data-t="s" data-a-v="middle" data-b-a-s="thin" data-f-bold="false"';
    var styleEmpty = ' data-fill-color="a6a6a6" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="false"';

    function dumpCSSText(element) {
        var s = '';
        var o = getComputedStyle(element);
        for (var i = 0; i < o.length; i++) {
            s += o[i] + ':' + o.getPropertyValue(o[i]) + ';';
        }
        return s;

        //get it
        //const css = dumpCSSText(document.getElementById('konten-absensi'));
        //console.log('test', css.html());
    }

    function print() {
        var title = document.title;
        document.title = docTitle;
        $('#konten-absensi').print(docTitle);
        document.title = title;
    }

    function exportWord() {
        var contentDocument = $('#konten-absensi').convertToHtmlFile(docTitle, '');
        var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
        //console.log('css', content);
        var converted = htmlDocx.asBlob(content, {
            orientation: 'landscape',
            size: 'A4',
            margins: {top: 700, bottom: 700, left: 1000, right: 1000}
        });

        saveAs(converted, docTitle + '.docx');
    }

    function exportExcel() {
        var table = document.querySelector("#excel");
        TableToExcel.convert(table, {
            name: docTitle + '.xlsx',
            sheet: {
                name: "Sheet 1"
            }
        });
    }

    function createTabelKehadiran(data) {
        console.log('respon', data);
        var kelas = $("#opsi-kelas option:selected").text();
        var table = '';
        if (data.info == null) {
            table += '<div class="alert alert-default-warning align-content-center" role="alert">Jadwal Pelajaran kelas ' + kelas + ' belum diatur</div>';
        } else {
            docTitle += ' Kls ' + kelas + ' ' + tgl + ' ' + bulans[parseInt(bln)] + ' ' + thn;
            var ctgl = tgl < 10 ? '0' + tgl : tgl;
            var tglMateri = buatTanggal(thn + '-' + bln + '-' + ctgl + ' 00:00:00', true);
            var totalMapel = data.info.kbm_jml_mapel_hari;
            table = '<div id="jdl" style="width:100%;">' +
                '    <p style="text-align:center;font-size:14pt; font-weight: bold">DAFTAR KEHADIRAN HARIAN SISWA</p>' +
                '</div>' +
                '<div style="display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-pack:center;justify-content:center;height:100%;">' +
                '    <table id="atas">' +
                '        <tr>' +
                '            <td colspan="2"><p style="margin: 1px; display: inline;">Kelas</p></td>' +
                '            <td><p style="margin: 1px; display: inline;">: <b>' + kelas + '</b></p></td>' +
                '        </tr>' +
                '        <tr>' +
                '            <td colspan="2"><p style="margin: 1px; display: inline;">Hari, Tanggal</p></td>' +
                '            <td><p style="margin: 1px; display: inline;">: <b>' + tglMateri + '</b></p></td>' +
                '        </tr>' +
                '        <tr>' +
                '            <td colspan="2"><p style="margin: 1px; display: inline;">Jml. Mata Pelajaran</p></td>' +
                '            <td><p style="margin: 1px; display: inline;">: <b>' + data.jadwal.length + '</b></p></td>' +
                '        </tr>' +
                '        <tr>' +
                '            <td colspan="2"><p style="margin: 1px; display: inline;">Tahun Pelajaran</p></td>' +
                '            <td><p style="margin: 1px; display: inline;">: <b><?= isset($tp_active) ? $tp_active->tahun : "Belum di set"?></b></p></td>' +
                '        </tr>' +
                '        <tr>' +
                '            <td colspan="2"><p style="margin: 1px; display: inline;">Semester</p></td>' +
                '            <td><p style="margin: 1px; display: inline;">: <b><?= isset($smt_active) ? $smt_active->nama_smt : "Belum di set" ?></b></p></td>' +
                '        </tr>' +
                '    </table>' +
                '</div><br>' +
                '<table id="tabelsiswa" class="table-responsive" style="width:100%;border-collapse: collapse; border-spacing: 0;">' +
                '<thead>' +
                '<tr style="background-color:lightgrey">' +
                '<th rowspan="3" width="40" ' + styleCenterMiddle + styleHead +'><p style="margin: 4px; display: inline;">No</p></th>' +
                '<th rowspan="3" ' + styleCenterMiddle + styleHead + '><p style="margin: 4px; display: inline;">N I S</p></th>' +
                '<th rowspan="3" ' + styleCenterMiddle + styleHead + '><p style="margin: 4px; display: inline;">Nama</p></th>' +
                '<th rowspan="3" ' + styleCenterMiddle + styleHead + '><p style="margin: 4px; display: inline;">Kelas</p></th>' +
                '<th colspan="' + (data.jadwal.length * 2) + '" ' + styleCenterMiddle + styleHead +'><p style="margin: 4px; display: inline;">Kehadiran Jam</p></th>' +
                '</tr>' +
                '<tr style="background-color:lightgrey">';

            var trJenis = '';
            var idsMapel = [];
            $.each(data.jadwal, function (k, v) {
                idsMapel.push(v.id_mapel);
                trJenis += '<th ' + styleCenterMiddle + styleHead +'><p style="margin: 4px; display: inline;">Materi</p></th>' +
                    '<th ' + styleCenterMiddle + styleHead + '><p style="margin: 4px; display: inline;">Tugas</p></th>';
                if (v.nama_mapel != null) {
                    table += '<th colspan="2" ' + styleCenterMiddle + styleHead + '><p style="margin: 4px; display: inline;">' + v.kode + '</p></th>';
                } else {
                    table += '<th colspan="2" ' + styleCenterMiddle + styleHead + '><p style="margin: 4px; display: inline;">Mapel</p></th>';
                }
            });

            table += '<tr style="background-color:lightgrey">' + trJenis + '</tr>' +
                '</tr></thead>';

            var no = 1;
            $.each(data.log, function (key, value) {
                table += '<tr>' +
                    '<td ' + styleCenterMiddle + styleNormal +'><p style="margin: 4px; display: inline;">' + no + '</p></td>' +
                    '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;">' + value.nis + '</p></td>' +
                    '<td ' + styleLeftMiddle + styleNama + '><p style="margin: 4px; display: inline;">' + value.nama + '</p></td>' +
                    '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;">' + value.kelas + '</p></td>';

                for (let i = 0; i < idsMapel.length; i++) {
                    const idMpl = idsMapel[i];
                    const materi = data.materi.find(function (mtr) {
                        return mtr.id_mapel === idMpl
                    })
                    console.log('mtr', materi)
                    if (materi) {
                        const adaStatus = value.status[idMpl] != null
                        if (adaStatus) {
                            const logMateri = value.status.filter(function (log) {
                                return log.id_mapel === materi.id_mapel && log.jenis === '1'
                            })
                            console.log('log', logMateri)
                            // MATERI
                            if (logMateri.length > 0) {
                                let minMateri = logMateri[0]?.log_time;
                                let maxMateri = logMateri[0]?.finish_time;

                                if (logMateri.length > 1) {
                                    logMateri.forEach(item => {
                                        if (item.log_time < minMateri) {
                                            minMateri = item.log_time;
                                        }
                                        if (item.finish_time > maxMateri) {
                                            maxMateri = item.finish_time;
                                        }
                                    });
                                }
                                var selesaiMateri = minMateri == null ? '' : buatTanggal(minMateri, false);
                                var durasiMateri = maxMateri != null && minMateri != null ? calculateTime(minMateri, maxMateri) : '';
                                table += '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;">' + selesaiMateri + '<br><i class="fa fa-clock-o"></i> ' + durasiMateri + '</p></td>';
                            } else {
                                table += '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;"> - - </p></td>';
                            }

                            // TUGAS
                            const logTugas = value.status.filter(function (log) {
                                return log.id_mapel === materi.id_mapel && log.jenis === '2'
                            })
                            console.log('log', logTugas)
                            if (logTugas.length > 0) {
                                let minTugas = logTugas[0]?.log_time;
                                let maxTugas = logTugas[0]?.finish_time;

                                if (logTugas.length > 1) {
                                    logTugas.forEach(item => {
                                        if (item.log_time < minTugas) {
                                            minTugas = item.log_time;
                                        }
                                        if (item.finish_time > maxTugas) {
                                            maxTugas = item.finish_time;
                                        }
                                    });
                                }
                                var selesaiTugas = minTugas == null ? '' : buatTanggal(minTugas, false);
                                var durasiTugas = maxTugas != null && minTugas != null ? calculateTime(minTugas, maxTugas) : '';
                                table += '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;">' + selesaiTugas + '<br><i class="fa fa-clock-o"></i> ' + durasiTugas + '</p></td>';
                            } else {
                                table += '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;"> - - </p></td>';
                            }
                        } else {
                            table += '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;"> - - </p></td>';
                            table += '<td ' + styleCenterMiddle + styleNormal + '><p style="margin: 4px; display: inline;"> - - </p></td>';
                        }
                    } else {
                        table += '<td ' + styleKosong + styleEmpty + '></td>';
                        table += '<td ' + styleKosong + styleEmpty + '></td>';
                    }
                }

                table += '</tr>';
                no++;
            });

            table += '</table>';
        }
        var catatan = '<p><b>Catatan:</b> Kehadiran siswa dihitung ketika siswa menyelesaikan materi/tugas serta durasi pengerjaan.</p>';
        table += catatan;

        $('#konten-absensi').html(table);
        $('#loading').addClass('d-none');

        //$("#konten-absensi").makeCssInline();

        var colWidth = '5,15,35,15';
        var title = $('#jdl').html();
        var trsAtas = $('table#atas tbody').html();
        var trsHead = $('table#tabelsiswa thead').html();
        var trsBody = $('table#tabelsiswa tbody').html();
        var copy = '<table id="excel" style="font-size: 11pt;" data-cols-width="' + colWidth + '"><tbody>' +
            '<tr>' +
            '<td colspan="10" data-a-v="middle" data-a-h="center" data-f-bold="true">' + title + '</td>' +
            '</tr>' +
            trsAtas +
            '<tr></tr>' +
            trsHead +
            trsBody +
            '<tr></tr>' +
            '<tr>' +
            '<td data-a-v="middle"">' + catatan + '</td>' +
            '</tr>' +
            '</tbody>';

        $('#konten-copy').html(copy);
    }

    $(document).ready(function () {
        var selKelas = $('#opsi-kelas');
        form = $('#formselect');

        jQuery.datetimepicker.setLocale('id');
        $('.tgl').datetimepicker({
            icons:
                {
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                },
            timepicker: false,
            format: 'D, d M Y',//'Y-m-d',
            widgetPositioning: {
                horizontal: 'left',
                vertical: 'bottom'
            },
            //disabledWeekDays: [0],
            scrollMonth: false,
            scrollInput: false,
            onChangeDateTime: function (date, $input) {
                if (date == null) return;
                tgl = date.getDate();
                var nb = date.getMonth() + 1;
                if (nb < 10) {
                    bln = '0' + nb;
                } else {
                    bln = nb;
                }
                thn = date.getFullYear();
                hari = date.getDay();
            },
        });

        selKelas.prepend("<option value='' selected='selected' disabled='disabled'>Pilih Kelas</option>");

        selKelas.change(function () {
            kls = $(this).val();
            reload(false);
        });

        $("#opsi-tgl").change(function () {
            kls = selKelas.val();
            reload(false);
        });

        $('#btn-reload').on('click', function () {
            reload(true)
        })

        function reload(force) {
            console.log(tgl, bln, thn, kls);
            var empty = tgl === '' || bln === '' || thn === '' || kls === '' || kls == null;
            var newData = '&thn=' + thn + '&bln=' + bln + '&tgl=' + tgl + '&hari=' + hari + '&kelas=' + kls;
            if (!empty && (oldData !== newData || force)) {
                oldData = newData;
                $('#loading').removeClass('d-none');

                setTimeout(function () {
                    $.ajax({
                        url: base_url + 'elearning/loadabsensi',
                        type: "POST",
                        dataType: "json",
                        data: form.serialize() + '&thn=' + thn + '&bln=' + bln + '&tgl=' + tgl + '&hari=' + hari + '&kelas=' + kls,
                        success: function (data) {
                            createTabelKehadiran(data);
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                }, 500);

            }
        }

        selKelas.select2({width: '100%', theme: 'bootstrap4'});
    });

    function buatTanggal(string, singkat) {
        //console.log("tgl", string);
        var selesai = string.replace(" ", "T");
        var d = new Date(selesai);
        var curr_day = d.getDay();
        var curr_date = d.getDate();
        var curr_month = d.getMonth();
        var curr_year = d.getFullYear();
        var curr_jam = d.getHours().toString().padStart(2, '0');
        var curr_mnt = d.getMinutes().toString().padStart(2, '0');

        //console.log("curr_month", curr_month);

        if (singkat) {
            return arrhari[curr_day] + ", " + curr_date + "  " + bulans[curr_month] + " " + curr_year;
        } else {
            return curr_date + "  " + arrbulan[curr_month] + " " + curr_year + " <br><b>" + curr_jam + ":" + curr_mnt + "</b>";
        }
    }

    function calculateTime(mulai, selesai) {
        var ONE_DAY = 1000 * 60 * 60 * 24;
        var ONE_HOUR = 1000 * 60 * 60;
        var ONE_MINUTE = 1000 * 60;

        var old_date = mulai.replace(" ", "T");//"2010-11-10T07:30:40";
        var new_date = selesai.replace(" ", "T");//"2010-11-15T08:03:22";

        // Convert both dates to milliseconds
        var old_date_obj = new Date(old_date).getTime();
        var new_date_obj = new Date(new_date).getTime();

        // Calculate the difference in milliseconds
        var difference_ms = Math.abs(new_date_obj - old_date_obj)

        // Convert back to days, hours, and minutes
        var days = Math.round(difference_ms / ONE_DAY);
        var hours = Math.round(difference_ms / ONE_HOUR) - (days * 24) - 1;
        var minutes = Math.round(difference_ms / ONE_MINUTE) - (days * 24 * 60) - (hours * 60);

        if (minutes > 60) {
            hours += 1;
            minutes -= 60;
        }
        return (days > 0 ? days + ' hari, ' : '') + (hours > 0 ? hours + ' jam, ' : '') + (minutes > 0 ? minutes + ' menit' : '');
    }

    function getStyles() {
        return '<style>\n' +
            //'*,::after,::before{box-sizing:border-box;}\n' +
            'p{margin-top:0;margin-bottom:1rem;}\n' +
            'table{border-collapse:collapse;}\n' +
            'th{text-align:inherit;}\n' +
            '.row{display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap;margin-right:-7.5px;margin-left:-7.5px;}\n' +
            '.table{width:100%;margin-bottom:1rem;color:#212529;background-color:transparent;}\n' +
            '.table td,.table th{padding:.75rem;vertical-align:top;border-top:1px solid #dee2e6;}\n' +
            '.table thead th{vertical-align:bottom;border-bottom:2px solid #dee2e6;}\n' +
            '.table-sm td,.table-sm th{padding:.3rem;}\n' +
            '.table-bordered{border:1px solid #dee2e6;border-collapse: collapse; border-spacing: 0;}\n' +
            '.table-bordered td,.table-bordered th{border:1px solid #dee2e6;border-collapse: collapse; border-spacing: 0;}\n' +
            '.table-bordered thead th{border-bottom-width:2px;}\n' +
            '.table-striped tbody tr:nth-of-type(odd){background-color:rgba(0,0,0,.05);}\n' +
            '.align-middle{vertical-align:middle;}\n' +
            '.justify-content-center{-ms-flex-pack:center;justify-content:center;}\n' +
            '.h-100{height:100%;}\n' +
            '.text-center{text-align:center;}\n' +
            //'@media all{\n' +
            //'*,::after,::before{text-shadow:none;box-shadow:none;}\n' +
            'thead{display:table-header-group;}\n' +
            'tr{page-break-inside:avoid;}\n' +
            'p{orphans:3;widows:3;}\n' +
            '.table{border-collapse:collapse;}\n' +
            '.table td,.table th{background-color:#fff;}\n' +
            '.table-bordered td,.table-bordered th{border:1px solid #dee2e6;border-collapse: collapse; border-spacing: 0;}\n' +
            '}\n' +
            '.table:not(.table-dark){color:inherit;}' +
            '</style>'
    }
</script>
