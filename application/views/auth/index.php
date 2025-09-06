<script>
    async function restrictToBrave() {
        const isBrave = navigator.brave && (await navigator.brave.isBrave());
        if (isBrave) {
            document.body.innerHTML = `
    
                        <div class="container-fluid text-white" style="background:#00238f; height:185px; position:relative; background-image: url('<?= base_url() ?>/assets/front_assets/images/header-bg.png'); background-size: contain; background-repeat: no-repeat; background-position: left;">
                            <div class="row">
                                <div class="col-md-12 pt-5">
                                    <img class="img-fluid" style="position:absolute; left:50%; transform: translateX(-50%);" src="<?= base_url() ?>/assets/front_assets/images/logo.png" alt="DISDIK NTT">
                                </div>
                            </div>
                        </div>
                        <div class="wrapper" style="margin-top:-65px;">
                            <div id="formContent" style="background-image: url(<?= base_url() ?>/assets/front_assets/images/logo-w.png'); background-size: 300px; background-repeat: no-repeat; background-position: top right;">
                                <div class="fadeIn text-left p-5">
                                     <div><h3>Selamat Datang</h3></div>
                                     <div><small>Silakan login dengan menggunakan username dan password yang anda miliki</small></div>
                                 <div id="infoMessage"> <?php echo $message; ?> </div>
	                            <?= form_open("auth/cek_login", array('id' => 'login', 'class' => 'login-form')); ?>
    							<div class="input-group mt-4 mb-3" style="padding-top:10px">
    								<div class="input-group-prepend">
    									<span class="input-group-text" style="border:0px;background:#fff"><i class="fa fa-user-circle"></i></span>
    								</div>
    							
    								<input type="text" class="form-control" placeholder="Username" style="border-radius:10px;" name="identity" id="identity" autocomplete="false" required="true" value="" oninvalid="this.setCustomValidity('Silakan masukan username Anda')" oninput="setCustomValidity('')">
    							</div>
    							<div class="input-group mt-3 mb-3">
    								<div class="input-group-prepend">
    									<span class="input-group-text" style="border:0px;background:#fff"><i class="fa fa-lock"></i></span>
    								</div>
    								<input type="password" class="form-control" style="border-radius:10px;" placeholder="Password" name="password" id="password" autocomplete="false" required="true" value="" oninvalid="this.setCustomValidity('Silakan masukan password Anda')" oninput="setCustomValidity('')">
    								<div class="input-group-prepend">
    									<span class="input-group-text" style="border:0px;background:#fff;padding-right:5px;padding-left:5px;margin-left:2px;border-radius:8px" onCLick="showPassword()" id="btn-eye"><i class="btn-eye fa fa-eye"></i></span>
    								</div>
    							</div>
        						<div class="icheck-cyan" hidden>
                                    <input type='checkbox' id="cbt-only" name='cbt-only'
                                         value='0' />
                                    <label for="cbt-only">Login CBT</label>
                                </div>							
							    <textarea class="d-none" id="data_uri" name="data_uri"></textarea>

            				<div class="row">
            					<div class="col-12" style="margin-top: 30px;">
            						<?= form_submit('submit', lang('login_submit_btn'), array('id' => 'submit', 'class' => 'btn-block btn btn-info', 'style' => 'font-size: 1rem')) ; ?>
            					</div>
            				</div>
            				
            				<?= form_close(); ?>
            					<!--	<div class="row">
            							<div class="col-12" style="margin-top: 10px;">
            								<a href='../' class='btn-block btn btn-danger' style="font-size: 1rem"><i class='fa fa-back' ></i> Kembali </a>
            							</div>
            						</div>	-->
                        	<div style="position:absolute;top:0px;left:0px;right:0px;bottom:0px;background:#fff;opacity:0.5;z-index:99999;display:none" id="loader">
                        		<img class="loader" src="<?= base_url() ?>/assets/front_assets/images/spinner.gif">
                        	</div>
                        </div>
                      </div> 
                    </div>
                        <footer class="container text-center py-3">
                            <small>COPYRIGHT © ${new Date().getFullYear()} | IKASMEDIA</small>
                        </footer>
            `;
        } else {
                 document.body.innerHTML = `
                        <div class="container-fluid text-white" style="background:#00238f; height:185px; position:relative; background-image: url('<?= base_url() ?>/assets/front_assets/images/header-bg.png'); background-size: contain; background-repeat: no-repeat; background-position: left;">
                            <div class="row">
                                <div class="col-md-12 pt-5">
                                    <img class="img-fluid" style="position:absolute; left:50%; transform: translateX(-50%);" src="<?= base_url() ?>/assets/front_assets/images/logo.png" alt="DISDIK NTT">
                                </div>
                            </div>
                        </div>
                        <div class="wrapper" style="margin-top:-65px;">
                            <div id="formContent" style="background-image: url(<?= base_url() ?>/assets/front_assets/images/logo-w.png'); background-size: 300px; background-repeat: no-repeat; background-position: top right;">
                                <div class="fadeIn text-left p-5">
                                    <h3>Browser anda tidak memiliki akses!</h3>
                                    <p>Silakan gunakan Browser Brave unduh dari <a href="https://brave.com" target="_blank">sini</a>.</p>
                                </div>
                            </div>
                        </div>
                        <footer class="container text-center py-3">
                            <small>COPYRIGHT © ${new Date().getFullYear()} | IKASMEDIA</small>
                        </footer>
                    `;
                }       
        
    }
    restrictToBrave();
</script>

