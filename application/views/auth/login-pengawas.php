
 <main>
 <div class="container">
            <div class="forms-container">
                <div class="signin-signup">
                    <?= form_open("loginpengawas/cek_login", array('id' => 'login', 'class' => 'sign-in-form')); ?>
                        <h2 class="title">Login Pengawas</h2>
                        <div class="input-field">
                            <i class="fas fa-user"></i>
                            <?= form_input($identity, '', 'required'); ?>
                        </div>
                        <div class="input-field" for="password">
                            <i class="fas fa-lock"></i>
                            <?= form_input($password, '', 'required'); ?>

                        </div>

                        <div class="btn-group" >
                            <button type="submit" id="btn-login" class="btn solid">
                                <i class="fa fa-check-circle"></i>
                                Masuk Aplikasi 
                            </button>
                        </div>

                        <p>
                            <br><br>
                            <p href="./" id="lupapassword" style="color:black"> &copy; 2023 - <?= $setting->sekolah ?> </p>
                            

                        </p>
                    <?= form_close(); ?>
                    
                    <form class="sign-up-form">
                        <img src="<?=base_url()?>assets/_login/assets/img/background.png" style="height:120px">
                        <table class="table table-striped">
                            <br>
                            <tbody>
                            <tr>
                            <td width="25">1.</td>
                            <td width="724">Pengawas Login ke aplikasi menggunakan Username dan Password yang diberikan Panitia.</td>
                            </tr>
                            <tr>
                            <td>
                            <p>2.</p>
                            </td>
                            <td>Pengawas memastikan jadwal yang di ujikan sudah muncul.</td>
                            </tr>
                            <tr>
                            <td>3.</td>
                            <td>Pengawas Mengisi pakta integritas dan menguploadnya</td>
                            </tr>
                            <tr>
                            <td>
                            <p>4.</p>
                            </td>
                            <td>Pengawas mengisi berita acara dan menguploadnya.</td>
                            </tr>
                            <tr>
                            <td>
                            <p>5.</p>
                            </td>
                            <td>Pengawas mengedarkan absen dan memastikan peserta yang hadir menandatangani absen dengan benar.</td>
                            </tr>
                            </tbody>
                            </table>
                            <br>
                        <div class="btn-group">
                            <button onclick="window.location.href='./'" class="btn solid">
                                <i class="fa fa-save"></i>
                                Kembali ke Halaman Login 
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
            <div class="panels-container ">
                <div class="panel left-panel">
                    <div class="content">
                        <h3>
                            <img src="<?=base_url()?>assets/_login/assets/img/background.png" style="height:100px">
                        </h3>
                        <p>Selamat Datang,<br> Pengawas Penilaian Akhir Tahun (PAT)<br> <?= $setting->sekolah ?><br> </p>
                        <button class="btn transparent" id="sign-up-btn">PANDUAN </button>
                        <button class="btn transparent" onclick="window.location.href='../'">PORTAL UTAMA </button>
                    </div>
                    <img src="<?=base_url()?>assets/_login/assets/login/img/login-bg.png" class="image" alt="" style="width:600px"/>
                </div>
                <div class="panel right-panel">
                    <div class="content">
                        <h3>Selamat Mengikuti Ujian !</h3>
                        <p>Mari Kita Jaga Keamanan dan Integritas Pelaksanaan Ujian. </p>
                    </div>
                    <img src="<?=base_url()?>assets/_login/assets/login/img/panduan.png" class="image" alt="" style="width:650px"/>
                </div>
            </div>
        </div>	
</main>
	<script src="<?= base_url() ?>assets/app/js/auth/login.js"></script>
    <script type="text/javascript">
	    let base_url = '<?=base_url();?>'


        $(document).off('click', '#btn-login').on('click', '#btn-login', function(event, messages) {
            $(".preloader").show();
            $(".preloader").fadeOut(5000);

    });
    </script>
    <script type="text/javascript">


