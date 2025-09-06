<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= html_escape($judul ?? '') ?></h1>
                </div>
                <div class="col-sm-6">
                    <a href="<?= base_url('cbtcetak') ?>" class="btn btn-sm btn-danger float-right">
                        <i class="fas fa-arrow-circle-left"></i>
                        <span class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body pb-4">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <?php
                                        echo form_dropdown(
                                            'kelas',
                                            $kelas,
                                            null,
                                            'id="kelas" class="form-control"'
                                        ); ?>
                                    </div>
                                    <div class="input-group">
                                        <?php
                                        echo form_dropdown(
                                            'ruang',
                                            $ruang,
                                            null,
                                            'id="ruang" class="form-control d-none"'
                                        ); ?>
                                    </div>
                                </div>
                                <div class="col-4">
                                       <button class="btn bg-success text-white" id="btn-print">
                                        <i class="fa fa-print"></i><span class="ml-1">Cetak</span>
                                    </button>
                                </div>
                            </div><br>
                            <p>*) Untuk mencetak kartu silakan pilih kelas terlebih dahulu!</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                Preview
                            </div>
                        </div>
                        <div class="card-body pb-4">
                            <div class="d-flex justify-content-center bg-gray-light" style="min-height: 300mm">
                                <div id="print-preview" class="m-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url('assets/app/js/print-area.js') ?>"></script>