<style>
	/* STRUCTURE */
	.loader {
	  margin: 0;
	  position: absolute;
	  top: 50%;
	  left: 50%;
	  -ms-transform: translate(-50%, -50%);
	  transform: translate(-50%, -50%);
	}

	.wrapper {
	  display: flex;
	  align-items: center;
	  flex-direction: column; 
	  justify-content: center;
	  width: 100%;
	  min-height: 100%;
	  padding: 20px;
	  margin-top:-80px;
	}

	#formContent {
	  -webkit-border-radius: 10px 10px 10px 10px;
	  border-radius: 10px 10px 10px 10px;
	  background: #fff;
	  padding: 30px;
	  width: 90%;
	  max-width: 450px;
	  position: relative;
	  padding: 0px;
	  -webkit-box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
	  box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
	  text-align: center;
	}

	


	/* TABS */

	h2.inactive {
	  color: #cccccc;
	}

	h2.active {
	  color: #0d0d0d;
	  border-bottom: 2px solid #5fbae9;
	}
	
	input[type=text] {
	  border: none;
	  color: #0d0d0d;
	  text-decoration: none;
	  display: inline-block;
	  font-size: 16px;
	  border-radius:0px;
	  border-bottom:2px solid #eee;
	}
	input[type=password] {
	  border: none;
	  color: #0d0d0d;
	  text-decoration: none;
	  display: inline-block;
	  font-size: 16px;
	  border-radius:0px;
	  border-bottom:2px solid #eee;
	}

	/* ANIMATIONS */

	/* Simple CSS3 Fade-in Animation */
	@-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
	@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
	@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

	.fadeIn {
	  opacity:0;
	  -webkit-animation:fadeIn ease-in 1;
	  -moz-animation:fadeIn ease-in 1;
	  animation:fadeIn ease-in 1;

	  -webkit-animation-fill-mode:forwards;
	  -moz-animation-fill-mode:forwards;
	  animation-fill-mode:forwards;

	  -webkit-animation-duration:1s;
	  -moz-animation-duration:1s;
	  animation-duration:1s;
	}

	.fadeIn.first {
	  -webkit-animation-delay: 0.4s;
	  -moz-animation-delay: 0.4s;
	  animation-delay: 0.4s;
	}

	.fadeIn.second {
	  -webkit-animation-delay: 0.6s;
	  -moz-animation-delay: 0.6s;
	  animation-delay: 0.6s;
	}

	.fadeIn.third {
	  -webkit-animation-delay: 0.8s;
	  -moz-animation-delay: 0.8s;
	  animation-delay: 0.8s;
	}

	.fadeIn.fourth {
	  -webkit-animation-delay: 1s;
	  -moz-animation-delay: 1s;
	  animation-delay: 1s;
	}

	/* Simple CSS3 Fade-in Animation */
	.underlineHover:after {
	  display: block;
	  left: 0;
	  bottom: -10px;
	  width: 0;
	  height: 2px;
	  background-color: #56baed;
	  content: "";
	  transition: width 0.2s;
	}

	.underlineHover:hover {
	  color: #0d0d0d;
	}

	.underlineHover:hover:after{
	  width: 100%;
	}
</style>

<script src="<?= base_url() ?>/assets/app/js/jquery.backstretch.js"></script>

<script type="text/javascript">
    let base_url = '<?=base_url();?>';

    $(document).ready(function(){
        $('#myCarousel').carousel({
            interval: 1000 * 2,
            pause: 'none'
        });

        $('form#login input').on('change', function(){
            $(this).parent().removeClass('has-error');
            $(this).next().next().text('');
        });

        $('form#login').on('submit', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();

            var infobox = $('#infoMessage');
            infobox.addClass('alert alert-info mt-3 text-center').text('Checking...');

            var btnsubmit = $('#submit');
            btnsubmit.attr('disabled', 'disabled').val('Wait...');

            const arrForm = $(this).serializeArray()
            const cbtOnly = arrForm.find(function (obj) {
                return obj.name === 'cbt-only'
            })
            localStorage.setItem('garudaCBT.login', cbtOnly !== undefined ? '1' : '0')

            $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(data){
                infobox.removeAttr('class').text('');
                btnsubmit.removeAttr('disabled').val('Login');
                console.log('login', data);
                if(data.status){
                    infobox.addClass('alert alert-success mt-3 text-center').text('Login Sukses');

                    const isLogin = localStorage.getItem('garudaCBT.login')
                    const isCbtMode = isLogin ? isLogin === '1' : false
                    let go = base_url + data.url;
                    if (isCbtMode && data.role === 'siswa') {
                        go = 'siswa/cbt'
                    }
                    window.location.href = go;
                }else{
                    if(data.invalid){
                        $.each(data.invalid, function(key, val){
                        $('[name="'+key+'"').parent().addClass('has-error');
                        $('[name="'+key+'"').next().next().text(val);
                        if(val == ''){
                            $('[name="'+key+'"').parent().removeClass('has-error');
                            $('[name="'+key+'"').next().next().text('');
                        }
                        });
                    }
                        if(data.failed){
                            infobox.addClass('alert alert-danger mt-3 text-center').text(data.failed);
                        }
                    }
                }
            });
        });

    });
</script>


<script>
	function showPassword() {
		var type = $('#password').attr('type');
		if (type ==='password') {
			$('.btn-eye').removeClass('fa fa-eye');
			$('.btn-eye').addClass('fa fa-eye-slash');	
			$('#password').attr('type','text');
		}
		else {
			$('.btn-eye').removeClass('fa fa-eye-slash');
			$('.btn-eye').addClass('fa fa-eye');	
			$('#password').attr('type','password');
		}
	}
</script>
  

</body>
</html>
