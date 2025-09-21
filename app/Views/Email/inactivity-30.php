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
    <p>It’s been a while since you last visited <strong><a href="<?= base_url() ?>">TourneyForge</a></strong>, and we miss you! 🚀</p>

    <p>New tournaments are happening, and your next competition could be just around the corner.</p>

    <p>
        🔥 <strong>Here’s What You’re Missing:</strong><br />
        ✅ <strong>New Tournaments</strong> – Spectate thrilling matches in the <a href="<?= base_url('gallery?filter=glr') ?>">Tournament Gallery</a>.<br />
        🎨 <strong>Customization</strong> – Personalize tournaments with themes, images, and media (audio/video) for dramatic bracket reveals!<br />
        🏆 <strong><a href="<?= base_url('participants') ?>">Leaderboard</a> Rankings</strong> – See who’s rising to the top and claim your spot!<br />
        🗳️ <strong>Voting & Engagement</strong> – Vote for your favorite participants and shape the competition.<br />
        📢 <strong>Share & Compete**</strong> – Invite participants, manage permissions, and track tournament history through your <a href="<?= base_url('tournaments') ?>">Tournament Dashboard</a> log.
    <p>And much more!</p>
    </p>

    <p>Your next big moment is waiting—jump back in and experience the action!</p>

    <p>👉 Visit <a href="<?= base_url() ?>">TourneyForge</a> Now</p>

    <p>See you in the arena!</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?> Team</p>
    <p>Disclaimer: To opt out of these emails, <a href="<?= base_url('login') ?>">login</a> and adjust the notification setting from the "bell" icon.</p>
</body>

</html>