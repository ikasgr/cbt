$(function () {

    //CBTModule.setDatePicker();
    //CBTModule.adminConfigurationInput();

    /*=======   plugin  ======    
       * A plugin normally registers itself with a service provided by the main application framework. In this way the plugin can be ‘called’ at a specified point during the execution stream of the application.
       * This is often done using event hooks, so for example a plugin may register functionality which is executed right before the application renders an interface, or perhaps just after. 
       * While a component or module may be portable to other applications and possibly usable on their own, a plugin depends on the ‘host’ application to function.
       * Plugins usually have a small set of ‘abilities’, or responsibilities, they often perform a task as small as outputting a message at the right time or replacing a bit of text.
   -----------------------------*/

    if (window.cbt && window.cbt.clrbxPopup) {
        $(".btn-clrbxpopup").each(function () {
            window.cbt.clrbxPopup.init($(this));
        });
    }

    if (window.cbt && window.cbt.soalTes) {
        $("#pnlSoal").each(function () {
            window.cbt.soalTes.init($(this));
        });
    }



    /*=======   component  ======    
        * High granularity.
        * Might not directly add functionality to a larger application but provides useful functions to the application e.g. a HTTP library component.
        * A “black box” grouping of code into an encapsulated package with a clear, limited interface.
        * High internal cohesion.
        * Deals with a specific function or closely related group of functions.
        * Composed together with other components into larger sub-systems (modules) or applications.
    -----------------------------*/

    if (window.cbt && window.cbt.timerCountDown) {
        $(".widget-timer").each(function () {
            window.cbt.timerCountDown.init($(this));
        });
    }

    if (window.cbt && window.cbt.getPelajaran) {
        $(".getMataPelajaranElement").each(function () {
            window.cbt.getPelajaran.init($(this));
        });
    }

    if (window.cbt && window.cbt.textHighlight) {
        $('span[class^="highlight-"], span[class*="highlight-"]').each(function () {
            window.cbt.textHighlight.init($(this));
        });
    }



    //CBTModule.formValidate();


    /*=======   Widgets  ======    
        * Associated with interfaces.
        * They are easy to use, portable UI components 
        * Often they may be third party widgets integrated into your application’s interface.
        * Internally they will often be small components used to place dynamic elements in the interface 
    -----------------------------*/
    CBTWidget.checkboxUnsure();
    CBTWidget.stickyHeaderBar();
    CBTWidget.showPassword();
    CBTWidget.inputOnFocus();
    CBTWidget.toggleClass();
    CBTWidget.assentChecxbox();
    CBTWidget.elevateZoomImage();
    CBTWidget.fontSoalSize();
    CBTWidget.panelSoalSlideListener();
    CBTWidget.bindClickCloseSlide();
    CBTWidget.disableBackspaceButton();
    //CBTWidget.disableRefreshWithf5();
    //CBTWidget.refreshNotification();
    CBTWidget.pnlSoalShow();

});
/*************************************************
 plugin dependencies: 
 * Jplayer http://www.jplayer.org
 * Version minimum : 2.9.2
****************************************************************/
// the semi-colon before the function invocation is a safety
// net against concatenated scripts and/or other plugins
// that are not closed properly.
; (function ($, window, document, undefined) {

    // undefined is used here as the undefined global
    // variable in ECMAScript 3 and is mutable (i.e. it can
    // be changed by someone else). undefined isn't really
    // being passed in so we can ensure that its value is
    // truly undefined. In ES5, undefined can no longer be
    // modified.

    // window and document are passed through as local
    // variables rather than as globals, because this (slightly)
    // quickens the resolution process and can be more
    // efficiently minified (especially when both are
    // regularly referenced in your plugin).

    // Create the defaults once
    var pluginName = "audioVideoCBT",
        defaults = {
            mediaIndex: null,
            mediaType: null, // "audio" for audio AND "video" for video
            pathFile: null, // the file location
            fileTitle: null, // the file's label for show on the player 
            fileThumbnail: null, // thumbnile file location for images only
            startTimePlay: null,
            endedService: null, // path webservices after playing            
            updateTimeService: null,
            examID: null
        };



    // The actual plugin constructor
    function Plugin(element, options, idx) {
        this.element = element;

        // destroy method

        // jQuery has an extend method that merges the
        // contents of two or more objects, storing the
        // result in the first object. The first object
        // is generally empty because we don't want to alter
        // the default options for future instances of the plugin
        this.options = $.extend({}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init(idx);

    }

    function setAudioVideoPlayer(el, options, idx) {
        var expiresTime = setExpireLenght(1);

        function callService() {
            $.ajax({
                type: Type, //GET or POST or PUT or DELETE verb
                url: Url, // Location of the service
                data: Data, //Data sent to server
                contentType: ContentType, // content type sent to server
                dataType: DataType, //Expected data format from server
                processdata: ProcessData, //True or False
                success: function (result) {//On Successfull service call
                    if (DataType == "json") {
                        resultObject = result.d;// result.GetUserResult;
                        //alert(resultObject);
                        //for (i = 0; i < resultObject.length; i++) {
                        //    console.log(resultObject[i]);
                        //}
                    }
                },
                error: function (result) {
                    console.error('Service call failed: ' + result.status + '' + result.statusText);
                    Type = null;
                    varUrl = null;
                    Data = null;
                    ContentType = null;
                    DataType = null;
                    ProcessData = null;

                    if (DataType == "json") {
                        resultObject = result.d;//result.GetUserResult;
                        //alert(resultObject);

                        //for (i = 0; i < resultObject.length; i++) {
                        //    console.log(resultObject[i]);
                        //}
                    }

                }
            });
        };

        function setExpireLenght(totalDay) {
            var expireAt = new Date();
            expireAt.setDate(expireAt.getDate() + totalDay);

            return expireAt.toGMTString();
        };

        function getCookie(cookieName) {
            var cookieSplited = document.cookie.split('; ');
            var res = null;

            for (var i = 0; i < cookieSplited.length; i++) {
                var C = cookieSplited[i].split('=');
                if (C[0] === cookieName) {
                    res = C[1];
                }
            }

            return res;
        };

        function destroyCookie(cookieName) {
            var cookies = document.cookie.split(";");
            for (var i = 0; i < cookies.length; i++) {
                var equals = cookies[i].indexOf("=");
                var name = equals > -1 ? cookies[i].substr(0, equals) : cookies[i];
                name = name.trim();
                if (name == cookieName) {
                    document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
                }
            }
        };

        function onReady($el) {
            //$el.jPlayer("setMedia", obj);                
            // get cookies playing progress
            //var checkCookie = getCookie("status_" + this.id);
            var hiddenValueAudio = $('.hfAudioTimeCheck').val();

            if (parseInt(hiddenValueAudio) > 0) {
                $el.jPlayer('pause', parseInt(hiddenValueAudio));
            }
            //if (checkCookie) {
            //    if (parseInt(hiddenValueAudio) > parseInt(checkCookie)) {
            //        $el.jPlayer('pause', parseInt(hiddenValueAudio));
            //    } else {
            //        $el.jPlayer('pause', parseInt(checkCookie));
            //    }
            //} else {
            //    if (parseInt(hiddenValueAudio) > 0) {
            //        $el.jPlayer('pause', parseInt(hiddenValueAudio));
            //    }
            //}
        };

        function onPlay() {
            // disabled some of button during playing an audio
            CBTWidget.disableOnAudioPlay();
            CBTWidget.disableKeyEnter();
        };

        function onPause() {
            // enabled some of button during paused
            CBTWidget.enableAfterAudioPlayed();
            CBTWidget.enableKeyEnter();
        };

        function onTimeupdate(event) {
            var checkCookieTime = $.cookie("status_JP-0");
            var currentTime = 0;
            if (event == undefined)
                currentTime = 0;
            else
                currentTime = event.jPlayer.status.currentTime;
            if (checkCookieTime == undefined)
                checkCookieTime = 0;
            if (checkCookieTime == currentTime) {
                $('.jp-controls').attr("style", 'visibility:visible');
            }
            if (currentTime != 0) {

                $('.jp-controls').attr("style", 'visibility:visible');
                if (TypeABC != 'ABC') {
                    document.cookie = "status_" + this.id + "=" + currentTime + ";expires=" + expiresTime;
                }

                if ((Math.round(currentTime) % 10) === 0) {
                    var fixed = (currentTime.toFixed(4) % 1).toFixed(4);

                    if (fixed <= 0.2 || fixed >= 0.9) {

                        // Call Services                                                                                
                        var sequenceId = $('.hfDetailSequenceNo').val();
                        //Type = "GET";
                        //Url =  "/UNBK/CbtService/SoalUpdateTime?puspendikData1=" + encodeURIComponent(sequenceId) + "&puspendikData2=" + (Math.round(currentTime)).toString();
                        //Data = '{"updateTime": "' + (Math.round(currentTime)).toString() + '"}';
                        //ContentType = "application/json; charset=utf-8";
                        //DataType = "json"; varProcessData = true;
                        //ProcessData = "FALSE";
                        //if (!event.jPlayer.status.paused)
                        //    CallService();


                        if (!event.jPlayer.status.paused) {
                            $.post($('.hfSoalUpdateTime').val(),
                                {
                                    puspendikData1: encodeURIComponent(sequenceId),
                                    puspendikData2: (Math.round(currentTime)).toString()
                                })
                                .done(function (data) {
                                });
                        }
                    }

                }

            }
        };

        function onEnded(event) {
            //Just finish playing audio, and do something
            $('#audioPlace').attr("style", 'display:none');

            // remove cookies playing progress
            destroyCookie("status_" + this.id);

            // enabled some of button after playing an audio
            CBTWidget.enableAfterAudioPlayed();
            CBTWidget.enableKeyEnter();

            // call web services
            //var soalID = options.examID;
            var soalID = $('.hfDetailSequenceNo').val();
            //Type = "GET";
            ////Url = options.endedService;
            //Url = "https://" + window.location.hostname + "/unbk/CbtService/UpdateAudioStatus?puspendikdata1=" + encodeURIComponent(soalID);
            //Data = '{"detailSequenceNo": "' + soalID + '"}';
            //ContentType = "application/json; charset=utf-8";
            //DataType = "json"; varProcessData = true;
            //ProcessData = "FALSE";
            //CallService();

            $.post($('.hfSoalUpdateAudioStatus').val(),
                {
                    puspendikData1: encodeURIComponent(soalID)
                })
                .done(function (data) {
                });

        };

        if (options.mediaType === 'video') {

            new jPlayerPlaylist({
                jPlayer: "#jquery_jplayer_" + idx,
                cssSelectorAncestor: "#jp_container_" + idx
            }, [
                    {
                        title: options.fileTitle,
                        m4v: options.pathFile,
                        poster: options.fileThumbnail
                    }
            ], {
                ready: function () {
                    onReady($(this));
                },

                play: function () {
                    onPlay();
                },

                timeupdate: function (event) {
                    onTimeupdate(event);
                },

                pause: function () {
                    onPause();
                },

                ended: function (event) {
                    onEnded(event);
                },
                swfPath: "Assets/Scripts/jplayer",
                supplied: "webmv, ogv, m4v",
                size: {
                    width: "630px",
                    height: "360px",
                    cssClass: "jp-video-360p"
                },
                useStateClassSkin: true,
                autoBlur: false,
                smoothPlayBar: true,
                keyEnabled: true
            });

        } else {

            new jPlayerPlaylist({
                jPlayer: "#jquery_jplayer_" + idx,
                cssSelectorAncestor: "#jp_container_" + idx
            }, [
                {
                    title: options.fileTitle,
                    mp3: options.pathFile
                }
            ], {
                ready: function () {
                    onReady($(this));
                },

                play: function () {
                    onPlay();
                },

                timeupdate: function (event) {
                    onTimeupdate(event);
                },

                pause: function () {
                    onPause();
                },

                ended: function (event) {
                    onEnded(event);
                },
                swfPath: "Assets/Scripts/jplayer",
                supplied: "oga, mp3",
                wmode: "window",
                useStateClassSkin: true,
                autoBlur: false,
                smoothPlayBar: true,
                keyEnabled: true
            });


        }

    };

    Plugin.prototype = {

        init: function (idx) {
            var $el = this,
                _base = $(this.element),
                _el = this.element,
                _opts = this.options,
                idx = _opts.mediaIndex;


            $.fn.replaceWithCallback = function (replace, callback) {
                var ret = $.fn.replaceWith.call(this, replace); // Call replaceWith
                if (typeof callback === 'function') {
                    callback.call(ret); // Call your callback
                }
                return ret;  // For chaining
            };

            _base.replaceWithCallback(this.playerTemplate(_el, _opts, idx), function () {

                console.log("CBT", _opts.mediaType, "initialized");
                // save the default structures for restore process
                $("#JP-" + idx).data("default", _base);

                setAudioVideoPlayer(_el, _opts, _opts.mediaIndex);


            });

        },

        playerTemplate: function (el, options, idx) {
            var templateVideo = '<div class="audio-video-wrapper">'
            + '<div id="jp_container_' + idx + '" class="jp-video jp-video-270p" role="application" aria-label="media player">'
            + '<div class="jp-type-playlist">'
            + '    <div id="jquery_jplayer_' + idx + '" class="jp-jplayer"></div>'
            + '    <div class="jp-gui">'
            + '        <div class="jp-video-play">'
            + '            <button class="jp-video-play-icon" role="button" tabindex="0">play</button>'
            + '        </div>'
            + '        <div class="jp-interface">'
            + '            <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>'
            + '            <div class="jp-controls-holder">'
            + '                <div class="jp-controls">'
            + '                    <button class="jp-play" role="button" tabindex="0">play</button>'
            + '                </div>'
            + '                <div class="jp-volume-controls">'
            + '                    <button class="jp-mute" role="button" tabindex="0">mute</button>'
            + '                    <button class="jp-volume-max" role="button" tabindex="0">max volume</button>'
            + '                    <div class="jp-volume-bar">'
            + '                        <div class="jp-volume-bar-value"></div>'
            + '                    </div>'
            + '                </div>'
            + '                <div class="jp-toggles hide">'
            + '                    <button class="jp-full-screen" role="button" tabindex="0">full screen</button>'
            + '                </div>'
            + '            </div>'
            + '            <div class="jp-details">'
            + '                <div class="jp-title" aria-label="title">&nbsp;</div>'
            + '            </div>'
            + '        </div>'
            + '    </div>'
            + '    <div class="jp-playlist hide">'
            + '        <ul>'
            + '            <!-- The method Playlist.displayPlaylist() uses this unordered list -->'
            + '            <li>&nbsp;</li>'
            + '        </ul>'
            + '    </div>'
            + '    <div class="jp-no-solution">'
            + '        <span>Update Required</span>'
            + '        To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.'
            + '    </div>'
            + '</div>'
            + '</div>'
            + '</div>';


            var templateAudio = '<div class="audio-video-wrapper">'
            + '<div id="jquery_jplayer_' + idx + '" class="jp-jplayer"></div>'
            + '<div id="jp_container_' + idx + '" class="jp-audio" role="application" aria-label="media player">'
            + '    <div class="jp-type-playlist">'
            + '        <div class="jp-gui jp-interface">'
            + '            <div class="jp-controls">'
            + '                <button class="jp-play" role="button" tabindex="0">play</button>'
            + '            </div>'
            + '            <div class="jp-volume-controls">'
            + '                <button class="jp-mute" role="button" tabindex="0">mute</button>'
            + '                <button class="jp-volume-max" role="button" tabindex="0">max volume</button>'
            + '                <div class="jp-volume-bar">'
            + '                    <div class="jp-volume-bar-value"></div>'
            + '                </div>'
            + '            </div>'
            + '            <div class="jp-time-holder">'
            + '                <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>'
            + '            </div>'
            + '            <div class="jp-toggles">'
            + '            </div>'
            + '        </div>'
            + '        <div class="jp-playlist hide">'
            + '            <ul>'
            + '                <li>&nbsp;</li>'
            + '            </ul>'
            + '        </div>'
            + '        <div class="jp-no-solution">'
            + '            <span>Update Required</span>'
            + '            To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.'
            + '        </div>'
            + '    </div>'
            + '</div>'
            + '</div>';


            return options.mediaType === 'video' ? templateVideo : templateAudio;
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
(function ($, _) {
    var NS = "soalTes"
    	, PRF = "sts-" // short hand for child class prefix (including dot)



    function init($el) {
        if ($("<textarea").length > 0 || ($("input:text").length > 0)) {
            return;
        }

        shortcut.add("A", function () {
            answerA();
        });
        shortcut.add("B", function () {
            answerB();
        });
        shortcut.add("C", function () {
            answerC();
        });
        shortcut.add("D", function () {
            answerD();
        });
        shortcut.add("E", function () {
            answerE();
        });

    };


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        init: init,
    }
})($, _);
// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "textHighlight"
    	, PRF = "highlight-" // short hand for child class prefix (including dot)


    function init($el) {
        //$el.off('click');
        $el.on('click', function (event) {
            event.stopPropagation();
            if ($(this).hasClass('highlight-radio')) {
                var $parent = $(this).parents('div');
                $($parent[0]).find('.highlight-radio').not($(this)).removeClass('clicked');
                $(this).toggleClass('clicked');
            } else {
                $(this).toggleClass('clicked');
            }
        });
    };

    function reinit() {
        $('span[class^="' + PRF + '"], span[class*="' + PRF + '"]').each(function () {
            var $el = $(this);
            $el.off('click');
            init($el);
        });
    };


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        init: init,
        reinit: reinit
    }
})($, _);

// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "topContentPanel";
    function reinit() {
        var topContent = $.trim($('#pnlSoalTopContent').html());
        if (topContent.length > 0 && $('#pnlSoalTop').length) {
            var $pnlSoalTop = $('#pnlSoalTop');
            var $pnlSoalShowButton = $pnlSoalTop.find('.pnlSoalShowButton');
            $pnlSoalShow = $pnlSoalTop.attr('data-pnlSoal-show');
            $timeTopContent = $pnlSoalTop.attr('data-time');
            $pnlSoalTop.removeClass('hide');
            $pnlSoalTop.parents('.page-section').find('#audioPlace').addClass('hide');
            $pnlSoalTop.parents('.page-section').find('#pnlSoal').addClass('hide');
            $pnlSoalShowButton.show();
            $pnlSoalTop.find('.textImage').addClass('blur');
            if ($pnlSoalShow) {
                $pnlSoalShowButton.on('click', function () {
                    CBTWidget.disableOnAudioPlay();
                    CBTWidget.disableKeyEnter();
                    // call web services
                    //var soalID = options.examID;
                    var soalID = $('.hfDetailSequenceNo').val();
                    Type = "GET";
                    //Url = options.endedService;


                    $(this).hide();
                    $pnlSoalTop.find('.textImage').removeClass('blur');
                    setTimeout(function () {
                        $pnlSoalTop.addClass('hide');
                        $pnlSoalTop.parents('.page-section').find('#audioPlace').removeClass('hide');
                        $pnlSoalTop.parents('.page-section').find('#pnlSoal').removeClass('hide');
                        CBTWidget.enableAfterAudioPlayed();
                    }, $timeTopContent);

                    $.post($('.hfSoalUpdateTopContent').val(),
                        { puspendikData1: encodeURIComponent(soalID) })
                        .done(function (data) {
                            //CallService();
                        });

                    //Url = "https://" + window.location.hostname + "/unbk/CbtService/UpdateTopContentStatus?puspendikdata1=" + encodeURIComponent(soalID);
                    //Data = '{"detailSequenceNo": "' + soalID + '"}';
                    //ContentType = "application/json; charset=utf-8";
                    //DataType = "json"; varProcessData = true;
                    //ProcessData = "FALSE";
                    //CallService();
                    //$(this).hide();
                    //$pnlSoalTop.find('.textImage').removeClass('blur');
                    //setTimeout(function () {
                    //    $pnlSoalTop.addClass('hide');
                    //    $pnlSoalTop.parents('.page-section').find('#audioPlace').removeClass('hide');
                    //    $pnlSoalTop.parents('.page-section').find('#pnlSoal').removeClass('hide');
                    //    CBTWidget.enableAfterAudioPlayed();
                    //}, $time);
                });
            }
        }
    };
    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        reinit: reinit
    };

})($, _);



// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "selesaiTimer";
    var timer = null;

    function reinit() {
        var $el = $('#btnSelesai');
        if (!$el.length) {
            return;
        }

        $el.removeAttr('disabled');
        $el.find('span').remove();

        if ($.cookie("examinationtime") === undefined) {
            return;
        }

        var aTR = $.cookie("examinationtime").split(':');
        var secondsRemain = (+aTR[0]) * 60 * 60 + (+aTR[1]) * 60 + (+aTR[2]);

        var dataTime = $el.attr('data-time');
        if (dataTime == 0) {
            dataTime = secondsRemain;
        }
        $time = secondsRemain - dataTime;
        if ($time <= 0) {
            return;
        }

        $el.removeClass('activebutton');
        $el.attr('disabled', 'disabled');
        $minute = parseInt($time / 60, 10);
        $second = parseInt($time % 60, 10);
        $el.attr('data-second', $second);
        $el.attr('data-minute', $minute);
        $el.append('<span></span>');
        function timerCounter($el) {
            if (timer) {
                clearInterval(timer);
            }
            timer = setInterval(function () {

                if ($second > 0) {
                    $second = $second - 1;
                    $el.attr('data-second', $second);

                    if ($minute === 0) {
                        $el.find('span').text(' [' + $second + ']');
                    } else {
                        $el.find('span').text(' [' + $minute + ':' + $second + ']');
                    }
                } else {

                    if ($minute === 0) {
                        clearInterval(timer);
                        $el.removeAttr('disabled');
                        $el.find('span').remove();
                        $el.addClass('activebutton');
                    }

                    if ($minute > 0) {
                        $minute = $minute - 1;
                        $el.attr('data-minute', $minute);
                    }

                    $second = 60;
                }

            }, 1000);
        }
        if ($time > 0)
            timerCounter($el);
    };


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        reinit: reinit
    };
})($, _);

// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "clrbxPopup"
    	, PRF = "clrbxpop-" // short hand for child class prefix (including dot)



    function init($el) {
        var $target = $el.data("target");

        $el.on("click", function () {
            $.colorbox({
                inline: true,
                href: $target,
                modal: true,
                scrolling: false,
                width: "500px",
                onCleanup: function () {
                    //$j("div#popup").hide();
                }
            });
        });
    };


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        init: init,
    }
})($, _);
// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "blockUI"
    	, PRF = "wg-blockui" // short hand for child class prefix (including dot)



    function init() {
        $("main, #questionModal").block({
            message: "<div class='blockloading'><span></span></div>",
            css: {
                padding: 0,
                margin: 0,
                width: '30%',
                top: '40%',
                left: '35%',
                textAlign: 'center',
                color: '#666666',
                border: 'none',
                backgroundColor: 'transparent',
                cursor: 'wait'
            },
            overlayCSS: {
                backgroundColor: '#000',
                opacity: 0.6,
                cursor: 'wait'
            }
        });

    };

    function destroy() {
        $("main, #questionModal").unblock();
    }


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        init: init,
        destroy: destroy
    }
})($, _);
// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "timerCountDown"
    	, PNL = "widget-timer"
    	, PRF = "wg-countdown" // short hand for child class prefix (including dot)
        , startTimeInSecond = {}
        , startCounter = 0
        , method = {
            setTimerInterval: function ($count, $countdown) {
                window.counter = setInterval(function () {
                    method.timer($count, $countdown);
                }, 1000); //1000 will  run it every 1 second
            }
        	, removeInterval: function () {
        	    clearInterval(window.counter);
        	}

        	, startCountingDown: function ($el, $value) {
        	    var $value = $value.split(':'); // split it at the colons				
        	    var $count = (+$value[0]) * 60 * 60 + (+$value[1]) * 60 + (+$value[2]);
        	    var $countdown = $el.find('.' + PRF);

        	    startTimeInSecond = new Date().getTime() / 1000;
        	    startCounter = $count;

        	    method.setTimerInterval($count, $countdown);
        	}

			, timer: function ($count, $countdown) {
			    var doCounting = method.getCookie('docount');
			    var examinationTime = method.getCookie('examinationtime');


			    if (doCounting == "true") {
			        examinationTime = examinationTime.split(':');
			        var $count = 0;//(+examinationTime[0]) * 60 * 60 + (+examinationTime[1]) * 60 + (+examinationTime[2]);

			        var currentInSecond = new Date().getTime() / 1000;
			        $count = Math.round(startCounter - (currentInSecond - startTimeInSecond));

			        if ($count <= -1) {
			            method.removeInterval();
			            destroy();
			            $('form#frmFinish').submit();
			            return;
			        }

			        var leftTime;
			        var leftTimeDisplay;
			        var seconds = $count % 60;
			        var minutes = Math.floor($count / 60);
			        var hours = Math.floor(minutes / 60);
			        minutes %= 60;
			        hours %= 60;

			        var hrs = hours < 10 ? "0" + hours : hours;
			        var mnts = minutes < 10 ? "0" + minutes : minutes;
			        var scns = seconds < 10 ? "0" + seconds : seconds;

			        leftTime = hrs + ":" + mnts + ":" + scns;

			        leftTimeDisplay = hrs + ":" + mnts;

			        //$countdown.html(leftTime);
			        $countdown.html(leftTimeDisplay);
			        document.cookie = "examinationtime=" + leftTime;

			        // notification message
			        if (hours === 0) {

			            if (minutes <= 4 && seconds <= 59) {
			                if ($('.timer-notification').length === 0) {
			                    $('.timer-section .col-xs-9').prepend('<div class="timer-notification">Waktu pengerjaan anda tinggal <span class="refresh-minute">' + minutes + '</span> menit  <span class="refresh-second">' + seconds + '</span> detik</div>');
			                } else {
			                    $('.timer-notification .refresh-minute').text(minutes);
			                    $('.timer-notification .refresh-second').text(seconds);
			                }
			            }
			        }

			    } else {
			        method.removeInterval();
			        if (examinationTime)
			            $countdown.html(examinationTime);
			        else
			            //$countdown.html("00:00:00");
			            $countdown.html("00:00");
			    }
			}

			, getCookie: function (cname) {
			    var cookieSplited = document.cookie.split('; ');
			    var res = null;

			    for (var i = 0; i < cookieSplited.length; i++) {
			        var C = cookieSplited[i].split('=');
			        if (C[0] === cname) {
			            res = C[1];
			        }
			    }

			    return res;
			}

			, setExpireLenght: function (totalDay) {
			    var expireAt = new Date();
			    expireAt.setDate(expireAt.getDate() + totalDay);

			    return expireAt.toGMTString();
			}
        };

    function init($el) {
        var testCoookie = method.getCookie('examinationtime');   // get value from cookies 		
        if (testCoookie && typeof testCoookie != "undefined") {
            var $value = testCoookie;
            var doCounting = method.getCookie('docount');

            if (doCounting && doCounting != "false") {
                method.startCountingDown($el, $value);
            } else {
                var $countdown = $el.find('.' + PRF);
                $countdown.html(testCoookie);
            }

        } else {
            // set cookie if doesn't exist from attribute[data-time], this allowed to be change ;
            var countdown = $el.find('.' + PRF);
            var providedTime = countdown.data('time');

            if (providedTime) {
                var expiresTime = method.setExpireLenght(1);

                document.cookie = "examinationtime=" + providedTime + ";expires=" + expiresTime;
                document.cookie = "docount=" + "true" + ";expires=" + expiresTime;

                var $value = providedTime;
                method.startCountingDown($el, $value);
            }
        }
    };

    function destroy() {
        var $countdown = $('.' + PRF);
        var cookies = document.cookie.split(";");
        //$countdown.html("00:00:00");
        $countdown.html("00:00");
        for (var i = 0; i < cookies.length; i++) {
            var equals = cookies[i].indexOf("=");
            var name = equals > -1 ? cookies[i].substr(0, equals) : cookies[i];
            name = name.trim();
            if (name == "examinationtime" || name == "docount") {
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
            }
        }
    };

    function pause() {
        var examinationTime = method.getCookie('examinationtime');
        if (examinationTime) {
            var expiresTime = method.setExpireLenght(1);
            document.cookie = "docount=" + "false" + ";expires=" + expiresTime;
        }
    };

    function play() {
        var examinationTime = method.getCookie('examinationtime');
        if (examinationTime) {
            var expiresTime = method.setExpireLenght(1);
            var $value = examinationTime.split(':'); // split it at the colons				
            var $count = (+$value[0]) * 60 * 60 + (+$value[1]) * 60 + (+$value[2]);
            var $countdown = $('.' + PRF);

            document.cookie = "docount=" + "true" + ";expires=" + expiresTime;

            method.setTimerInterval($count, $countdown);
        }
    };

    function setNew($value) {
        var $el = $('.' + PNL);
        //$el.find('.' + PRF).html($value); 

        /* split new set value */
        var hr = $value.substring(0, 2);
        var mn = $value.substring(3, 5);
        $el.find('.' + PRF).html(hr + ":" + mn);

        if ($el.length) {
            var expiresTime = method.setExpireLenght(1);
            document.cookie = "examinationtime=" + $value + ";expires=" + expiresTime;
            document.cookie = "docount=" + "true" + ";expires=" + expiresTime;

            method.removeInterval();
            method.startCountingDown($el, $value);
        }
    };


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        init: init,
        destroy: destroy,
        pause: pause,
        play: play,
        setNew: setNew

    }
})($, _);
// Pass in all dependencies as arguments, so they are obvious to other devs
(function ($, _) {
    var NS = "getPelajaran"
    	, PRF = "wg-gpelajaran" // short hand for child class prefix (including dot)



    function init($el) {
        $el.on("change", function () {
            var $this = $(this);
            if ($this.val() !== "") {
                // delete if any
                $this.parent().siblings('.multipleMataPelajaranWrapper').remove();
                $this.closest('div').after("<div class='multipleMataPelajaranWrapper col-xs-8' style='margin-top: 5px;'>Loading...</div>");

                var jqxhr = $.post("URL", {
                    "test": "test message"


                }).done(function (data) {
                    var items = [];
                    $.each(data, function (key, val) {
                        items.push("<option value='" + key + "'>" + val + "</option>");
                    });

                    var newData = "<select id='multipleMataPelajaran' name='multipleMataPelajaran' class='form-control multipleMataPelajaran' required='' multiple>" + items.join("") + "</select>";
                    $this.parent().siblings('.multipleMataPelajaranWrapper').html(newData);

                })
            .fail(function (jqxhr, textStatus, error) {
                var err = textStatus + ", " + error;
                console.log("Request Failed: " + err);
            });

            } else {
                // delete if any
                $this.parent().siblings('.multipleMataPelajaranWrapper').remove();
            }

        });

    };


    // public API
    window.cbt = window.cbt || {};
    window.cbt[NS] = {
        init: init,
    }
})($, _);
// Pass in all dependencies as arguments, so they are obvious to other devs
var CBTWidget = CBTWidget || {};
CBTWidget = (function () {
    var $document = $(document),
        $window = $(window),
        $bodyHTML = $("html, body"),
        $mainContent = $(".main-content");

    return {
        checkboxUnsure: function () {
            function setState(stts) {
                if (stts) {
                    $('.panel-slide-content').find('.current').addClass('unsure');
                    $('.soal-label > .soal-no').addClass('unsure');
                } else {
                    $('.panel-slide-content').find('.current').removeClass('unsure');
                    $('.soal-label > .soal-no').removeClass('unsure');
                }
            };

            function checkboxState(cb) {
                if (cb.is(':checked')) {
                    setState(true);
                    cb.attr('aria-checked', 'true');
                } else {
                    setState(false);
                    cb.attr('aria-checked', 'false');
                }
            };

            $('.labelUnsureCheckbox').each(function () {
                var $this = $(this),
                     $checkbox = $this.siblings('input[type=checkbox]');

                // detect checkbox state at first time loading
                checkboxState($checkbox);

                // event on change
                $checkbox.on('change', function () {
                    checkboxState($(this));
                });

            });
        }

        , stickyHeaderBar: function () {
            var gapCounterPanelToTop = 135;
            var $body = $("body");
            $(window).on("scroll", function () {
                var HTMLBody = $("html").scrollTop() || $("body").scrollTop();
                HTMLBody >= gapCounterPanelToTop ? $body.addClass("counter-fixed-top") : $body.removeClass("counter-fixed-top");
            });
        }

        , showPassword: function () {
            var $button = $('.showPassword');
            var $input = $button.siblings('input');

            $button.on("mouseup", function () {
                $input.prop('type', 'password');
                $(this).removeClass('pressed');
            }).on("mousedown", function () {
                $input.prop('type', 'text');
                $(this).addClass('pressed');
            });
        }

        , inputOnFocus: function () {
            $('input[type=text], input[type=password], input[type=email], input[type=number]').on("focus", function () {
                var $this = $(this);
                var $errorMessage = $(this).siblings('.error-message');

                $errorMessage.fadeOut(400, function () {
                    $(this).remove();
                    $this.closest('.form-group').removeClass('error');
                });



            });
        }

        , toggleClass: function () {
            $('.toggleClass').each(function () {
                var $this = $(this),
                    $className = $this.data('class'),
                    $target = $this.data('target');

                $this.on("click", function (e) {
                    e.preventDefault();

                    // this disabled attribute created by audio on play
                    var attr = $($target).attr('disabled');

                    if (typeof attr === typeof undefined || attr === false) {
                        $($target).toggleClass($className);

                        //create own event
                        $(this).trigger('cssClassToggled');
                    }
                });
            });
        }

        , assentChecxbox: function () {

            function setStatus($el, $target) {
                if ($el.is(":checked")) {
                    $target.prop('disabled', false);
                } else {
                    $target.prop('disabled', true);
                }
            }

            $('.assent-checkbox').each(function (idx) {
                var $this = $(this)
                    , $cb = $this.find(".assentcb-input")
                    , $lb = $this.find(".assentcb-label")
                    , $target = $($cb.data("target"))

                $cb.attr("id", idx + "-ascb");
                $lb.attr("for", idx + "-ascb");

                //set first render the page 
                setStatus($cb, $target);

                //event on change
                $cb.change(function () {
                    setStatus($(this), $target);
                });

            });
        }

        , elevateZoomImage: function () {
            $("img[data-zoom-image]").elevateZoom({ zoomType: "inner", cursor: "crosshair" });
        }

        , fontSoalSize: function () {
            $('.sizing-text').each(function () {
                var $this = $(this);
                var fontCookie = getCookie("fontStylesCookies");
                var $active = "fontMedium";

                if (fontCookie != null) {
                    switch (fontCookie) {
                        case "font-small":
                            $active = "fontSmall";
                            break;

                        case "font-medium":
                            $active = "fontMedium";
                            break;

                        case "font-large":
                            $active = "fontLarge";
                            break;
                    };
                };

                $this.find("." + $active).parent().addClass('current');

                $this.find('a').on("click", function (e) {
                    e.preventDefault();
                    var $el = $(this);
                    var $parent = $el.closest('.sizing-text-list');
                    var $thisClassName = $el.attr("class");
                    var className = "font-medium";
                    var expTime = setExpireLenght(2);

                    $parent.find('.current').removeClass('current');
                    $el.parent().addClass('current');

                    switch ($thisClassName) {
                        case "fontSmall":
                            className = "font-small";
                            break;

                        case "fontMedium":
                            className = "font-medium";
                            break;

                        case "fontLarge":
                            className = "font-large";
                            break;
                    };

                    document.cookie = "fontStylesCookies=" + className + ";expires=" + expTime;
                    document.body.className = className;
                });
            });
        }

        , panelSoalSlideListener: function () {
            var soalNavigation = $('.soal-navigation') || $('.action-wrapper').parent('.page-section');
            $("#questionModal").on('cssClassToggled', function () {
                if ($(this).hasClass("open")) {
                    soalNavigation.addClass("narrow");
                } else {
                    soalNavigation.removeClass("narrow");
                }
            });
        }

        , bindClickCloseSlide: function () {
            $(document).on("click", function (event) {
                if ($("#questionModal").hasClass('open')) {
                    var $el = $(event.target);
                    if (!$el.parents("#questionModal").length) {
                        $('span[data-target="#questionModal"]').trigger("click");
                    }
                }
            });
        }

        , disableOnAudioPlay: function () {
            // Slide panel
            var pnlSlide = $("#questionModal"),
                btnPrev = $(".btn-prev"),
                btnNext = $(".btn-next"),
                btnDaftarSoal = $("#buttonDaftarSoal");

            if (pnlSlide.hasClass('open')) {
                pnlSlide.children('.toggleClass').trigger("click");
            }

            pnlSlide.attr('disabled', 'disabled');
            btnPrev.attr('disabled', 'disabled');
            btnNext.attr('disabled', 'disabled');
            btnDaftarSoal.attr('disabled', 'disabled');
        }


        , enableAfterAudioPlayed: function () {
            // Slide panel
            var pnlSlide = $("#questionModal"),
                btnPrev = $(".btn-prev"),
                btnNext = $(".btn-next"),
                btnDaftarSoal = $("#buttonDaftarSoal");

            pnlSlide.removeAttr('disabled');
            btnPrev.removeAttr('disabled');
            btnNext.removeAttr('disabled');
            btnDaftarSoal.removeAttr('disabled');
        }

        , disableKeyEnter: function () {
            //disabled enter button
            shortcut.remove("ENTER");
            $(document).keypress(
                function (e) {
                    var keycode = e.which || e.keyCode;
                    if (keycode == 13) {
                        return false;
                    }

                }
            );
        }

        , enableKeyEnter: function () {
            //enable enter button
            shortcut.remove("ENTER");
            shortcut.add("ENTER", function () { $(".activebutton").click(); });

            $(document).keypress(
                function (e) {
                    var keycode = e.which || e.keyCode;
                    if (keycode == 13) {
                        return true;
                    }

                }
            );
        }

        , disableBackspaceButton: function () {
            $(document).unbind('keydown').bind('keydown', function (e) {
                if (true) {
                    var doPrevent = false;
                    var keycode = e.which || e.keyCode;
                    if (keycode === 8) {
                        var d = e.srcElement || e.target;
                        if ((d.tagName.toUpperCase() === 'INPUT' &&
                            (
                                d.type.toUpperCase() === 'TEXT' ||
                                d.type.toUpperCase() === 'PASSWORD' ||
                                d.type.toUpperCase() === 'FILE' ||
                                d.type.toUpperCase() === 'SEARCH' ||
                                d.type.toUpperCase() === 'EMAIL' ||
                                d.type.toUpperCase() === 'NUMBER' ||
                                d.type.toUpperCase() === 'DATE')
                            ) ||
                            d.tagName.toUpperCase() === 'TEXTAREA') {
                            doPrevent = d.readOnly || d.disabled;
                        }
                        else {
                            doPrevent = true;
                        }
                    }

                    if (doPrevent) {
                        event.preventDefault();
                    }
                }
            });
        }

        //, disableRefreshWithf5: function () {
        //    //disabled F5 button
        //    $(document).unbind('keydown').bind('keydown',
        //        function (e) {
        //            if ($("#pnlSoal").length) {
        //                var keycode = e.which || e.keyCode;
        //                if (keycode === 116) {
        //                    e.preventDefault();
        //                    $.ajax({
        //                        type: 'GET',
        //                        url: '/Assets/Scripts/mockjs/dummyRequestSoalJSON.txt',
        //                        data: { get_param: 'value' },
        //                        dataType: 'text',
        //                        success: function (data) {
        //                            var jsonData = JSON.parse(data);

        //                            $("#pnlSoal").html(jsonData.content);
        //                        }
        //                    });


        //                }
        //            };
        //        });

        //}

        , refreshNotification: function () {
            function startRefreshCounter() {
                setInterval(function () {
                    var $counterMinute = $('.refresh-notification .refresh-minute'),
                        $minute = $counterMinute.attr('data-minute'),
                        $valMinute,

                        $counterSecond = $('.refresh-notification .refresh-second'),
                        $second = $counterSecond.attr('data-second'),
                        $valsecond;

                    if ($second > 0) {
                        $second = $second - 1;
                        $counterSecond.attr('data-second', $second);
                        $counterSecond.html($second);

                    } else {

                        if ($minute > 0) {
                            $minute = $minute - 1;
                            $counterMinute.attr('data-minute', $minute);
                            $counterMinute.html($minute);
                        };

                        if ($minute === 3) {
                            $('.refresh-notification').removeClass('hide');
                        }

                        if ($minute === 0) {
                            location.reload();
                        }

                        $counterSecond.attr('data-second', "59");
                        $counterSecond.html("59");
                        $second = 60;
                    };

                }, 1000);
            };

            if ($('#pnlSoal').length) {
                if ($('.refresh-notification').length < 1) {
                    $('body').append('<div class="refresh-notification hide">Browser akan auto refresh dalam <span class="refresh-minute" data-minute="29">' + 29 + '</span> menit  <span class="refresh-second" data-second="60">' + 60 + '</span> detik, tekan F5 untuk manual refresh</div>');
                    startRefreshCounter();
                }
            }

        }
        , pnlSoalShow: function () {
            var topContent = $.trim($('#pnlSoalTopContent').html());
            if (topContent.length > 0) {
                var $pnlSoalTop = $('#pnlSoalTop');
                var $pnlSoalShowButton = $pnlSoalTop.find('.pnlSoalShowButton');
                $pnlSoalShow = $pnlSoalTop.attr('data-pnlSoal-show');
                $time = $pnlSoalTop.attr('data-time');
                $pnlSoalTop.removeClass('hide');
                $pnlSoalTop.parents('.page-section').find('#audioPlace').addClass('hide');
                $pnlSoalTop.parents('.page-section').find('#pnlSoal').addClass('hide');
                $pnlSoalShowButton.show();
                $pnlSoalTop.find('.textImage').addClass('blur');
                if ($pnlSoalShow) {
                    $pnlSoalShowButton.on('click', function () {
                        CBTWidget.disableOnAudioPlay();
                        CBTWidget.disableKeyEnter();
                        // call web services
                        //var soalID = options.examID;
                        var soalID = $('.hfDetailSequenceNo').val();
                        Type = "GET";
                        //Url = options.endedService;


                        $.post($('.hfSoalUpdateTopContent').val(),
                            { puspendikData1: encodeURIComponent(soalID) })
                            .done(function (data) {
                                //CallService();
                                $(this).hide();
                                $pnlSoalTop.find('.textImage').removeClass('blur');
                                setTimeout(function () {
                                    $pnlSoalTop.addClass('hide');
                                    $pnlSoalTop.parents('.page-section').find('#audioPlace').removeClass('hide');
                                    $pnlSoalTop.parents('.page-section').find('#pnlSoal').removeClass('hide');
                                    CBTWidget.enableAfterAudioPlayed();
                                }, $time);
                            });

                        //Url = "https://" + window.location.hostname + "/unbk/CbtService/UpdateTopContentStatus?puspendikdata1=" + encodeURIComponent(soalID);
                        //Data = '{"detailSequenceNo": "' + soalID + '"}';
                        //ContentType = "application/json; charset=utf-8";
                        //DataType = "json"; varProcessData = true;
                        //ProcessData = "FALSE";
                        //CallService();
                        //$(this).hide();
                        //$pnlSoalTop.find('.textImage').removeClass('blur');
                        //setTimeout(function () {
                        //    $pnlSoalTop.addClass('hide');
                        //    $pnlSoalTop.parents('.page-section').find('#audioPlace').removeClass('hide');
                        //    $pnlSoalTop.parents('.page-section').find('#pnlSoal').removeClass('hide');
                        //    CBTWidget.enableAfterAudioPlayed();
                        //}, $time);
                    });
                }
            }
        }

    };

})();
/**
 * http://www.openjs.com/scripts/events/keyboard_shortcuts/
 * Version : 2.01.B
 * By Binny V A
 * License : BSD
 */
