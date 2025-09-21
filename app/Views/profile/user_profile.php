<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Brackets<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?><?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script>
$(document).ready(function() {
    const cookieSetting = localStorage.getItem('cookie_consent');

    if (cookieSetting) {
        if (cookieSetting == 'accepted') {
            document.querySelector('.cookie-status').textContent = "Accepted"
            document.querySelector('.cookie-status').setAttribute('class', 'cookie-status p-1 ps-2 pe-2 rounded-2 text-bg-success')
        } else {
            document.querySelector('.cookie-status').textContent = "Rejected"
            document.querySelector('.cookie-status').setAttribute('class', 'cookie-status p-1 ps-2 pe-2 rounded-2 text-bg-danger')
        }

        document.querySelector('.cookie-reset-btn').classList.remove('disabled')
        document.querySelector('.cookie-reset-btn').setAttribute('data-bs-toggle', "modal")
    } else {
        document.querySelector('.cookie-status').textContent = "Pending"
        document.querySelector('.cookie-status').setAttribute('class', 'cookie-status p-1 ps-2 pe-2 rounded-2 text-bg-secondary')
        document.querySelector('.cookie-reset-btn').setAttribute('disabled', true)
        document.querySelector('.cookie-reset-btn').classList.add('disabled')
        document.querySelector('.cookie-reset-btn').setAttribute('data-bs-toggle', "")
    }

    const resetCookieConsentModal = document.getElementById('resetCookieConsentModal');
    if (resetCookieConsentModal) {
        resetCookieConsentModal.addEventListener('show.bs.modal', event => {
            const confirmBtn = resetCookieConsentModal.querySelector('.modal-footer .confirm');
            confirmBtn.addEventListener('click', event => {
                localStorage.removeItem('cookie_consent')

                document.querySelector('.cookie-status').textContent = "Pending"
                document.querySelector('.cookie-status').setAttribute('class', 'cookie-status p-1 ps-2 pe-2 rounded-2 text-bg-secondary')
                document.querySelector('.cookie-reset-btn').setAttribute('disabled', true)
                document.querySelector('.cookie-reset-btn').classList.add('disabled')
                document.querySelector('.cookie-reset-btn').setAttribute('data-bs-toggle', "")

                $(resetCookieConsentModal).modal('hide')
            })
        })
    }

    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url('profile/update-password') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                $('#responseMessage').html(
                    `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                );
                if (response.success) {
                    $('#responseMessage').html()
                    $('#notification-area').html(
                        `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                    );
                    $('#changePasswordForm')[0].reset(); // Reset form if successful
                    $('#changePasswordModal').modal('hide')
                }

                // Clear notification after 10 seconds
                setTimeout(function() {
                    $('#notification-area').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                    $('#responseMessage').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                }, 10000);
            },
            error: function() {
                $('#responseMessage').html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            }
        });
    });

    $('#switchHideEmail').on('change', (e) => {
        e.preventDefault();

        let data = {
            'hide_email': e.target.checked ? 1 : 0
        }

        if (!e.target.checked) {
            data.hide_email_host = 0
            data.hide_email_participant = 0
        }

        $.ajax({
            url: '<?= base_url('api/usersettings/save') ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    $('#responseMessage').html()
                    $('#notification-area').html(
                        `<div class="alert ${response.status == 'success' ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                    );
                    $('#changePasswordForm')[0].reset(); // Reset form if successful
                    $('#changePasswordModal').modal('hide')
                }

                if (e.target.checked) {
                    $('#hideEmailOptions').removeClass('d-none')
                } else {
                    $('#hideEmailOptions').addClass('d-none')
                    $('#hideEmailOptions input').attr('checked', false)
                }

                // Clear notification after 10 seconds
                setTimeout(function() {
                    $('#notification-area').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                }, 5000);
            },
            error: function() {
                $('#notification-area').html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            }
        });
    })

    $('#hideEmailOptions input').on('change', (e) => {
        e.preventDefault();
        const name = e.target.getAttribute('name')
        let data = {}
        data[name] = e.target.checked ? 1 : 0

        $.ajax({
            url: '<?= base_url('api/usersettings/save') ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    $('#responseMessage').html()
                    $('#notification-area').html(
                        `<div class="alert ${response.status == 'success' ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                    );
                    $('#changePasswordForm')[0].reset(); // Reset form if successful
                    $('#changePasswordModal').modal('hide')
                }

                // Clear notification after 10 seconds
                setTimeout(function() {
                    $('#notification-area').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                }, 5000);
            },
            error: function() {
                $('#notification-area').html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            }
        });
    })

    $('#switchAllowInvitations').on('change', (e) => {
        e.preventDefault();

        let data = {
            'disable_invitations': e.target.checked ? 0 : 1
        }

        $.ajax({
            url: '<?= base_url('api/usersettings/save') ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    $('#responseMessage').html()
                    $('#notification-area').html(
                        `<div class="alert ${response.status == 'success' ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
                    );
                }

                // Clear notification after 10 seconds
                setTimeout(function() {
                    $('#notification-area').fadeOut('slow', function() {
                        $(this).empty().show(); // Empty and show to reset for future messages
                    });
                }, 5000);
            },
            error: function() {
                $('#notification-area').html(
                    '<div class="alert alert-danger">An error occurred. Please try again.</div>'
                );
            }
        });
    })

    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
});

let sendVerificationCode = (resend = false) => {
    let resendButton = document.getElementById('resend-code');
    resendButton.disabled = true;

    $.ajax({
        url: '<?= base_url('profile/update-email') ?>',
        type: 'POST',
        data: $('#updateEmailForm').serialize(),
        dataType: 'json',
        success: function(response) {
            $('#email-update-notification-area').html(
                `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
            );
            if (response.status == 'success') {
                $('.update-email-block').addClass('d-none')
                $('.confirm-code-block').removeClass('d-none')
            } else {
                $('#email-update-notification-area').html(
                    `<div class="alert alert-danger">${response.errors.new_email}</div>`
                );
            }

            startCooldown(resendButton);

            // Clear notification after 10 seconds
            setTimeout(function() {
                $('#email-update-notification-area').fadeOut('slow', function() {
                    $(this).empty().show(); // Empty and show to reset for future messages
                });
            }, 10000);
        },
        error: function() {
            $('#email-update-notification-area').html(
                '<div class="alert alert-danger">An error occurred. Please try again.</div>'
            );
        }
    });
}

