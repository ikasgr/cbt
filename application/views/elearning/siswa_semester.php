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
                    <?php
                    //$day = date('N', strtotime(date('Y-m-d')));
                    //echo '<pre>';
                    //echo json_encode($num_day, JSON_PRETTY_PRINT);
                    //var_dump($day);
                    //var_dump($num_day);
                    //echo '<br>';
                    //echo json_encode($kbm, JSON_PRETTY_PRINT);
                    //var_dump($kbm);
                    //echo '</pre>';
                    ?>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <?php
                            echo form_dropdown(
                                'mapel',
                                $mapel,
                                null,
                                'id="opsi-mapel" class="form-control"'
                            ); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <?php
                            echo form_dropdown(
                                'kelas',
                                $kelas,
                                null,
                                'id="opsi-kelas" class="form-control"'
                            ); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <select name="tahun" id="opsi-tahun" class="form-control">
                                <option value="" selected="selected" disabled="disabled">Pilih Tahun
                                </option>
                                <?php foreach ($tp as $tahun) : ?>
                                    <option value="<?= $tahun->id_tp ?>"><?= $tahun->tahun ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <select name="smt" id="opsi-semester" class="form-control">
                                <option value="" selected="selected" disabled="disabled">Pilih Smt</option>
                                <?php foreach ($smt as $sm) : ?>
                                    <option value="<?= $sm->id_smt ?>"><?= $sm->smt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-3 col-sm-6 mb-2 d-none" id="input-bulan">
                            <?php
                            echo form_dropdown(
                                'bulan',
                                $bulans,
                                '00',
                                'id="opsi-bulan"class="form-control select2"'
                            ); ?>
                        </div>
                    </div>
                    <hr>
                    <div id="konten-absensi" class="table-responsive"></div>
                    <hr>
                    <div id="konten-copy" class="d-none"></div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>

