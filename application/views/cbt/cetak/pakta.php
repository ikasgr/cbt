<div class="content-wrapper bg-white pt-4">
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-6">
					<h1><i class='fa fa-file-pdf'></i> Pakta Integritas</h1>
				</div>
				<div class="col-6">
					<a href="<?= base_url('cbtcetak') ?>" type="button" class="btn btn-sm btn-danger float-right">
						<i class="fas fa-arrow-circle-left"></i><span
							class="d-none d-sm-inline-block ml-1">Kembali</span>
					</a>
				</div>
			</div>
		</div>
	</section>

	<section class="content">
		<div class="container-fluid">
			<div class="card my-shadow">
				<div class="card-header">
					<div class="card-title">
						<h6></h6>
					</div>
					<div id="selector" class="card-tools btn-group">
                        <button class="btn bg-success text-white" id="btn-print">
								<i class="fa fa-print"></i><span class="ml-1">Cetak</span>
							</button>
					</div>
				</div>
				<div class="card-body">
					        <div class="alert bg-info text-center p-2">
                                <div class="text-lg">
                                   Pengawas WAJIB mengisi dan melengkapi bagian yang ditandai warna <span class="badge badge-warning pt-1 pb-1">  KUNING 
                                    </span><br>
                                    Format nama Pakta Integritas : PI_[Nama_Ujian]_[Nama_Pengawas] contoh : <span class="badge badge-success pt-1 pb-1"> PI_USBK_RANDY-IKAS</span>
                                </div>
                             </div>
					
					<div id="print-preview" class="p-4">
						<div style="display: flex; justify-content: center; align-items: center;">
							<div style="width: 21cm; height: 30cm; padding: 1cm" class="border my-shadow">


    							    	<div style="line-height: 1.2; font-family: 'Verdana'; font-size: 12pt; text-align: center;">
    							    	    <b>PAKTA INTEGRITAS</b><br>
    							    	    <b>PETUGAS RUANG <a class="editable bg-yellow"> PENILAIAN TENGAH SEMESTER</a></b><br>
    							    	    <b>TAHUN PELAJARAN <a class="editable bg-yellow"> <?= buat_tanggal(date('Y'))?>/<?= buat_tanggal(date('Y')) + 1?></a></b>
    							    	    
    							    	</div>

								<br>
								<div style="text-align: justify; font-family: 'Verdana'">
									Saya yang bertanda tanggan di bawah ini :
								</div>
								<br>
								<table style="width: 100%;font-family: 'Verdana';">
									<tr>
										<td style="width: 30px;"></td>
										<td style="width: 30%;">
											Nama
										</td>
										<td>:</td>
										<td class="editable bg-yellow"><?=$profile->nama_lengkap == null ? '.................................................................' : $profile->nama_lengkap?></td>
									</tr>
									<tr>
										<td></td>
										<td id="title-ruang">
											NIP
										</td>
										<td>:</td>
										<td class="editable bg-yellow" id="edit-ruang"><?=$profile->nip == null ? '.................................................................' : $profile->nip?></td>
									</tr>
									<tr>
										<td></td>
										<td>Jabatan</td>
										<td>:</td>
										<td class="editable bg-yellow" id="edit-sesi"><?=$profile->jabatan == null ? 'Pengawas Ruang' : $profile->jabatan?></td>
									</tr>
									<tr>
										<td></td>
										<td><i>Bertugas di </i></td>
										<td>:</td>
										<td></td>
									</tr>									
									<tr>
										<td></td>
										<td>
											Provinsi
										</td>
										<td>:</td>
										<td class="editable bg-yellow" >Nusa Tenggara Timur</td>
									</tr>
									<tr>
										<td></td>
										<td>
											Kabupaten/ Kota
										</td>
										<td>:</td>
										<td class="editable bg-yellow" >Kabupaten Kupang</td>
									</tr>
									<tr>
										<td></td>
										<td>
											Nama Sekolah
										</td>
										<td>:</td>
										<td class="editable bg-yellow" id="edit-nama_sekolah"><?= isset($kop->sekolah) ? $kop->sekolah : '' ?></td>
									</tr>
									<tr>
										<td></td>
										<td id="title-ruang">
											Ruang
										</td>
										<td>:</td>
										<td class="editable bg-yellow" >.................................................................</td>
									</tr>
									<tr>
										<td></td>
										<td>Sesi</td>
										<td>:</td>
										<td class="editable bg-yellow" >I (satu)</td>
									</tr>									
									<tr>
										<td></td>
										<td>Tanggal Pelaksanaan</td>
										<td>:</td>
										<td class="editable bg-yellow" >10 s/d 14 <?= buat_tanggal(date('M Y'))?></td>
									</tr>
								</table>
								<br>
							<table border='0' width='90%' align='center'>
                                <tr height='50'>
                                    <td height='30' colspan='4' style="text-align: justify; font-family: 'Verdana'" >Bahwa dalam rangka pelaksanaan <a class="editable bg-yellow">Penilaian Tengah Semester Berbasis Komputer (PTS-BK) </a> Tahun Pelajaran <?= buat_tanggal(date('Y'))?>/<?= buat_tanggal(date('Y')) + 1?>, Dengan ini menyatakan : </td>
                                </tr>
							</table><br>								

                            <table border='0' width='90%' style="text-align: justify; font-family: 'Verdana' ;margin-left:50px">
                                <tr>
                                    <td width='5%'>1. </td>
                                    <td width='95%'>Berkomitmen untuk melaksanakan Ujian secara jujur agar hasilnya kredibel demi meningkatkan mutu pendidikan nasional.</td>

                                </tr>
                                <tr>
                                    <td height='10' width='5%'></td>
									<td height='10' width='95%'></td>
                                </tr>
                                </tr>
                                <tr>
                                    <td width='5%'>2. </td>
                                    <td width='95%'>Sanggup untuk melakukan pekerjaan sebagai Pengawas Ruang Ujian agar berlangsung sesuai dengan ketentuan pada POS Ujian.</td>

                                </tr>
							    <tr>
                                    <td height='10' width='5%'></td>
									<td height='10' width='95%'></td>
                                </tr>
                                <tr>
                                    <td width='5%'>3. </td>
                                    <td width='95%'>Sanggup untuk <b>TIDAK</b> membantu peserta ujian mengerjakan soal ujian, memberi kunci jawaban kepada peserta ujian, maupun memberi kesempatan peserta ujian untuk bekerja sama dalam mengerjakan soal ujian.</td>

                                </tr>
                                <tr>
                                    <td height='10' width='5%'></td>
									<td height='10' width='95%'></td>
                                </tr>								
                                <tr>
                                    <td width='5%'>4. </td>
                                    <td width='95%'>Apabila saya melanggar hal-hal yang telah saya nyatakan dalam Pakta Integritas ini, saya bersedia dikenakan sanksi moral, sanksi administratif, dan dituntut sesuai dengan hukum dan ketentuan peraturan perundang-undangan yang berlaku.</td>

                                </tr>
								</table><br><br>
								
								<table width='100%' height='20' style="text-align: justify; font-family: 'Verdana'">
								<tr>
									<td width='60%'></td>
									<td width='50%' class="editable bg-yellow">
										Nunuanah,&nbsp;  <?= buat_tanggal(date('d M Y'))?>
										<br><?=$profile->jabatan == null ? 'Pengawas Ruang' : $profile->jabatan?>, 
										<br>
										<br>
										<br>
										<br>

										(<u>&nbsp;&nbsp; <?=$profile->nama_lengkap == null ? '...................................' : $profile->nama_lengkap?>&nbsp;&nbsp;</u>)
										<br>NIP. <?=$profile->nip == null ? '...................................' : $profile->nip?></nip>
									</td>
								</tr>
								</table>
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

