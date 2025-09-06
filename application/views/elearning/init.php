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
                </div>

                <div class="card-body">
                    <div class="col-lg-12 p-0 mt-3">
                        <div class="alert alert-default-warning align-content-center" role="alert">
                            <div class="row py-1">
                                <div class="col-12">
                                    Aplikasi harus diupdate untuk menjalankan fitur terbaru
                                    <ul>
                                        <li>
                                            Database akan di-BACKUP secara otomatis.
                                        </li>
                                        <li>
                                            Untuk berjaga-jaga jika backup otomatis gagal <strong>BACKUP DATABASE SECARA MANUAL terlebih dahulu</strong>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-danger float-right" id="update-aplikasi">Update Aplikasi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function ($) {
        const dataUpdate = JSON.parse('<?= json_encode($uptodate) ?>');
        console.log('data', dataUpdate)
        $('#update-aplikasi').on('click', function () {
            alertDialog(dataUpdate.need_convert)
        })
    });

    function alertDialog(need) {
        swal.fire({
            title: "Update Aplikasi",
            text: "Database akan dibackup otomatis!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Update!"
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
                if (need) {
                    convert()
                } else {
                    updateApp()
                }
            }
        });
    }

    function convert() {
        console.log('convert')
        $.ajax({
            url: base_url + 'elearning/convertMateri',
            type: "GET",
            success: function (respon) {
                console.log('res', respon)
                if (respon.success) {
                    updateApp()
                } else {
                    swal.fire({
                        title: "Gagal",
                        text: 'Gagal konversi jadwal materi',
                        icon: "error"
                    });
                }
            },
            error: function (xhr, status, error) {
                swal.fire({
                    title: "Error",
                    text: 'Gagal konversi jadwal materi',
                    icon: "error"
                });
            }
        });
    }

    function updateApp() {
        console.log('update')
        $.ajax({
            url: base_url + 'migrate/updateApp',
            type: "GET",
            success: function (respon) {
                if (respon.success) {
                    swal.fire({
                        title: "Berhasil",
                        text: respon.message,
                        icon: "success"
                    }).then(result => {
                        if (result.value) {
                            window.location.reload();
                        }
                    });
                } else {
                    swal.fire({
                        title: "Gagal",
                        text: respon.message,
                        icon: "error"
                    });
                }
            },
            error: function (xhr, status, error) {
                swal.fire({
                    title: "Error",
                    text: 'Update gagal',
                    icon: "error"
                });
            }
        });
    }
</script>
