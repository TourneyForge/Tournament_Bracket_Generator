function getSeconds(time) {
    const timeArray = time.split(":");

    return parseInt(timeArray[0] * 3600) + parseInt(timeArray[1] * 60) + parseInt(timeArray[2]);
}

function audioSettingToggleChange(element) {
    const settingPanel = $(element).parents('.audio-setting').find('.setting');
    if ($(element).prop("checked") == true) {
        settingPanel.find('input[type="radio"], input[type="checkbox"], .preview input').attr('disabled', false);
        const radioElement = $(element).parent().parent().find('input[type="radio"]:checked');
        radioElement.parent().parent().children('.audio-source').attr('disabled', false);
        settingPanel.removeClass('visually-hidden');

        if ($(element).data('media-type') == 0) {
            $('.toggle-audio-settings').eq(2).prop('checked', false)
            audioSettingToggleChange($('.toggle-audio-settings').eq(2))
            $('.toggle-audio-settings').eq(2).prop('disabled', true)
        }
        if ($(element).data('media-type') == 2) {
            $('.toggle-audio-settings').eq(0).prop('checked', false)
            audioSettingToggleChange($('.toggle-audio-settings').eq(0))
            $('.toggle-audio-settings').eq(0).prop('disabled', true)
        }
    } else {
        settingPanel.find('input[type!="hidden"]').attr('disabled', true);
        settingPanel.addClass('visually-hidden');

        if ($(element).data('media-type') == 0) {
            $('.toggle-audio-settings').eq(2).prop('disabled', false)
        }
        if ($(element).data('media-type') == 2) {
            $('.toggle-audio-settings').eq(0).prop('disabled', false)
        }
    }

    settingPanel.find('.duration[type="text"]').attr('disabled', true);
};

function audioSourceChange(element) {
    $(element).parents('.setting').find('.audio-source').attr('disabled', true);
    const panel = $(element).parent().parent();

    if ($(element).data('target') == 'file') {
        panel.children('[data-source="file"]').attr('disabled', false);
        $(element).parents('.setting').find('.fileupload-hint').removeClass('d-none');
        $(element).parents('.setting').find('.urlupload-hint').addClass('d-none');
    }

    if ($(element).data('target') == 'url') {
        panel.children('[data-source="url"]').attr('disabled', false);
        $(element).parents('.setting').find('.fileupload-hint').addClass('d-none');
        $(element).parents('.setting').find('.urlupload-hint').removeClass('d-none');
    }

};

function audioFileUpload(element) {
    var allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mid', 'audio/x-midi'];

    if (element.files[0] && !allowedTypes.includes(element.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload audio as *.mp3, *.wav, *.midi format.')
        $("#errorModal").modal('show');

        element.value = '';
        return
    }

    if (element.files[0] && element.files[0].size > 102400000) {
        $('#errorModal .errorDetails').html('Max audio size is 100MB. Please upload small audio.')
        $("#errorModal").modal('show');
        
        element.value = '';
        return
    }

    let panel = $(element).parent();
    let index = $('.audio-source[data-source="file"]').index($(element));
    $(this).parents('.audio-setting').find('input[type="radio"][value="f"]').prop('checked', true);

    var formData = new FormData();
    formData.append('audio', element.files[0]);
    $.ajax({
        url: apiURL + '/tournaments/upload',
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
            $("#beforeProcessing").removeClass('d-none')
        },
        success: function (data) {
            $("#beforeProcessing").addClass('d-none')

            if (data.errors) {
                $('#errorModal .errorDetails').html(data.errors.audio)
                $("#errorModal").modal('show');

                return false
            }
            else {
                let audioElement = panel.parents('.audio-setting').find('.player');
                applyDuration(audioElement[0], panel.parents('.audio-setting'))
                audioElement.removeClass('d-none')
                audioElement[0].src = '/uploads/' + data.path
                audioElement[0].load();

                panel.find('input[type="hidden"]').val(data.path);

                if (index == 0 && document.getElementById('myAudio')) {
                    document.getElementById('audioSrc').setAttribute('src', '/uploads/' + data.path);
                    document.getElementById('myAudio').load();
                }

                panel.parents('.audio-setting').find('.startAt[type="hidden"]').val(0);
                panel.parents('.audio-setting').find('.startAt[type="text"]').val("00:00:00");
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function videoFileUpload(element) {
    var allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];

    if (element.files[0] && !allowedTypes.includes(element.files[0].type)) {
        $('#errorModal .errorDetails').html('Please upload video as *.mp4, *.webm, *.ogg format.')
        $("#errorModal").modal('show');

        element.value = '';
        return
    }

    if (element.files[0] && element.files[0].size > 512000000) {
        $('#errorModal .errorDetails').html('Max video size is 500MB. Please upload small image.')
        $("#errorModal").modal('show');
        
        element.value = '';
        return
    }

    let panel = $(element).parent();
    let index = $('.audio-source[data-source="file"]').index($(element));
    $(this).parents('.audio-setting').find('input[type="radio"][value="f"]').prop('checked', true);

    var formData = new FormData();
    formData.append('video', element.files[0]);
    $.ajax({
        url: apiURL + '/tournaments/upload-video',
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
            $("#beforeProcessing").removeClass('d-none')
        },
        success: function (data) {
            $("#beforeProcessing").addClass('d-none')

            if (data.errors) {
                $('#errorModal .errorDetails').html(data.errors.audio)
                $("#errorModal").modal('show');

                return false
            }
            else {
                panel.find('input[type="hidden"]').val(data.path);

                let audioElement = panel.parents('.audio-setting').find('.player');
                applyDuration(audioElement[0], panel.parents('.audio-setting'))
                audioElement.removeClass('d-none')
                audioElement[0].src = '/uploads/' + data.path
                audioElement[0].load();

                if (index == 0 && document.getElementById('myAudio')) {
                    document.getElementById('audioSrc').setAttribute('src', '/uploads/' + data.path);
                    document.getElementById('myAudio').load();
                }

                panel.parents('.audio-setting').find('.startAt[type="hidden"]').val(0);
                panel.parents('.audio-setting').find('.startAt[type="text"]').val("00:00:00");
            }
        },
        error: function (e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function applyDuration(audioElement, panel) {
    $(audioElement).on("loadedmetadata", function () {
        const date = new Date(null);
        date.setSeconds(this.duration);
        panel.find('.stopAt[type="hidden"]').val(this.duration);
        panel.find('.stopAt[type="text"]').val(date.toISOString().slice(11, 19));
        panel.find('.duration').val(this.duration);
    });
}

function audioDurationChange(element) {
    const starttime = getSeconds($(element).parents('.preview').find('.startAt').val());
    $(element).parents('.preview').find('.startAt[type="hidden"]').val(starttime);
    const stoptime = getSeconds($(element).parents('.preview').find('.stopAt').val());
    $(element).parents('.preview').find('.stopAt[type="hidden"]').val(stoptime);

    if (starttime >= 0 && stoptime >= 0) {
        if ((stoptime - starttime) <= 0) {

        }

        $(element).parents('.preview').find('.duration').val(stoptime - starttime);
    }
}
