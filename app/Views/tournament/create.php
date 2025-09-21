<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Participants<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/themes/nano.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/themes/classic.min.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/at.js/1.5.4/css/jquery.atwho.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js" integrity="sha512-efAcjYoYT0sXxQRtxGY37CKYmqsFVOIwMApaEbrxJr4RwqVVGw8o+Lfh/+59TU07+suZn1BWq4fDl5fdgyCNkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/pickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/pickr.es5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- Popperjs -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha256-BRqBN7dYgABqtY9Hd4ynE+1slnEw+roEPFzQ7TRRfcg=" crossorigin="anonymous"></script>
<!-- Tempus Dominus JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.rawgit.com/ichord/Caret.js/master/dist/jquery.caret.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/at.js/1.5.4/js/jquery.atwho.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
var hash = '<?= uniqid(rand(), TRUE);?>';
</script>
<script src="/js/participants.js"></script>
<script src="/js/tournament.js"></script>
<!-- <script src="/js/player.js"></script> -->
<script type="text/javascript">
let eleminationType;
let tournament = null;
<?php if (isset($tournament)): ?>
tournament = <?= json_encode($tournament) ?>;
<?php endif; ?>
let tournament_id = tournament ? tournament.id : 0;
var user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>;
let shuffle_duration = 10;
let audio = document.getElementById("myAudio");
let audioStartTime = 0;
let duplicates = [];
let insert_count = 0;
let ptNames
let filteredNames
let videoPlayer = document.getElementById('videoPlayer');
let videoStartTime = 0;
let video_duration = 10;
let tournamentTypeConsts = {
    's': '<?= TOURNAMENT_TYPE_SINGLE ?>',
    'd': '<?= TOURNAMENT_TYPE_DOUBLE ?>',
    'k': '<?= TOURNAMENT_TYPE_KNOCKOUT ?>'
};

var enable_confirmPopup = false;

const deviceId = getOrCreateDeviceId();

const itemList = document.getElementById('newList');

let users = <?= json_encode($users) ?>

let group_participants = [];

