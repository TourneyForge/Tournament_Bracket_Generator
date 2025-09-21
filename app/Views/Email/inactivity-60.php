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
    <p>Hi <?= esc($username) ?>,</p>

    <p>We’ve been hosting some epic tournaments, and it’s just not the same without you! 💥</p>

    <p>
        <strong>You can:</strong>
        🎭 <strong>Spectate</strong> – 🎭 (bold) – Check out the <a href="<?= base_url('gallery?filter=glr') ?>">Tournament Gallery</a> and experience the action.<br />
        🏆 <strong>Climb the <a href="<?= base_url('participants') ?>">Leaderboard</a></strong> – Get back in and make your mark!<br />
        🎨 <strong>Customize Your Brackets</strong> – Style your tournaments with themes, images, and even a mix of audio/viddo media!<br />
    <p>And much more!</p>
    </p>

    <p>It’s time to jump back into the competition—your next victory awaits!</p>

    <p>👉 <a href="<?= base_url() ?>">Jump Back In</a></p>

    <p>We’d love to see you back!</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?> Team</p>
    <p>Disclaimer: To opt out of these emails, <a href="<?= base_url('login') ?>">login</a> and adjust the notification setting from the "bell" icon.</p>
</body>

</html>