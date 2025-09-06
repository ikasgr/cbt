<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-yellow navbar-light shadow">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>

        <li class="nav-item">
            <span class="nav-link text-dark"><b>TP: <?= isset($tp_active) ? $tp_active->tahun : "Belum di set" ?> Smt: <?= isset($smt_active) ? $smt_active->nama_smt : "Belum di set" ?></b></span>
        </li>
    </ul>


    <ul class='navbar-nav ml-auto'>
        <li class="nav-item mr-3">
            <strong>
                <div id="live-clock" class="text-right"></div>
            </strong>
        </li>

        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <?php $avatar = $profile->foto == null ? base_url() . 'assets/img/user.png' : base_url() . $profile->foto; ?>
                <img src="<?= $avatar ?>" class="user-image img-circle elevation-2" alt="User Image">
                <span style="color:#000"> <i class='fa fa-caret-down'></i></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-dark">
                    <?php $avatar = $profile->foto == null ? base_url() . 'assets/img/user.png' : base_url() . $profile->foto; ?>
                    <div class="image">
                        <img src="<?= $avatar ?>" class="img-circle" alt="User Image" style="height:90px">
                    </div>
                    <p>
                        <?= $profile->nama_lengkap == null ? 'IKASMEDIA' : $profile->nama_lengkap ?>
                        <small class="text-muted">
                            <?= $profile->jabatan == null ? 'Admin' : $profile->jabatan ?>
                        </small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer bg-yellow">
                    <a href="<?= base_url() ?>sekolah" class="btn btn-xs bg-teal float-left"> <span><i class="fas fa-user-cog nav-icon"></i> PROFIL </span></a>
                    <a href="#" class="btn btn-xs bg-red float-right" role="button" onclick="logout()"><span><i class="fas fa-sign-out-alt nav-icon"></i> LOGOUT</span></a>
                </li>
            </ul>
        </li>
    </ul>

</nav>