<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script>
	var oldVal1 = '<?=isset($kop->header_1) ? $kop->header_1 : ""?>';
	var oldVal2 = '<?=isset($kop->header_2) ? $kop->header_2 : ""?>';
	var oldVal3 = '<?=isset($kop->header_3) ? $kop->header_3 : ""?>';
	var oldVal4 = '<?=isset($kop->header_4) ? $kop->header_4 : ""?>';

	var kepsek = '<?=isset($kop->kepsek) ? $kop->kepsek : ""?>';
	var logoKanan = '<?=isset($kop->logo_kanan) ? base_url().$kop->logo_kanan : ""?>';
	var logoKiri = '<?=isset($kop->logo_kiri) ? base_url().$kop->logo_kiri : ""?>';
	var tandatangan = '<?=isset($kop->tanda_tangan) ? base_url().$kop->tanda_tangan : ""?>';

	var printBy = 1;
	var infoData = {};
	var infoSiswa = [];
	var allInfo = '';
	var oldInfo = '';

	var hari = ['Minngu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
	var bulan = ['Jan', 'Feb', 'Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

	var d = new Date();
	var curr_day = d.getDay();
	var curr_date = d.getDate();

	var curr_month = d.getMonth();
	var curr_year = d.getFullYear();

	function buatTanggal() {
		return  hari[curr_day] + ", " + curr_date + "  " + bulan[curr_month] + " " + curr_year;
	}

	function submitKop() {
		$('#set-kop').submit();
	}

	$(document).ready(function () {
		ajaxcsrf();
		var opsiJadwal = $("#jadwal");
		var opsiRuang = $("#ruang");
		var opsiSesi = $("#sesi");
		var opsiKelas = $("#kelas");

		$('.editable').attr('contentEditable',true);

		function loadSiswaRuang(ruang, sesi, jadwal) {
		    var notempty = ruang && sesi && jadwal;
		    if (notempty) {
                $.ajax({
                    type: "GET",
                    url: base_url + "cbtmodcetak/getsiswaruang?ruang=" + ruang + '&sesi=' +sesi + '&jadwal=' + jadwal,
                    success: function (response) {
                        console.log('respon', response);
                        $('#edit-jml-peserta').html('<b>'+response.siswa.length+'</b>');

                        $('#edit-jenis-ujian').html('<b>'+response.info.jadwal.nama_jenis+'</b>');
                        $('#edit-nama-ujian').html('<b>'+response.info.jadwal.nama_jenis+'<b>');
                        $('#edit-waktu-mulai').html('<b>'+response.info.sesi.waktu_mulai.substring(0, 5)+'</b>');
                        $('#edit-waktu-akhir').html('<b>'+response.info.sesi.waktu_akhir.substring(0, 5)+'</b>');
                        $('#edit-mapel').html('<b>'+response.info.jadwal.nama_mapel+'</b>');
                        $('#edit-pengawas').text(response.info.pengawas[0].nama_guru);
                    }
                });
            }
		}

		function loadSiswaKelas(kelas, sesi, jadwal) {
            var notempty = kelas && sesi && jadwal;
            if (notempty) {
                $.ajax({
                    type: "GET",
                    url: base_url + "cbtmodcetak/getsiswakelas?kelas=" + kelas + '&sesi=' + sesi + '&jadwal=' + jadwal,
                    success: function (response) {
                        console.log('respon', response);
                        $('#edit-jml-peserta').html('<b>' + response.siswa.length + '</b>');

                        $('#edit-jenis-ujian').html('<b>' + response.info.jadwal.nama_jenis + '</b>');
                        $('#edit-nama-ujian').text(response.info.jadwal.nama_jenis);
                        $('#edit-waktu-mulai').html('<b>' + response.info.sesi.waktu_mulai.substring(0, 5) + '</b>');
                        $('#edit-waktu-akhir').html('<b>' + response.info.sesi.waktu_akhir.substring(0, 5) + '</b>');
                        $('#edit-mapel').html('<b>' + response.info.jadwal.nama_mapel + '</b>');
                        $('#edit-pengawas').text(+response.info.pengawas[0].nama_guru);
                    }
                });
            }
		}

		opsiJadwal.prepend("<option value='' selected='selected'>Pilih Jadwal</option>");
		opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
		opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");
		opsiKelas.prepend("<option value='' selected='selected'>Pilih Kelas</option>");


		opsiKelas.change(function () {
			$('#edit-ruang').text($("#kelas option:selected").text());
			loadSiswaKelas($(this).val(), opsiSesi.val(), opsiJadwal.val())
		});

		opsiRuang.change(function () {
			$('#edit-ruang').text($("#ruang option:selected").text());
			loadSiswaRuang($(this).val(), opsiSesi.val(), opsiJadwal.val())
		});

		opsiSesi.change(function () {
			$('#edit-sesi').text($("#sesi option:selected").text());
			if (printBy === 1) {
				loadSiswaRuang(opsiRuang.val(), $(this).val(), opsiJadwal.val())
			} else {
				loadSiswaKelas(opsiKelas.val(), $(this).val(), opsiJadwal.val())
			}
		});

		opsiJadwal.change(function () {
			if (printBy === 1) {
				loadSiswaRuang(opsiRuang.val(), opsiSesi.val(), $(this).val())
			} else {
				loadSiswaKelas(opsiKelas.val(), opsiSesi.val(), $(this).val())
			}
		});

		$("#btn-print").click(function () {
			var kosong = printBy===2 ? ($('#kelas').val() === '' || ($('#sesi').val() === '') || ($('#jadwal').val() === '')) : ($('#ruang').val() === '' || ($('#sesi').val() === '') || ($('#jadwal').val() === ''));
			if (kosong) {
				Swal.fire({
					title: "ERROR",
					text: "Isi semua pilihan terlebih dulu",
					icon: "error"
				})
			} else {
                $('#print-preview').print();
			    /*
				var header = '<style>' +
					'@media print {' +
					'    body{' +
					'        width: 21cm;' +
					'        height: 29.7cm;' +
					'        margin: auto;' +
					'   }' +
					'}' +
					//'* { margin:auto; padding:0; line-height:100%; }' +
					'</style>' +
					'</head>' +
					'<body onload="window.print()">';
				var divToPrint = document.getElementById('print-preview');
				var newWin = window.open('', 'Print-Window');
				newWin.document.open();
				newWin.document.write(header + divToPrint.innerHTML + '</body>');
				newWin.document.close();

				//setTimeout(function(){newWin.close();
				//},10);
				*/
			}
		});

	})
</script>
