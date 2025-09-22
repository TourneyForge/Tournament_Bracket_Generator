var ws;
$(document).on('ready', function () {
    try {
        ws = new WebSocket('ws://' + location.hostname + ':8089');
        ws.onopen = function(e) {
            console.log("Connection established!");
            if ($('#brackets').length) {
                loadBrackets();
            }
        };

        ws.onmessage = function(e) {
            console.log(e.data);

            let data = e.data.split(',')
            if (data[0] == "updateNotifications") {
                $.ajax({
                    type: "get",
                    url: apiURL + '/notifications/get-notifications',
                    contentType: "application/json",
                    success: function (result) {
                        if (result.status == 'success') {
                            let notifications = result.notifications
                            let notificationBtn = $('.notification-box > button').first()

                            if (notifications) {
                                if ($('.notification-box > button badge').length) {
                                    $('.notification-box > button badge').html(notifications.length)
                                } else {
                                    notificationBtn.append(`<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">${notifications.length}</span>`)
                                }

                                $('.notification-box .dropdown-menu .notification').remove()
                                notifications.forEach((notification, i) => {
                                    $('.notification-box .clear-notifications').show()
                                    $('.notification-box .dropdown-menu').append(`
                                        <li class="notification">
                                            <p class="dropdown-item border-bottom p-2 pe-5">
                                                <a class="text-end ms-2 me-4" href="javascript:;" onclick="readNotification(this)" data-link="${notification.link}" data-id="${notification.id}">
                                                    <span>${notification.message}</span><br />
                                                    <span class="d-block w-100 text-end">By ${notification.created_by} on ${notification.created_at}</span>
                                                </a>
                                                <a class="delete" onclick="deleteNotification(this)" data-id="${notification.id}"><i class="fa fa-remove"></i></a>
                                            </p>
                                        </li>
                                        `)
                                })
                            }
                        }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                }).done(() => {
                    setTimeout(function () {
                        $("#overlay").fadeOut(300);
                    }, 500);
                });

                
            }

            if (data[0] == "tournamentUpdated") {
                const pathname = window.location.pathname
                const paths = pathname.split(/(\/|\?)/)

                if (paths[2] == 'gallery') {
                    tournamentsTable.ajax.reload()
                }

                if (paths[2] == 'participants') {
                    participantsTable.ajax.reload()
                    drawChart()
                }
            }

            if ($('#brackets').length) {
                if (data[1] == tournament_id) {
                    if (data[0] == "winnerChange") {
                        loadBrackets('initConfetti');
                    } else {
                        loadBrackets();
                    }
                }
            }
        };
    }catch(exception){
        alert("Websocket is not running now. The result will not be updated real time.");
    }
});

let getCookie = (cname) => {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

setCookie = (name, value, days) => {
    const d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

let acceptCookies = () => {
    localStorage.setItem('cookie_consent', 'accepted')
    // setCookie('cookie_consent', 'accepted', 365);
    document.getElementById('cookieConsentModal').style.display = 'none';
}

let rejectCookies = () => {
    localStorage.setItem('cookie_consent', 'rejected')
    // setCookie('cookie_consent', 'rejected', 365);
    document.getElementById('cookieConsentModal').style.display = 'none';
    alert('Cookies rejected. To reactivate, clear your browser history and visit the site again.');
}

function getOrCreateDeviceId() {
    let deviceId = localStorage.getItem('deviceId');
    if (!deviceId) {
        function hashString(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = (hash << 5) - hash + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            return hash;
        }

        // Convert the hash to a UUID-like format
        function formatToUUID(hash) {
            const hexString = (hash >>> 0).toString(16);
            return `${hexString.substring(0, 8)}-${hexString.substring(8, 12)}-${hexString.substring(12, 16)}-${hexString.substring(16, 20)}-${hexString.substring(20, 32)}`;
        }

        const navigatorInfo = window.navigator.userAgent;  // User agent info
        const screenInfo = `${screen.height}x${screen.width}x${screen.colorDepth}`;  // Screen resolution and color depth

        // Combine device information into a single string
        const deviceInfo = navigatorInfo + screenInfo;

        const hashedDeviceInfo = hashString(deviceInfo);
        deviceId = formatToUUID(hashedDeviceInfo);

        localStorage.setItem('deviceId', deviceId);
    }
    
    return deviceId;
}

var updateStorage = (key, value, type = 'add') => {
    let existingValue

    if ((typeof tournament_id !== 'undefined' && key == 'alert-expanded-' + tournament_id) || (key == 'alert-expanded-pl')) {
        existingValue = JSON.parse(localStorage.getItem(key))
        if (!existingValue) {
            existingValue = []
        }

        if (type == 'add') {
            if (!existingValue.filter(item => item == value).length) {
                existingValue.push(value)
            }
        }

        if (type == 'remove') {
            existingValue = existingValue.filter(item => item !== value)
        }

        value = JSON.stringify(existingValue)
    }

    if (typeof tournament_id !== 'undefined' && key == 'alert-expanded-' + tournament_id) {

        if (!existingValue.length || existingValue.length < (document.querySelectorAll('.alert-btn-container .btn').length - 1)) {
            localStorage.setItem('collapse-on-t-' + tournament_id, true)
            document.getElementById('expandBtn').classList.remove('d-none')
            document.getElementById('collapseBtn').classList.add('d-none')
        }

        if (existingValue.length && existingValue.length == (document.querySelectorAll('.alert-btn-container .btn').length - 1)) {
            localStorage.removeItem('collapse-on-t-' + tournament_id)
            document.getElementById('expandBtn').classList.add('d-none')
            document.getElementById('collapseBtn').classList.remove('d-none')
        }
    }

    if (key == 'alert-expanded-pl') {
        if (!existingValue.length || existingValue.length < (document.querySelectorAll('.alert-btn-container .btn').length)) {
            localStorage.removeItem('collapse-on-pl')
            document.getElementById('expandBtn').classList.remove('d-none')
            document.getElementById('collapseBtn').classList.add('d-none')
        }

        if (existingValue.length && existingValue.length == (document.querySelectorAll('.alert-btn-container .btn').length)) {
            localStorage.setItem('collapse-on-pl', true)
            document.getElementById('expandBtn').classList.add('d-none')
            document.getElementById('collapseBtn').classList.remove('d-none')
        }
    }

    localStorage.setItem(key, value)
}

let appendAlert = (message, type) => {
    const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
    if (alertPlaceholder) {
        alertPlaceholder.innerHTML = ''
        const wrapper = document.createElement('div')

        if (Array.isArray(message)) {
            wrapper.innerHTML = ''
            message.forEach((item, i) => {
                wrapper.innerHTML += [
                    `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                    `   <div>${item}</div>`,
                    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                    '</div>'
                ].join('')
            })
        } else {
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                `   <div>${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('')
        }

        alertPlaceholder.append(wrapper)

        $("div.alert").fadeTo(5000, 500).slideUp(500, function() {
            $("div.alert").slideUp(500);
        });
    }
}

let appendNotification = (message, type) => {
    const notificationPlaceholder = document.getElementById('notificationAlertPlaceholder')
    if (notificationPlaceholder) {
        notificationPlaceholder.innerHTML = ''
        const wrapper = document.createElement('div')

        if (Array.isArray(message)) {
            wrapper.innerHTML = ''
            message.forEach((item, i) => {
                wrapper.innerHTML += [
                    `<div class="alert alert-${type} alert-dismissible" role="alert">`,
                    `   <div>${item}</div>`,
                    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                    '</div>'
                ].join('')
            })
        } else {
            wrapper.innerHTML = [
                `<div class="alert alert-${type} alert-dismissible position-fixed top-1 end-0 z-3 me-3 mt-1" role="alert">`,
                `   <div class="d-flex">${message}</div>`,
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                '</div>'
            ].join('')
        }

        notificationPlaceholder.append(wrapper)

        $("div.alert").fadeTo(3000, 500).slideUp(500, function() {
            $("div.alert").slideUp(500);
        });
    }

}

let readNotification = (notificationElement) => {
    const link = $(notificationElement).data('link')
    const notificationId = $(notificationElement).data('id')

    $.ajax({
        type: "put",
        url: `${apiURL}/notifications/mark-as-read/${notificationId}`,
        success: function(result) {
            $(notificationElement).remove()
            window.location.href = link
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

let deleteNotification = (notificationElement) => {
    const notificationId = $(notificationElement).data('id')

    $.ajax({
        type: "delete",
        url: `${apiURL}/notifications/delete/${notificationId}`,
        success: function(result) {
            $(notificationElement).parent().remove()

            if ($('.notification-box .notification').length == 0) {
                let liElement = document.createElement('li')
                let pElement = document.createElement('p')
                pElement.setAttribute('class', "dropdown-item notification p-2 pe-5")
                pElement.textContent = 'No Notifications'
                liElement.append(pElement)
                $('.notification-box .dropdown-menu').append(liElement)
            }

            let count = parseInt($('.notification-box span.badge').html())
            if (count > 1) {
                $('.notification-box span.badge').html(count - 1)
            } else {
                $('.notification-box span.badge').remove()
            }
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

let clearNotifications = () => {
    $.ajax({
        type: "delete",
        url: `${apiURL}/notifications/clear`,
        success: function(result) {
            $('.notification-box .dropdown-menu .notification').remove()
            $('.notification-box span.badge').remove()
            $('.notification-box .clear-notifications').hide()
            $('#clearNotificationsModal').modal('hide')
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}
    
let toggleShuffleParticipants = (checkbox) => {
    var enableShufflingHint = document.querySelector('.enable-shuffling-hint');
    var disableShufflingHint = document.querySelector('.disable-shuffling-hint');

    if (checkbox.checked) {
        enableShufflingHint.classList.remove('d-none');
        disableShufflingHint.classList.add('d-none');
    } else {
        enableShufflingHint.classList.add('d-none');
        disableShufflingHint.classList.remove('d-none');
    }
}

const stopBtn = document.getElementById('stopAudioButton')
if (stopBtn) {
    stopBtn.addEventListener('click', function () {
        stopAudioPlaying()
    });
}

let stopAudioPlaying = () => {
    // Your code to stop audio goes here
    const audio = document.getElementById('myAudio');

    if (audio.paused) {
        audio.play().then(() => {
            document.getElementById('stopAudioButton').textContent = "Pause Audio";
        }).catch((error) => {
            console.log('Audio play failed:', error);
            document.getElementById('stopAudioButton').textContent = "Start Audio";
        });
    } else {
        audio.pause();
        document.getElementById('stopAudioButton').textContent = "Resume Audio";
    }
}

let stopVideoPlaying = () => {
    // Your code to stop audio goes here
    const videoPlayer = document.getElementById('videoPlayer');

    if (videoPlayer.paused) {
        videoPlayer.play();
        document.getElementById('stopVideoButton').textContent = "Pause Video"
    } else {
        videoPlayer.pause();
        document.getElementById('stopVideoButton').textContent = "Resume Video"
    }
}

let saveGeneralSettings = () => {
    form = $('#settingsForm')

  $.ajax({
    url: apiURL + '/usersettings/save',
    type: 'POST',
    data: form.serialize(),
    success: function(response) {
      if (response.status == 'success') {
        // Close the modal
        $('#settingsModal').modal('hide');
      } else {
        alert('Failed to save settings');
        }
        $('#settingsModal').modal('hide')
    },
    error: function() {
      alert('An error occurred while saving the settings');
    }
  });
}

let changeEmailNotificationSetting = (element) => {
    let value = element.checked ? 'on' : 'off'
    $.ajax({
        url: apiURL + '/usersettings/save',
        type: 'POST',
        data: {'email_notification': value},
        success: function (response) {
            console.log(element.value)
        },
        error: function() {
            alert('An error occurred while saving the settings');
        }
    });
}

$(document).ready(function () {
    const timezoneSelect = $('#timezone');
    const timezones = moment.tz.names();

    timezones.forEach(timezone => {
        const option = new Option(timezone, timezone);
        if (timezone === defaultTimezone) {
            option.selected = true;
        }
        timezoneSelect.append(option);
    });

    timezoneSelect.on('change', function() {
        const selectedTimezone = $(this).val();
        updateTime(selectedTimezone);

        let currentYear = new Date().getFullYear();
        let dstStart = getSecondSundayOfMarch(currentYear, selectedTimezone);
        const formattedDate = formatDateToTimeZone(dstStart, selectedTimezone);

        // Update other timezone information if needed
        $('#timezoneStatus').text(`This timezone is currently in ${selectedTimezone}.`);
        $('#daylightSaving').text(`Daylight saving time begins on: ${formattedDate}.`);
    });
    $('[data-toggle="tooltip"]').tooltip();
})

function getSecondSundayOfMarch(year, timeZone) {
    // Helper function to convert local date to a given timezone
    function toTimeZone(date, timeZone) {
        return new Date(date.toLocaleString('en-US', { timeZone }));
    }

    // Get the local date for March 1st of the given year
    let localDate = new Date(year, 2, 1);

    // Convert the local date to the specified timezone
    let tzDate = toTimeZone(localDate, timeZone);

    // Get the day of the week (0-6, where 0 is Sunday)
    let day = tzDate.getUTCDay();

    // Calculate the second Sunday of March
    let secondSunday = 7 + (7 - day) % 7 + 1;

    // Create a new date for the second Sunday in the specified timezone
    let secondSundayDate = new Date(Date.UTC(year, 2, secondSunday));

    // Convert back to the specified timezone
    let finalDate = toTimeZone(secondSundayDate, timeZone);

    return finalDate;
}

function formatDateToTimeZone(date, timeZone) {
    return date.toLocaleString('en-US', {
        timeZone,
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        timeZoneName: 'short'
    });
}

function formatTime(date, options) {
    return new Intl.DateTimeFormat('en-US', options).format(date);
}

function updateTime(selectedTimezone) {
    const utcDate = new Date().toLocaleString("en-US", { timeZone: 'UTC' });
    const localDate = new Date().toLocaleString("en-US", { timeZone: selectedTimezone });
    const formattedUtcTime = formatTime(new Date(utcDate), { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    const formattedLocalTime = formatTime(new Date(localDate), { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });

    $('#utcTime').text(formattedUtcTime);
    $('#localTime').text(formattedLocalTime);
}

let toggleScoreOption = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('#scorePerBracket').prop('disabled', false)
        //$('#incrementScore').prop('disabled', false)
        $('#scoreOptions').removeClass('d-none')
        $('.enable-scoreoption-hint').removeClass('d-none')
    } else {
        $('#scorePerBracket').prop('disabled', true)
        $('#enableIncrementScore').prop('checked', false)
        $('#incrementScore').prop('disabled', true)
        $('#scoreOptions').addClass('d-none')
        $('.enable-scoreoption-hint').addClass('d-none')
    }
}

let toggleIncrementScore = (element) => {
    if ($(element).is(':checked')) {
        $('#incrementScore').prop('disabled', false)
        $('#incrementPlus').prop('disabled', false)
        $('#incrementMultiply').prop('disabled', false)
        $('.enable-increamentscoreoption-hint').removeClass('d-none')
    } else {
        $('#incrementScore').prop('disabled', true)
        $('#incrementPlus').prop('disabled', true)
        $('#incrementMultiply').prop('disabled', true)
        $('.enable-increamentscoreoption-hint').addClass('d-none')
    }

    changeIncrementScoreType($('input:radio[name="increment_score_type"]'))

    document.querySelectorAll("#tournamentSettings .scoring-settings .read-more-container").forEach(container => {
        adjustReadMore(container)
    })
}

let changeIncrementScoreType = (radio) => {
    if ($('input:radio[name="increment_score_type"]:checked').val() == 'p') {
        $('.enable-increamentscoreoption-hint .plus').removeClass('d-none').addClass('text-content')
        $('.enable-increamentscoreoption-hint .multiply').addClass('d-none').removeClass('text-content')
    } else {
        $('.enable-increamentscoreoption-hint .plus').addClass('d-none').removeClass('text-content')
        $('.enable-increamentscoreoption-hint .multiply').removeClass('d-none').addClass('text-content')
    }

    document.querySelectorAll("#tournamentSettings .scoring-settings .read-more-container").forEach(container => {
        adjustReadMore(container)
    })
}

const enableDescriptionEdit = (button) => {
    const descriptionDiv = button.parentElement.querySelector('.description')
    const originalText = descriptionDiv.innerHTML
    originalDescriptionContent = originalText
    descriptionDiv.innerHTML = `<div id="summernote">${originalText}</div>`

    $('#summernote').summernote({
        height: 400,
        callbacks: {
            onMediaDelete: function(target) {
                // Handle media deletion if needed
            },
            onVideoInsert: function(target) {
                $(target).wrap('<div class="responsive-video"></div>');
            }
        }
    })

    let buttonsWrapper = document.createElement('div')
    buttonsWrapper.className = 'd-flex justify-content-end mt-3'

    const saveButton = document.createElement('button')
    saveButton.innerText = 'Save'
    saveButton.className = 'btn btn-primary'
    saveButton.onclick = () => {
        newDescriptionContent = $('#summernote').summernote('code')
        currentDescriptionDiv = descriptionDiv
        $('#saveDescriptionConfirmModal').modal('show')
    }

    const dismissButton = document.createElement('button')
    dismissButton.innerText = 'Discard'
    dismissButton.className = 'btn btn-secondary ms-2'
    dismissButton.onclick = () => {
        currentDescriptionDiv = descriptionDiv
        $('#dismissDescriptionEditConfirmModal').modal('show')
    }

    buttonsWrapper.append(saveButton)
    buttonsWrapper.append(dismissButton)

    descriptionDiv.append(buttonsWrapper)
    
    document.getElementById('editDescriptionBtn').classList.add('d-none')
}

const saveDescription = () => {
    $.ajax({
        url: apiURL + `/tournaments/${tournament_id}/update`,
        type: 'POST',
        data: {
            description: newDescriptionContent
        },
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
            $('#saveDescriptionConfirmModal').modal('hide')
        },
        success: function(response) {
            currentDescriptionDiv.innerHTML = newDescriptionContent
            document.getElementById('editDescriptionBtn').classList.remove('d-none')
            $('#beforeProcessing').addClass('d-none')
        },
        error: function() {
            alert('Failed to save description.')
        }
    })
}

const dismissEdit = () => {
    currentDescriptionDiv.innerHTML = originalDescriptionContent
    document.getElementById('editDescriptionBtn').classList.remove('d-none')
    $('#dismissDescriptionEditConfirmModal').modal('hide')
}

var changeEliminationType = (element) => {
    let parent = $(element).parent();
    parent.find('.form-text').addClass('d-none');
    parent.find('.form-text').removeClass('text-content');
    parent.find('.elimination-type-update-note').html('')

    if ($(element).val() == 1) {
        parent.find('.single-type-hint').removeClass('d-none');
        parent.find('.single-type-hint').addClass('text-content');
    }
    if ($(element).val() == 2) {
        parent.find('.double-type-hint').removeClass('d-none');
        parent.find('.double-type-hint').addClass('text-content');
    }
    if ($(element).val() == 3) {
        parent.find('.knockout-type-hint').removeClass('d-none');
        parent.find('.knockout-type-hint').addClass('text-content');
    }

    if (typeof actionLogsTable != 'undefined') {
        parent.find('.text-content').append(parent.find('.elimination-type-hint').html())
    }

    adjustReadMore(parent.find('.read-more-container')[0])
}

let toggleVisibility = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('.visibility-hint').removeClass('d-none');
    } else {
        $('.visibility-hint').addClass('d-none');
    }
}

let toggleAvailability = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('#enableAvailability').removeClass('is-invalid')
        $('.availability-option').removeClass('d-none');
        $('.startAv').attr('disabled', false);
        $('.endAv').attr('disabled', false);
        $('#startAvPickerInput').attr('required', true)

        $('.evaluation-vote-round-availability-required').addClass('d-none')
        $('#votingMechanism').removeClass('is-invalid')
        $('.round-duration-combine-required').addClass('d-none')
        $('#roundDurationCheckbox').removeClass('is-invalid')
    } else {
        $('.availability-option').addClass('d-none');
        $('.startAv').attr('disabled', true);
        $('.endAv').attr('disabled', true);
        $('#startAvPickerInput').attr('required', false)

        if ($('#votingMechanism').val() == 1) {
            $('.evaluation-vote-round-availability-required').removeClass('d-none')
            $('#votingMechanism').addClass('is-invalid')
        }

        if ($('#evaluationMethod').val() == "m" || ($('#evaluationMethod').val() == "v" && $('#votingMechanism').val() == 2)) {
            $('.round-duration-combine-required').removeClass('d-none')
            $('#roundDurationCheckbox').addClass('is-invalid')
        }
    }

    toggleRoundDuration($('#roundDurationCheckbox'))
}

let toggleRoundDuration = (checkbox) => {
    if ($(checkbox).is(':checked')) {
        $('#enableAvailability').attr('required', true)

        if (!$('#enableAvailability').is(':checked')) {
            $('#enableAvailability').addClass('is-invalid')

            $('.round-duration-combine-required').removeClass('d-none')
            $('#roundDurationCheckbox').addClass('is-invalid')
        }
    } else {
        $('#enableAvailability').attr('required', false)
        $('#enableAvailability').removeClass('is-invalid')

        if (!$('#enableAvailability').is(':checked')) {
            $(checkbox).addClass('is-invalid')
        }

        $('.round-duration-combine-required').addClass('d-none')
        $('#roundDurationCheckbox').removeClass('is-invalid')
    }
}

var changeEvaluationMethod = (element) => {
    $(element).parent().parent().find('.form-text').removeClass('text-content')
    $('.round-duration-combine .form-text').removeClass('text-content')
    // EVALUATION_METHOD_MANUAL = m
    // EVALUATION_METHOD_VOTING = v
    if ($(element).val() == "m") {
        $('.voting-settings-panel').addClass('d-none')
        $('.evaluation-method-manual-hint').removeClass('d-none')
        $('.evaluation-method-voting-hint').addClass('d-none')
        $('#enableAvailability').prop('required', false)

        $('.round-duration-combine, .round-duration-combine .round-duration-manual-checkbox-hint').removeClass('d-none')
        $('.round-duration-combine .round-duration-maxVote-checkbox-hint').addClass('d-none')

        $('.evaluation-method-manual-hint').addClass('text-content')
        $('.round-duration-combine .round-duration-manual-checkbox-hint').addClass('text-content')
    } else {
        $('.voting-settings-panel').removeClass('d-none')
        $('.evaluation-method-manual-hint').addClass('d-none')
        $('.evaluation-method-voting-hint').removeClass('d-none')
        
        $('.round-duration-combine, .round-duration-combine .form-text').addClass('d-none')

        if ($('#votingMechanism').val() == 1) {
            $('#enableAvailability').prop('required', true)
            $('.round-duration-combine .round-duration-manual-checkbox-hint').addClass('text-content')
        }

        if ($('#votingMechanism').val() == 2) {
            $('.round-duration-combine, .round-duration-combine .round-duration-maxVote-checkbox-hint').removeClass('d-none')
            $('.round-duration-combine .round-duration-maxVote-checkbox-hint').addClass('text-content')
        }

        $('.evaluation-method-voting-hint').addClass('text-content')
    }

    document.querySelectorAll("#tournamentSettings .evaluation-settings .read-more-container").forEach(container => {
        adjustReadMore(container)
    })

    toggleAvailability($('#enableAvailability'))
}

var changeVotingAccessbility = (element) => {
    // EVALUATION_VOTING_RESTRICTED = 1
    // EVALUATION_VOTING_UNRESTRICTED = 0
    if (parseInt($(element).val()) == 1) {
        $('.evaluation-vote-restricted').removeClass('d-none')
        $('.evaluation-vote-restricted').addClass('text-content')
        $('.evaluation-vote-unrestricted').addClass('d-none')
        $('.evaluation-vote-unrestricted').removeClass('text-content')
    } else {
        $('.evaluation-vote-restricted').addClass('d-none')
        $('.evaluation-vote-restricted').removeClass('text-content')
        $('.evaluation-vote-unrestricted').removeClass('d-none')
        $('.evaluation-vote-unrestricted').addClass('text-content')
    }

    adjustReadMore($(element).parent().parent().find('.read-more-container')[0])
}

var changeVotingMechanism = (element) => {
    $(element).parent().parent().find('.form-text').removeClass('text-content')
    $('.round-duration-combine, .round-duration-combine .form-text').addClass('d-none')
    // EVALUATION_VOTING_MECHANISM_ROUND = 1
    // EVALUATION_VOTING_MECHANISM_MAXVOTE = 2
    // EVALUATION_VOTING_MECHANISM_OPENEND = 3
    if (parseInt($(element).val()) == 1) {
        $('.max-vote-setting').addClass('d-none')
        $('.evaluation-vote-round').removeClass('d-none')
        $('.evaluation-vote-max').addClass('d-none')
        $('.evaluation-open-ended').addClass('d-none')
        $('#maxVotes').attr('required', false)
        $('#votingMechanism').removeClass('is-invalid')
        $('.allow-host-override-setting').removeClass('d-none')

        /** Check if availability is enabled */
        if ($('#enableAvailability').is(':checked') == false) {
            $('#votingMechanism').addClass('is-invalid')
            $('.evaluation-vote-round-availability-required').removeClass('d-none')
            $('#enableAvailability').prop('required', true)
        }

        $('.evaluation-vote-round').addClass('text-content')
    }

    if (parseInt($(element).val()) == 2) {
        $('.max-vote-setting').removeClass('d-none')
        $('.evaluation-vote-round').addClass('d-none')
        $('.evaluation-vote-max').removeClass('d-none')
        $('.evaluation-open-ended').addClass('d-none')
        $('#maxVotes').attr('required', true)
        $('#votingMechanism').removeClass('is-invalid')
        $('.evaluation-vote-round-availability-required').addClass('d-none')
        $('.allow-host-override-setting').removeClass('d-none')
        $('#enableAvailability').prop('required', false)

        $('.round-duration-combine, .round-duration-combine .round-duration-maxVote-checkbox-hint').removeClass('d-none')
        $('.round-duration-combine .round-duration-maxVote-checkbox-hint').addClass('text-content')

        $('.evaluation-vote-max').addClass('text-content')

        toggleAvailability($('#enableAvailability'))
    }

    if (parseInt($(element).val()) == 3) {
        $('#maxVotes').attr('required', false)
        $('.max-vote-setting').addClass('d-none')
        $('.evaluation-vote-round').addClass('d-none')
        $('.evaluation-vote-max').addClass('d-none')
        $('.evaluation-open-ended').removeClass('d-none')
        $('.allow-host-override-setting').addClass('d-none')
        $('#votingMechanism').removeClass('is-invalid')
        $('.evaluation-vote-round-availability-required').addClass('d-none')
        $('#enableAvailability').prop('required', false)

        $('.evaluation-open-ended').addClass('text-content')
    }

    document.querySelectorAll("#tournamentSettings .evaluation-settings .read-more-container").forEach(container => {
        adjustReadMore(container)
    })
}

var changeTournamentTheme = (element) => {
    $('.tournament-theme-settings-hints > div').addClass('d-none')

    if ($(element).val() == "cl") {
        $('.theme-classic-hint').removeClass('d-none')
    }
    if ($(element).val() == "cs") {
        $('.theme-champion-hint').removeClass('d-none')
    }
    if ($(element).val() == "dr") {
        $('.theme-darkroyale-hint').removeClass('d-none')
    }
    if ($(element).val() == "gr") {
        $('.theme-gridiron-hint').removeClass('d-none')
    }
    if ($(element).val() == "mm") {
        $('.theme-modernmetal-hint').removeClass('d-none')
    }
}

var adjustReadMore = (container) => {
    if (container.querySelector(".read-more-btn")) {
        container.querySelector(".read-more-btn").remove();
    }

    let textElement = container.querySelector(".text-content");

    if (!textElement) return false

    // Check if text is overflowing (more than 3 lines)
    function isOverflowing(element) {
        return element.scrollHeight > element.clientHeight;
    }

    if (isOverflowing(textElement)) {
        let readMoreButton = $('<button type="button" class="read-more-btn float-end pe-3">Show More</button>')[0]
        container.append(readMoreButton)

        readMoreButton.addEventListener("click", function () {
            // expanded or text-content
            if (textElement.classList.contains("text-content")) {
                textElement.classList.remove("text-content");
                readMoreButton.textContent = "Show Less";
            } else {
                textElement.classList.add("text-content");
                readMoreButton.textContent = "Show More";
            }
        });
    }
}

var copyClipboard = (url_id) => {
    // Get the text field
    var copyText = document.getElementById(url_id);

    // Select the text field
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    // Copy the text inside the text field
    if (navigator.clipboard) {
        navigator.clipboard.writeText(copyText.value);
    } else {
        document.execCommand('copy');
    }
}

let toggleCollapseAlertBtns = (element) => {
    if (element.classList.contains('collapsee')) {
        document.getElementById('expandBtn').classList.remove('d-none')
        document.getElementById('collapseBtn').classList.add('d-none')

        document.querySelectorAll('.alert-container button.btn-close').forEach((btn) => {
            if (btn.id == 'viewQRBtn') {
                return
            }
            
            btn.click()
        })

        if (typeof tournament_id !== 'undefined') {
            localStorage.setItem('collapse-on-t-' + tournament_id, true)
        } else {
            localStorage.setItem('collapse-on-pl', true)
        }
    } else {
        document.getElementById('expandBtn').classList.add('d-none')
        document.getElementById('collapseBtn').classList.remove('d-none')

        document.querySelectorAll('.alert-container button.btn-close').forEach((btn) => {
            if (btn.id == 'viewQRBtn') {
                return
            }
            
            btn.click()
        })
        
        document.querySelectorAll('.alert-btn-container button').forEach((btn) => {
            if (btn.id == 'viewQRBtn') {
                return
            }
            
            btn.click()
        })

        if (typeof tournament_id !== 'undefined') {
            localStorage.removeItem('collapse-on-t-' + tournament_id)
        } else {
            localStorage.removeItem('collapse-on-pl')
        }
    }
}