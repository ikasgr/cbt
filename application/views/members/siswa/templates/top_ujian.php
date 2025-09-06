    <div class="container-fluid">
        <div class="row no-gutters">
            <div class="col-md-8 col-sm-8 col-8">
                <img src="<?=base_url()?>assets/front_assets/images/logo.png" id="img-logo">
            </div>
            <div class="col-md-4 col-sm-4 col-4">
                <div class="user-header-info">
                    <div class="d-inline float-right user-header-thumb" id="img-account">
                        <?php
                        if (!file_exists(FCPATH.$siswa->foto) || $siswa->foto == ""): ?>
                            <?php if ($siswa->jenis_kelamin == 'L'): ?>
                                <img src="<?= base_url() ?>/assets/img/siswa_icon.png"
                                    class="user-thumb-wrapper" alt="User avatar">
                            <?php else: ?>
                                <img src="<?= base_url() ?>/assets/img/siswa_icon.png"
                                   class="user-thumb-wrapper" alt="User avatar">
                                <?php endif; ?>
                            <?php else: ?>
                                <img src="<?= base_url() ?><?= $siswa->foto ?>"
                                    class="user-thumb-wrapper" alt="User avatar">
                            <?php endif; ?>                       
                        </div>
                    <div class="d-inline float-right">
                        <div class="user-header-wrapper">
                            <div><?= $siswa->nama ?></div>
						    <div><small><?= $siswa->nisn ?> - <?= $siswa->kode_kelas ?></small></div>
                            <a href="#"  class="btn doblockui" id="btn-kembali-selesai" role="button" onclick="BlokirKeluar()">Logout</a>
                        </div>
                    </div>
                </div>
            </div>			
        </div>
    </div>
