$(document).ready(function () {
    var defaultData = "";
    initSelectboxStyle1(defaultData);
    initSelectboxStyle2();
    initSelectboxStyle3();
    initSelectboxGetAnswer();
});

function initSelectboxStyle1(defaultData) {
    // alert("initSelectboxStyle1");
    var wrapper = $('.wrapperSelectbox.style1');
    var ex = [];
    if (defaultData != "" && defaultData.length > 0) {
        ex = defaultData.split("%#%");
    }
    wrapper.each(function (index, element) {
        var thisWrapper = $(this);
        thisWrapper.find('.placeholder').on('click', function (ev) {
            thisWrapper.find('.placeholder').css('opacity', '0');
            thisWrapper.find('.list__ul').toggle();
            wrapper.not(thisWrapper).find('.placeholder').css('opacity', '1');
            wrapper.not(thisWrapper).find('.list__ul').css('display', 'none');
        });

        thisWrapper.find('.list__ul a').on('click', function (ev) {
            ev.preventDefault();
            var index = $(this).parent().index();
            var value = $(this).attr('data-value');

            thisWrapper.find('.placeholder').text($(this).text()).css('opacity', '1');

            // console.log(thisWrapper.find('.list__ul').find('li').eq(index).html());

            thisWrapper.find('.list__ul').find('.li').eq(index).prependTo(thisWrapper.find('.list__ul'));
            thisWrapper.find('.list__ul').toggle();

            if (value != "") {
                thisWrapper.find('select').val(value);
                thisWrapper.find('.list__ul .li').each(function () {
                    if ($(this).find('a').attr('data-value') == "") {
                        $(this).remove();
                    }
                });

                if ($('.show-selectbox-answer').length) {
                    $('.show-selectbox-answer').removeClass('hide');
                }
            }

        });

        thisWrapper.find('select').on('change', function (e) {
            // // Set text on placeholder hidden element
            // thisWrapper.find('.placeholder').text(this.value);

            // // Animate select width as placeholder
            // $(this).animate({width: thisWrapper.find('.placeholder').width() + 'px' });

        });
        if (ex.length) {
            var thisSelectbox = thisWrapper.find('select');
            var thisOption = thisSelectbox.find('option[value="' + ex[index] + '"]');
            if (thisOption.length) {
                var thisOptionValue = thisOption.text();
                var thisOptionIndex = thisOption.index() - 1;
                thisOption.attr("selected", "selected");
                thisSelectbox.trigger("change");

                thisWrapper.find(".placeholder").text(thisOptionValue);
                thisWrapper.find(".list__ul .li:eq(0)").remove();
                thisWrapper.find('.list__ul').find('.li').eq(thisOptionIndex).prependTo(thisWrapper.find('.list__ul'));

                if ($('.show-selectbox-answer').length) {
                    $('.show-selectbox-answer').removeClass('hide');
                }
            }
        }

        $('body').on('click', function (e) {
            if ($(e.target).hasClass("placeholder")) {
                return;
            }
            thisWrapper.find(".list__ul .li").each(function () {
                if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
                    thisWrapper.find('.list__ul').hide();
                    thisWrapper.find('.list__ul').prev(".placeholder").css('opacity', '1');
                }
            });
        });
    });
}

function initSelectboxStyle2(){
    // alert("initSelectboxStyle2");
    var wrapper = $('.wrapperSelectbox.style2');
    wrapper.each(function(){
        var thisWrapper = $(this);
        thisWrapper.find(".drop .option").click(function() {
            var val = $(this).attr("data-value"),
                $drop = thisWrapper.find(".drop"),
                prevActive = thisWrapper.find(".drop .option.active").attr("data-value"),
                options = thisWrapper.find(".drop .option").length;
            $drop.find(".option.active").addClass("mini-hack");
            $drop.toggleClass("visible");
            $drop.removeClass("withBG");
            $(this).css("top");
            $drop.toggleClass("opacity");
            thisWrapper.find(".mini-hack").removeClass("mini-hack");
            if ($drop.hasClass("visible")) {
              setTimeout(function() {
                $drop.addClass("withBG");
              }, 400 + options*100); 
            }
            triggerAnimation(thisWrapper);
            if (val !== "placeholder" || prevActive === "placeholder") {
                thisWrapper.find(".drop .option").removeClass("active");
                $(this).addClass("active");
            }

            if (val !== "placeholder"){
                thisWrapper.find('select').val(val);
                
                // alert(val);
                if($('.show-selectbox-answer').length){
                    $('.show-selectbox-answer').removeClass('hide');
                }
            }
        });
    });
    
      
      function triggerAnimation(thisWrapper) {
        var finalWidth = thisWrapper.find(".drop").hasClass("visible") ? 22 : 20;
        // thisWrapper.find(".drop").css("width", "24em");
        setTimeout(function() {
            // thisWrapper.find(".drop").css("width", finalWidth + "em");
        }, 400);
      }
}

function initSelectboxStyle3(){
    // alert("initSelectboxStyle3");
    var wrapper = $('.wrapperSelectbox.style3');
    wrapper.each(function(){
        var thisWrapper = $(this);
        thisWrapper.find('select').on('change', function(){
            // alert($(this).val());
            if($('.show-selectbox-answer').length){
                $('.show-selectbox-answer').removeClass('hide');
            }
        });
    });
}

function initSelectboxGetAnswer(){
    var wrapper = $('.show-selectbox-answer');
    var pnlSoal = $('#pnlSoal');
    if(wrapper.length){
        wrapper.find('#btnshowSelectboxAnswer').on('click', function(){
            var optionsSelected = "";
            pnlSoal.find('.wrapperSelectbox').each(function(){
                var optionSelected =  $(this).find('select option:selected');
                if(optionSelected.val() != ""){
                    if(optionsSelected == ""){
                        optionsSelected += optionSelected.val();
                    }else{
                        optionsSelected += "#"+optionSelected.val();
                    }
                }else{
                    if(optionsSelected == ""){
                        optionsSelected += "-1";
                    }else{
                        optionsSelected += "#-1";
                    }
                }
            });
            if($('.wrapperSelectboxAnswer').length){
                $('.wrapperSelectboxAnswer').removeClass('hide');
                $('.wrapperSelectboxAnswer').html("Jawaban : "+optionsSelected);
            }
            // console.log(optionsSelected);
        });
    }
}