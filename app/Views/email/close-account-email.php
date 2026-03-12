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
        <a href="<?= base_url() ?>"><img src="<?= base_url('images/logo.png') ?>" style="max-height: 120px;"></a>
    </div>
    <p>Hi <?= esc($username) ?>,</p>
    <p>This is to confirm that your <a href="<?= base_url() ?>"><?= esc($sendername) ?></a> account has been permanently deleted.</p><br />
    <p>📌 <b>What this means:</b></p>
    <p> • You can no longer access your tournaments or history.</p>
    <p> • Any shared tournaments or votes associated with your account are no longer valid. </p>
    <p>• If this was a mistake, you’ll need to create a new account to use the platform again.</p>

    <br />
    <p>We’re sad to see you go, but if you ever decide to return, we’ll be here!</p>
    <br />
    <p>Best regards,</p>
    <p>🏆 <?= esc($sendername) ?> Team</p>
</body>

</html>
