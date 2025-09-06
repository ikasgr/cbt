(function($) {
  "use strict"; // Start of use strict
    $(document).ready(function () {

        stickyCol();

    });

    $(window).scroll(function () {
        stickyColScroll();
    });

  	// password toogle
	$( "a.password-toggle" ).click(function() {
		var attr = $( 'input#password' ).attr( "type" );
		var elPassword = $( 'input#password' );
		var elToogle = $( '.password-toggle' );
		if(attr == 'password'){
			elPassword.attr( "type", "text" );
			elToogle.removeClass('icon-eye');
			elToogle.addClass('icon-eye-slash');
		}else{
			elPassword.attr( "type", "password" );
			elToogle.removeClass('icon-eye-slash');
			elToogle.addClass('icon-eye');
		}
	});

	// on key up, show remove icon
	$( "form input" ).keyup(function() {
		var iconRemove = $(this).parent().find('.icon-remove');
		if($(this).val() != ''){
			if(iconRemove.length == 0){
				$( '<a class="icon-remove"></a>' ).insertAfter( $(this) );
			}
		}
	});

	// clear text icon
	$(document).on('click', "a.icon-remove", function() {
		var field = $(this).parent().find('input.form-control');
		field.val('').focus();
		$(this).remove();
	});

	// field focus in
	$( "form input" ).focusin(function() {
		var fieldTitle = $(this).prev();
		if(!$(this).hasClass('form-control-plaintext')){
			fieldTitle.addClass('focustext');
		}
		fieldTitle.show();
	});

	// field focus out
	$( "form input" ).focusout(function() {
		var fieldTitle = $(this).prev();
		var getField = $(this).val();
		fieldTitle.removeClass('focustext');

		var form = $(this).closest('form');
		if(form.hasClass('login-form') && getField == ''){
			fieldTitle.hide();
		}
	});

	// sticky element
	$(window).scroll(function(){
	  var sticky = $('#questionHeader'),
	      scroll = $(window).scrollTop();

	  if (scroll >= 190) sticky.addClass('fixed-top');
	  else sticky.removeClass('fixed-top');
	});

	// font-size
	$( ".sizing-text-list a" ).click(function() {
		var font = $(this).attr("class");

		// set active class
		var current = $('.sizing-text-list').find('.current');
		current.removeClass('current')
		$(this).parent().addClass('current');

		// set body class
		$('body').removeClass('fontSmall').removeClass('fontMedium').removeClass('fontLarge');
		$('body').addClass(font);
	});

	// checkbox unsure
	$( "input#unsureCheckbox" ).click(function() {
		if ($(this).is(":checked")){
			$("h1.question-heading strong").addClass("unsure");
		}else{
			$("h1.question-heading strong").removeClass("unsure");
		}
	});

	// test submit / sample if error for login form
	//$( ".login-form" ).submit(function( event ) {
	//	$(".error-message").show();
	//	$(".invalid-feedback").show();
	//	var form = $( ".login-form" ).find( "input.form-control" ).addClass("is-invalid");
	//	form.addClass("is-invalid");
	//	form.prev().addClass("is-invalid");
	//	event.preventDefault();
	//});

	// test submit / sample if error for konfirmasi form
	//$( ".konfirmasi-form" ).submit(function( event ) {
	//	$(".invalid-feedback").show();
	//	var form = $( ".konfirmasi-form" ).find( "input.form-control" ).addClass("is-invalid");
	//	form.addClass("is-invalid");
	//	form.prev().addClass("is-invalid");
	//	event.preventDefault();
    //	});

    function stickyCol() {
        var wrap = $('.sticky-col');
        var col5 = wrap.find('.col-md-5');
        var col7 = wrap.find('.col-md-7');
        if (col5.find('.content').height() < col7.find('.content').height()) {
            col5.addClass('sidebar-fixed');
        } else {
            col7.addClass('sidebar-fixed');
        }
    }

    function stickyColScroll() {
        var sidebarFixed = $('.sidebar-fixed');
        var pos = sidebarFixed.offset();
        var width = sidebarFixed.width();
        if (pos == undefined) return;
        if (pos.top < $(window).scrollTop()) {
            sidebarFixed.find('.content').css({
                "position": "fixed",
                "width": width,
                "top": "0"
            });
        } else {
            sidebarFixed.find('.content').removeAttr("style");
        }
    }

})(jQuery); // End of use strict
