<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>LOGIN PENGAWAS RUANG - <?= $setting->sekolah ?> BY IKASMEDIA</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <?php $logo_app = $setting->logo_kiri == null ? base_url().'assets/img/favicon.png' : base_url().$setting->logo_kiri; ?>
    <link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">
		<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<meta name="description" content="Aplikasi Asesmen Berbasis Komputer membantu sekolah dan siswa dalam pelaksanaan penilaian di sekolah."> 
	<meta name="keyword" content="ANBK, Ujian, Ujian Nasional, Ulangan Harian, Ulangan Semester, Mid Semester, Asesmen Nasional, Ujian Sekolah">
	<meta name="google" content="nositelinkssearchbox" />
	<meta name="robots" content="index, follow">
	
	<link rel="stylesheet" href="<?=base_url()?>assets/_login/assets/front/css/theme.fonts.css">
    <link rel="icon" href="<?=base_url()?>assets/_login/assets/img/logo.png">
    <link rel="stylesheet" href="<?=base_url()?>assets/_login/assets/login/style.css"/>
    <link rel="stylesheet" type="<?=base_url()?>assets/_login/text/css" href="assets/js/alert/alert.css">
	<link rel="stylesheet" href="<?=base_url()?>assets/plugins/fontawesome-free/css/all.min.css">
	<link rel="stylesheet" href="<?=base_url()?>assets/app/css/mystyle.css">
	<script src="<?=base_url()?>assets/plugins/jquery/jquery.min.js"></script>
		<style>
            .btn-group button {
                background-color: #04AA6D;
                border: 1px solid green;
                color: white;
                padding: 10px 24px;
                cursor: pointer;
                float: left;
            }

            .btn-group button:not(:last-child) {
                border-right: none;
            }

            .btn-group:after {
                content: "";
                clear: both;
                display: table;
            }

            .btn-group button:hover {
                background-color: #3e8e41;
            }

    .preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      background-color: #fff;
    }
    .preloader .loading {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%,-50%);
      font: 14px arial;
    }
    
    
    </style>
<script>
    $(document).ready(function(){
      $(".preloader").fadeOut('slow');
    })
</script>
</head>
<body>

<div class="preloader">
      <div class="loading">
	  	<img src="<?=base_url()?>assets/img/ajax-loader.gif" width="80px">
		<br>
		<br>
        <p id="infoMessage" > <?php echo $message; ?></p>
      </div>
    </div> 
