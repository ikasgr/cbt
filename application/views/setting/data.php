<?php
$satuan = ["1" => ["SD", "MI"], "2" => ["SMP", "MTS"], "3" => ["SMA", "MA", "SMK"]];
?>
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
                        <h6>Setting</h6>
                    </div>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="submitSetting()">
                            <i class="fa fa-plus mr-1"></i>Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?= form_open('', array('id' => 'savesetting')) ?>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label>Nama Aplikasi *</label>
                            <input type="text" name="nama_aplikasi" class="form-control required"
                                   value="<?= html_escape($setting->nama_aplikasi ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label>Nama Sekolah *</label>
                            <input type="text" name="nama_sekolah" class="form-control required"
                                   value="<?= html_escape($setting->sekolah ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>NSS/NSM</label>
                            <input type="text" name="nss" class="form-control" value="<?= $setting->nss ?>" pattern="[0-9]{10,12}" title="NSS/NSM harus berupa angka 10-12 digit">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>NPSN</label>
                            <input type="text" name="npsn" class="form-control" value="<?= $setting->npsn ?>" pattern="[0-9]{8}" title="NPSN harus berupa 8 digit angka">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Jenjang *</label>
                            <select id="jenjang" class="form-control required" data-placeholder="Pilih Jenjang"
                                    name="jenjang" required>
                                <option value="" disabled>Pilih Jenjang</option>
                                <?php
                                $jenjang = ["SD/MI", "SMP/MTS", "SMA/MA/SMK"];
                                for ($i = 0; $i < 3; $i++) {
                                    $arrJenjang[$i + 1] = $jenjang[$i];
                                }
                                foreach ($arrJenjang as $key => $val) :
                                    $selected = $setting->jenjang == $key ? 'selected' : '';
                                    ?>
                                    <option value="<?= $key ?>" <?= $selected ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Satuan Pend. *</label>
                            <select id="satuan-pend" class="form-control required" data-placeholder="Satuan Pendidikan"
                                    name="satuan_pendidikan" required>
                                <option value="0" disabled>Satuan Pendidikan</option>
                                <?php
                                $satuan_selected = $satuan[$setting->jenjang];
                                for ($i = 0; $i < count($satuan_selected); $i++) {
                                    $arrSatuan[$i + 1] = $satuan_selected[$i];
                                }
                                foreach ($arrSatuan as $keys => $vals) :
                                    $selecteds = $setting->satuan_pendidikan == $keys ? 'selected' : '';
                                    ?>
                                    <option value="<?= $keys ?>" <?= $selecteds ?>><?= $vals ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label>Alamat *</label>
                            <textarea class="form-control required" name="alamat" rows="5" placeholder="Masukkan alamat lengkap" required><?= html_escape($setting->alamat ?? '') ?></textarea>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label>Desa/Kelurahan *</label>
                                    <input type="text" name="desa" class="form-control required"
                                           value="<?= $setting->desa ?>" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label>Kecamatan *</label>
                                    <input type="text" name="kec" class="form-control required"
                                           value="<?= $setting->kecamatan ?>" required>
                                </div>
                                <div class="col-md-5 mb-4">
                                    <label>Kabupaten/Kota *</label>
                                    <input type="text" name="kota" class="form-control required" value="<?= $setting->kota ?>"
                                           required>
                                </div>
                                <div class="col-md-2 mb-4">
                                    <label>Kode Pos</label>
                                    <input type="number" name="kode_pos" class="form-control" value="<?= $setting->kode_pos ?>">
                                </div>
                                <div class="col-md-5 mb-4">
                                    <label>Provinsi *</label>
                                    <input type="text" name="provinsi" class="form-control required"
                                           value="<?= $setting->provinsi ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Faksimili</label>
                            <input type="text" name="fax" class="form-control" value="<?= $setting->fax ?>">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Website</label>
                            <input type="url" name="web" class="form-control" value="<?= $setting->web ?>" placeholder="https://ikasmedia.com">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $setting->email ?>" placeholder="ikasmedia@gmail.com">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Nomor Telepon</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+62</span>
                                </div>
                                <input type="number" name="tlp" class="form-control" value="<?= $setting->telp ?>">
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Kepala Sekolah *</label>
                            <input type="text" name="kepsek" class="form-control required"
                                   value="<?= $setting->kepsek ?>" required>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>NIP</label>
                            <input type="number" name="nip" class="form-control" value="<?= $setting->nip ?>">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>Proktor *</label>
                            <input type="text" name="proktor" class="form-control required"
                                   value="<?= $setting->proktor ?>" required>
                        </div>
                        <div class="col-md-3 mb-4">
                            <label>NIP</label>
                            <input type="number" name="nip_proktor" class="form-control" value="<?= $setting->nip_proktor ?>">
                        </div>   
                        
                         <div class="col-md-3 mb-4">
                            <label>Status Bank Soal</label>
                            <select id="ba_aktif" class="form-control required" data-placeholder="Pilih Status"
                                    name="ba_aktif" required>
                                <option value="" disabled>Pilih Status</option>
                                <?php
                                $bas = ["AKTIF", "NON-AKTIF"];
                                for ($i = 0; $i < 2; $i++) {
                                    $arrBas[$i + 1] = $bas[$i];
                                }
                                foreach ($arrBas as $key => $val) :
                                    $selected = $setting->ba_aktif == $key ? 'selected' : '';
                                    ?>
                                    <option value="<?= $key ?>" <?= $selected ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-4">
                            <label>Waktu Bank Soal </label>
                            <input type="datetime-local" name="ba_waktu" class="form-control" value="<?= $setting->ba_waktu ?>">
                        </div>

                        <div class="col-md-3 mb-4">
                            <label>Tampilkan token di siswa</label>
                            <select id="tkn_siswa" class="form-control required" data-placeholder="Pilih Status"
                                    name="tkn_siswa" required>
                                <option value="" disabled>Pilih Status</option>
                                <?php
                                $bas = ["YA", "TIDAK"];
                                for ($i = 0; $i < 2; $i++) {
                                    $arrBas[$i + 1] = $bas[$i];
                                }
                                foreach ($arrBas as $key => $val) :
                                    $selected = $setting->tkn_siswa == $key ? 'selected' : '';
                                    ?>
                                    <option value="<?= $key ?>" <?= $selected ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-4">
                            <label>Mode</label>
                            <select id="mode_app" class="form-control required" data-placeholder="Pilih Status"
                                    name="mode_app" required>
                                <option value="" disabled>Pilih Status</option>
                                <?php
                                $bas = ["CBT", "ELEARNING"];
                                for ($i = 0; $i < 2; $i++) {
                                    $arrBas[$i + 1] = $bas[$i];
                                }
                                foreach ($arrBas as $key => $val) :
                                    $selected = $setting->mode_app == $key ? 'selected' : '';
                                    ?>
                                    <option value="<?= $key ?>" <?= $selected ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
 
                        
                    </div>
                    <?= form_close() ?>
                    <div class="row">
                        <div class="col-md-2">
                            <?= form_open_multipart('', array('id' => 'set-stampel')) ?>
                            <div class="form-group pb-2">
                                <label for="stampel">Cab/Stampel</label>
                                <input type="file" id="stampel" name="logo" class="dropify"
                                       data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->stampel ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>
                        <div class="col-md-2">
                            <?= form_open_multipart('', array('id' => 'set-tandatangan')) ?>
                            <div class="form-group pb-2">
                                <label for="logo-kiri">TTD Kepsek</label>
                                <input type="file" id="tanda-tangan" name="logo" class="dropify"
                                       data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->tanda_tangan ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>
                        <div class="col-md-2">
                            <?= form_open_multipart('', array('id' => 'set-ttdproktor')) ?>
                            <div class="form-group pb-2">
                                <label for="ttdproktor">TTD Proktor</label>
                                <input type="file" id="ttdproktor" name="logo" class="dropify"
                                       data-max-file-size-preview="2M" data-allowed-file-extensions="png"
                                       data-default-file="<?= base_url() . $setting->ttdproktor ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>
                        
                        <div class="col-md-2">
                            <?= form_open_multipart('', array('id' => 'set-logo-kiri')) ?>
                            <div class="form-group pb-2">
                                <label for="logo-kiri">Logo Kiri / Logo Aplikasi</label>
                                <input type="file" id="logo-kiri" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->logo_kiri ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>
                        <div class="col-md-2">
                            <?= form_open_multipart('', array('id' => 'set-logo-kanan')) ?>
                            <div class="form-group pb-2">
                                <label for="logo-kanan">Logo Kanan</label>
                                <input type="file" id="logo-kanan" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->logo_kanan ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>
                        <div class="col-md-2">
                            <?= form_open_multipart('', array('id' => 'set-kodescan')) ?>
                            <div class="form-group pb-2">
                                <label for="kodescan">Qr Code</label>
                                <input type="file" id="kodescan" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->kodescan ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>
                        <div class="col-md-6">
                            <?= form_open_multipart('', array('id' => 'set-kopsekolah')) ?>
                            <div class="form-group pb-2">
                                <label for="kopsekolah">Kop Sekolah</label>
                                <input type="file" id="kopsekolah" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->kopsekolah ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>   
                        
                        <div class="col-md-6">
                            <?= form_open_multipart('', array('id' => 'set-banner1')) ?>
                            <div class="form-group pb-2">
                                <label for="banner1">Banner 1 (format .jpg ukuran 1020x250mm)</label>
                                <input type="file" id="banner1" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->banner1 ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>  
                        
                        <div class="col-md-6">
                            <?= form_open_multipart('', array('id' => 'set-banner2')) ?>
                            <div class="form-group pb-2">
                                <label for="banner2">Banner 2 (format .jpg ukuran 1020x250mm)</label>
                                <input type="file" id="banner2" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->banner2 ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>  
                        
                        <div class="col-md-6">
                            <?= form_open_multipart('', array('id' => 'set-banner3')) ?>
                            <div class="form-group pb-2">
                                <label for="banner3">Banner 3 (format .jpg ukuran 1020x250mm)</label>
                                <input type="file" id="banner3" name="logo" class="dropify"
                                       data-max-file-size-preview="2M"
                                       data-allowed-file-extensions="jpg jpeg png"
                                       data-default-file="<?= base_url() . $setting->banner3 ?>"/>
                            </div>
                            <?= form_close() ?>
                        </div>                          
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // Objek untuk menyimpan path logo
    const logos = {
        'logo_kanan': '<?= base_url() . html_escape($setting->logo_kanan ?? '') ?>',
        'logo_kiri': '<?= base_url() . html_escape($setting->logo_kiri ?? '') ?>',
        'kodescan': '<?= base_url() . html_escape($setting->kodescan ?? '') ?>',
        'kopsekolah': '<?= base_url() . html_escape($setting->kopsekolah ?? '') ?>',
        'stampel': '<?= base_url() . html_escape($setting->stampel ?? '') ?>',
        'ttdproktor': '<?= base_url() . html_escape($setting->ttdproktor ?? '') ?>',
        'tanda_tangan': '<?= base_url() . html_escape($setting->tanda_tangan ?? '') ?>',
        'banner1': '<?= base_url() . html_escape($setting->banner1 ?? '') ?>',
        'banner2': '<?= base_url() . html_escape($setting->banner2 ?? '') ?>',
        'banner3': '<?= base_url() . html_escape($setting->banner3 ?? '') ?>'
    };

    // Data satuan pendidikan
    const satuanPend = <?= json_encode($satuan) ?>;

    function submitSetting() {
        $('#savesetting').submit();
    }

    $(document).ready(function () {
        // Inisialisasi CSRF untuk AJAX
        ajaxcsrf();

        // Inisialisasi Dropify
        const drEvent = $('.dropify').dropify({
            messages: {
                default: 'Seret logo kesini atau klik',
                replace: 'Seret atau klik untuk mengganti logo',
                remove: 'Hapus',
                error: 'Oops, ada kesalahan!'
            },
            error: {
                fileSize: 'Ukuran file terlalu besar (maks {{ value }}).',
                imageFormat: 'Format gambar tidak diizinkan (hanya {{ value }}).'
            }
        });

        // Event: Setelah file dihapus di Dropify
        drEvent.on('dropify.afterClear', function (event, element) {
            const id = element.element.id;
            const src = $(event.currentTarget).data('default-file');
            deleteImage(src);
            logos[id] = '';
            $(`#prev-${id}`).attr('src', '');

            // Khusus untuk tanda tangan dan ttd proktor
            if (['tanda-tangan', 'ttdproktor'].includes(id)) {
                $(`#prev-${id}`).css({
                    background: 'none',
                    'font-family': 'Times New Roman',
                    'font-size': '10pt'
                });
            }
        });

        // Event: Error pada Dropify
        drEvent.on('dropify.errors', function () {
            $.toast({
                heading: 'Error',
                text: 'File tidak valid atau terlalu besar',
                icon: 'warning',
                showHideTransition: 'fade',
                allowToastClose: true,
                hideAfter: 5000,
                position: 'top-right'
            });
        });

        // Event: Perubahan dropdown jenjang
        $('#jenjang').change(function () {
            const jenjangVal = $(this).val();
            const options = ['<option value="" disabled selected>Pilih Satuan Pendidikan</option>'];
            if (satuanPend[jenjangVal]) {
                satuanPend[jenjangVal].forEach((val, index) => {
                    options.push(`<option value="${index + 1}">${val}</option>`);
                });
            }
            $('#satuan-pend').html(options.join(''));
        });

        // Event: Submit form pengaturan
        $('#savesetting').on('submit', function (e) {
            e.preventDefault();

            // Validasi input yang wajib
            const hasInput = $('.required').toArray().every(input => $(input).val().trim() !== '');
            if (!hasInput) {
                Swal.fire({
                    title: 'Error',
                    text: 'Isi semua kolom yang bertanda bintang (*)',
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                text: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // Kirim data termasuk path logo
            $.ajax({
                url: base_url + 'settings/savesetting',
                type: 'POST',
                data: $(this).serialize() + '&' + $.param(logos),
                success: function (response) {
                    Swal.fire({
                        title: 'Sukses',
                        text: 'Pengaturan berhasil disimpan',
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        window.location.href = base_url + 'sekolah';
                    });
                },
                error: function (xhr) {
                    let errorMsg = 'Gagal menyimpan pengaturan';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errorMsg = err.Message || errorMsg;
                    } catch (e) {
                        console.error('Error parsing response:', xhr.responseText);
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error'
                    });
                }
            });
        });

        // Event: Upload file untuk semua input Dropify
        $('.dropify').change(function () {
            const input = this;
            const id = input.id;

            if (input.files && input.files[0]) {
                // Validasi ukuran file (2MB)
                if (input.files[0].size > 2 * 1024 * 1024) {
                    $.toast({
                        heading: 'Error',
                        text: 'Ukuran file maksimum adalah 2MB',
                        icon: 'error',
                        position: 'top-right',
                        hideAfter: 5000
                    });
                    return;
                }

                // Pratinjau gambar
                const reader = new FileReader();
                reader.onload = function (e) {
                    $(`#prev-${id}`).attr('src', e.target.result);
                    if (['tanda-tangan', 'ttdproktor'].includes(id)) {
                        $(`#prev-${id}`).css({
                            background: `url(${e.target.result}) no-repeat center`,
                            'background-size': '100px 60px',
                            'font-family': id === 'tanda-tangan' ? 'Verdana' : 'Times New Roman',
                            'font-size': '10pt'
                        });
                    }
                };
                reader.readAsDataURL(input.files[0]);

                // Upload file
                const form = new FormData($(`#set-${id}`)[0]);
                uploadAttach(base_url + `settings/uploadfile/${id.replace(/-/g, '_')}`, form, id);
            }
        });

        // Fungsi untuk upload file
        function uploadAttach(action, data, id) {
            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                url: action,
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (data) {
                    if (data.src) {
                        logos[id] = data.src;
                    } else {
                        $.toast({
                            heading: 'Error',
                            text: 'Gagal mengunggah file: respons tidak valid',
                            icon: 'error',
                            position: 'top-right',
                            hideAfter: 5000
                        });
                    }
                },
                error: function (xhr) {
                    $.toast({
                        heading: 'Error',
                        text: 'Gagal mengunggah file',
                        icon: 'error',
                        position: 'top-right',
                        hideAfter: 5000
                    });
                    console.error('Upload error:', xhr.responseText);
                }
            });
        }

        // Fungsi untuk hapus file
        function deleteImage(src) {
            if (!src) return;
            $.ajax({
                type: 'POST',
                url: base_url + 'settings/deletefile',
                data: { src: src },
                cache: false,
                success: function (response) {
                    console.log('File deleted:', response);
                },
                error: function (xhr) {
                    console.error('Delete error:', xhr.responseText);
                }
            });
        }
    });
</script>
