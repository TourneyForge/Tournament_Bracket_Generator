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
    <p>🏅 Congratulations! You have won the tournament "<strong><a href="<?= base_url("tournaments/$tournament->id/view") ?>"><?= $tournament->name ?></a></strong>" hosted by <?= $creator ? "$creator->username ($creator->email)" : 'Guest' ?> and claimed the top spot! Your hard work, strategy, and skill have led you to victory.</p>

    🔹 <a href="<?= base_url('participants') ?>">Check your stats on the leaderboard!</a>

    <p>Celebrate your achievement and share your victory with others! 🏆</p>

    <p>Thank you for participating, and we hope to see you in future tournaments!</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?> Team</p>
    <br />
    <p>Disclaimer: To opt out of these emails, <a href="<?= base_url('login') ?>">login</a> and adjust the notification setting from the "bell" icon.</p>
</body>

</html>
