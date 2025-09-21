<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/at.js/1.5.4/css/jquery.atwho.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tsparticles/confetti@3.0.3/tsparticles.confetti.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.rawgit.com/ichord/Caret.js/master/dist/jquery.caret.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/at.js/1.5.4/js/jquery.atwho.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
<?php if (url_is('/tournaments/shared/*')): ?>
apiURL = "<?= base_url('api/shared') ?>";
<?php else: ?>
apiURL = "<?= base_url('api') ?>";
<?php endif; ?>

const tournament = <?= json_encode($tournament) ?>;
const tournament_id = <?= $tournament['id'] ?>;
const tournament_type = <?= intval($tournament['type']) ?>;
const KNOCKOUT_TOURNAMENT_TYPE = <?= TOURNAMENT_TYPE_KNOCKOUT ?>;
const markWinnerActionCode = '<?= BRACKET_ACTIONCODE_MARK_WINNER ?>';
const unmarkWinnerActionCode = '<?= BRACKET_ACTIONCODE_UNMARK_WINNER ?>';
const changeParticipantActionCode = '<?= BRACKET_ACTIONCODE_CHANGE_PARTICIPANT ?>';
const addParticipantActionCode = '<?= BRACKET_ACTIONCODE_ADD_PARTICIPANT ?>';
const removeParticipantActionCode = '<?= BRACKET_ACTIONCODE_REMOVE_PARTICIPANT ?>';
const deleteBracketActionCode = '<?= BRACKET_ACTIONCODE_DELETE ?>';
var hasEditPermission = <?= ($editable) ? 1 : 0 ?>;
var hasParticipantImageUpdatePermission = <?= $tournament['pt_image_update_enabled'] ? intval($tournament['pt_image_update_enabled']) : 0 ?>;
const isScoreEnabled = <?= $tournament['score_enabled'] ? 1 : 0 ?>;
const scoreBracket = parseInt(<?= ($tournament['score_bracket']) ? $tournament['score_bracket'] : 0 ?>);
const incrementScore = Number(<?= (intval($tournament['increment_score_enabled']) && $tournament['increment_score']) ? $tournament['increment_score'] : 0 ?>);
const incrementScoreType = '<?= (intval($tournament['increment_score_enabled']) && $tournament['increment_score_type']) ? $tournament['increment_score_type'] : TOURNAMENT_SCORE_INCREMENT_PLUS ?>';
let votingEnabled = <?= $votingEnabled ? $votingEnabled : 0 ?>;
let voteActionAvailable = <?= $votingBtnEnabled ? $votingBtnEnabled : 0 ?>;
let votingMechanism = <?= $tournament['voting_mechanism'] ? intval($tournament['voting_mechanism']) : 1 ?>;
let allowHostOverride = <?= $tournament['allow_host_override'] ? $tournament['allow_host_override'] : 0 ?>;
let maxVoteCount = <?= $tournament['max_vote_value'] ? $tournament['max_vote_value'] : 0 ?>;
const votingMechanismRoundDurationCode = <?= EVALUATION_VOTING_MECHANISM_ROUND ?>;
const votingMechanismMaxVoteCode = <?= EVALUATION_VOTING_MECHANISM_MAXVOTE ?>;
const votingMechanismOpenEndCode = <?= EVALUATION_VOTING_MECHANISM_OPENEND ?>;
const evaluationMethodVotingCode = "<?= EVALUATION_METHOD_VOTING ?>";
let winnerAudioPlayingForEveryone = <?= $tournament['winner_audio_everyone'] ? $tournament['winner_audio_everyone'] : 0 ?>;
let initialUsers = <?= json_encode($users) ?>;
let timezone = "<?= config('app')->appTimezone ?>";

const is_temp_tournament = false;

const UUID = getOrCreateDeviceId()

let currentDescriptionDiv, newDescriptionContent, originalDescriptionContent

if (!location.href.includes('shared')) {
    <?php if (!auth()->user()) { ?>
    var dc = new Date();
    dc.setTime(dc.getTime() + (24 * 60 * 60 * 1000));
    document.cookie = 'device_id=' + UUID + 'tournament_id=<?= $tournament["id"] ?>;expires=' + dc.toUTCString() + ';path=/';
    <?php } else { ?>
    document.cookie = 'device_id=' + UUID + 'tournament_id=;Max-Age=0'
    <?php } ?>
} else {
    if (parseInt(getCookie('tournament_id')) == tournament_id) hasEditPermission = true;
}

