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
    <p>You've been invited to join the tournament "<strong><a href="<?= base_url("tournaments/$tournament->id/view") ?>"><?= $tournament->name ?></a></strong>"!</p>

    <p>Get ready to compete and showcase your skills.</p>

    <?php $user = auth()->user() ? auth()->getProvider()->findById(auth()->user()->id) : null; ?>
    🔹 <strong>Added By</strong>: <?= $user ? "$user->username ($user->email)" : "Guest User" ?><br />
    🔹 <strong>Your Role</strong>: Participant<br />
    🔹 <strong>Group</strong>: <?= $groupName ?? "None (Individual Participant)" ?>

    <p>Prepare yourself for an exciting competition. If you weren’t expecting this invitation, you can ignore this email.</p>

    <p>See you in the brackets!</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?> Team</p>
    <br />
    <p>Disclaimer: To opt out of these emails, <a href="<?= base_url('login') ?>">login</a> and adjust the notification setting from the "bell" icon.</p>
    <p>If you would like not to be invited by organizers/hosts to future tournaments, you may disable the "Allow Invitations" option from your Profile Settings.
    </p>
</body>

</html>