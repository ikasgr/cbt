<script src="<?=base_url()?>assets/_login/assets/js/jquery.min.js"></script>
<script src="<?=base_url()?>assets/_login/assets/js/alert/alert.js"></script>
<script src="<?=base_url()?>assets/_login/assets/login/app.js"></script>
<script src="<?=base_url()?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!--Start Security Script by OperatorFlobamora-->
	<script type="text/javascript">
	$(document).bind('contextmenu',function(e) {
	 return false;
	});
	document.onkeydown = function(e) {
	if(event.keyCode == 123) {
	return false;
	}
	if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)){
	return false;
	}
	if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)){
	return false;
	}
	if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)){
	return false;
	}
	}
	</script><!--End Of Security Script by OperatorFlobamora-->
</body>
</html>