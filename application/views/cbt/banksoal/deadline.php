<?php
                $tglwaktubank="$setting->ba_waktu";
                $pecah=explode('T',$tglwaktubank);
                $ftw=$pecah[0].' '.$pecah[1];
                $timeshow=date('d-m-Y', strtotime($ftw));
                $timeshow2=date("H:i", strtotime($ftw));
                $nselesai=date("Y-m-d H:i:s", strtotime($ftw));
                $nsekarang=date("Y-m-d H:i:s", strtotime('now'));
                $waktu_selesai=strtotime($nselesai);
                $waktu_sekarang=strtotime($nsekarang);
                $diff = $waktu_selesai - $waktu_sekarang;
                $diff2 = $waktu_sekarang - $waktu_selesai;
                $jam=floor($diff / (60 * 60));
                $menit=$diff - $jam * (60 * 60); 
?>             
                            <div class='small-box' style="background-color: #35e1d3;background-image: linear-gradient(141deg, #9fb8ad 0%, #1fc8db 51%, #35e1d3 75%);color: white;opacity: 0.85;">
									<div class='inner'>
                                        <h4> Waktu pembuatan dan penginputan bank soal akan berakhir dalam :  </h4>
                                            <?php 
											date_default_timezone_set('Asia/Makassar');
											if (number_format($diff,0,",",".")<=0 and number_format($diff2,0,",",".")<=0){?>											
											<?php }
											else if (number_format($diff,0,",",".")>0){?>
											<?php 
											if(floor($jam / 24 ) >= 1)
												{	
													echo '<h3> ' . floor($jam/24).' <span class="badge badge-primary">Hari</span> ' .($jam-(floor($jam/24)*24)).' <span class="badge badge-primary">Jam</span> ' . floor( $menit / 60 ) . ' <span class="badge badge-primary">Menit</span> </h3>';
												}	
											else if (floor($jam / 60 ) < 1 and $jam >= 1 )
												{
													echo '<h3> ' . $jam .  ' <span class="badge bg-yellow">Jam</span> ' . floor( $menit / 60 ) . ' <span class="badge bg-yellow">Menit</span> </h3>';
												} 
											else
												{
													echo '<h3> ' . floor( $menit / 60 ) . ' <span class="badge bg-red">Menit lagi...!</span></h3>';
												}
											}
											else{?>
											<h3><label class="badge bg-red">SUDAH TUTUP</label></h3>
											<?php }
											?>

										<p> *) Pada tanggal <label class="badge bg-gray"> <?=$timeshow?> Jam <?=$timeshow2?> WITA </label>  menu TAMBAH dan EDIT bank soal akan otomatis dinonaktifkan. </p>
									</div>
									<div class='icon'>
										<i class='fa fa-spinner fa-spin'></i>
									</div>
							</div>   