shortcut = {
    'all_shortcuts': {},//All the shortcuts are stored in this array
    'add': function (shortcut_combination, callback, opt) {
        //Provide a set of default options
        var default_options = {
            'type': 'keydown',
            'propagate': false,
            'disable_in_input': true,
            'target': document,
            'keycode': false
        }
        if (!opt) opt = default_options;
        else {
            for (var dfo in default_options) {
                if (typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
            }
        }

        var ele = opt.target;
        if (typeof opt.target == 'string') ele = document.getElementById(opt.target);
        var ths = this;
        shortcut_combination = shortcut_combination.toLowerCase();

        //The function to be called at keypress
        var func = function (e) {
            e = e || window.event;

            if (opt['disable_in_input']) { //Don't enable shortcut keys in Input, Textarea fields
                var element;
                if (e.target) element = e.target;
                else if (e.srcElement) element = e.srcElement;
                if (element.nodeType == 3) element = element.parentNode;

                if (element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') return;
            }

            //Find Which key is pressed
            if (e.keyCode) code = e.keyCode;
            else if (e.which) code = e.which;
            var character = String.fromCharCode(code).toLowerCase();

            if (code == 188) character = ","; //If the user presses , when the type is onkeydown
            if (code == 190) character = "."; //If the user presses , when the type is onkeydown

            var keys = shortcut_combination.split("+");
            //Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
            var kp = 0;

            //Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
            var shift_nums = {
                "`": "~",
                "1": "!",
                "2": "@",
                "3": "#",
                "4": "$",
                "5": "%",
                "6": "^",
                "7": "&",
                "8": "*",
                "9": "(",
                "0": ")",
                "-": "_",
                "=": "+",
                ";": ":",
                "'": "\"",
                ",": "<",
                ".": ">",
                "/": "?",
                "\\": "|"
            }
            //Special Keys - and their codes
            var special_keys = {
                'esc': 27,
                'escape': 27,
                'tab': 9,
                'space': 32,
                'return': 13,
                'enter': 13,
                'backspace': 8,

                'scrolllock': 145,
                'scroll_lock': 145,
                'scroll': 145,
                'capslock': 20,
                'caps_lock': 20,
                'caps': 20,
                'numlock': 144,
                'num_lock': 144,
                'num': 144,

                'pause': 19,
                'break': 19,

                'insert': 45,
                'home': 36,
                'delete': 46,
                'end': 35,

                'pageup': 33,
                'page_up': 33,
                'pu': 33,

                'pagedown': 34,
                'page_down': 34,
                'pd': 34,

                'left': 37,
                'up': 38,
                'right': 39,
                'down': 40,

                'f1': 112,
                'f2': 113,
                'f3': 114,
                'f4': 115,
                'f5': 116,
                'f6': 117,
                'f7': 118,
                'f8': 119,
                'f9': 120,
                'f10': 121,
                'f11': 122,
                'f12': 123
            }

            var modifiers = {
                shift: { wanted: false, pressed: false },
                ctrl: { wanted: false, pressed: false },
                alt: { wanted: false, pressed: false },
                meta: { wanted: false, pressed: false }	//Meta is Mac specific
            };

            if (e.ctrlKey) modifiers.ctrl.pressed = true;
            if (e.shiftKey) modifiers.shift.pressed = true;
            if (e.altKey) modifiers.alt.pressed = true;
            if (e.metaKey) modifiers.meta.pressed = true;

            for (var i = 0; k = keys[i], i < keys.length; i++) {
                //Modifiers
                if (k == 'ctrl' || k == 'control') {
                    kp++;
                    modifiers.ctrl.wanted = true;

                } else if (k == 'shift') {
                    kp++;
                    modifiers.shift.wanted = true;

                } else if (k == 'alt') {
                    kp++;
                    modifiers.alt.wanted = true;
                } else if (k == 'meta') {
                    kp++;
                    modifiers.meta.wanted = true;
                } else if (k.length > 1) { //If it is a special key
                    if (special_keys[k] == code) kp++;

                } else if (opt['keycode']) {
                    if (opt['keycode'] == code) kp++;

                } else { //The special keys did not match
                    if (character == k) kp++;
                    else {
                        if (shift_nums[character] && e.shiftKey) { //Stupid Shift key bug created by using lowercase
                            character = shift_nums[character];
                            if (character == k) kp++;
                        }
                    }
                }
            }

            if (kp == keys.length &&
						modifiers.ctrl.pressed == modifiers.ctrl.wanted &&
						modifiers.shift.pressed == modifiers.shift.wanted &&
						modifiers.alt.pressed == modifiers.alt.wanted &&
						modifiers.meta.pressed == modifiers.meta.wanted) {
                callback(e);

                if (!opt['propagate']) { //Stop the event
                    //e.cancelBubble is supported by IE - this will kill the bubbling process.
                    e.cancelBubble = true;
                    e.returnValue = false;

                    //e.stopPropagation works in Firefox.
                    if (e.stopPropagation) {
                        e.stopPropagation();
                        e.preventDefault();
                    }
                    return false;
                }
            }
        }
        this.all_shortcuts[shortcut_combination] = {
            'callback': func,
            'target': ele,
            'event': opt['type']
        };
        //Attach the function with the event
        if (ele.addEventListener) ele.addEventListener(opt['type'], func, false);
        else if (ele.attachEvent) ele.attachEvent('on' + opt['type'], func);
        else ele['on' + opt['type']] = func;
    },

    //Remove the shortcut - just specify the shortcut and I will remove the binding
    'remove': function (shortcut_combination) {
        shortcut_combination = shortcut_combination.toLowerCase();
        var binding = this.all_shortcuts[shortcut_combination];
        delete (this.all_shortcuts[shortcut_combination])
        if (!binding) return;
        var type = binding['event'];
        var ele = binding['target'];
        var callback = binding['callback'];

        if (ele.detachEvent) ele.detachEvent('on' + type, callback);
        else if (ele.removeEventListener) ele.removeEventListener(type, callback, false);
        else ele['on' + type] = false;
    }
}