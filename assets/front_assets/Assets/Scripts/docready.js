$(document).ready(function(){
	/* silahkan digunakan untuk pemakaian setelah document ready dan digunakan bersama-sama, 
	untuk initialisasi widget/plugin tertentu ataupun untuk menulis beberapa dummies */

    $(".audioVideoData").each(function (idx) {
        var $el = $(this);

        if ($el.attr('data-type') === 'video') {
            $el.audioVideoCBT({
                mediaIndex: idx,
                mediaType: $el.attr('data-type'), // "audio" for audio AND "video" for video
                pathFile: $el.attr('data-file'), // the file location
                fileTitle: $el.attr('data-title'), // the file's label for show on the player 
                fileThumbnail: $el.attr('data-thumbnail'),
                examID: $el.attr('data-examid')
            });
        } else {

            if ($el.attr('data-type') === 'audio') {
                $el.audioVideoCBT({
                    mediaIndex: idx,
                    mediaType: $el.attr('data-type'), // "audio" for audio AND "video" for video
                    pathFile: $el.attr('data-file'), // the file location
                    fileTitle: $el.attr('data-title'), // the file's label for show on the player 
                    examID: $el.attr('data-examid')
                });

            }

        }

    });

});