<script>
    var oldVal1 = "<?= isset($kartu->header_1) ? $kartu->header_1 : "" ?>";
    var oldVal2 = "<?= isset($kartu->header_2) ? $kartu->header_2 : "" ?>";
    var oldVal3 = "<?= isset($kartu->header_3) ? $kartu->header_3 : "" ?>";
    var oldVal4 = "<?= isset($kartu->header_4) ? $kartu->header_4 : "" ?>";
    var oldTgl = "<?= isset($kartu->tanggal) ? $kartu->tanggal : "" ?>";
    var oldKota = "<?=$setting->kota?>";
    var logoKanan = "<?=base_url() . $setting->logo_kanan?>";
    var logoKiri = "<?=base_url() . $setting->logo_kiri?>";
    var kodescan = "<?=base_url() . $setting->kodescan?>";
    var tandatangan = "<?=base_url() . $setting->tanda_tangan?>";
    var kepsek = "<?= $setting->kepsek ?>";
    var nip = "<?= $setting->nip ?>";
    var satuanPend = "<?= $setting->satuan_pendidikan ?>" === "2" ? "Madrasah" : "Sekolah";
    var printBy = 1;

    var raporSetting = JSON.parse(JSON.stringify(<?= json_encode($setting_rapor) ?>));
    var nipKepsek = raporSetting != null && raporSetting.nip_kepsek === "1" ? nip : " -";
    console.log('nip', nipKepsek)

    function submitKartu() {
        $('#set-kartu').submit();
    }

    function createPrintPreview(data) {
        var konten = '';
        if (data.length > 8) {
            var bagi2 = Math.round(data.length / 2);
            var pages = Math.round(bagi2 / 4);
            //console.log('pages', pages);
            for (let a = 0; a < pages; a++) {
                var card = '<div class="border my-shadow mb-3 pt-3 bg-white"><div class="pt-4" ' +
                    'style="display: flex;-webkit-justify-content: center;justify-content: center;background: white;width: 210mm; height: 297mm;padding: 1mm">';

                var tds = [];
                var kelas = printBy === 1 ? 'Kelas/Sesi' : 'Ruang/Sesi';

                let t = a * 8;
                let end = (a + 1) < pages ? t + 8 : data.length;
                //console.log('t', t);
                //console.log('end', end);

                for (let i = t; i < end; i++) {
                    var setSiswa = data[i].set_siswa === '1';
                    var ruang = setSiswa ? data[i].ruang_kelas : data[i].kode_ruang;
                    var sesi = setSiswa ? data[i].sesi_kelas : data[i].kode_sesi;
                    //var kelasVal = printBy === 1 ? data[i].nama_kelas : ruang;

                    //var foto = data[i].foto == null || data[i].foto === '' ? 'siswa.png' : data[i].foto;
                    //var foto = getFoto(data[i].foto);
                      var td = '<div style="display: flex; justify-content: center; align-items: center;">' +
                    '<div style="width: 10cm">' +
                    '<table id="table-header-print" style="width: 100%; border-top: 1px solid black; border-bottom: 0;border-left: 1px solid black; border-right: 1px solid black">' +
                    
                    '<tr>' +
                    '<td style="width:20%;">' +
                   //'<img id="prev-logo-kiri-print" src="' + logoKiri + '" style="width:55px; height:55px; margin-left: 6px; margin-right: 6px; margin-top:4px;">' +
                   // '</td>' +
                   // '<td style="width:60%; text-align: center;">' +
                    '<div class="text-center" style="line-height: 1.1; font-family: \'Verdana\'; font-size: 14pt; text-align: center;"><b>KARTU MEJA PESERTA ASESMEN</b></div>' +
                   // '<div class="text-center" style="line-height: 1.1; font-family: \'Verdana\'; font-size: 11pt"><b>' + oldVal2 + '</b></div>' +
                   // '<div class="text-center" style="line-height: 1.2; font-family: \'Verdana\'; font-size: 8pt">' + oldVal3 + '</div>' +
                   // '<div class="text-center" style="line-height: 1.2; font-family: \'Verdana\'; font-size: 8pt">' + oldVal4 + '</div>' +
                   // '</td>' +
                   //  '<td style="width:20%;">' +
                   // '<img id="prev-logo-kanan-print" src="' + logoKanan + '" style="width:55px; height:55px; margin-left: 6px; margin-right: 6px; margin-top:4px; border-style: none">' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '<table id="table-body-print" style="width:100%;border: 1px solid black">' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 10pt">' +
                    '<td style="padding-top:4px;padding-left:22px;width: 30%"></td>' +
                    '<td style="padding-top:4px;"></td>' +
                    '<td style="padding-top:4px; line-height: 1.1; font-family: \'Verdana\'; font-size: 16pt"></td>' +

                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 10pt">' +
                    '<td style="padding-left:22px;width: 30%">Nama</td>' +
                    '<td>:</td>' +
                        '<td><b>' + data[i].nama + '</b></td>' +
                        '</tr>' +
                        '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                        '<td style="padding-left:22px;width: 30%">NIS / NISN</td>' +
                        '<td>:</td>' +
                        '<td>' + data[i].nis + '/' + data[i].nisn + '</td>' +
                        '</tr>' +
                        '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                        '<td style="padding-left:22px;width: 30%">Kelas</td>' +
                        '<td>:</td>' +
                        '<td>' + data[i].nama_kelas + '</td>' +
                        '</tr>' +
                        '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                        '<td style="padding-left:22px;width: 30%">Ruang/Sesi</td>' +
                        '<td>:</td>' +
                        '<td>' + ruang + '/' + sesi + '</td>' +
                        '</tr>' +
                        '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                        '<td style="padding-left:22px;width: 30%">Username</td>' +
                        '<td>:</td>' +
                        '<td><b>' + data[i].username + '</b></td>' +
                        '</tr>' +
                        '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                        '<td style="padding-left:22px;width: 30%">Password</td>' +
                        '<td>:</td>' +
                        '<td><b>---> Lihat di Kartu Peserta <---</b></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="2" style="padding-top: 6px; padding-bottom: 6px; padding-left:22px;width: 35%">' +
                        '<img class="avatar" style="width: 75px; height: 95px; object-fit: cover;object-position: center; ' +
                        'outline: 1px solid;" ' +
                        'src= "' + base_url + data[i].foto + '"' +
                        '/>' +
                        '</td>' +
                        '<td style="text-align: center;">' +
                        '<div id="prev-kodescan-print" style="font-family: \'Verdana\'; font-size: 9pt; line-height: 1; background: url('+kodescan+') no-repeat right;  background-size: 120px 120px">' +
                        //'<span>' + oldKota + '</span>, <span>' + oldTgl + '</span>' +
                        '<br>' +
                        '<br><div class="text-left" style="line-height: 1.1; font-family: \'Verdana\'; font-size: 10pt; text-align: left;"><b>PLEASE</b>' +
                        '<br><div class="text-left" style="line-height: 1.1; font-family: \'Verdana\'; font-size: 10pt; text-align: left;"><b>SCAN HERE >></b></div>' +
                       // '<br><div class="text-left" style="line-height: 1.1; font-family: \'Verdana\'; font-size: 8pt; text-align: left;"><b>EXAM SMANDU</b></div>' +
                       // '<span>' + kepsek + '</span>' +
                       '<br>' +
                       '<br>' +
                       '<br>' +
                        '</div>' +
                        '</td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>' +
                        '</div>';

                    tds.push(td);
                }
                var table = '<table>';
                for (let j = 0; j < tds.length; j++) {
                    if ((j + 1) % 2 === 0) {
                        table += '<td style="padding: 5px;">' + tds[j] + '</td></tr>';
                    } else {
                        table += '<tr><td style="padding: 5px;">' + tds[j] + '</td>';
                    }
                }
                table += '</table>';
                card += table + '</div></div>';
                konten += card + '<div style="page-break-after: always"></div>';
            }
        } else {
            var card = '<div class="border my-shadow mb-3 pt-3 bg-white"><div class="pt-4" ' +
                'style="display: flex;-webkit-justify-content: flex-start;justify-content: flex-start; align-items: start;background: white;width: 210mm; height: 297mm;padding: 1mm">';
            var tds = [];
            for (let i = 0; i < data.length; i++) {
                var setSiswa = data[i].set_siswa === '1';
                var ruang = setSiswa ? data[i].ruang_kelas : data[i].kode_ruang;
                var sesi = setSiswa ? data[i].sesi_kelas : data[i].kode_sesi;
                //var kelasVal = printBy === 1 ? data[i].nama_kelas : ruang;

                //var foto = data[i].foto == null || data[i].foto === '' ? 'siswa.png' : data[i].foto;
                //var foto = getFoto(data[i].foto);
                var td = '<div style="display: flex; justify-content: center; align-items: center;">' +
                    '<div style="width: 10cm">' +
                    '<table id="table-header-print" style="width: 100%; border-top: 1px solid black; border-bottom: 0;border-left: 1px solid black; border-right: 1px solid black">' +
                    '<tr>' +
                    '<td style="width:20%;">' +
                    '<img id="prev-logo-kiri-print" src="' + logoKiri + '" style="width:55px; height:55px; margin-left: 6px; margin-right: 6px; margin-top:4px;">' +
                    '</td>' +
                    '<td style="width:60%; text-align: center;">' +
                    '<div style="line-height: 1.1; font-family: \'Verdana\'; font-size: 8pt">' + oldVal1 + '</div>' +
                    '<div class="text-center" style="line-height: 1.1; font-family: \'Verdana\'; font-size: 9pt"><b>' + oldVal2 + '</b></div>' +
                    '<div class="text-center" style="line-height: 1.2; font-family: \'Verdana\'; font-size: 8pt">' + oldVal3 + '</div>' +
                    '<div class="text-center" style="line-height: 1.2; font-family: \'Verdana\'; font-size: 8pt">' + oldVal4 + '</div>' +
                    '</td>' +
                    '<td style="width:20%;">' +
                    '<img id="prev-logo-kanan-print" src="' + logoKanan + '" style="width:55px; height:55px; margin-left: 6px; margin-right: 6px; margin-top:4px; border-style: none">' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '<table id="table-body-print" style="width:100%;border: 1px solid black">' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 10pt">' +
                    '<td style="padding-top:4px;padding-left:22px;width: 30%">No. Peserta</td>' +
                    '<td style="padding-top:4px;">:</td>' +
                    '<td style="padding-top:4px;width: 65%">' + data[i].nomor_peserta + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Nama</td>' +
                    '<td>:</td>' +
                    '<td>' + data[i].nama + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">NIS / NISN</td>' +
                    '<td>:</td>' +
                    '<td>' + data[i].nis + '/' + data[i].nisn + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Kelas</td>' +
                    '<td>:</td>' +
                    '<td>' + data[i].nama_kelas + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Ruang/Sesi</td>' +
                    '<td>:</td>' +
                    '<td>' + ruang + '/' + sesi + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Username</td>' +
                    '<td>:</td>' +
                    '<td>' + data[i].username + '</td>' +
                    '</tr>' +
                    '<tr style="line-height: 1; font-family: \'Verdana\'; font-size: 9pt">' +
                    '<td style="padding-left:22px;width: 30%">Password</td>' +
                    '<td>:</td>' +
                    '<td>' + data[i].password + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td colspan="2" style="padding-top: 6px; padding-bottom: 6px; padding-left:22px;width: 35%">' +
                    '<img class="avatar" style="width: 60px; height: 70px; object-fit: cover;object-position: center; ' +
                    'outline: 1px solid;" ' +
                    'src= "' + base_url + data[i].foto + '"' +
                    '/>' +
                    '</td>' +
                    '<td style="text-align: center;">' +
                    '<div id="prev-tandatangan-print" style="font-family: \'Verdana\'; font-size: 9pt; line-height: 1; background: url(' + tandatangan + ') no-repeat center; background-size: 100px 60px">' +
                    '<span>' + oldKota + '</span>, <span>' + oldTgl + '</span>' +
                    '<br>Kepala ' + satuanPend +
                    '<br>' +
                    '<br>' +
                    '<br>' +
                    '<span>' + kepsek + '</span>' +
                    '<p id="prev-nip" style="margin-top: 4px; margin-bottom: 4px">NIP: '+nipKepsek+'</p>' +
                    '</div>' +
                    '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>' +
                    '</div>';

                tds.push(td);
            }
            var table = '<table>';
            for (let j = 0; j < tds.length; j++) {
                if ((j + 1) % 2 === 0) {
                    table += '<td style="padding: 5px;">' + tds[j] + '</td></tr>';
                } else {
                    table += '<tr><td style="padding: 5px;">' + tds[j] + '</td>';
                }
            }
            table += '</table>';
            card += table + '</div></div>';
            konten += card;
        }

        $("#print-preview").html(konten);

        $(`.avatar`).each(function () {
            $(this).on("error", function () {
                var src = $(this).attr('src').replace('profiles', 'foto_siswa');
                $(this).attr("src", src);
                $(this).on("error", function () {
                    $(this).attr("src", base_url + 'assets/img/siswa.png');
                });

            });
        });
    }

    $(document).ready(function () {
        $("#header-1").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal1) {
                return;
            }

            oldVal1 = currentVal;
            $('#prev-header-1').html(currentVal);
            //alert("changed!");
        });
        $("#header-2").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal2) {
                return;
            }

            oldVal2 = currentVal;
            $('#prev-header-2').text(currentVal);
            //alert("changed!");
        });
        $("#header-3").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal3) {
                return;
            }

            oldVal3 = currentVal;
            $('#prev-header-3').text(currentVal);
            //alert("changed!");
        });
        $("#header-4").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal4) {
                return;
            }

            oldVal4 = currentVal;
            $('#prev-header-4').text(currentVal);
            //alert("changed!");
        });

        $('#input-tanggal').on('input', function (e) {
            var tgl = $(this).val();
            $('#prev-tanggal').text(tgl);
            oldTgl = tgl;
        });

        $('#set-kartu').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            swal.fire({
                text: "Silahkan tunggu....",
                button: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            let form = new FormData($('#set-kartu')[0]);
            $.ajax({
                url: base_url + 'cbtcetak/savekartu',
                type: 'POST',
                processData: false,
                contentType: false,
                data: form,
                success: function (response) {
                    console.log(response);
                    swal.fire({
                        title: 'Sukses',
                        text: "Header kartu berhasil disimpan",
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                    }).then(result => {
                        if (result.value) {
                            window.location.href = base_url + 'cbtcetak/kartupeserta';
                        }
                    });
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        function uploadAttach(action, data) {
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: action,
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (data) {
                    if (data.src.includes('kanan')) {
                        logoKanan = data.src;
                        //console.log('kanan', data.src);
                    } else if (data.src.includes('kiri')) {
                        logoKiri = data.src;
                        //console.log('kiri', data.src);
                    } else if (data.src.includes('tanda')) {
                        tandatangan = data.src;
                        //console.log('tandatangan', data.src);
                    }
                },
                error: function (xhr, error, status) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        }

        function deleteImage(src) {
            $.ajax({
                data: {src: src},
                type: "POST",
                url: base_url + "cbtcetak/deletefile",
                cache: false,
                success: function (response) {
                    console.log(response);
                }
            });
        }

        function loadSiswaKelas(kelas) {
            $.ajax({
                type: "GET",
                url: base_url + "cbtcetak/getsiswakelas?kelas=" + kelas,
                success: function (response) {
                    createPrintPreview(response.siswa);
                }
            });
        }

        function loadSiswaRuang(ruang) {
            $.ajax({
                type: "GET",
                url: base_url + "cbtcetak/getsiswaruang?ruang=" + ruang,
                success: function (response) {
                    createPrintPreview(response.siswa);
                }
            });
        }

        //loadSiswaKelas($('#kelas').val());
        $("#kelas").prepend("<option value='' selected='selected' disabled>Pilih Kelas</option>");
        $("#ruang").prepend("<option value='' selected='selected' disabled>Pilih Ruang</option>");
        $("#kelas").change(function () {
            loadSiswaKelas($(this).val());
        });

        $("#ruang").change(function () {
            loadSiswaRuang($(this).val());
        });

        $('#kelas').select2({theme: 'bootstrap4'});
        //$('#ruang').select2({theme: 'bootstrap4'});

        $("#btn-print").click(function () {
            if ($('#kelas').val() === '') {
                Swal.fire({
                    title: "ERROR",
                    text: "Pilih kelas dulu",
                    icon: "error"
                })
            } else {
                $('#print-preview').print();
            }
        });

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().addClass('btn-outline-primary').removeClass('active btn-primary');

            if (!$('#kelas').is(':hidden')) {
                $('#kelas').addClass('d-none');
                $('#ruang').removeClass('d-none');
                printBy = 2;
            } else {
                $('#kelas').removeClass('d-none');
                $('#ruang').addClass('d-none');
                printBy = 1;
            }
        });
    })

</script>
