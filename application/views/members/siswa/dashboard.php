	<!-- ikasmedia mod anbk -->

	<style>
			.no-close .ui-dialog-titlebar-close {
				display: none;
			}
	</style>
<main>
    <!-- Header Section -->
    <header class="masthead">
        <?php $this->load->view('members/siswa/templates/top'); ?>
    </header>

    <!-- Main Content -->
    <div class="container-fluid fadeInDown" style="margin-top: 10px;">
        <div class="main-content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="content logo-bg">
                            <!-- Breadcrumb Navigation -->
                            <div class="row">
                                <div class="col-12">
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
                                            <li class="breadcrumb-item active" aria-current="page">Selamat Datang</li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>

                            <!-- Card Grid -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-lg-3 col-sm-6 col-6 mb-3">
                                            <div class="card mb-3 shadow mx-auto" style="width: 10rem;">
                                                <img class="card-img-top" src="<?= base_url() ?>assets/app/img/absensi.png" alt="Absensi">
                                                <div class="card-body text-center">
                                                    <a href="<?= base_url() ?>siswa/kehadiran" class="btn btn-primary btn-block">Absensi</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-6 mb-3">
                                            <div class="card mb-3 shadow mx-auto" style="width: 10rem;">
                                                <img class="card-img-top" src="<?= base_url() ?>assets/app/img/exam.png" alt="Asesmen">
                                                <div class="card-body text-center">
                                                    <a href="<?= base_url() ?>siswa/cbt" class="btn btn-primary btn-block">Asesmen</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-6 mb-3">
                                            <div class="card mb-3 shadow mx-auto" style="width: 10rem;">
                                                <img class="card-img-top" src="<?= base_url() ?>assets/app/img/nilai.png" alt="Nilai">
                                                <div class="card-body text-center">
                                                    <a href="<?= base_url() ?>siswa/hasil" class="btn btn-primary btn-block">Nilai</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-6 col-6 mb-3">
                                            <div class="card mb-3 shadow mx-auto" style="width: 10rem;">
                                                <img class="card-img-top" src="<?= base_url() ?>assets/app/img/catatan.png" alt="Catatan">
                                                <div class="card-body text-center">
                                                    <a href="<?= base_url() ?>siswa/catatan" class="btn btn-primary btn-block">Catatan</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Welcome Card -->
                                <div class="col-12">
                                    <div class="card bg-dark text-white shadow-lg rounded-lg">
                                        <img class="card-img" src="<?= base_url() ?>uploads/settings/banner1.jpg" alt="Welcome">

                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-12 mt-3">
                                    <div class="d-flex justify-content-center">
                                        <div class="col-4 col-md-3">
                                            <a href="<?= base_url() ?>dashboard" class="btn btn-info btn-block"><i class="fas fa-undo"></i> Refresh</a>
                                        </div>
                                        <div class="col-4 col-md-3">
                                            <a href="#" class="btn btn-danger btn-block" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="copyright">COPYRIGHT © <?= date('Y'); ?> | <?= htmlspecialchars($setting->sekolah); ?></div>
                </div>
            </div>
        </div>
    </footer>
</main>
