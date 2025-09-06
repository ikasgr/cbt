<style>

	/* STRUCTURE */
	
	.no-close .ui-dialog-titlebar-close {
		display: none;
	}	

</style>
<main>

<header class="masthead">
	<?php $this->load->view('members/siswa/templates/top'); ?>
</header>

<div class="container-fluid mt-3 fadeInDown">
    <div class="main-content">
        <div class="konfirmasi-form">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="content logo-bg">
                    <div id="konfirm_data" style="display:block; -moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;" unselectable="on" onselectstart="return false;">
                       <h3 class="box-title">Konfirmasi Data Peserta</h3>
                            <form class="text-left" action="" method="post" id="formKonfirmasi">
                                <div class="mt-1">
                                    <label class="m-0 p-0"><small style="font-size:11px"><b>Mata Pelajaran</b></small></label> 
                                    <br /><?= htmlspecialchars($bank->nama_mapel) ?>					
                                </div>
                                <div class="mt-1">
                                    <label class="m-0 p-0"><small style="font-size:11px"><b>Nama Peserta</b></small></label> 
                                    <br /><?= htmlspecialchars($siswa->nama) ?>					
                                </div>					
                                <div class="mt-1">
                                    <label class="m-0 p-0"><small style="font-size:11px"><b>NISN</b></small></label> 
                                    <br /><?= htmlspecialchars($siswa->nisn) ?>					
                                </div>
                                <div class="mt-1 d-none">
                                    <label class="m-0 p-0"><small style="font-size:11px"><b>Jenis Kelamin</b></small></label> 
                                    <br /><?= htmlspecialchars($siswa->jenis_kelamin ?? 'Laki-Laki') ?>					
                                </div>
                                <div class="mt-1">
                                    <label class="m-0 p-0"><small style="font-size:11px"><b>Kelas</b></small></label> 
                                    <br /><?= htmlspecialchars($siswa->kode_kelas) ?>					
                                </div>
                                <div class="d-flex justify-content-center">
                                        <a href="<?= base_url() ?>siswa/cbt" class="btn btn-danger btn-round mt-4 mr-5"><i class="fa fa-arrow-left" aria-label="Batalkan konfirmasi data"></i> Kembali</a>
                                        <button type="button" class="btn btn-primary btn-round mt-4" onclick="konfirmasiData()" aria-label="Lanjutkan konfirmasi data"> Lanjutkan <i class="fa fa-arrow-right"></i> </button>
                                </div>
                            </form>
                    </div>                        
                    
                   <div id="konfirm_test" style="display:none; -moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;" unselectable="on" onselectstart="return false;">
                        <div class="row fadeInDown" >
                            <!-- Form Section -->
                            <div class="col-md-8">
                                <div class="box border-0">
                                    <div class="box-header">
                                        <h3 class="box-title">Konfirmasi Ujian</h3>
                                    </div>
                                    <div class="box-body">
                                    
                                        <?php if ($support && $valid): ?>
                                            <?php
                                            // Proses kelas bank
													$jk = json_decode(json_encode($bank->bank_kelas));
													$jumlahKelas = json_decode(json_encode(unserialize($jk)));

													$kelasbank = '';
													$no = 1;
													foreach ($jumlahKelas as $j) {
														foreach ($kelas as $k) {
															if ($j->kelas_id === $k->id_kelas) {
																if ($no > 1) {
																	$kelasbank .= ', ';
																}
																$kelasbank .= $k->nama_kelas;
																$no++;
															}
														}
													}

													?>

													<?= form_open('', array('id' => 'konfir')) ?>
													<input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
													<input type="hidden" name="jadwal" value="<?= $bank->id_jadwal ?>">
													<input type="hidden" name="bank" value="<?= $bank->id_bank ?>">
											
                                                    <div class="col-12 col-md-6 mt-3">
                                                        <label class="m-0 p-0 mb-2"><small style="font-size:12px"><b>Nama Peserta</b></small></label>
                                                        <input type="text" class="form-control field-xs" placeholder="Ketik ulang nama Anda disini" required="true" name="nama_peserta" id="nama_peserta" value="" oninvalid="this.setCustomValidity('Silakan ketikkan nama Anda disini')" oninput="setCustomValidity('')">
                                                        <div id="nama_peserta_error" style="color: red; font-size: 12px;"></div>
                                                    </div>
                                                    
                                                    <div class="col-12 col-md-6 mt-3">
                                                        <label class="m-0 p-0 mb-2"><small style="font-size:12px"><b>Jenis Kelamin</b></small></label>
                                                           <select id="jenis_kelamin" name="jenis_kelamin" style="background-color:#eee;border:0px;margin-left:5px ;border-radius:5px; width: 100%;"required="true" value="" oninvalid="this.setCustomValidity('Silakan Isi Jenis Kelamin Anda')" oninput="setCustomValidity('')">
                												<option value=""> ---Jenis Kelamin--- </option>
                												<option value="L"> Laki-Laki </option>
                												<option value="P"> Perempuan </option>
                                                            </select>
                                                    </div>
                                                    													
													<div class="col-12 col-md-12 mt-3">
														<label class="m-0 p-0"><small style="font-size:12px"><b>Tanggal Lahir</b></small></label>
													
														<div class="input-group mt-2">
															<select class="form-group mr-2" id="tgl" name="tgl" style="background-color:#eee;border:0px;margin-right:4px;margin-left:5px ;border-radius:5px; width: 28%;" required="true" value="" oninvalid="this.setCustomValidity('Silakan Isi tanggal lahir Anda')" oninput="setCustomValidity('')">
                                                                <option value="">Hari</option>
                                                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                                                    <option value="<?= sprintf('%02d', $i) ?>"><?= sprintf('%02d', $i) ?></option>
                                                                <?php endfor; ?>
															</select>
															<select class="form-group mr-2" id="bulan" name="bulan" style="background-color:#eee;border:0px;margin-right:4px;border-radius:5px; width: 28%;" required="true" value="" oninvalid="this.setCustomValidity('Silakan isi bulan lahir Anda')" oninput="setCustomValidity('')">
                                                                <option value="">Bulan</option>
                                                                <?php
                                                                $bulan = [
                                                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                                                ];
                                                                foreach ($bulan as $key => $value):
                                                                ?>
                                                                    <option value="<?= $key ?>"><?= $value ?></option>
                                                                <?php endforeach; ?>
															</select>
															<select class="form-group mr-2" id="tahun" name="tahun" style="background-color:#eee;border:0px;margin-right:4px;border-radius:5px; width: 28%;" required="true" value="" oninvalid="this.setCustomValidity('Silakan isi tahun lahir Anda')" oninput="setCustomValidity('')">
                                                                <option value="">Tahun</option>
                                                                <?php for ($i = 1990; $i <= 2020; $i++): ?>
                                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                                <?php endfor; ?>
															</select>
													
														</div>
													</div>
													<?php if ($bank->token === '1') : ?>
														<div class="mt-3 mb-5">
															<div class="col-sm-6 col-md-6 col-6">
																<label class="m-0 p-0"><small style="font-size:12px"><b>Token</b></small></label>
																<input type="text" class="form-control field-xs" placeholder="Ketikkan token di sini" id="input-token" name="token" required="true" value="" oninvalid="this.setCustomValidity('Silakan Isi TOKEN UJIAN')" oninput="setCustomValidity('')">
															</div>
														</div>
													<?php endif; ?>
											</div>
										</div>
										
									</div>


                                    <div class="col-md-4">
                                        <div class="box border-0">
                                            <div class="box-body">
                                                <h3>Pernyataan Ujian</h3>
                                                <p>
                                                    "Saya bersedia menandatangani pernyataan ini dan berjanji akan mengerjakan dengan
                                                    <span class="badge bg-info">SERIUS</span>,
                                                    <span class="badge bg-info">JUJUR</span> dan
                                                    <span class="badge bg-info">BERTANGGUNG-JAWAB</span>
                                                    menjaga kerahasiaan soal dan integritas pelaksanaan Ujian. Jika saya melanggar, saya siap untuk dikenakan sanksi sesuai peraturan yang berlaku."
                                                </p>
    
                                                <div class="signature-pad mt-3" id="signature-pad">
                                                    <div class="text-center">
                                                        <canvas class="signature-pad bg-white border border-primary" style="border-width: 3px;" width="300" height="150"></canvas>
                                                    </div>
                                                    <textarea style="display: none;" id="ttdku" name="ttdku"></textarea>
                                                    <div class="d-flex justify-content-center mt-2">
                                                        <button type="button" data-action="clear" class="btn btn-warning btn-sm w-50"><i class="fas fa-trash"></i> Hapus</button>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a href="<?= base_url('siswa/cbt') ?>" class="btn btn-danger btn-round mt-4  mr-2"><i class="fas fa-times"></i> Batal</a>
                                                    <button type="submit" form="konfir" class="btn btn-success btn-round mt-4"><i class="fas fa-check"></i> Mulai</button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>

									<?= form_close(); ?>

									<?php elseif (!$valid) : ?>
									<div class="alert alert-default-danger text-center p-5">
										<h2><i class="icon fas fa-ban"></i> WARNING..!!</h2>
										<div class="text-lg">
											Ujian tidak bisa dilanjutkan, Silahkan minta RESET
											<br>
											Hubungi Pengawas atau Proktor.
										</div>
                                        <small>*) Refresh halaman ini jika sudah diizinkan</small>
									</div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="" class="btn btn-success btn-round mt-4 mr-2" role="button"><i class='fa fa-check'></i> Refresh</a>
                                        <a href="<?= base_url('siswa/cbt') ?>" class="btn btn-danger btn-round mt-4 mr-2" role="button"><i class='fa fa-times'></i> Kembali</a>
                                    </div>
                                
                                <?php elseif (!$support): ?>
									<div class="alert alert-default-danger text-center p-5">
										<h2><i class="icon fas fa-ban"></i> WARNING..!!</h2>
										<div class="text-lg">
											Browser yang digunakan tidak mendukung
											<br>
											silahkan gunakan browser chrome atau yang lain dengan versi terbaru
										</div>
									</div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="" class="btn btn-success btn-round mt-4 mr-2" role="button"><i class='fa fa-check'></i> Refresh</a>
                                        <a href="<?= base_url('siswa/cbt') ?>" class="btn btn-danger btn-round mt-4 mr-2" role="button"><i class='fa fa-times'></i> Kembali</a>
                                    </div>									
                                <?php endif; ?>
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
    
	<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
	
		<script>
			$('#konfir').submit(function(e) {
				e.stopPropagation();
				e.preventDefault();
				
        // Validasi nama peserta
                var namaPesertaInput = $('#nama_peserta').val().trim();
                var namaSiswa = "<?= addslashes($siswa->nama) ?>".trim();
        
                if (namaPesertaInput !== namaSiswa) {
					swal.fire({
						"title": "Error",
						"html": "Nama peserta tidak sesuai! Harus sama dengan " + namaSiswa,
						"icon": "error"
					});                    
                    return; // Hentikan proses jika nama tidak sesuai
                }				

				swal.fire({
					title: "Membuka Soal",
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
				console.log($(this).serialize());
				var jadwal = $(this).find('input[name="jadwal"]').val();
				$.ajax({
					type: 'POST',
					url: base_url + 'siswa/validasisiswa',
					data: $(this).serialize(),
					success: function(data) {
						console.log(data);
						// jika menggunakan token, cek token
						if (data.token === true) {
							// token ok
							// cek browser dulu
							
							if (signaturePad.isEmpty()) {
								// browser tidak support
								// siswa stop disini
								swal.fire({
									"title": "Error",
									"html": "TANDA TANGAN KOSONG!<br> Silakan isi tanda tangan digital <br>  pada kotak yang tersedia",
									"icon": "error"
								});
							} else {
								// browser OK
								// cek izin ujian
								if (data.izinkan === true) {
									// diizinkan
									// cek sisa waktu
									if (data.ada_waktu === true) {
										// masih ada waktu
										// cek apakah ada soal?
										if (data.jml_soal > 0) {
											// ada soal
											// siswa masuk halaman ujian
											var data = signaturePad.toDataURL('image/png');
                                            $('#ttdku').html(data);
											window.location.href = base_url + 'siswa/penilaian/' + jadwal;
										} else {
											// soal belum dibuat
											swal.fire({
												"title": "Error",
												"html": "Tidak ada soal ujian<br>Hubungi Pengawas/Proktor<br>  Error 004",
												"icon": "error"
											});
										}
									} else {
										// siswa logout ditengah ujian dan tidak melanjutkan sampai waktu ujian habis
										// admin harus reset waktu
										swal.fire({
											"title": "Error",
											"html": data.warn.msg + "<br>Hubungi Pengawas/Proktor<br> Error 003",
											"icon": "error"
										});
									}
								} else {
									// ditengah ujian, siswa ganti hape/komputer
									// siswa tidak diizinkan ujian
									// admin perlu reset izin
									swal.fire({
										"title": "Error",
										"html": "Anda sedang mengerjakan ujian di perangkat lain<br>Hubungi Pengawas/Proktor<br> Error 002",
										"icon": "error"
									});
								}
							}
						} else {
							// token salah, atau token tidak dibuat oleh admin
							swal.fire({
								"title": "Error",
								"html": "TOKEN salah!<br>Hubungi Pengawas/Proktor<br> Error 001",
								"icon": "error"
							});
						}
					},
					error: function(xhr, error, status) {
						swal.fire({
							"title": "Error",
							"html": "Coba kembali ke beranda, lalu ulangi lagi<br>  Error 006",
							"icon": "error"
						});
						console.log(xhr.responseText);
					}
				});
			});

			console.log('mnt', getMinutes('2023-01-30 11:30:30'));

			function getMinutes(d) {
				var startTime = new Date(d);
				var endTime = new Date();
				endTime.setHours(endTime.getHours() - startTime.getHours());
				endTime.setMinutes(endTime.getMinutes() - startTime.getMinutes());
				endTime.setSeconds(endTime.getSeconds() - startTime.getSeconds());

				return {
					h: endTime.getHours(),
					m: endTime.getMinutes(),
					s: endTime.getSeconds()
				}
			}
		</script>
		    <script>		
        localStorage.clear();
        var peserta = '';
        if (peserta == false) {
            var wrapper = document.getElementById("signature-pad"),
                clearButton = wrapper.querySelector("[data-action=clear]"),
                saveButton = wrapper.querySelector("[data-action=save]"),
                canvas = wrapper.querySelector("canvas"),
                signaturePad;
            signaturePad = new SignaturePad(canvas);
    
            clearButton.addEventListener("click", function(event) {
                signaturePad.clear();
            });
        }
    </script>
    
    <script>
    // Simpan nama siswa dari PHP ke JavaScript
    var namaSiswa = "<?= addslashes($siswa->nama) ?>";

    // Fungsi untuk validasi nama peserta
    function validateNamaPeserta() {
        var namaPesertaInput = document.getElementById('nama_peserta').value.trim();
        var errorMessage = document.getElementById('nama_peserta_error');

        if (namaPesertaInput !== namaSiswa) {
            errorMessage.textContent = 'Nama peserta tidak sesuai data! ';
            return false;
        } else {
            errorMessage.textContent = '';
            return true;
        }
    }

    // Validasi saat input berubah (real-time)
    document.getElementById('nama_peserta').addEventListener('input', validateNamaPeserta);
    
    function konfirmasiData() {
        Swal.fire({
            title: 'Konfirmasi Data',
            text: 'Apakah data Anda sudah sesuai? ',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal',
            focusConfirm: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('#konfirm_data').css('display', 'none');
                $('#konfirm_test').css('display', 'block');
            }
        });
    }

    </script>

	</main>