$(document).ready(function() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    const alertPlaceholder = document.getElementById('liveAlertPlaceholder')
    const appendAlert = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container alert alert-${type} alert-dismissible" id="tournamentInfoAlert" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        alertPlaceholder.append(wrapper)
    }

    const alertTrigger = document.getElementById('liveAlertBtn')
    if (alertTrigger) {
        const msg = $('#liveAlertMsg').html();
        alertTrigger.addEventListener('click', () => {
            appendAlert(msg, 'success')
            alertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('tournamentInfoAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                alertTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'al', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'al')
        })
    }

    const settingInfoAlertPlaceholder = document.getElementById('settingInfoAlertPlaceholder')
    const appendSettingInfoAlert = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container alert alert-${type} alert-dismissible" id="settingInfoAlert" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        settingInfoAlertPlaceholder.append(wrapper)

        const popoverTriggerList = document.querySelectorAll('#settingInfoAlertPlaceholder [data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
    }

    const settingInfoAlertTrigger = document.getElementById('settingInfoAlertBtn')
    if (settingInfoAlertTrigger) {
        const msg = $('#settingInfoAlertMsg').html();
        settingInfoAlertTrigger.addEventListener('click', () => {
            appendSettingInfoAlert(msg, 'success')
            settingInfoAlertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('settingInfoAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                settingInfoAlertTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'st', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'st')
        })
    }

    const warningPlaceholder = document.getElementById('warningPlaceholder')
    const appendWarning = (message, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container alert alert-${type} alert-dismissible" id="tournamentWarning" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        warningPlaceholder.append(wrapper)
    }
    const warningTrigger = document.getElementById('toggleWarningBtn')
    if (warningTrigger) {
        const msg = $('#warningMsg').html();
        warningTrigger.addEventListener('click', () => {
            appendWarning(msg, 'warning')
            warningTrigger.classList.add('d-none')

            const warning = document.getElementById('tournamentWarning')
            warning.addEventListener('closed.bs.alert', event => {
                warningTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'wn', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'wn')
        })
    }

    <?php if ($tournament['description']): ?>
    const descriptionPlaceholder = document.getElementById('descriptionPlaceholder')
    const appendDescription = (description, type) => {
        const wrapper = document.createElement('div')
        let editBtn = ''
        <?php if (auth()->user() && $tournament['user_id'] == auth()->user()->id): ?>
        editBtn = '<button type="button" class="btn-edit" id="editDescriptionBtn" onclick="enableDescriptionEdit(this)"><i class="fa-solid fa-pen-to-square"></i></button>'
        <?php endif ?>
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="descriptionAlert" role="alert">`,
            `   <div class="description" id="description">${description}</div>`,
            editBtn,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        descriptionPlaceholder.append(wrapper)
    }

    const descriptionTrigger = document.getElementById('toggleDescriptionBtn')
    if (descriptionTrigger) {
        const description = $('#description').html();
        descriptionTrigger.addEventListener('click', () => {
            appendDescription(description, 'secondary')
            descriptionTrigger.classList.add('d-none')

            const myAlert = document.getElementById('descriptionAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                descriptionTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'ds', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'ds')
        })
    }
    <?php endif; ?>

    <?php if ($tournament['availability']): ?>
    // Update the countdown timer
    let remainingTime = 0;
    let tournament_start = new Date(tournament.available_start.replace(/-/g, "/"))
    let tournament_end = new Date(tournament.available_end.replace(/-/g, "/"))
    let currentTime = new Date(new Date().toLocaleString("en-US", {
        timeZone: timezone
    }))

    if (currentTime < tournament_start) {
        remainingTime = Math.round((tournament_start.getTime() - currentTime.getTime()) / 1000)
    }

    if (currentTime >= tournament_start && currentTime < tournament_end) {
        remainingTime = Math.round((tournament_end.getTime() - currentTime.getTime()) / 1000)
    }

    function updateCountdown() {
        currentTime = new Date(new Date().toLocaleString("en-US", {
            timeZone: timezone
        }))

        if (currentTime < tournament_start) {
            document.getElementById('availabilityTimer').parentElement.innerHTML = `<strong>The tournament will start in </strong><span class="timer" id="availabilityTimer"></span>`

        }

        if (currentTime > tournament_start) {
            document.getElementById('availabilityTimer').parentElement.innerHTML = `<strong>The tournament has started!</strong><br /><strong>Remaining: </strong><span class="timer" id="availabilityTimer"></span>`
        }

        if (currentTime > tournament_end) {
            document.getElementById("availabilityTimer").parentElement.innerHTML = '<strong>Tournament has ended!</strong><span class="timer" id="availabilityTimer"></span>'
            voteActionAvailable = false
            loadBrackets()

            return;
        }

        if (remainingTime <= 0) {
            if (currentTime > tournament_start) {
                remainingTime = Math.round((tournament_end.getTime() - currentTime.getTime()) / 1000)
                voteActionAvailable = true
                loadBrackets()
            }
        }

        let days = Math.floor(remainingTime / (60 * 60 * 24));
        let hours = Math.floor((remainingTime % (60 * 60 * 24)) / (60 * 60));
        let minutes = Math.floor((remainingTime % (60 * 60)) / 60);
        let seconds = remainingTime % 60;

        document.getElementById("availabilityTimer").innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;

        remainingTime--;

        setTimeout(updateCountdown, 1000);
    }

    updateCountdown()

    const availabilityAlertPlaceholder = document.getElementById('availabilityAlertPlaceholder')
    const appendAvailabilityAlert = (content, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="availabilityAlert" role="alert">`,
            `   <div class="availabilityAlert" id="availabilityAlertContent">${content}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        availabilityAlertPlaceholder.append(wrapper)
    }

    const availabilityAlertTrigger = document.getElementById('toggleVoteWarningBtn')
    if (availabilityAlertTrigger) {
        const msg = $('#availabilityAlertMsg').html();
        availabilityAlertTrigger.addEventListener('click', () => {
            appendAvailabilityAlert(msg, 'dark')
            availabilityAlertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('availabilityAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                availabilityAlertTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'vw', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'vw')
        })
    }

    const countTimerAlertPlaceholder = document.getElementById('countTimerAlertPlaceholder')
    const appendCountTimerAlert = (content, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="countTimerAlert" role="alert">`,
            `   <div class="countTimerAlert" id="countTimerAlertContent">${content}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        countTimerAlertPlaceholder.append(wrapper)
    }

    const countTimerAlertTrigger = document.getElementById('countTimerNoteBtn')
    if (countTimerAlertTrigger) {
        countTimerAlertTrigger.addEventListener('click', () => {
            currentTime = new Date(new Date().toLocaleString("en-US", {
                timeZone: timezone
            }))

            if (currentTime < tournament_start) {
                document.getElementById('availabilityTimer').parentElement.innerHTML = `<strong>The tournament will start in </strong><span class="timer" id="availabilityTimer"></span>`
            }

            if (currentTime >= tournament_start && currentTime < tournament_end) {
                document.getElementById('availabilityTimer').parentElement.innerHTML = `<strong>The tournament has started!</strong><br /><strong>Remaining: </strong><span class="timer" id="availabilityTimer"></span>`
            }

            if (currentTime > tournament_end) {
                document.getElementById('availabilityTimer').parentElement.innerHTML = `<strong>The tournament has ended!</strong><span class="timer" id="availabilityTimer"></span>`
            }

            const msg = $('#countTimerAlertMsg').html();
            appendCountTimerAlert(msg, 'dark')
            countTimerAlertTrigger.classList.add('d-none')

            const myAlert = document.getElementById('countTimerAlert')
            myAlert.addEventListener('closed.bs.alert', event => {
                countTimerAlertTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'ct', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'ct')
        })
    }
    <?php endif; ?>

    <?php if ($votingEnabled): ?>
    const voteDisplayingModePlaceholder = document.getElementById('voteDisplayingModePlaceholder')
    const appendVoteDisplayingHtml = (html, type) => {
        const wrapper = document.createElement('div')
        wrapper.innerHTML = [
            `<div class="container border pt-5 pe-3 alert alert-${type} alert-dismissible" id="voteDisplayingMode" role="alert">`,
            `   <div class="text-center">${html}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('')

        voteDisplayingModePlaceholder.append(wrapper)
    }

    const voteDisplayingModeTrigger = document.getElementById('toggleVoteDisplayingModeBtn')
    if (voteDisplayingModeTrigger) {
        const voteDisplayingHtml = $('#voteDisplayingHtml').html();
        voteDisplayingModeTrigger.addEventListener('click', () => {
            appendVoteDisplayingHtml(voteDisplayingHtml, 'secondary')
            voteDisplayingModeTrigger.classList.add('d-none')

            const voteDisplayingPlaceholder = document.getElementById('voteDisplayingMode')
            voteDisplayingPlaceholder.addEventListener('closed.bs.alert', event => {
                voteDisplayingModeTrigger.classList.remove('d-none')
                updateStorage('alert-expanded-' + tournament_id, 'vd', 'remove')
            })

            updateStorage('alert-expanded-' + tournament_id, 'vd')
        })
    }
    <?php endif; ?>

    document.getElementById('confirmSaveButton').addEventListener('click', saveDescription)
    document.getElementById('confirmDismissButton').addEventListener('click', dismissEdit)

    if (localStorage.getItem('collapse-on-t-' + tournament_id)) {
        document.getElementById('collapseBtn').click()
    }

    if (localStorage.getItem('alert-expanded-' + tournament_id)) {
        let expanded = JSON.parse(localStorage.getItem('alert-expanded-' + tournament_id))
        if (expanded.length) {
            expanded.forEach(value => {
                document.querySelector(`.alert-btn-container .btn[data-code="${value}"]`).click()
            })
        }
    }

    <?php if (!auth()->user() && isset($editable) && $editable && !$tournament['user_id']): ?>
    var leaveUrl;
    $(document).on('click', function(e) {
        var linkElement;
        if (e.target.tagName == 'A') {
            linkElement = e.target
        }

        if (e.target.parentElement && e.target.parentElement.tagName == 'A') {
            linkElement = e.target.parentElement
        }

        if (linkElement) {
            if (linkElement.href && linkElement.href.includes('login')) {
                return true
            }

            e.preventDefault();
            leaveUrl = linkElement.href;

            // Show Bootstrap modal
            var modal = new bootstrap.Modal(document.getElementById('leaveConfirm'));
            modal.show();
        }
    })

    // Handle the modal confirmation
    document.getElementById('leaveToSignin').addEventListener('click', function() {
        // Allow the window/tab to close
        window.location.href = "/login"; // or use `window.close()` in some cases
    });

    $("#leaveConfirm .leave").on('click', function() {
        $('#leaveConfirm').modal('hide');
        location.href = leaveUrl;
    })
    <?php endif; ?>

    if (hasEditPermission || hasParticipantImageUpdatePermission) {
        $(document).on("click", ".p-image img", function(e) {
            var pid = $(this).data('pid');
            if ($(this).hasClass('temp')) {
                $("#image_" + pid).trigger('click');
            } else {
                $(this).parent().addClass('active');
                $(this).parent().find('.btn').removeClass('d-none')
            }
        })

        $(document).on("click", function(e) {
            if (!$(e.target.parentElement).hasClass('p-image')) {
                $(".p-image").removeClass('active')
                $(".p-image button").addClass('d-none')
            };
        })
    }
})

var changeVoteDisplayingMode = (element) => {
    localStorage.setItem('vote_displaying_mode', element.value)
    tournament.vote_displaying = element.value
    loadBrackets()

    <?php if (auth()->user()): ?>
    $.ajax({
        type: "POST",
        url: apiURL + '/usersettings/save',
        contentType: "application/json",
        data: {
            'vote_displaying_mode': element.value
        },
        success: function(result) {
            console.log(result)
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
    <?php endif; ?>
}
</script>

<script src="/js/brackets.js"></script>
<?= $this->endSection() ?>

<?php $userSettingService = service('userSettings') ?>

<?= $this->section('main') ?>
<div class="background">
    <div class="corner-top-left"></div>
    <div class="corner-top-right"></div>
    <div class="corner-bottom-left"></div>
    <div class="corner-bottom-right"></div>
    <div class="top-bg"></div>
    <div class="left-bg"></div>
    <div class="right-bg"></div>
    <div class="bottom-bg"></div>
</div>
<div class="card col-12 shadow-sm" style="min-height: calc(100vh - 60px);">
    <div class="card-body">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <a href="<?= $_SERVER['HTTP_REFERER'] ?? site_url('/') ?>"><i class="fa fa-angle-left"></i> Back</a>
            </ol>
        </nav>
        <h5 class="card-title d-flex justify-content-center mb-5">
            <?= $tournament['name'] ?>&nbsp;
            <?php if ($tournament['created_by']): ?>
            <button type="button" class="btn btn-light p-0 bg-transparent border-0" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<strong>Organized/Hosted by</strong>:<br/><?= $tournament['created_by']->username ?> <?= !$userSettingService->get('hide_email_host', $tournament['user_id']) ? "(" . $tournament['created_by']->email . ")" : '' ?>"><i class="fa-classic fa-solid fa-circle-exclamation"></i></button>
            <?php else: ?>
            <button type="button" class="btn btn-light p-0 bg-transparent border-0" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<strong>Organized/Hosted by</strong>:<br/>Guest"><i class="fa-classic fa-solid fa-circle-exclamation"></i></button>
            <?php endif ?>
        </h5>

        <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
        <?php elseif (session('errors') !== null): ?>
        <div class="alert alert-danger" role="alert">
            <?php if (is_array(session('errors'))): ?>
            <?php foreach (session('errors') as $error): ?>
            <?= $error ?>
            <br>
            <?php endforeach ?>
            <?php else: ?>
            <?= session('errors') ?>
            <?php endif ?>
        </div>
        <?php endif ?>

        <?php if (session('message') !== null): ?>
        <div class="alert alert-success" role="alert"><?= session('message') ?></div>
        <?php endif ?>

        <div class="container alert-collapse-btn-container mb-1 d-flex justify-content-end">
            <button type="button" class="btn expand p-0 d-none" id="expandBtn" onclick="toggleCollapseAlertBtns(this)">
                <svg fill="#000000" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <title>expand</title>
                        <path d="M13.816 5.989l-7.785 0.046 0.003 7.735 2.59-2.591 3.454 3.454 2.665-2.665-3.453-3.454 2.526-2.525zM12.079 17.35l-3.454 3.455-2.59-2.592-0.003 7.799 7.785-0.018-2.526-2.525 3.454-3.453-2.666-2.666zM19.922 14.633l3.453-3.454 2.59 2.591 0.004-7.735-7.785-0.046 2.526 2.525-3.454 3.454 2.666 2.665zM23.375 20.805l-3.453-3.455-2.666 2.666 3.454 3.453-2.526 2.525 7.785 0.018-0.004-7.799-2.59 2.592z"></path>
                    </g>
                </svg>
            </button>
            <button type="button" class="btn collapsee p-0" id="collapseBtn" onclick="toggleCollapseAlertBtns(this)">
                <svg fill="#000000" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <title>collapse</title>
                        <path d="M11.493 8.757l-3.454-3.453-2.665 2.665 3.454 3.453-2.59 2.59 7.797 0.004-0.017-7.784-2.525 2.525zM23.172 11.422l3.454-3.453-2.665-2.665-3.454 3.453-2.525-2.525-0.017 7.784 7.797-0.004-2.59-2.59zM8.828 20.578l-3.454 3.453 2.665 2.665 3.454-3.453 2.526 2.525 0.017-7.784-7.797 0.004 2.589 2.59zM25.762 17.988l-7.797-0.004 0.017 7.784 2.525-2.525 3.454 3.453 2.665-2.665-3.454-3.453 2.59-2.59z"></path>
                    </g>
                </svg>
            </button>
        </div>

        <div class="container alert-btn-container mb-1 d-flex justify-content-end">
            <?php if ($votingEnabled): ?>
            <button type="button" class="btn ps-2 pe-2" id="toggleVoteDisplayingModeBtn" data-code="vd">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path d="M17.4 8h-1.8A1.6 1.6 0 0 0 14 9.6v4.8a1.6 1.6 0 0 0 1.6 1.6h1.8a1.6 1.6 0 0 0 1.6-1.6V9.6A1.6 1.6 0 0 0 17.4 8ZM8.4 4H6.6A1.6 1.6 0 0 0 5 5.6v12.8A1.6 1.6 0 0 0 6.6 20h1.8a1.6 1.6 0 0 0 1.6-1.6V5.6A1.6 1.6 0 0 0 8.4 4Z" fill="#000000" fill-opacity=".16" stroke="#000000" stroke-width="1.5" stroke-miterlimit="10"></path>
                        <path d="M2 12h3M19 12h3M10 12h4" stroke="#000000" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
                    </g>
                </svg>
            </button>
            <?php endif ?>

            <?php if ($tournament['availability']): ?>
            <button type="button" class="btn" id="countTimerNoteBtn" data-code="ct">
                <i class="fa-solid fa-clock"></i>
            </button>
            <button type="button" class="btn" id="toggleVoteWarningBtn" data-code="vw">
                <i class="fa-solid fa-calendar"></i>
            </button>
            <?php endif ?>

            <button type="button" class="btn" id="liveAlertBtn" data-code="al">
                <i class="fa-classic fa-solid fa-circle-exclamation fa-fw"></i>
            </button>

            <button type="button" class="btn" id="settingInfoAlertBtn" data-code="st">
                <i class="fa-solid fa-gear"></i>
            </button>

            <?php if ($tournament['description']): ?>
            <button type="button" class="btn" id="toggleDescriptionBtn" data-code="ds">
                <i class="fa-solid fa-book"></i>
            </button>
            <?php endif ?>

            <?php if ($tournament['user_id'] == 0 && isset($editable) && $editable): ?>
            <button type="button" class="btn" id="toggleWarningBtn" data-code="wn">
                <i class="fa-solid fa-warning"></i>
            </button>
            <?php endif; ?>

            <button type="button" class="btn" id="viewQRBtn" onclick="displayQRCode()">
                <i class="fa fa-qrcode" aria-hidden="true"></i>
            </button>
        </div>

        <div class="alert-container">
            <?php if ($tournament['availability']): ?>
            <div id="countTimerAlertPlaceholder" class="text-center"></div>
            <div id="countTimerAlertMsg" class="d-none">
                <?php $startTime = new \DateTime($tournament['available_start']) ?>
                <?php $endTime = new DateTime($tournament['available_end']) ?>
                <?php $interval = $startTime->diff($endTime); ?>

                <span class="me-5">
                    <strong>Duration: </strong><span class="start"><?= $tournament['available_start'] ?></span> - <span class="end"><?= $tournament['available_end'] ?></span>
                    <br />
                    (<?= "{$interval->d} Days {$interval->h} Hours" ?>)
                </span>
                <br />
                <span class="status pt-2"><span class="timer" id="availabilityTimer"></span></span>
            </div>

            <div id="availabilityAlertPlaceholder"></div>
            <div id="availabilityAlertMsg" class="d-none">
                <?php $created_by = $tournament['created_by'] ?>
                <?php $created_by_name = $created_by ? $created_by->username : 'Guest' ?>
                <?php $created_by_name .= (!$userSettingService->get('hide_email_host') && $created_by) ? " ($created_by->email)" : '' ?>
                The tournament <strong><?= $tournament['name'] ?></strong> hosted by <strong><?= $created_by_name ?></strong> will be available starting <?= auth()->user() ? convert_to_user_timezone($tournament['available_start'], user_timezone(auth()->user()->id)) : $tournament['available_start'] ?> and ending on <?= auth()->user() ? convert_to_user_timezone($tournament['available_end'], user_timezone(auth()->user()->id)) : $tournament['available_end'] ?>. <br />
                If voting is enabled, the voting period will begin once the tournament availability starts and conclude once the availability ends.
            </div>
            <?php endif; ?>

            <?php if ($tournament['user_id'] == 0 && isset($editable) && $editable): ?>
            <div id="warningPlaceholder"></div>
            <div id="warningMsg" class="d-none">
                <div class="text-center">⚠️ WARNING ⚠️</div>
                This tournament will only be available on the Tournament Gallery if visibility option was enabled; otherwise the tournament, alongside any progress, will be lost if the page is closed and you're not registered/loggedin!
                <br>
                If you didn't enable visibility setting in the tournament properties and would like to preserve the tournament and its progress, please Signup/Login and unlock much more features (such as controlling availability, visibility, sharing and audio settings and more!) from your very own dedicated Tournament Dashboard available for registered users!
                <br>
                Note: Unaffiliated tournaments, meaning those created by unregistered visitors, will be deleted after 24 hours from the Tournament Gallery.
                <div class="text-center">
                    <?php if (!auth()->user()): ?><br>
                    <a href="<?= base_url('/login') ?>" class="btn btn-primary">Signup/Login to preserve tournament</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div id="settingInfoAlertPlaceholder"></div>
            <div id="settingInfoAlertMsg" class="d-none">
                <div class="row">
                    <div class="col-md-7 col-sm-12 m-auto">
                        <p class="text-center"><strong>Tournament Properties:</strong></p>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Elimination Type</strong>
                            <span>
                                <?= $tournament['type'] == TOURNAMENT_TYPE_SINGLE ? "Single" : ($tournament['type'] == TOURNAMENT_TYPE_DOUBLE ? "Double" : "Knockout") ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= $tournament['type'] == TOURNAMENT_TYPE_SINGLE ? lang('Descriptions.tournamentSingleTypeDesc') : ($tournament['type'] == TOURNAMENT_TYPE_DOUBLE ? lang('Descriptions.tournamentDoubleTypeDesc') : lang('Descriptions.tournamentKockoutTypeDesc')) ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Visibility</strong>
                            <span>
                                <?= $tournament['visibility'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentVisibilityDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Availability </strong>
                            <span>
                                <?= $tournament['availability'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentAvailabilityDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Evaluation Method</strong>
                            <span>
                                <?= $tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL ? "Manual" : "Voting" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= $tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL ? lang('Descriptions.tournamentEvaluationManualDesc') : lang('Descriptions.tournamentEvaluationVotingDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <?php if ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING): ?>
                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Voting Accessibility</strong>
                            <span>
                                <?= $tournament['voting_accessibility'] == EVALUATION_VOTING_RESTRICTED ? "Restricted" : "Unrestricted" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content='<?= $tournament['voting_accessibility'] == EVALUATION_VOTING_RESTRICTED ? lang('Descriptions.tournamentVotingRestrictedgDesc') : lang('Descriptions.tournamentVotingUnrestrictedDesc') ?>'>
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Voting Mechanism</strong>
                            <span>
                                <?= $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE ? "Max Votes" : ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND ? "Round Duration" : "Open-Ended") ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE ? lang('Descriptions.tournamentVotingMaxVotesDesc') : ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND ? lang('Descriptions.tournamentVotingRoundDurationDesc') : lang('Descriptions.tournamentVotingOpenEndedDesc')) ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <?php if ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE): ?>
                        <p class="property-info d-flex justify-content-between mb-1 ps-4">
                            <strong>Max Votes</strong>
                            <span>
                                <?= $tournament['max_vote_value'] ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentVotingMaxVoteLimitDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>
                        <?php endif; ?>

                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Retain vote count across rounds</strong>
                            <span>
                                <?= $tournament['voting_retain'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentRetainVoteCountDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Allow Host override</strong>
                            <span>
                                <?= $tournament['allow_host_override'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentAllowHostOverrideDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>
                        <?php endif; ?>

                        <?php if ($tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL || ($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING && $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE)): ?>
                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Round Duration</strong>
                            <span>
                                <?= $tournament['round_duration_combine'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content='<?= $tournament['evaluation_method'] == EVALUATION_METHOD_MANUAL ? lang('Descriptions.tournamentRoundDurationCombineManual') : lang('Descriptions.tournamentRoundDurationCombineMaxVote') ?>'>
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>
                        <?php endif; ?>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Participant Image Customization Access</strong>
                            <span>
                                <?= $tournament['pt_image_update_enabled'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentParticipantImageCustomizationDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Audio for Final Winner</strong>
                            <span>
                                <?= $tournament['win_audio_enabled'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentAudioFinalWinnerDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <?php if ($tournament['winner_audio_everyone']): ?>
                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Play for everyone</strong>
                            <span>
                                <?= $tournament['winner_audio_everyone'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentPlayForEveryoneDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>
                        <?php endif; ?>

                        <p class="property-info d-flex justify-content-between mb-1">
                            <strong>Enable Scoring</strong>
                            <span>
                                <?= $tournament['score_enabled'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentEnableScoringDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <?php if ($tournament['score_enabled']): ?>
                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Score per bracket per round</strong>
                            <span>
                                <?= $tournament['score_bracket'] ?>&nbsp;&nbsp;&nbsp;&nbsp;
                            </span>
                        </p>
                        <?php endif; ?>

                        <p class="property-info d-flex justify-content-between mb-1 ps-2">
                            <strong>Increment Score</strong>
                            <span>
                                <?= $tournament['increment_score_enabled'] ? "On" : "Off" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= lang('Descriptions.tournamentIncrementScoreDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <?php if ($tournament['increment_score_enabled']): ?>
                        <p class="property-info d-flex justify-content-between mb-1 ps-4">
                            <strong>Increment Type</strong>
                            <span>
                                <?= $tournament['increment_score_type'] == TOURNAMENT_SCORE_INCREMENT_PLUS ? "Plus" : "Multiply" ?>
                                <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="<?= $tournament['increment_score_type'] == TOURNAMENT_SCORE_INCREMENT_PLUS ? lang('Descriptions.tournamentIncrementScoreTypePlusDesc') : lang('Descriptions.tournamentIncrementScoreTypeMultipleDesc') ?>">
                                    <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                </button>
                            </span>
                        </p>

                        <p class="property-info d-flex justify-content-between mb-1 ps-4">
                            <strong>Increment Value</strong>
                            <span>
                                <?= $tournament['increment_score'] ?>&nbsp;&nbsp;&nbsp;&nbsp;
                            </span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="liveAlertPlaceholder"></div>
            <div id="liveAlertMsg" class="d-none">
                Note: <br />
                The tournament brackets are generated along a sequence of [2, 4, 8, 16, 32] in order to maintain bracket advancement integrity, otherwise there would be odd matchups that wouldn't make sense to the tournament structure.
                <?php if ((auth()->user() && auth()->user()->id == $tournament['user_id']) || (session('share_permission') && session('share_permission') == SHARE_PERMISSION_EDIT)): ?>
                You also have actions available to you by right clicking (or holding on mobile devices) the individual bracket box throughout the tournament availability window (assuming its set).<br>
                This limitation isn't applicable to the tournament host.<br>
                In other words, actions will be restricted for all after availability ends (e.g. if tournament is shared with edit permissions) except for the host, in which even if availability ends, the host would still be able to control actions.
                <br />
                <?php endif ?>
            </div>

            <div id="descriptionPlaceholder"></div>
            <div id="description" class="d-none">
                <?= $tournament['description'] ?>
            </div>

            <?php if ($votingEnabled): ?>
            <div id="voteDisplayingModePlaceholder"></div>
            <div id="voteDisplayingHtml" class="vote-display-mode text-center d-none">
                <label for="inputPassword6" class="col-form-label mt-3 me-3"><strong>Vote Display :</strong> </label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vote-display-mode" onchange="changeVoteDisplayingMode(this)" value="n" <?= (!$tournament['vote_displaying'] || $tournament['vote_displaying'] == 'n') ? "checked" : ''; ?>>
                    <label class="form-check-label" for="votes_in_point">Points</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vote-display-mode" onchange="changeVoteDisplayingMode(this)" value="p" <?= ($tournament['vote_displaying'] == 'p') ? "checked" : ''; ?>>
                    <label class="form-check-label" for="votes_in_percentage">Percentage</label>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div id="roundTimerPlaceholder"></div>
        <div id="brackets" class="brackets d-flex p-5 pt-2"></div>
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
                <button type="button" class="btn btn-primary signin" id="leaveToSignin">Signup/Signin to preserve tournament</button>
                <button type="button" class="btn btn-danger leave">Disregard and leave anyways</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($audioSettings) && $audioSettings): ?>
<audio id="myAudio" preload="auto" data-starttime="<?= ($audioSettings[0]['start']) ? $audioSettings[0]['start'] : '' ?>" data-duration="<?= ($audioSettings[0]['duration']) ? $audioSettings[0]['duration'] : '' ?>">
    <source src="<?= ($audioSettings[0]['source'] == 'f') ? '/uploads/' . $audioSettings[0]['path'] : '/uploads/' . $audioSettings[0]['path'] ?>" type="audio/mpeg" id="audioSrc">
</audio>

<div class="buttons skipButtons">
    <button id="stopAudioButton" class="d-none">Pause Audio</button>
</div>
<?php endif; ?>


<!-- Save Confirmation Modal -->
<div class="modal fade" id="saveDescriptionConfirmModal" tabindex="-1" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveModalLabel">Confirm Save</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to save the changes?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSaveButton">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Dismiss Confirmation Modal -->
<div class="modal fade" id="dismissDescriptionEditConfirmModal" tabindex="-1" aria-labelledby="dismissModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dismissModalLabel">Confirm Discard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to discard the changes?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmDismissButton">Discard</button>
            </div>
        </div>
    </div>
</div>

<!-- Display QR Modal -->
<div class="modal fade" id="displayQRCodeModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dismissModalLabel">Share the Tournament!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center fs-4 fw-medium mb-4">Scan the QR code</p>
                <div id="qrcode" class="d-flex justify-content-center"></div>
                <p class="text-center mt-4">Or Copy/Share this link</p>
                <div class="col-auto input-group">
                    <input type="text" class="form-control" id="tournamentURL" value="" aria-label="Tournament URL" aria-describedby="urlCopy" readonly="">
                    <button class="btn btn-primary input-group-text btnCopy" data-copyid="tournamentURL" type="button" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="Link Copied!" onclick="copyClipboard('tournamentURL')">Copy</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>