$(window).on('load', function() {
    $("#preview").fadeIn();
});
$(document).ready(function() {
    if (tournament) {
        document.querySelector('.card-title .tournament-name').textContent = tournament.name
        loadParticipants()
    }

    const linkedPicker1Element = document.getElementById('startAvPicker');
    const linked1 = new tempusDominus.TempusDominus(linkedPicker1Element, {
        'localization': {
            format: 'yyyy-MM-dd HH:mm'
        },
        restrictions: {
            minDate: new Date(),
        },
    });
    const linked2 = new tempusDominus.TempusDominus(document.getElementById('endAvPicker'), {
        useCurrent: false,
        'localization': {
            format: 'yyyy-MM-dd HH:mm'
        }
    });

    //using event listeners
    linkedPicker1Element.addEventListener('change.td', (e) => {
        linked2.updateOptions({
            restrictions: {
                minDate: e.detail.date,
            },
        });
    });

    //using subscribe method
    const subscription = linked2.subscribe('change.td', (e) => {
        linked1.updateOptions({
            restrictions: {
                maxDate: e.date,
            },
        });
    });
    $("textarea#description").summernote({
        callbacks: {
            onMediaDelete: function(target) {
                // Handle media deletion if needed
            },
            onVideoInsert: function(target) {
                $(target).wrap('<div class="responsive-video"></div>');
            },
            onVideoUpload: function(files) {
                uploadVideo(files[0]);
            }

        }
    });

    $("#participantNames").atwho({
        at: "@",
        searchKey: 'username',
        data: users,
        limit: 5, // Show only 5 suggestions
        displayTpl: "<li data-value='@${id}'>${username}</li>",
        insertTpl: "@${username}",
        callbacks: {
            remoteFilter: function(query, callback) {
                if (query.length < 1) return; // Don't fetch on empty query
                $.ajax({
                    url: apiURL + '/tournaments/get-users', // Your API endpoint
                    type: "GET",
                    data: {
                        query: query
                    },
                    dataType: "json",
                    success: function(data) {
                        callback(data);
                    }
                });
            }
        }
    });

    /** Submit the tournament settings when create the new tournament */
    $('#submit').on('click', function(event) {
        const form = document.getElementById('tournamentForm');

        let isValid = true;

        if (document.getElementById('enableAvailability').checked) {
            let startDateInput = document.getElementById('startAvPickerInput')
            let endDateInput = document.getElementById('endAvPickerInput')

            if (!startDateInput.value.trim()) {
                // Trigger validation error for empty readonly field
                document.getElementById('startAvPicker').classList.add("is-invalid");
                startDateInput.addEventListener('change', () => {
                    if (startDateInput.value.trim()) {
                        document.getElementById('startAvPicker').classList.remove("is-invalid");
                    }
                })
                startDateInput.reportValidity(); // Shows default browser error message
                event.preventDefault(); // Prevent form submission

                isValid = false
            }

            if (!endDateInput.value.trim()) {
                // Trigger validation error for empty readonly field
                document.getElementById('endAvPicker').classList.add("is-invalid");
                endDateInput.addEventListener('change', () => {
                    if (endDateInput.value.trim()) {
                        document.getElementById('endAvPicker').classList.remove("is-invalid");
                    }
                })
                endDateInput.reportValidity(); // Shows default browser error message
                event.preventDefault(); // Prevent form submission

                isValid = false
            }

            const currentDate = new Date()
            const startDate = new Date(document.getElementById('startAvPickerInput').value)
            const endDate = new Date(document.getElementById('endAvPickerInput').value)

            if (startDate > endDate) {
                isValid = false
                document.getElementById('availability-end-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-end-date-error').textContent = "Stop date must be greater than start date."
                document.getElementById('availability-end-date-error').classList.remove('d-none')
            }

            if (startDate < currentDate) {
                isValid = false
                document.getElementById('availability-start-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-start-date-error').textContent = "You cannot select a past date/time!"
                document.getElementById('availability-start-date-error').classList.remove('d-none')
            } else {
                document.getElementById('availability-start-date-error').classList.add('d-none')
            }

            if (endDate < currentDate) {
                isValid = false
                document.getElementById('availability-end-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-end-date-error').textContent = "You cannot select a past date/time!"
                document.getElementById('availability-end-date-error').classList.remove('d-none')
            } else {
                document.getElementById('availability-end-date-error').classList.add('d-none')
            }
        }

        $('.audio-setting').each((i, settingBox) => {
            const startTime0 = document.getElementsByName('start[' + i + ']')[0].value;
            const stopTime0 = document.getElementsByName('stop[' + i + ']')[0].value;

            if (parseInt(stopTime0) <= parseInt(startTime0)) {
                document.getElementById('start-time-error-' + i + '').previousElementSibling.classList.add('is-invalid')
                document.getElementById('start-time-error-' + i + '').classList.remove('d-none');
                document.getElementById('stop-time-error-' + i + '').previousElementSibling.classList.add('is-invalid')
                document.getElementById('stop-time-error-' + i + '').classList.remove('d-none');
                isValid = false;
            } else {
                document.getElementById('start-time-error-' + i + '').previousElementSibling.classList.remove('is-invalid')
                document.getElementById('start-time-error-' + i + '').classList.add('d-none');
                document.getElementById('stop-time-error-' + i + '').previousElementSibling.classList.remove('is-invalid')
                document.getElementById('stop-time-error-' + i + '').classList.add('d-none');
            }
        })

        if (!isValid || !form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
            form.classList.add('was-validated');
            return false;
        }

        const values = $('#tournamentForm').serializeArray();

        sendSubmitAjax(values)
    });

    let sendSubmitAjax = (values) => {
        const data = Object.fromEntries(values.map(({
            name,
            value
        }) => [name, value]));

        data['hash'] = hash

        $.ajax({
            url: apiURL + '/tournaments/save',
            type: "POST",
            data: data,
            beforeSend: function() {
                //$("#preview").fadeOut();
                $('#tournamentSettings').modal('hide');
                $('#beforeProcessing').removeClass('d-none')
                $("#err").fadeOut();
                // Store the start time (in milliseconds)
                this.startTime = new Date().getTime();
            },
            success: function(result) {
                // Calculate remaining time to enforce a 1s minimum delay
                const elapsed = new Date().getTime() - this.startTime;
                const remainingDelay = Math.max(1000 - elapsed, 0); // Ensure at least 0ms

                setTimeout(() => {
                    $('#beforeProcessing').addClass('d-none'); // Hide after delay

                    if (result.errors) {
                        if (result.errors === 'duplicated') {
                            const userConfirmed = confirm(result.message);
                            if (userConfirmed) {
                                values.push({
                                    name: 'confirm_duplicate_save',
                                    value: true
                                });
                                sendSubmitAjax(values);
                            } else {
                                $('#tournamentSettings').modal('show');
                            }
                        } else {
                            $('#errorModal .errorDetails').html(
                                "An error occurred while saving the tournament!");
                        }
                    } else {
                        tournament = result.tournament;
                        tournament_id = tournament.id;
                        $('#generate').trigger('click');
                    }
                }, remainingDelay);
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    }

    $('#generate').on('click', function() {
        let minParticipantCounts = 2
        if (tournament && tournament.type == tournamentTypeConsts.k) {
            minParticipantCounts = 4
        }

        let notAllowedItems = document.querySelectorAll('#newList .list-group-item.not-allowed')
        if ((document.querySelectorAll('#newList .list-group-item').length - notAllowedItems.length) < minParticipantCounts) {
            $('#generateErrorModal .count').html(minParticipantCounts)
            $('#generateErrorModal').modal('show')
            return false;
        }

        document.getElementsByClassName('participants-box')[0].scrollIntoView({
            behavior: "smooth",
            block: "start",
            inline: "nearest"
        })

        if (tournament) {
            eleminationType = parseInt(tournament.type)

            /** 
             * Audio player setting 
             *  define('AUDIO_TYPE_BRACKET_GENERATION', 0)
             *  define('AUDIO_TYPE_FINAL_WINNER', 1)
             *  define('AUDIO_TYPE_BRACKET_GENERATION_VIDEO', 2)
             */
            if (tournament && tournament.audio) {
                if (tournament.audio[0]) {
                    audio.load();
                    audio.currentTime = parseInt(tournament.audio[0].start);
                    audio.src = '/uploads/' + tournament.audio[0].path
                    audio.play()
                    shuffle_duration = parseInt(tournament.audio[0].duration);

                    document.getElementById('stopAudioButton').classList.remove('d-none');
                }

                /** Video player setting */
                if (tournament.audio[2]) {
                    videoPlayer.classList.remove('d-none')
                    videoPlayer.currentTime = parseInt(tournament.audio[2].start);
                    videoPlayer.src = '/uploads/' + tournament.audio[2].path
                    videoPlayer.play()
                    shuffle_duration = parseInt(tournament.audio[2].duration);

                    document.getElementById('stopVideoButton').classList.remove('d-none');
                    document.getElementById('stopVideoButton').addEventListener('click', function() {
                        stopVideoPlaying()
                    });
                }
            }

            document.getElementById('skipShuffleButton').classList.remove('d-none');
            document.getElementById('skipShuffleButton').addEventListener('click', function() {
                skipShuffling()
            });

            let shuffle_enable = 0
            if (tournament.shuffle_enabled) {
                shuffle_enable = 1
            }

            document.querySelectorAll('.list-group.collapse').forEach(element => {
                element.classList.remove('show')
            })

            callShuffle(shuffle_enable);
        } else {
            $('#tournamentSettings').modal('show');
        }
    });

    $('#addParticipants').on('click', function() {
        var opts = $('#participantNames').val();

        if (opts == '') {
            return false;
        }

        ptNames = opts.replaceAll(', ', ',').split(',');

        let validatedParticipantNames = validateParticipantNames(ptNames)
        let duplicatedNames = validatedParticipantNames.duplicates
        filteredNames = validatedParticipantNames.validNames

        if (duplicatedNames.length) {
            $('#confirmSave .names').html(duplicatedNames.join(', '));
            $('#confirmSave').modal('show');

            return false;
        }

        if (ptNames.length) {
            addParticipants({
                names: ptNames,
                tournament_id: tournament_id
            });
        }
    });

    $('#confirmSave .include').on('click', () => {
        if (ptNames.length) {
            addParticipants({
                names: ptNames,
                user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                tournament_id: tournament_id
            });
        } else {
            $('#confirmSave').modal('hide')
        }
    })

    $('#confirmSave .remove').on('click', () => {
        if (filteredNames.length) {
            addParticipants({
                names: filteredNames,
                user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                tournament_id: tournament_id
            });
        } else {
            $('#confirmSave').modal('hide')
        }

        appendAlert('Duplicate records discarded!', 'success');
    })

    $('#clearParticipantsConfirmBtn').on('click', () => {
        let items = $('#newList').children();
        if (!items.length) {
            appendAlert('There is no participants to clear.', 'danger');
            $('#clearParticipantsConfirmModal').modal('hide')

            return false;
        }

        let ajax_url = apiURL + '/participants/clear'
        if (tournament) {
            ajax_url = apiURL + '/participants/clear?t_id=' + tournament_id
        }

        $.ajax({
            type: "POST",
            url: ajax_url,
            data: {
                'hash': hash
            },
            success: function(result) {
                if (result.result == 'success') {
                    $('#newList').html('')
                    $('.participant-list .list-tool-bar').addClass('d-none')
                    $('.empty-message-wrapper').removeClass('d-none')
                    $('#clearParticipantsConfirmModal').modal('hide')
                    appendAlert('Participant list cleared!', 'success');
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
    })

    $('#checkDuplicationBtn').on('click', function() {
        var items = $('#newList span.p-name')
        const names = _.map(items, (ele) => {
            return {
                'id': ele.parentElement.dataset.id,
                'name': ele.textContent,
                'lowercase': ele.textContent.replace(/\s+/g, '').toLowerCase()
            }
        })

        if (!names.length) {
            return false;
        }

        let groupedNames = _.chain(names).groupBy('lowercase').filter(function(v) {
            return v.length > 1
        }).flatten().uniq().value()

        if (groupedNames.length) {
            const groupedByLowercase = Object.values(
                groupedNames.reduce((acc, name) => {
                    if (!acc[name.lowercase]) {
                        acc[name.lowercase] = [];
                    }
                    acc[name.lowercase].push(name);
                    return acc;
                }, {})
            );

            let elements = []
            let duplicatedParticipants = []
            duplicates = []
            groupedByLowercase.forEach(group => {
                group.forEach(ele => {
                    if (elements[ele.lowercase]) {
                        duplicates.push(ele.id)
                        duplicatedParticipants.push(ele)
                    } else {
                        elements[ele.lowercase] = ele
                    }
                })
            })

            duplications = _.map(_.uniq(duplicatedParticipants, function(item) {
                return item.name;
            }), function(item) {
                return item.name
            })

            duplicate_names = duplications.join(", ")
            $('#removeDuplicationsConfirmModal span.names').html(duplicate_names)
            $('#removeDuplicationsConfirmModal').modal('show')
        } else {
            appendAlert('No duplicates detected.', 'success');
        }

    });

    $('#removeDuplicationsConfirmBtn').on('click', function() {
        $.ajax({
            type: "POST",
            url: apiURL + '/participants/deletes',
            data: {
                'p_ids': duplicates,
                'tournament_id': tournament ? tournament.id : 0,
                'hash': hash
            },
            dataType: "JSON",
            beforeSend: function() {
                $('#beforeProcessing').removeClass('d-none')
            },
            success: function(result) {
                $('#beforeProcessing').addClass('d-none')
                $('#removeDuplicationsConfirmModal').modal('hide')
                if (result.result == 'success') {
                    renderParticipants(result);

                    $('#participantNames').val(null);
                    $('input.csv-import').val(null)
                    $('#confirmSave').modal('hide');
                    $('#collapseAddParticipant').removeClass('show');

                    appendAlert('Duplicate record(s) removed!', 'success');
                }

                $('#collapseAddParticipant').removeClass('show');
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })

    const tournamentSettingsModal = document.getElementById('tournamentSettings');
    if (tournamentSettingsModal) {
        tournamentSettingsModal.addEventListener('shown.bs.modal', event => {
            tournamentSettingsModal.querySelectorAll(".read-more-container").forEach(container => {
                adjustReadMore(container)
            })
        })
    }

    const selectBackgroundColorModal = document.getElementById('selectBackgroundColorModal');
    if (selectBackgroundColorModal) {
        selectBackgroundColorModal.addEventListener('show.bs.modal', event => {
            selectBackgroundColorModal.setAttribute('data-setting-id', event.relatedTarget.getAttribute('data-setting-id'));
        })
    }

    $('#selectBackgroundColorConfirmBtn').on('click', function() {
        let color = $('#bgColorInput').val()

        <?php if (!auth()->user()): ?>
        color += '_ ' + deviceId
        <?php endif; ?>

        $.ajax({
            type: "POST",
            url: apiURL + '/usersettings/save',
            data: {
                '<?= USERSETTING_PARTICIPANTSLIST_BG_COLOR ?>': color
            },
            beforeSend: function() {
                $("#err").fadeOut();
                $('#beforeProcessing').removeClass('d-none')
            },
            success: function(result) {
                $('.participant-list').css('background-color', color)
                $(selectBackgroundColorModal).modal('hide')
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            $('#beforeProcessing').addClass('d-none')
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    })

    const selectTournamentModal = document.getElementById('selectTournamentModal');
    if (selectTournamentModal) {
        selectTournamentModal.addEventListener('show.bs.modal', event => {
            selectTournamentModal.setAttribute('data-setting-id', event.relatedTarget.getAttribute('data-id'));

            drawTournamentsTable()
        })
    }

    $('#selectTournamentConfirmBtn').on('click', function() {
        const tournament_id = selectTournamentConfirmModal.dataset.id
        performReuseParticipants(tournament_id);
    })

    const makeGroupModal = document.getElementById('makeGroupModal');
    if (makeGroupModal) {
        makeGroupModal.addEventListener('show.bs.modal', event => {
            if (event.target.querySelector('.group_image_delete')) {
                event.target.querySelector('.group_image_delete').remove()
            }
            document.getElementById('group_image').classList.add('temp')
            document.getElementById('group_image').src = '/images/group-placeholder.png'
            document.getElementById('create_group_form').reset()
            document.getElementById('group_image_path').value = null
            document.getElementById('input_group_name').classList.remove('d-none')
            document.querySelector('#input_group_name input').removeAttribute('disabled')
            document.getElementById('select_group').classList.add('d-none')
            document.querySelector('#select_group select').setAttribute('disabled', 'disabled')
            document.querySelector('#select_group select').firstChild.selected = true
            drawGroupsInModal()
        })
    }
});

document.addEventListener('DOMContentLoaded', (event) => {
    const pickr = Pickr.create({
        el: '#color-picker-button',
        // Where the pickr-app should be added as child.
        container: 'body',
        theme: 'classic', // or 'monolith', or 'nano'
        default: '<?= (isset($userSettings) && isset($userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR])) ? $userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR] : '' ?>',
        inline: false,
        autoReposition: true,
        sliders: 'v',
        swatches: [
            'rgba(244, 67, 54, 1)',
            'rgba(233, 30, 99, 0.95)',
            'rgba(156, 39, 176, 0.9)',
            'rgba(103, 58, 183, 0.85)',
            'rgba(63, 81, 181, 0.8)',
            'rgba(33, 150, 243, 0.75)',
            'rgba(3, 169, 244, 0.7)',
            'rgba(0, 188, 212, 0.7)',
            'rgba(0, 150, 136, 0.75)',
            'rgba(76, 175, 80, 0.8)',
            'rgba(139, 195, 74, 0.85)',
            'rgba(205, 220, 57, 0.9)',
            'rgba(255, 235, 59, 0.95)',
            'rgba(255, 193, 7, 1)'
        ],
        components: {
            // Main components
            preview: true,
            opacity: true,
            hue: true,
            // Input / output Options
            interaction: {
                hex: true,
                rgba: true,
                input: true,
                clear: true,
                save: true
            }
        },
        i18n: {
            'btn:save': 'Apply',
        },
    });

    pickr.on('change', (color) => {
        document.getElementById('color-picker-text-hint').classList.remove('d-none')
    });

    $('.pcr-interaction .pcr-save').on('click', function() {
        const rgbaColor = pickr.getColor().toRGBA().toString();
        $('.participant-list').css('background-color', rgbaColor)
        $('#bgColorInput').val(pickr.getColor().toRGBA().toString())
        $('.pcr-app').removeClass('visible')
    })

});

var csvUpload = (element) => {
    if (!$('.csv-import')[0].files) {
        $('#errorModal .errorDetails').html('Please upload a CSV file.')
        $("#errorModal").modal('show');

        return false;
    }

    // Validate file type
    const allowedExtensions = ['text/csv'];
    const fileExtension = $('.csv-import')[0].files[0].type;
    if (!allowedExtensions.includes(fileExtension)) {
        $('#errorModal .errorDetails').html('Please upload a CSV file.')
        $("#errorModal").modal('show');

        return false;
    }

    if (!$('.csv-import').val()) {
        $('.csv-import').addClass('is-invalid');
        return false;
    }
    $('.csv-import').removeClass('is-invalid');
    var formData = new FormData();
    formData.append('file', $('.csv-import')[0].files[0]);
    formData.append('tournament_id', tournament_id)
    formData.append('hash', hash)

    $.ajax({
        url: apiURL + '/participants/import',
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function() {
            $("#err").fadeOut();
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')

            if (result.errors) {
                $('#errorModal .errorDetails').html(result.errors.file)
                $("#errorModal").modal('show');

                return false
            }

            ptNames = result.names
            let validatedParticipantNames = validateParticipantNames(ptNames)
            let duplicatedNames = validatedParticipantNames.duplicates
            filteredNames = validatedParticipantNames.validNames

            if (duplicatedNames.length) {
                $('#confirmSave .names').html(duplicatedNames.join(', '));
                $('#confirmSave').modal('show');

                return false;
            }

            if (result.names.length) {
                addParticipants({
                    names: ptNames,
                    user_id: <?= (auth()->user()) ? auth()->user()->id : 0 ?>,
                    tournament_id: tournament_id
                });
            }
        },
        error: function(e) {
            $('#errorModal .errorDetails').html(e)
            $("#errorModal").show();
        }
    });
}

var tournamentsTable = null
var datatableRows;
var drawTournamentsTable = () => {
    // Check if the DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#tournamentTable')) {
        // Destroy the existing DataTable before reinitializing it
        tournamentsTable.destroy();
    }

    $('#searchTournament').val(null)
    $('#typeFilter').val(null)
    $('#evaluationFilter').val(null)
    $('#stautsFilter').val(null)
    $('#userByFilter').val(null)
    $('#userByFilter').html('<option value="">All Users</option>')

    tournamentsTable = $('#tournamentTable').DataTable({
        "searching": true,
        "processing": true,
        "ajax": {
            "url": apiURL + '/tournaments/get-gallery' + window.location.search,
            "type": "POST",
            "dataSrc": "",
            "data": function(d) {
                d.user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>; // Include the user_id parameter
                d.is_reuse = true;
                d.search_tournament = $('#searchTournament').val();
                d.type = $('#typeFilter').val();
                d.evaluation_method = $('#evaluationFilter').val();
                d.status = $('#stautsFilter').val();
                d.created_by = $('#userByFilter').val();
            }
        },
        scrollX: true,
        // Add custom initComplete to initialize select all checkbox
        "initComplete": function(settings, json) {
            datatableRows = tournamentsTable.rows({
                'search': 'applied'
            }).nodes();

            $('#typeFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#evaluationFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#stautsFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#userByFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            var nameColumns = $('td[data-label="name"] span', datatableRows)
            var names = []
            nameColumns.each((i, element) => {
                if (!names[element.dataset.id]) {
                    var option = $(`<option value="${element.dataset.id}">${element.textContent}</option>`)
                    $('#userByFilter').append(option)

                    names[element.dataset.id] = element.textContent.trim()
                }
            })

            /** Display processing message of response is too long over 1s */
            let requestCompleted = false;

            // Set a timeout to check if the request exceeds the time limit
            const timeout = () => {
                requestCompleted = false
                setTimeout(() => {
                    if (!requestCompleted) {
                        console.warn("The request took too long!");
                        $('#beforeProcessing').removeClass('d-none')
                        // You can also abort the request here if needed
                        // xhr.abort(); // Uncomment if you implement an XMLHttpRequest
                    }
                }, 1000);
            }

            $('#tournamentTable').on('preXhr.dt', function() {
                // $('#beforeProcessing').removeClass('d-none')
                timeout();
            });

            // Hide custom loading overlay after reload
            $('#tournamentTable').on('xhr.dt', function() {
                requestCompleted = true; // Mark the request as completed
                clearTimeout(timeout); // Clear the timeout
                $('#beforeProcessing').addClass('d-none')
            });
        },
        "columns": [{
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    return meta.row + 1; // Display index number
                }
            },
            {
                "data": "name",
                "render": function(data, type, row, meta) {
                    return `<a href="${window.location.origin}/tournaments/${row.id}/view" target="__blank">${row.name}</a>`
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'name');
                }
            },
            {
                "data": "type",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var type = 'Single'
                    if (row.type == <?= TOURNAMENT_TYPE_DOUBLE ?>) {
                        type = "Double"
                    }

                    if (row.type == <?= TOURNAMENT_TYPE_KNOCKOUT ?>) {
                        type = "Knockout"
                    }

                    return type;
                }
            },
            {
                "data": "evaluation_method",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var type = 'Manual'
                    if (row.evaluation_method == "<?= EVALUATION_METHOD_VOTING ?>") {
                        type = "Voting"
                    }

                    return type;
                }
            },
            {
                "data": "status",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var status = 'In progress'
                    if (row.status == <?= TOURNAMENT_STATUS_NOTSTARTED ?>) {
                        status = 'Not started'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_COMPLETED ?>) {
                        status = 'Completed'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_ABANDONED ?>) {
                        status = 'Abandoned'
                    }

                    return status;
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'status');
                }
            },
            {
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    return `<span class="tooltip-span" data-bs-toggle="tooltip" data-placement="top" data-bs-title="${row.email ? row.email : row.username}" data-id="${row.user_id}">${row.username}</span>`;
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'name');
                }
            },
            {
                "data": "created_at",
                "className": "text-center",
            },
            {
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    return `
                        <a class="edit-btn" data-tournament-id="${row.id}" data-name="${row.name}" onClick="reuseParticipant(this)">Reuse</a>
                    `;
                }
            }
        ],
        "columnDefs": [{
            "orderable": false,
            "targets": [2, 3, 4, 5, 7]
        }],
    });

    tournamentsTable.on('draw.dt', function() {
        document.querySelectorAll('span.tooltip-span').forEach((element, i) => {
            var tooltip = new bootstrap.Tooltip(element)
        })
    })

    $('#searchTournamentBtn').on('click', function() {
        tournamentsTable.ajax.reload();
    });
}

var handleKeyPress = (event) => {
    tournamentsTable.ajax.reload()
}

var reuseParticipant = (element) => {
    if ($('.participant-list .list-group-item').length) {
        const selectTournamentConfirmModal = document.getElementById('selectTournamentConfirmModal');
        selectTournamentConfirmModal.dataset.id = element.dataset.tournamentId
        var tournamentNameElement = selectTournamentConfirmModal.querySelector('.tournament-name')
        tournamentNameElement.textContent = element.dataset.name
        $('#selectTournamentConfirmModal').modal('show')
    } else {
        performReuseParticipants(element.dataset.tournamentId)
    }
}
var performReuseParticipants = (reuse_id = null) => {
    $.ajax({
        type: "POST",
        url: apiURL + '/tournaments/reuse-participants',
        data: {
            id: reuse_id,
            tournament_id: tournament_id,
            hash: hash
        },
        beforeSend: function() {
            //$("#preview").fadeOut();
            $('#beforeProcessing').removeClass('d-none')
            $("#err").fadeOut();
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')
            renderParticipants(result)
            $(selectTournamentConfirmModal).modal('hide')
            $(selectTournamentModal).modal('hide')

            if (result.notAllowedParticipants && result.notAllowedParticipants.length) {
                let names = ''
                result.notAllowedParticipants.forEach((name, i) => {
                    names += name
                    if (i < (result.notAllowedParticipants.length - 1)) {
                        names += ', '
                    }
                })
                let html = `<h5>The following participant(s) declined invitations to tournaments.<h5>
                    <span class="text-danger">${names}</span><br/><br/>
                    <h5>Therefore, the invitation will be voided.</h5>`

                $('#errorModal .errorDetails').html(html);

                $("#errorModal").modal('show');
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
</script>

<?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="card shadow-sm">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <a href="<?= previous_url() ?>"><i class="fa fa-angle-left"></i> Back</a>
            </ol>
        </nav>

        <?php if (session('error') !== null) : ?>
        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
        <?php elseif (session('errors') !== null) : ?>
        <div class="alert alert-danger" role="alert">
            <?php if (is_array(session('errors'))) : ?>
            <?php foreach (session('errors') as $error) : ?>
            <?= $error ?>
            <br>
            <?php endforeach ?>
            <?php else : ?>
            <?= session('errors') ?>
            <?php endif ?>
        </div>
        <?php endif ?>

        <?php if (session('message') !== null) : ?>
        <div class="alert alert-success" role="alert"><?= session('message') ?></div>
        <?php endif ?>

        <h5 class="card-title d-flex justify-content-center">
            <? //= lang('Auth.login') ?>Tournament Participants
        </h5>

        <div id="liveAlertPlaceholder"></div>

        <div class="participants-box m-auto">
            <div class="buttons d-flex justify-content-center">
                <button id="add-participant" class="btn btn-default" data-bs-toggle="collapse" data-bs-target="#collapseAddParticipant" aria-expanded="false" aria-controls="collapseAddParticipant"><?= lang('Button.addParticipant') ?></button>
                <button id="generate" class="btn btn-default"><?= lang('Button.generateBrackets') ?></button>
                <a class="btn btn-default dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-gear"></i> Additional Options
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button id="clearParticipant" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#clearParticipantsConfirmModal"><?= lang('Button.clearParticipants') ?></button></li>
                    <li><button id="checkDuplicationBtn" class="btn btn-default"><?= lang('Button.checkDuplicates') ?></button></li>
                    <li><button id="reuseParticipantsBtn" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#selectTournamentModal" data-id="<?= (isset($tournament)) ? $tournament['id'] : '' ?>"><?= lang('Button.reuseParticipants') ?></button></li>
                    <li><button id="changeBackgroundColor" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#selectBackgroundColorModal" data-id="<?= (isset($tournament)) ? $tournament['id'] : '' ?>"><?= lang('Button.changeBackground') ?></button></li>
                </ul>
            </div>
            <div class="collapse" id="collapseAddParticipant">
                <div class="card card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <textarea class="form-control form-control-lg" id="participantNames" placeholder="Enter unique participant names and/or @username to officially invite registered user(s).&#10;Example: name1, name2, @username1, etc..."></textarea>
                            <button type="button" class="btn btn-primary mt-2 float-end" id="addParticipants">Save</button>
                        </div>
                    </form>
                    <div id="namesdHelpBlock" class="form-text">
                        Or, upload a csv file of participant names. <a href="<?= base_url('/uploads/CSV/Samples/sample.csv') ?>">Download sample template file</a>
                        <br />
                        Note that the first row header, as well as any other columns besides 1st column are ignored.<br />
                        Also you may include @username to officially invite registered user(s).
                    </div>

                    <form class="row row-cols-lg-auto g-3 align-items-center mt-1" enctype="multipart/form-data" method="post">
                        <div class="input-group mb-3">
                            <input type="file" class="form-control csv-import" data-source="file" name="file" accept=".csv" required>
                            <button type="button" class="input-group-text btn btn-primary" for="file-input" onClick="csvUpload(this)"><i class="fa fa-upload"></i> Upload</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="participant-list d-flex flex-wrap" <?= (isset($userSettings) && isset($userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR])) ? 'style="background-color: ' . $userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR] . '"' : '' ?>>
                <div class="empty-message-wrapper col-12 p-2 text-bg-info rounded">
                    <p class="text-center">Wow, such empty!</p>
                    <p> To get started, "Add Participants" or from Additional Options, "Reuse Participants" from another tournament.</p>
                    <p> Once you've populated the participants list, proceed with the "Generate Brackets" option to generate the tournament!</p>
                    <p> FYI, you may right click (or hold on mobile) to edit/delete individual participants here.</p>
                    <p> Btw, if you want to personalize your participants with images, you may do so here or in the tournament brackets by clicking the placeholder photo icon.</p>
                </div>

                <div class="video-wrapper col-12">
                    <?php if (isset($tournament['audio']) && isset($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO])) : ?>
                    <video id="videoPlayer" class="col-12 d-none" preload="auto" data-starttime="<?= ($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['start']) ? $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['start'] : '' ?>" data-duration="<?= ($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['duration']) ? $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['duration'] : '' ?>">
                        <source src="<?= ($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['source'] == 'f') ? '/uploads/' . $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['path'] : '/uploads/' . $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION_VIDEO]['path'] ?>" type="audio/mpeg" id="audioSrc">
                    </video>
                    <?php else: ?>
                    <video id="videoPlayer" class="col-12 d-none"></video>
                    <?php endif; ?>
                </div>

                <div class="col-12 d-flex justify-content-center flex-column">
                    <div class="list-tool-bar d-flex justify-content-end col-10 m-auto ps-3 pe-3">
                        <button type="button" class="enableBtn btn btn-primary d-none" onclick="enableGroupParticipants()"><i class="fa-classic fa-solid fa-link fa-fw"></i> Group Participants</button>
                    </div>
                    <div id="newList" class="list-group list-group-numbered col-10 m-auto"></div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="leaveConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">You're about to leave this page and thus will lose access to the tournament!</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You can preserve this tournament by signing up/signing in and accessing much more features from your very own dedicated Tournament Dashboard available for registered users!</p>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary signin">Signup/Signin to preserve tournament</button>
                <button type="button" class="btn btn-danger leave">Discard</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="confirmSave" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Duplicate record(s) detected!</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>The following participant(s) appear to be duplicated.</h1>
                    <h6 class="text-danger"><span class="names"></span></h6>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary include">Include duplicate record(s)</button>
                <button type="button" class="btn btn-danger remove">Discard duplicate record(s)</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="tournamentSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="tournamentSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="tournamentSettingsModalLabel">Tournament Properties</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form id="tournamentForm" class="create-settings needs-validation" method="POST" endtype="multipart/form-data">
                    <?= $settingsBlock ?>

                    <div id="audio-settings-panel">
                        <?= $audioSettingsBlock ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="clearParticipantsConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clearParticipantsConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Are you sure you want to clear the participants list?</h4>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="clearParticipantsConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectBackgroundColorModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectBackgroundColorModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectBackgroundColorModalLabel">Choose the background color in participants list</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <label class="color-picker-text-hint d-none" id="color-picker-text-hint">Once done choosing a color, make sure you click Apply and then Save to persist the change!</label>
                </div>
                <div class="d-flex justify-content-center align-items-center">
                    <label for="bgColorInput" class="form-label me-2">Choose a Background Color:</label>
                    <input type="hidden" class="form-control form-control-color" id="bgColorInput" value="<?= (isset($userSettings) && isset($userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR])) ? $userSettings[USERSETTING_PARTICIPANTSLIST_BG_COLOR] : '' ?>" title="Choose your color">
                    <button id="color-picker-button"></button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectBackgroundColorConfirmBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectTournamentModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectTournamentModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectTournamentModalLabel">Select the tournament to reuse.</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="searchTournament" onkeydown="handleKeyPress(event)">
                        <button id="searchTournamentBtn" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                    </div>
                </div>
                <div class="tournaments-table">
                    <table id="tournamentTable" class="table-responsive display col-12" style="width: 100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th scope="col">
                                    <label for="typeFilter">Type:</label>
                                    <select id="typeFilter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="<?= TOURNAMENT_TYPE_SINGLE ?>">Single</option>
                                        <option value="<?= TOURNAMENT_TYPE_DOUBLE ?>">Double</option>
                                        <option value="<?= TOURNAMENT_TYPE_KNOCKOUT ?>">Knockout</option>
                                    </select>
                                </th>
                                <th scope="col">
                                    <label for="evaluationFilter">Evaluation Method:</label>
                                    <select id="evaluationFilter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="<?= EVALUATION_METHOD_MANUAL ?>">Manual</option>
                                        <option value="<?= EVALUATION_METHOD_VOTING ?>">Voting</option>
                                    </select>
                                </th>
                                <th scope="col">
                                    <label for="statusFilter">Status:</label>
                                    <select id="stautsFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="<?= TOURNAMENT_STATUS_NOTSTARTED ?>">Not started</option>
                                        <option value="<?= TOURNAMENT_STATUS_INPROGRESS ?>">In progress</option>
                                        <option value="<?= TOURNAMENT_STATUS_COMPLETED ?>">Completed</option>
                                        <option value="<?= TOURNAMENT_STATUS_ABANDONED ?>">Abandoned</option>
                                    </select>
                                </th>
                                <th scope="col">
                                    <label for="userByFilter">Created By:</label>
                                    <select id="userByFilter" class="form-select form-select-sm">
                                        <option value="">All Users</option>
                                    </select>
                                </th>
                                <th scope="col">Created Time<br />&nbsp;</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectTournamentConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectTournamentConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectTournamentConfirmModalLabel">Confirmation Message</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                <h6>Upon confirmation, the participants list will be overwritten with tournament "<span class="tournament-name"></span>"'s participants list.</h6>
                </p>
                <p class="mt-3">Are you sure you want to proceed?
                <h6 class="text-danger">This action cannot be undone!</h6>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Discard</button>
                <button type="button" class="btn btn-primary" id="selectTournamentConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="generateErrorModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="generateErrorModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="generateErrorModalLabel">Alert</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Please populate the participant list first before generating the brackets.</h6>
                <h6>There should be at least <span class="count">2</span> or more participants.</h6>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="removeDuplicationsConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="removeDuplicationsConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="removeDuplicationsConfirmModalLabel">Confirm to remove duplicates</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                <h6>The following duplicate participants were found.<br /><span class="names text-danger"></span></h6>
                </p>
                <p class="mt-3">Are you sure you want to proceed?
                <h6 class="text-danger">This action cannot be undone!</h6>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-danger" id="removeDuplicationsConfirmBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="selectParticipantsAlertModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="selectParticipantsAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="selectParticipantsAlertModalLabel">Warning!</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Please select at least one individual participant!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="makeGroupModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="makeGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="makeGroupModalLabel">Group participants</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="container" id="create_group_form">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="group-image d-flex align-items-center flex-column">
                                <img src="/images/group-placeholder.png" class="temp col-auto" id="group_image">
                                <input type="file" accept=".jpg,.jpeg,.gif,.png,.webp" class="d-none file_image" onchange="uploadGroupImage(this)" name="image" id="group_image_input">
                                <input type="hidden" name="image_path" id="group_image_path">
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="create_group_type" id="create_new_group" value="new" onchange="chooseGroupType(this)" checked>
                                <label class="form-check-label" for="create_new_group">
                                    Create new Group
                                </label>
                            </div>
                            <div class="ms-3" id="input_group_name">
                                <input type="text" class="form-control form-control-sm" name="group_name" placeholder="" aria-label="">
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="radio" name="create_group_type" id="reuse_existing_group" value="reuse" onchange="chooseGroupType(this)">
                                <label class="form-check-label" for="reuse_existing_group">
                                    Use existing group
                                </label>
                            </div>
                            <div class="ms-3 d-none" id="select_group">
                                <select class="form-select" aria-label="Default select example" name="group_id" onchange="selectGroup(this)" size="5">
                                    <option selected></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary save" onclick="saveGroup(event)">Save</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Discard</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="confirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="confirmModalLabel">Confirm</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 class="message"></h4>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger confirmBtn">Confirm</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($tournament['audio']) && isset($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION])) : ?>
<audio id="myAudio" preload="auto" data-starttime="<?= ($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['start']) ? $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['start'] : '' ?>" data-duration="<?= ($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['duration']) ? $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['duration'] : '' ?>">
    <source src="<?= ($tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['source'] == 'f') ? '/uploads/' . $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['path'] : '/uploads/' . $tournament['audio'][AUDIO_TYPE_BRACKET_GENERATION]['path'] ?>" type="audio/mpeg" id="audioSrc">
</audio>
<?php else : ?>
<audio id="myAudio" preload="auto">
    <source src="" type="audio/mpeg" id="audioSrc">
</audio>
<?php endif; ?>

<div class="buttons skipButtons">
    <button id="skipShuffleButton" class="d-none">Skip</button>
    <button id="stopAudioButton" class="d-none">Pause Audio</button>
    <button id="stopVideoButton" class="d-none">Pause Video</button>
</div>

<div id="generateProcessing" class="overlay d-none">
    <div class="snippet p-3 .bg-light" data-title="dot-elastic">
        <p>Generating Tournament Brackets...</p>
        <div class="stage">
            <div class="dot-elastic"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>