<script>
    var docTitle = '';
    const namaBulan = ["", "Januar1", "Februar1", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    var styleHead = 'data-fill-color="ffffff" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" data-a-wrap="true"';
    var styleNormal = 'data-fill-color="ffffff" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="false"';
    var styleEmpty = 'data-fill-color="D3D3D3" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="false"';
    var styleNama = 'data-fill-color="ffffff" data-t="s" data-a-v="middle" data-b-a-s="thin" data-f-bold="false"';
    var styleRata = 'data-fill-color="d1ecf1" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true"';
    var styleNonaktif = 'data-fill-color="FEFEC5" data-t="s" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="false"';

    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var catatan = '';
    var weekday = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
    let lastUrl;

    function createTable(data) {
        docTitle = '';
        var selmapel = $('#opsi-mapel option:selected').text();
        var selkelas = $('#opsi-kelas option:selected').text();
        var thnSel = $("#opsi-tahun option:selected").text();
        var selsmt = $('#opsi-semester option:selected').text();

        var smt = $('#opsi-semester').val();
        var thnSplit = thnSel.split('/');
        var sthn = smt === '1' ? thnSplit[0] : thnSplit[1];

        docTitle += 'Rekap Nilai ' + selmapel + ' ' + selkelas + ' ' + sthn + ' ' + selsmt;

        if (Array.isArray(data.mapels) && data.mapels.length === 0) {
            $('#konten-absensi').html('<p>Tidak Jadwal untuk mapel ' + selmapel + ' kelas ' + selkelas + '</p>');
            $('#loading').addClass('d-none');
            return;
        }

        var numCol = 0;
        $.each(data.bulans, function (k, v) {
            numCol += Object.keys(data.materi[v]).length;
        });

        var konten = '<div style="width:100%;" id="jdl"><p style="text-align:center;font-size:14pt; font-weight: bold">REKAPITULASI NILAI SISWA</p></div>' +
            '<div style="display:-ms-flexbox;display:flex;-ms-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-pack:center;justify-content:center;height:100%;">' +
            '    <table id="atas">' +
            '        <tr>' +
            '            <td colspan="2"><p style="margin: 1px; display: inline;">Mata Pelajaran</p></td>' +
            '            <td><p style="margin: 1px; display: inline;">: <b>' + selmapel + '</b></p></td>' +
            '        </tr>' +
            '        <tr>' +
            '            <td colspan="2"><p style="margin: 1px; display: inline;">Kelas</p></td>' +
            '            <td><p style="margin: 1px; display: inline;">: <b>' + selkelas + '</b></p></td>' +
            '        </tr>' +
            '        <tr>' +
            '            <td colspan="2"><p style="margin: 1px; display: inline;">Tahun/Semester</p></td>' +
            '            <td><p style="margin: 1px; display: inline;">: <b>' + thnSel + ' (' + selsmt + ')</b></p></td>' +
            '        </tr>' +
            '    </table>' +
            '</div><br>' +
            '<table id="log-nilai" class="table table-sm" style="width:100%;border:1px solid #c0c0c0;border-collapse: collapse; border-spacing: 0; font-size: 10pt">' +
            '<thead>' +
            '<tr>' +
            '<th rowspan="3" style="min-width: 40px; border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleHead + '>No.</th>' +
            '<th rowspan="3" style="min-width: 100px; border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleHead + '>NIS</th>' +
            '<th rowspan="3" style="min-width: 200px; border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleHead + '>Nama</th>';


        $.each(data.bulans, function (k, v) {
            var ind = parseInt(v);
            var lon = Object.keys(data.materi[v]).length;
            konten += '<th colspan="' + ((lon*2)+2) + '"  style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleHead + '>' + namaBulan[ind] + '</th>';
        });
        konten += '<th rowspan="2" colspan="2" style="min-width: 100px; border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleHead + '>Nilai Rata-rata</th>' +
            '</tr><tr data-height="35">';

        var colWidth = '4,20,35';
        let totalHari = 0;
        let totalBln = 0;
        const batasBln = [];
        $.each(data.bulans, function (i, bln) {
            const sortedMateri = Object.keys(data.materi[bln]).sort((a, b) => a - b);
            if (i===0) batasBln.push(0)
            $.each(sortedMateri, function (key, tgl) {
                batasBln[i] ++;
                var d2 = new Date(data.tahun  + '-' + bln + '-' + tgl);
                var hari = weekday[d2.getDay()];
                konten += '<th colspan="2" class="tanggal" style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleHead + '>' + hari + '<br/>'+tgl+'</th>';
                totalHari++;
                colWidth += ',4,4';
            });
            batasBln.push(batasBln[i]+1)
            konten += '<th colspan="2" class="tanggal" style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px;background: #d1ecf1;" ' + styleRata + '>NR</th>';
            totalBln++;
            colWidth += ',4';
        });

        konten += '</tr><tr>';
        for (let i = 0; i < totalHari+totalBln; i++) {
            let bg = ''
            let styleExcel = styleHead
            if (batasBln.includes(i)) {
                bg = 'background: #d1ecf1;'
                styleExcel = styleRata
            }
            konten += '<th class="tanggal" style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px;'+bg+'" ' + styleExcel + '>P</th>' +
                '<th class="tanggal" style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px;'+bg+'" ' + styleExcel + '>K</th>';
        }
        konten += '<th class="tanggal" style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px;" ' + styleHead + '>P</th>' +
            '<th class="tanggal" style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px;" ' + styleHead + '>K</th>';
        colWidth += ',4,4,4,4,4,4,8,8';
        konten += '</tr></thead><tbody>';

        var no = 1;
        $.each(data.log, function (key, value) {
            konten += '<tr>' +
                '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleNormal + '>' + no + '</td>' +
                '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px" ' + styleNormal + '>' + value.nis + '</td>' +
                '<td class="nama-siswa" style="border: 1px solid #c0c0c0; vertical-align: middle; margin: 0; padding: 2px" ' + styleNama + '>' + value.nama + '</td>';
            var totalMtr = 0;
            var totalNilaiMtr = 0;
            var totalTgs = 0;
            var totalNilaiTgs = 0;
            $.each(data.bulans, function (i, nbln) {
                var tgls = Object.keys(data.materi[nbln]);
                tgls.sort(function (a, b) {
                    return (a < b) ? -1 : 1;
                });
                var jmlMtrBulan = 0;
                var jmlTgsBulan = 0;
                var jmlNilaiMtrBulan = 0;
                var jmlNilaiTgsBulan = 0;

                var jadwalPerBulan = data.mapels[nbln];
                $.each(tgls, function (index, tgl) {
                    var a = new Date(data.tahun  + '-' + nbln + '-' + tgl);
                    var d = a.getDay();
                    if (a <= today) {
                        var jadwalPerHari = jadwalPerBulan[d] || null;
                        var bgm = 'lightgrey';
                        var stylem = styleEmpty;
                        var nilaiMateri = 0;
                        var jmlJamMtr = 0;

                        var bgt = 'lightgrey';
                        var stylet = styleEmpty;
                        var nilaiTugas = 0;
                        var jmlJamTgs = 0;

                        if (jadwalPerHari) {
                            const adaMateriTugas = data.materi[nbln] != null && data.materi[nbln][tgl] != null;
                            if (adaMateriTugas) {
                                const adaMateri = data.materi[nbln][tgl]['1'] || null;
                                bgm = adaMateri ? 'white' : 'lightgrey';
                                stylem = adaMateri ? styleNormal : styleEmpty;
                                if (adaMateri) {
                                    const adaLogMateri = value.nilai != null && value.nilai['1'] != null;
                                    if (adaLogMateri) {
                                        $.each(value.nilai['1'], function (idxLog, log) {
                                            nilaiMateri += parseInt(log.nilai);
                                            jmlJamMtr ++;
                                        })
                                    }
                                }

                                const adaTugas = data.materi[nbln][tgl]['2'] || null;
                                bgt = adaMateri ? 'white' : 'lightgrey';
                                stylet = adaMateri ? styleNormal : styleEmpty;
                                if (adaTugas) {
                                    const adaLogTugas = value.nilai != null && value.nilai['2'] != null;
                                    if (adaLogTugas) {
                                        $.each(value.nilai['2'], function (idxLog, log) {
                                            nilaiTugas += parseInt(log.nilai);
                                            jmlJamTgs ++;
                                        })
                                    }
                                }

                            }
                        }
                        var nmtr = nilaiMateri === 0 ? '&ensp;' : '' + Math.round(nilaiMateri / jmlJamMtr);
                        konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0; padding: 2px;background: ' + bgm + '" ' + stylem + '>' + nmtr + '</td>';
                        jmlMtrBulan += jmlJamMtr;
                        jmlNilaiMtrBulan += nilaiMateri

                        var ntgs = nilaiTugas === 0 ? '&ensp;' : '' + Math.round(nilaiTugas / jmlJamTgs);
                        konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;background: ' + bgt + '" ' + stylet + '>'+ntgs+'</td>';
                        jmlTgsBulan += jmlTgsBulan
                        jmlNilaiTgsBulan += nilaiTugas
                    } else {
                        konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;background: #FEFEC5" ' + styleNonaktif + '>&ensp;</td>';
                        konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;background: #FEFEC5" ' + styleNonaktif + '>&ensp;</td>';
                    }
                });

                // nr materi
                totalMtr += jmlMtrBulan;
                totalNilaiMtr += jmlNilaiMtrBulan;
                var rtbm = jmlMtrBulan === 0 && jmlNilaiMtrBulan === 0 ? '0' : '' + Math.round(jmlNilaiMtrBulan / jmlMtrBulan);
                konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;background: #d1ecf1;" ' + styleRata + '>' + rtbm + '</td>';

                // nr tugas
                totalTgs += jmlTgsBulan;
                totalNilaiTgs += jmlNilaiTgsBulan;
                var rtbt = jmlTgsBulan === 0 && jmlNilaiTgsBulan === 0 ? '0' : '' + Math.round(jmlNilaiTgsBulan / jmlTgsBulan);
                konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;background: #d1ecf1;" ' + styleRata + '>' + rtbt + '</td>';
            });
            var rtsm = totalMtr === 0 && totalNilaiMtr === 0 ? '0' : '' + Math.round(totalNilaiMtr / totalMtr);
            konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;" ' + styleHead + '>' + rtsm + '</td>';

            var rtst = totalTgs === 0 && totalNilaiTgs === 0 ? '0' : '' + Math.round(totalNilaiTgs / totalTgs);
            konten += '<td style="border: 1px solid #c0c0c0; text-align: center; vertical-align: middle;margin: 0;padding: 2px;" ' + styleHead + '>' + rtst + '</td>' +
                '</tr>';
            no += 1;
        });
        catatan = '<span><b>Catatan:</b></span><ul>' +
            '<li> Jumlah penilaian dihitung dari jumlah hari tiap mapel dalam 1 bulan. </li>' +
            '<li> Nilai harian dihitung rata-rata dari jumlah jam perhari</li>' +
            '</ul>';
        konten += '</tbody></table>' + catatan;
        $('#konten-absensi').html(konten);

        $.each($('table#log-nilai').find('th'), function () {
            if ($(this).hasClass("tanggal")) {
                $(this).html('<p style=" font-size: 8pt; margin: 1px 2px; display: block; text-align: center; vertical-align: middle;"> ' + $(this).html() + '</p>')
            } else {
                $(this).html('<p style="margin: 1px 2px; display: block; text-align: center; vertical-align: middle;"> ' + $(this).html() + '</p>')
            }
        });

        $.each($('table#log-nilai').find('td'), function () {
            if ($(this).hasClass("nama-siswa")) {
                $(this).html('<p style="width: 150px; margin: 1px 2px; -webkit-line-clamp: 1; overflow : hidden; text-overflow: ellipsis; display: -webkit-box;-webkit-box-orient: vertical;"> ' + $(this).text() + '</p>')
            } else {
                $(this).html('<p style="margin: 1px 2px; display: inline;"> ' + $(this).text() + '</p>')
            }
        });

        $('#loading').addClass('d-none');

        var title = $('#jdl').html();
        var trsAtas = $('table#atas tbody').html();
        var trsHead = $('table#log-nilai thead').html();
        var trsBody = $('table#log-nilai tbody').html();
        var copy = '<table id="excel" style="font-size: 11pt;" data-cols-width="' + colWidth + '"><tbody>' +
            '<tr>' +
            '<td colspan="' + (numCol + 9) + '" data-a-v="middle" data-a-h="center" data-f-bold="true">' + title + '</td>' +
            '</tr>' +
            trsAtas +
            '<tr></tr>' +
            trsHead +
            trsBody +
            '<tr></tr>' +
            '<tr>' +
            '<td colspan="' + (numCol + 9) + '" data-a-v="middle"">' + catatan + '</td>' +
            '</tr>' +
            '</tbody>';

        $('#konten-copy').html(copy);
        //$('#input-bulan').removeClass('d-none')
    }

    $(document).ready(function () {
        var selKelas = $('#opsi-kelas');
        var selMapel = $('#opsi-mapel');
        var selTahun = $('#opsi-tahun');
        var selSmt = $('#opsi-semester');
        var selBulan = $('#opsi-bulan');

        selMapel.prepend("<option value='' selected='selected' disabled='disabled'>Pilih Mapel</option>");
        selKelas.prepend("<option value='' selected='selected' disabled='disabled'>Pilih Kelas</option>");

        $('#btn-reload').on('click', function (e) {
            if (lastUrl) reload(true)
        })

        function reload(force, mapel, kls, thn, smt, bln) {
            if (!force) {
                var thnSel = $("#opsi-tahun option:selected").text();
                var thnSplit = thnSel.split('/');
                var sthn = smt === '1' ? thnSplit[0] : thnSplit[1];
                var empty = mapel === '' || kls === '' || thn === '' || smt === '' || mapel == null || kls == null || thn == null || smt == null;
                var newData = 'kelas=' + kls + '&mapel=' + mapel + '&tahun=' + thn + '&smt=' + smt + '&stahun=' + sthn + '&bulan=' + bln;
                //console.log(newData);
                lastUrl = base_url + 'elearning/loadnilaisemester?' + newData
            }

            if (force || !empty) {
                $('#loading').removeClass('d-none');

                setTimeout(function () {
                    $.ajax({
                        url: lastUrl,
                        type: "GET",
                        success: function (data) {
                            if (data.length === 0) {
                                $('#log-nilai').html('');
                                $('#loading').addClass('d-none');
                            } else {
                                createTable(data)
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                }, 500);
            }
        }

        selMapel.on('change', function () {
            reload(false, $(this).val(), selKelas.val(), selTahun.val(), selSmt.val(), selBulan.val());
        });

        selKelas.change(function () {
            reload(false, selMapel.val(), $(this).val(), selTahun.val(), selSmt.val(), selBulan.val());
        });

        selTahun.change(function () {
            reload(false, selMapel.val(), selKelas.val(), $(this).val(), selSmt.val(), selBulan.val());
        });

        selSmt.on('change', function () {
            reload(false, selMapel.val(), selKelas.val(), selTahun.val(), $(this).val(), selBulan.val());
        });

        selBulan.change(function () {
            reload(false, selMapel.val(), selKelas.val(), selTahun.val(), selSmt.val(), $(this).val());
        });

        selMapel.select2({width: '100%', theme: 'bootstrap4'});
        selKelas.select2({width: '100%', theme: 'bootstrap4'});
        selSmt.select2({width: '100%', theme: 'bootstrap4'});
        selTahun.select2({width: '100%', theme: 'bootstrap4'});
    });

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

</script>
