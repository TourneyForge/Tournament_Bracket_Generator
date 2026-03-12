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
    <p>Hi, <?= auth()->user()->username ?>,</p>
    <p>Welcome to <b><a href="<?= base_url() ?>"><?= setting('Email.fromName') ?></a></b>! We’re thrilled to have you join us. Whether you're hosting epic tournaments or cheering for your favorites, we’ve got everything you need to make competitions legendary.</p>
    <br />
    <p>🔥 <b>Create Tournaments</b> – Choose from Single, Double, or Knockout elimination styles.</p>
    <p>🎨 <b>Customize Your Experience</b> – Personalize themes, add images, and even play audio or video for dramatic bracket reveals.</p>
    <p>🗳️ <b>Engage with Votes</b> – Vote or let others vote for participants to determine winners.</p>
    <p>🔗 <b>Share & Manage Permissions</b> – Easily share tournaments and control access levels.</p>
    <p>🥇 <b><a href="<?= base_url('participants') ?>">Track the Leaderboard</a></b> – See top competitors and explore public tournaments in the gallery</p>
    <p>📺 <b><a href="<?= base_url('gallery?filter=glr') ?>">Tournament Gallery</a></b> – Spectate live tournaments and watch the competition unfold in real-time—whether you're signed in or just visiting!</p>
    <p>✨️And much more!</p>
    <br />
    <p>🚀 Your journey starts now—<a href="<?= url_to('tournaments/create') ?>" style="color: #ffffff; font-size: 16px; font-family: Helvetica, Arial, sans-serif; text-decoration: none; border-radius: 6px; line-height: 20px; display: inline-block; font-weight: normal; white-space: nowrap; background-color: #0d6efd; padding: 8px 12px; border: 1px solid #0d6efd;">Click here to Create a Tournament</a></p>
    <br />
    <p>Should you have any questions/concerns, don't hesitate to <a href="<?= base_url('contact')?>">contact us</a> and we'll respond accordingly. 😊</p>
    <br />
    <p>⚔️ Let the games begin!</p>
</body>

</html>