let startCooldown = (button) => {
    let cooldown = 60;
    let interval = setInterval(() => {
        if (cooldown <= 0) {
            clearInterval(interval);
            button.disabled = false;
            button.textContent = "Resend Code";
        } else {
            button.textContent = `Resend Code (${cooldown}s)`;
            cooldown--;
        }
    }, 1000);
}

let confirmVerificationCode = () => {
    $.ajax({
        url: '<?= base_url('profile/update-email-confirm') ?>',
        type: 'POST',
        data: $('#updateEmailForm').serialize(),
        success: function(response) {
            $('#notification-area').html(
                `<div class="alert ${response.success ? 'alert-success' : 'alert-danger'}">${response.message}</div>`
            );
            if (response.status == 'success') {
                $('.update-email-block').addClass('d-none')
                $('.confirm-code-block').removeClass('d-none')
            } else {
                $('#email-update-notification-area').html(
                    `<div class="alert alert-danger">${response.message}</div>`
                );
            }

            // Clear notification after 10 seconds
            setTimeout(function() {
                $('#notification-area').fadeOut('slow', function() {
                    $(this).empty().show(); // Empty and show to reset for future messages
                });
                $('#email-update-notification-area').fadeOut('slow', function() {
                    $(this).empty().show(); // Empty and show to reset for future messages
                });
            }, 10000);
        },
        error: function() {
            $('#notification-area').html(
                '<div class="alert alert-danger">An error occurred. Please try again.</div>'
            );
        }
    });
}

