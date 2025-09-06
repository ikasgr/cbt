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
                </div>
                <div class="card-body">
                    <div class="alert alert-default-info border-info">
                        <div id="info-penggunaan">
                            Halaman ini untuk membatasi jumlah siswa yang aktif.
                            <ul>
                                <li>
                                    Lakukan aktivasi sebelum sesi dimulai
                                </li>
                                <li>
                                    Hanya siswa aktif yang bisa login
                                </li>
                                <li>
                                    Siswa yang dinonaktifkan tidak akan bisa login
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php
                    $dnone = count($ruangs) === 0 ;
                    ?>
                    <div class="by-ruang <?=$dnone ? 'd-none' : ''?>">
                        <div class="row mb-4">
                            <div class="col-12 mb-2 d-flex flex-row justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-action-ruang btn-outline-success btn-sm"
                                            data-action="aktifkan"
                                            data-toggle="tooltip"
                                            title="Aktifkan Ruang" disabled>
                                        <i class="fas fa-user-check m-1"></i>
                                        <span class="d-none d-sm-inline-block">Aktifkan</span>
                                        <span class="text-bold" id="aktifkan-ruang">0</span>
                                        <span class="mx-1">Sesi</span>
                                    </button>
                                    <button type="button" class="btn btn-action-ruang btn-outline-danger btn-sm"
                                            data-action="nonaktifkan"
                                            data-toggle="tooltip" title="Nonaktifkan" disabled>
                                        <i class="fa fa-ban m-1"></i>
                                        <span class="d-none d-sm-inline-block">Nonaktifkan </span>
                                        <span class="mx-1 text-bold" id="nonaktifkan-ruang">0</span>
                                        <span class="mx-1">Sesi</span>
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" title="By Ruang/Sesi" class="btn btn-sm btn-outline-primary btn-by-ruang active"><i class="fa fa-door-open"></i></button>
                                    <button type="button" title="By Siswa" class="btn btn-sm btn-outline-primary btn-by-siswa"><i class="fa fa-users"></i></button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive mb-3">
                                    <?= form_open('', array('id' => 'bulkruang')); ?>
                                    <table class="table table-bordered table-striped w-100">
                                        <thead>
                                        <tr>
                                            <th class="text-center align-middle" style="width: 40px">
                                                <input class="check my-checkbox"
                                                       type="checkbox"
                                                       id="check-all-ruang">
                                            </th>
                                            <th class="text-center align-middle" style="width: 40px">No</th>
                                            <th class="align-middle">Ruang</th>
                                            <th class="text-center align-middle">Sesi</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table-body-ruang">
                                        <?php
                                        $idx = 1;
                                        foreach ($ruangs as $ru) :
                                            foreach ($ru as $ses) :
                                                ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <input class="check my-checkbox check-ruang-sesi"
                                                               type="checkbox" name="ids[]" value="<?= $ses->ruang_id .'-'. $ses->sesi_id ?>">
                                                    </td>
                                                    <td class="text-center align-middle"><?= $idx ?></td>
                                                    <td class="align-middle"><?= $ses->kode_ruang .' ('. $ses->nama_ruang .')'?> </td>
                                                    <td class="text-center align-middle"><?= $ses->kode_sesi?></td>
                                                </tr>
                                                <?php $idx++; endforeach; endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="by-siswa d-none">
                        <div class="row mb-4">
                            <div class="col-12 col-sm-5 col-md-3 mb-3 order-1 order-sm-2">
                                <?php
                                echo form_dropdown(
                                    'ruang',
                                    $ruang,
                                    null,
                                    'id="ruang" class="form-control form-control-sm select2"'
                                ); ?>
                            </div>
                            <div class="col-12 col-sm-5 col-md-3 mb-3 order-2 order-sm-3">
                                <select name="sesi" id="sesi" class="form-control form-control-sm select2"></select>
                            </div>
                            <div class="col-12 col-sm-2 col-md-6 mb-3 order-0 order-sm-3">
                                <div class="text-right">
                                    <div class="btn-group">
                                        <button type="button" title="By Ruang/Sesi" class="btn btn-sm btn-outline-primary btn-by-ruang active"><i class="fa fa-door-open"></i></button>
                                        <button type="button" title="By Siswa" class="btn btn-sm btn-outline-primary btn-by-siswa"><i class="fa fa-users"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none" id="table-siswa">
                            <div class="col-12 mb-2 d-flex flex-row justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-action btn-outline-success btn-sm"
                                            data-action="aktifkan"
                                            data-toggle="tooltip"
                                            title="Aktifkan" disabled>
                                        <i class="fas fa-user-check m-1"></i>
                                        <span class="d-none d-sm-inline-block">Aktifkan</span>
                                        <span class="mx-1 text-bold" id="nonaktifkan">0</span>
                                    </button>
                                    <button type="button" class="btn btn-action btn-outline-danger btn-sm"
                                            data-action="nonaktifkan"
                                            data-toggle="tooltip" title="Nonaktifkan" disabled>
                                        <i class="fa fa-ban m-1"></i>
                                        <span class="d-none d-sm-inline-block">Nonaktifkan </span>
                                        <span class="mx-1 text-bold" id="aktifkan">0</span>
                                    </button>
                                </div>
                                <div class="d-flex flex-row align-items-center" style="width: 120px">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                                        </div>
                                        <input type="search" id="cari-siswa" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive mb-3">
                                    <table id="users" class="w-100 table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th class="text-center align-middle">
                                                <input class="check my-checkbox"
                                                       type="checkbox"
                                                       id="check-all">
                                            </th>
                                            <th class="text-center" style="width: 40px">No.</th>
                                            <th class="text-center">NIS</th>
                                            <th>Nama</th>
                                            <th class="text-center">Kelas</th>
                                            <th class="text-center">Username</th>
                                            <th class="text-center">Password</th>
                                            <th class="text-center">Reset Login</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class=" <?=$dnone ? '' : 'd-none'?>">
                        <div class="alert alert-default-danger border-danger">
                            Belum ada siswa yang tergabung dalam sesi
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
                <?= form_open('', array('id' => 'bulk')); ?><?= form_close() ?>
            </div>
        </div>
    </section>
