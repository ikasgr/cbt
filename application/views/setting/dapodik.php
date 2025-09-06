<div class="content-wrapper bg-white pt-4">
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
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <?= $subjudul ?>
                    </div>
                    <div class="card-tools">
                        <a href="#" id="dapodikTarikSiswa" class="btn btn-primary <?= !isset($setting->npsn) ? 'disabled' : '' ?>">
                            Tarik Siswa
                        </a>
                        <a href="#" id="dapodikTarikGtk" class="btn btn-secondary <?= !isset($setting->npsn) ? 'disabled' : '' ?>">
                            Tarik GTK
                        </a>
                    </div>
                </div>
            </div>
        </div>
</section>
<script>
    $(document).ready(function() {
        ajaxcsrf();

        $('#dapodikTarikGtk').on('click', () => {
            swal.fire({
                title: "Anda yakin?",
                html: "Anda akan melakukan penarikan data GTK dari Dapodik",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Tarik!"
            }).then(result => {
                if (result.value) {
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

                    $.ajax({
                        url: base_url + 'dapodik/tarikDataGtk',
                        type: 'GET',
                        success: function (response) {
                            if (response.message) {
                                swal.fire({
                                    title: "Sukses",
                                    html: `${response.message}<br />Nama siswa yang gagal diimport:<br />${response.fails.map(x => `<li>${x}</li>`).join('<br/>')}`,
                                    icon: "success",
                                    showCancelButton: false,
                                    confirmButtonColor: "#3085d6",
                                }).then(result => {
                                    if (result.value) {
                                        window.location.href = base_url + 'dapodik';
                                    }
                                });
                            }

                            if (response.error) {
                                swal.fire({
                                    title: "Error",
                                    text: respon.error,
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            swal.fire({
                                title: "Error",
                                text: error,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });

        $('#dapodikTarikSiswa').on('click', function() {
            swal.fire({
                title: "Anda yakin?",
                html: "Anda akan melakukan penarikan data dari Dapodik",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Tarik!"
            }).then(result => {
                if (result.value) {
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

                    $.ajax({
                        url: base_url + 'dapodik/tarikDataSiswa',
                        type: 'GET',
                        success: function (respon) {
                            if (respon.message) {
                                swal.fire({
                                    title: "Sukses",
                                    html: `${respon.message}<br />Nama siswa yang gagal diimport:<br />${respon.fails.map(x => `<li>${x}</li>`).join('<br/>')}`,
                                    icon: "success",
                                    showCancelButton: false,
                                    confirmButtonColor: "#3085d6",
                                }).then(result => {
                                    if (result.value) {
                                        window.location.href = base_url + 'dapodik';
                                    }
                                });
                            }

                            if (respon.error) {
                                swal.fire({
                                    title: "Error",
                                    text: respon.error,
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            swal.fire({
                                title: "Error",
                                text: error,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    });
</script>