let deleteAccount = () => {
    window.location.href = "<?= base_url('close-account')?>"
}
</script>
<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="d-flex justify-content-center" style="flex:auto;">
    <div class="card col-12 shadow-sm">
        <div class="card-body">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Information</li>
                </ol>
            </nav>
            <h5 class="card-title d-flex justify-content-center mb-5">User Information</h5>

            <div id="notification-area">
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
                <div class="alert alert-success" role="alert"><?= session('message') ?><?= \Config\Services::validation()->listErrors() ?></div>
                <?php endif ?>
            </div>

            <div class="container">
                <?php $userSettingService = service('userSettings') ?>
                <form>
                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-md-3 col-sm-6 col-form-label text-start">Email</label>
                        <div class="col-sm-6">
                            <input type="email" class="form-control" id="inputEmail3" value="<?= $userInfo->email ?>" disabled>
                        </div>
                        <div class="col-sm-3 text-start d-flex align-items-center">
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#changeEmailModal">Change Email</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="switchHideEmail" class="col-md-3 col-sm-6 col-form-label text-start pe-0">
                            Hide Email Address
                            <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="For enhanced privacy on the site.<br/>Enabling the switch will not apply this effect, instead, it simply reveals two additional sub-options for specific customizations, which are the options that actually apply the privacy settings if selected.">
                                <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                            </button>
                        </label>
                        <div class="col-sm-6 pt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="hide-email" role="switch" id="switchHideEmail" <?= $userSettingService->get('hide_email') ? 'checked' : '' ?>>
                            </div>

                            <div class="hide-email-options <?= $userSettingService->get('hide_email') ? '' : 'd-none' ?>" id="hideEmailOptions">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="hideEmailAsHost" name="hide_email_host" <?= $userSettingService->get('hide_email_host') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hideEmailAsHost">
                                        As Tournament Host
                                        <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="Hides Email Address from being displayed in the Tournament Organizer/Host informational modals.">
                                            <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                        </button>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="hideEmailAsParticipant" name="hide_email_participant" <?= $userSettingService->get('hide_email_participant') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hideEmailAsParticipant">
                                        As Tournament Participant
                                        <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="Hides Email Address from being displayed in the Tournament Participants field and Leaderboard informational modals.">
                                            <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                                        </button>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="switchAllowInvitations" class="col-md-3 col-sm-6 col-form-label text-start pe-0">
                            Allow Invitations
                            <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content="If enabled, Tournament organizers/hosts can invite you to participate by adding your username to the participants list.">
                                <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                            </button>
                        </label>
                        <div class="col-sm-6 pt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="disable_invitations" role="switch" id="switchAllowInvitations" <?= $userSettingService->get('disable_invitations') ? '' : 'checked' ?>>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputEmail3" class="col-md-3 col-sm-6 col-form-label text-start">
                            Cookie Permission Status
                            <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content='Your cookie consent preference is saved only in this browser. <br/>If you access this site from a different device or browser, you will need to set your preference again.'>
                                <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                            </button>
                        </label>
                        <div class="col-md-9 col-sm-6">
                            <label class="cookie-status p-1 ps-2 pe-2 rounded-2">Pending</label><br />
                            <a href="javascript:;" class="cookie-reset-btn" data-bs-toggle="modal" data-bs-target="#resetCookieConsentModal">Reset Cookie Permissions</a>
                            <button type="button" class="btn btn-light p-0 bg-transparent border-0" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-html="true" data-bs-content='We respect your privacy. <br/>If you’ve previously accepted or rejected cookies on this site and want to change your choice, <strong>click here</strong> to reset your cookie preferences.<br/> <strong>Note</strong>: This option is inactive when the status is "Pending."'>
                                <i class="fa-classic fa-solid fa-circle-exclamation"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="inputPassword3" class="col-md-3 col-sm-6 col-form-label text-start">Password</label>
                        <div class="col-sm-6 text-start d-flex align-items-center">
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="closeAccount" class="col-md-3 col-sm-6 col-form-label text-start">Close Account</label>
                        <div class="col-sm-6 text-start">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeAccountModal">Close Account</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="changePasswordModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="changePasswordModalLabel">Change Password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2" id="responseMessage"></div>
                <form id="changePasswordForm" action="<?= site_url('profile/update-password') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label for="new_password" class="form-label col-md-4 col-sm-12">New Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="password" id="new_password" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="confirm_password" class="form-label col-md-4 col-sm-12">Confirm Password</label>
                        <div class="col-sm-8">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="changeEmailModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="changeEmailModalLabel">Change Email</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="email-update-notification-area"></div>
                <div class="text-hint mb-3">
                    To change your account email, enter the new email address you want to use. <br />
                    After clicking "Update Email," a verification code will be sent to your new email address. Enter the code in the field provided to finalize the update process.<br /><br />
                    Note: Check your spam folder in case the email may have been sent there and you're not seeing it in your primary inbox.<br />
                    If you still haven't received the verification code after a few minutes, it's possible you may have entered an invalid email address.
                </div>
                <form action="<?= site_url('profile/update-email-confirm') ?>" id="updateEmailForm" method="post">
                    <?= csrf_field() ?>

                    <div class="row mb-3">
                        <label for="current_email" class="form-label col-md-4 col-sm-12">Current Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" name="current_email" id="current_email" value="<?= auth()->user()->email ?>" disabled>
                        </div>
                    </div>

                    <div class="update-email-block">
                        <div class="row mb-3">
                            <label for="new_email" class="form-label col-md-4 col-sm-12">New Email</label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" name="new_email" id="new_email" required>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100" id="sendVerificationCodeBtn" onclick="sendVerificationCode()">Update Email</button>
                    </div>

                    <div class="confirm-code-block d-none">
                        <div class="row mb-3">
                            <label for="new_email" class="form-label col-md-4 col-sm-12">Verification Code</label>
                            <div class="col-md-4 col-sm-6">
                                <input type="text" class="form-control" name="confirm_code" id="confirm_code" required>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <button class="resend-verification-code-link btn btn-primary" id="resend-code" onclick="sendVerificationCode()">Resend Code</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary w-100" id="confirmVerificationCodeBtn" onclick="confirmVerificationCode()">Confirm</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer visually-hidden">

            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="closeAccountModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="closeAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="closeAccountModalLabel">⚠️ Confirm Account Deletion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h3>Are you sure you want to delete your account?</h3>
                <p>This action is permanent and cannot be undone.</p>
                <p>You will lose access to:</p>
                <p>
                    • All your tournaments<br />
                    • Voting records and leaderboard rankings<br />
                    • Any saved customizations and media<br />
                </p>

                This action cannot be reversed!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteAccount()">Close Account</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="resetCookieConsentModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="resetCookieConsentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="resetCookieConsentModalLabel">⚠️ Confirm Cookie Permission Reset</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h3>Are you sure you want to reset the cookie consent permission?</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary confirm">Confirm</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>