</div>

<script>
    const arrRuang = JSON.parse('<?=json_encode($ruangs)?>');
    let ruang, sesi, url, newResponse;

    let uncheckedRuang = [];
    let checkedRuang = [];

    let uncheckedSiswa = [];
    let checkedSiswa = [];

    let timerCari;

    $(document).ready(function () {
        ajaxcsrf();

        console.log('arr', arrRuang)
        const btnByRuang = $('.btn-by-ruang')
        const btnBySiswa = $('.btn-by-siswa')
        btnByRuang.on('click', function () {
            $('.by-ruang').removeClass('d-none')
            $('.by-siswa').addClass('d-none')

            $("#check-all").prop("checked", false);
            $('#check-all').prop('indeterminate', false)
            $('#aktifkan').text('0')
            $('#nonaktifkan').text('0')

            $("#check-all-ruang").prop("checked", false);
            $('#check-all-ruang').prop('indeterminate', false)
            $('#aktifkan-ruang').text('0')
            $('#nonaktifkan-ruang').text('0')

            $(".check-ruang-sesi").each(function () {
                this.checked = false;
            });

            $(".check-siswa").each(function () {
                this.checked = false;
            });

            findUncheckedRuang()
            findUnchecked()

            btnByRuang.addClass('active')
            btnBySiswa.removeClass('active')
        })
        btnBySiswa.on('click', function () {
            $('.by-ruang').addClass('d-none')
            $('.by-siswa').removeClass('d-none')

            $("#check-all").prop("checked", false);
            $('#check-all').prop('indeterminate', false)
            $('#aktifkan').text('0')
            $('#nonaktifkan').text('0')

            $("#check-all-ruang").prop("checked", false);
            $('#check-all-ruang').prop('indeterminate', false)
            $('#aktifkan-ruang').text('0')
            $('#nonaktifkan-ruang').text('0')

            $(".check-ruang-sesi").each(function () {
                this.checked = false;
            });

            $(".check-siswa").each(function () {
                this.checked = false;
            });

            findUncheckedRuang()
            findUnchecked()

            btnBySiswa.addClass('active')
            btnByRuang.removeClass('active')
        })

        var opsiRuang = $("#ruang");
        var opsiSesi = $("#sesi");

        opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
        opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");

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
            loadSiswaRuang(ruang, $(this).val())
        })

        opsiRuang.select2({width: '100%', theme: 'bootstrap4'});
        opsiSesi.select2({width: '100%', theme: 'bootstrap4'});

        $("#table-body-ruang").on("change", ".check-ruang-sesi", function () {
            findUncheckedRuang();
        });

        $("#check-all-ruang").on("click", function () {
            const count = $('#table-body-ruang .check-ruang-sesi').length;
            if (count > 0) {
                if (this.checked) {
                    $(".check-ruang-sesi").each(function () {
                        this.checked = true;
                    });
                } else {
                    $(".check-ruang-sesi").each(function () {
                        this.checked = false;
                    });
                }
                findUncheckedRuang()
            }
        });

        $(".btn-action-ruang").on("click", function() {
            let action = $(this).data("action");
            let uri = action === 'aktifkan' ? base_url + "cbtactivate/aktifkanSesi" : base_url + "cbtactivate/nonaktifkanSesi";

            swal.fire({
                title: action === 'aktifkan' ?
                    "Aktifan "+checkedRuang.length+" sesi" :
                    "Nonaktifkan "+checkedRuang.length+" sesi",
                text: "",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Lanjutkan"
            }).then(result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    swal.fire({
                        title: action === 'aktifkan' ? "Mengaktifkan sesi terpilih" : "Menonaktifkan sesi terpilih",
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        onOpen: () => {
                            swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: uri,
                        type: "POST",
                        data: $('#bulkruang').serialize(),
                        success: function (response) {
                            $('#loading').addClass('d-none');
                            console.log("result", response);
                            swal.fire({
                                title: response.status ? "Sukses" : "Gagal",
                                text: response.msg,
                                icon: response.status ? "success" : "error"
                            }).then(result => {
                                window.location.reload()
                                //loadSiswaRuang($('#ruang').val(), $('#sesi').val())
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr);
                            Swal.fire({
                                title: "Gagal",
                                html: xhr.responseText,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });

    })

    function findUncheckedRuang() {
        uncheckedRuang = [];
        checkedRuang = [];
        const count = $('#table-body-ruang tr:visible .check-ruang-sesi').length;

        $("#table-body-ruang tr:visible .check-ruang-sesi:not(:checked)").each(function () {
            uncheckedRuang.push($(this).val());
        });
        $("#table-body-ruang tr:visible .check-ruang-sesi:checked").each(function () {
            checkedRuang.push($(this).val());
        });
        const countChecked = $("#table-body-ruang tr:visible .check-ruang-sesi:checked").length;

        if (countChecked === 0) {
            $("#check-all-ruang").prop("checked", false);
            $('#check-all-ruang').prop('indeterminate', false)
        } else if (countChecked === count) {
            $("#check-all-ruang").prop("checked", true);
            $('#check-all-ruang').prop('indeterminate', false)
        } else {
            $("#check-all-ruang").prop("checked", false);
            $('#check-all-ruang').prop('indeterminate', true)
        }
        $(".btn-action-ruang").attr('disabled', countChecked === 0);
        $('#aktifkan-ruang').text(checkedRuang.length)
        $('#nonaktifkan-ruang').text(checkedRuang.length)
    }

    function loadSiswaRuang(ruang, sesi) {
        var empty = ruang === '' || sesi === '';
        if (!empty) {
            url = base_url + "cbtactivate/getsiswaruang?ruang=" + ruang + '&sesi=' + sesi;
            refreshTable();
        } else {
            console.log('empty')
        }
    }

    function refreshTable() {
        $('#cari-siswa').val('')
        $('#loading').removeClass('d-none');
        setTimeout(function () {
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    newResponse = response
                    createPreview(response)
                }
            });
        }, 500);
    }

    function createPreview(response) {
        $("#check-all").prop("checked", false);
        $('#check-all').prop('indeterminate', false)
        $('#aktifkan').text('0')
        $('#nonaktifkan').text('0')

        $("#check-all-ruang").prop("checked", false);
        $('#check-all-ruang').prop('indeterminate', false)
        $('#aktifkan-ruang').text('0')
        $('#nonaktifkan-ruang').text('0')

        //console.log('response', response)
        var html = '';
        if (response.length > 0) {
            $.each(response, function (idx, siswa) {
                response.sort((a, b) => a.nama.localeCompare(b.nama, undefined, {numeric: true}))
                const kls = siswa.nama_kelas != null ? siswa.nama_kelas : '';
                html += `<tr><td class="text-center align-middle">
                <input type="checkbox" name="ids[]" value="${siswa.id_siswa}" class="check check-siswa my-checkbox">
            </td>
            <td class="text-center align-middle">${idx + 1}</td>
            <td class="align-middle">${siswa.nis}</td>
            <td>
                <div class="media d-flex h-100">
                    ${loadImage(siswa.foto)}
                    <div class="media-body ml-2 justify-content-center align-self-center">
                       ${siswa.nama}<br />`
                if (siswa.aktif == "0") {
                    html += `<span class="badge badge-danger">Nonaktif</span>`;
                } else {
                    html += `<span class="badge badge-success">Aktif</span>`;
                }
                html += `</div></div>
            </td>
            <td class="text-center align-middle">${kls}</td>
            <td class="text-center align-middle">${siswa.username}</td>
            <td class="text-center align-middle">${siswa.password}</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-default btn-xs btn-reset" ${siswa.reset == '0' ? 'disabled' : ''} data-username="${siswa.username}" data-nama="${siswa.nama}" data-toggle="tooltip" title="Reset Login"> <i class="fa fa-sync text-xs mr-1 ml-1"></i></button>
            </td></tr>`;
            })
        } else {
            html += '<tr><td colspan="8" class="text-center align-middle">Tidak ada data siswa</td><tr>';
        }

        $('#table-siswa').removeClass('d-none')
        $('#table-body').html(html);
        $('#loading').addClass('d-none');

        $('#cari-siswa').quicksearch('table tbody tr')

        $("#table-body").on("change", ".check-siswa", function () {
            findUnchecked();
        });

        $("#check-all").on("click", function () {
            const count = $('#table-body .check-siswa').length;
            if (count > 0) {
                if (this.checked) {
                    $(".check-siswa").each(function () {
                        this.checked = true;
                    });
                } else {
                    $(".check-siswa").each(function () {
                        this.checked = false;
                    });
                }
                findUnchecked()
            }
        });

        $("#users").on("click", ".btn-reset", function() {
            let username = $(this).data("username");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');
            $.ajax({
                url: base_url + "usersiswa/reset_login/" + username +"/"+nama,
                type: "GET",
                success: function (response) {
                    $('#loading').addClass('d-none');
                    if (response.msg) {
                        if (response.status) {
                            swal.fire({
                                title: "Sukses",
                                html: "<b>"+decodeURIComponent(response.msg)+"<b>",
                                icon: "success"
                            }).then(result => {
                                loadSiswaRuang($('#ruang').val(), $('#sesi').val())
                            });
                        } else {
                            swal.fire({
                                title: "Error",
                                html: "<b>"+decodeURIComponent(response.msg)+"<b>",
                                icon: "error"
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    Swal.fire({
                        title: "Gagal",
                        html: xhr.responseText,
                        icon: "error"
                    });
                }
            });
        });

        $(".btn-action").on("click", function() {
            let action = $(this).data("action");
            let uri = action === 'aktifkan' ? base_url + "cbtactivate/aktifkanSemua" : base_url + "cbtactivate/nonaktifkanSemua";

            swal.fire({
                title: action === 'aktifkan' ?
                    "Aktifan "+checkedSiswa.length+" siswa" :
                    "Nonaktifkan "+checkedSiswa.length+" siswa",
                text: "",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Lanjutkan"
            }).then(result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    swal.fire({
                        title: action === 'aktifkan' ? "Mengaktifkan siswa terpilih" : "Menonaktifkan siswa terpilih",
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        onOpen: () => {
                            swal.showLoading();
                        }
                    });

                    let formData = new FormData($('#bulk')[0])
                    for (const idsiswa of checkedSiswa) {
                        formData.append("ids[]", idsiswa)
                    }

                    $.ajax({
                        url: uri,
                        method: "POST",
                        processData: false,
                        contentType: false,
                        data: formData,
                        success: function (response) {
                            $('#loading').addClass('d-none');
                            //console.log("result", response);
                            swal.fire({
                                title: response.status ? "Sukses" : "Gagal",
                                text: response.msg,
                                icon: response.status ? "success" : "error"
                            }).then(result => {
                                loadSiswaRuang($('#ruang').val(), $('#sesi').val())
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr);
                            Swal.fire({
                                title: "Gagal",
                                html: xhr.responseText,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    }

    function findUnchecked() {
        uncheckedSiswa = [];
        checkedSiswa = [];
        const count = $('#table-body tr:visible .check-siswa').length;

        $("#table-body tr:visible .check-siswa:not(:checked)").each(function () {
            uncheckedSiswa.push($(this).val());
        });
        $("#table-body tr:visible .check-siswa:checked").each(function () {
            checkedSiswa.push($(this).val());
        });
        const countChecked = $("#table-body tr:visible .check-siswa:checked").length;

        if (countChecked === 0) {
            $("#check-all").prop("checked", false);
            $('#check-all').prop('indeterminate', false)
        } else if (countChecked === count) {
            $("#check-all").prop("checked", true);
            $('#check-all').prop('indeterminate', false)
        } else {
            $("#check-all").prop("checked", false);
            $('#check-all').prop('indeterminate', true)
        }
        //console.log('checked', checkedSiswa.length, uncheckedSiswa.length)
        $(".btn-action").attr('disabled', countChecked === 0);
        $('#aktifkan').text(checkedSiswa.length)
        $('#nonaktifkan').text(checkedSiswa.length)
    }

    function loadImage(srcImg) {
        let temp = document.createElement('div')
        temp.innerHTML = `<img class="avatar img-circle justify-content-center align-self-center" src="${base_url+srcImg}" width="36" height="36">`
        const defSrc = base_url + 'assets/img/siswa.png'

        const imgs = temp.getElementsByTagName('img')
        imgs[0].setAttribute('alt', 'foto');
        let src = imgs[0].getAttribute('src');
        if (src.startsWith('profiles')) {
            src = src.replace('profiles', 'foto_siswa');
            imgs[0].setAttribute("src", src);
        }
        imgs[0].setAttribute('onerror', 'javascript:this.src="' + defSrc + '"');
        return temp.innerHTML
    }

    /*
    function loadImagesHtml() {
        const body = document.getElementById('table-body')
        const imgs = body.getElementsByTagName('img')
        const defSrc = base_url + 'assets/img/siswa.png'
        for (let i = 0; i < imgs.length; i++) {
            imgs[i].setAttribute('alt', 'foto');
            let src = imgs[i].getAttribute('src');
            if (src.startsWith('profiles')) {
                src = src.replace('profiles', 'foto_siswa');
                imgs[i].setAttribute("src", src);
            }
            imgs[i].setAttribute('onerror', 'javascript:this.src="' + defSrc + '"');
        }
    }
     */
</script>