<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.emailActivateTitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card col-12 col-md-5 shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?= lang('Auth.emailActivateTitle') ?></h5>

            <div id="notification-area">
                <?php if (session('error')) : ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
                <?php endif; ?>
            </div>

            <p>
                <?= lang('Auth.emailActivateBody', [$user->email]) ?>
            </p>

            <form action="<?= url_to('auth-action-verify') ?>" method="post">
                <?= csrf_field() ?>

                <!-- Code -->
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" id="floatingTokenInput" name="token" placeholder="000000" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" value="<?= old('token') ?>" required>
                    <label for="floatingTokenInput"><?= lang('Auth.token') ?></label>
                </div>

                <!-- Resend Code Button -->
                <div class="text-end mb-2">
                    <button class="resend-verification-code-link btn btn-link" id="resend-code" onclick="sendVerificationCode()">Resend Code</b>
                </div>

                <div class="d-grid col-4 col-sm-6 mx-auto mb-3">
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.send') ?></button>
                </div>

                <div class="d-grid col-4 col-sm-6 mx-auto">
                    <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#abortVerificationModal"><?= lang('Auth.abortVerification') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="abortVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="abortVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abortVerificationModalLabel"><?= lang('Auth.abortVerification') ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= lang('Auth.abortVerificationModalBody') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang('Button.dismiss') ?></button>
                <button type="button" class="btn btn-primary" onclick="abortVerification()"><?= lang('Button.ok') ?></button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<?= $this->section('pageScripts') ?>
<script>
$(document).ready(function() {
    let resendButton = document.getElementById('resend-code');
    resendButton.disabled = true;

    startCooldown(resendButton);

    // Clear notification after 10 seconds
    setTimeout(function() {
        $('#notification-area').fadeOut('slow', function() {
            $(this).empty().show(); // Empty and show to reset for future messages
        });
    }, 10000);
});
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

let sendVerificationCode = (resend = false) => {
    let resendButton = document.getElementById('resend-code');
    resendButton.disabled = true;

    $.ajax({
        url: '<?= base_url('auth/resend-verification') ?>',
        type: 'get',
        success: function(response) {
            let message = `<?= isset($user) ? lang('Auth.newVerificationCodeSentMessage', [$user->email]) : '' ?>`
            $('#notification-area').html(
                `<div class="alert alert-success">${message}</div>`
            );

            startCooldown(resendButton);

            // Clear notification after 10 seconds
            setTimeout(function() {
                $('#notification-area').fadeOut('slow', function() {
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

let abortVerification = () => {
    window.location.href = "<?= base_url('auth/a/abort-verification') ?>"
}
</script>
<?= $this->endSection() ?>