<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.magicLinkSubject') ?></title>
</head>

<body>
    <div class="logo" style="text-align: center;">
        <a href="<?= base_url() ?>"><img src="<?= base_url('images/logo.jpg') ?>" style="max-height: 120px;"></a>
    </div>
    <p>Hi, <?= esc($username) ?>,</p>
    <p>We want to let you know that your <a href="<?= base_url() ?>"><?= esc($sendername) ?></a> account email has been updated.</p>
    <p>📧 <b>Old Email:</b> <?= esc($oldEmail) ?> </p>
    <p>📧 <b>New Email:</b> <?= esc($newEmail) ?> </p>
    <p>If you made this change, no further action is needed.</p><br />
    <p>🔹 <b>Didn’t request this change?</b></p>
    <p>If you did not authorize this update, please <a href="<?= base_url('contact') ?>">contact us</a> immediately to secure your account.</p>
    <br />
    <p>Best regards,</p>

    <p>🏆 <?= esc($sendername) ?> Team</p>
</body>

</html>