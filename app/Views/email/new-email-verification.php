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
    <p>We received a request to change the email associated with your <a href="<?= base_url() ?>"><?= esc($sendername) ?></a> account to <?= esc($newEmail) ?>. Before we update your account, please verify your new email by entering the code provided below:</p>
    <br />
    <p>🔹 <b>Your Verification Code:</b> <?= esc($code) ?></p><br />
    <p>If you didn’t request this change, please ignore this email—your current email will remain unchanged.</p><br />
    <p>🏆 <?= esc($sendername) ?> Team</p>
</body>

</html>