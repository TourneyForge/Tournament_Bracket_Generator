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
    <p>It’s been a long time since we last saw you on <strong><a href="<?= base_url() ?>">TourneyForge</a></strong>, and we don’t want you to miss out on all the exciting things happening!</p>

    <p>
        🔥 Forge new tournaments, customize your brackets, and jump back into the competition.<br />
        🎭 Spectate thrilling matchups in the <a href="<?= base_url('gallery?filter=glr') ?>">Tournament Gallery</a>.<br />
        🏆 Your <a href="<?= base_url('participants') ?>">Leaderboard</a> ranking is still there—come back and reclaim your spot!
    </p>

    <p>Come back and experience the action today!</p>

    <p>👉 <a href="<?= base_url() ?>">Join</a> the Fun!</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?> Team</p>
    <p>Disclaimer: To opt out of these emails, <a href="<?= base_url('login') ?>">login</a> and adjust the notification setting from the "bell" icon.</p>
</body>

</html>
