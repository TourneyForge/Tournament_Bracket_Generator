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
    <p>The sharing permissions for the tournament "<strong><a href="<?= base_url("tournaments/shared/{$share['token']}") ?>"><?= $tournament->name ?></a></strong>" privately shared with you have been updated.</p>

    <p><b>Here’s what changed:</b></p>

    <?php $user = auth()->user() ? auth()->getProvider()->findById(auth()->user()->id) : null; ?>
    🔹 <strong>Updated By</strong>: <?= $user ? "$user->username ($user->email)" : "Guest User" ?><br />
    🔹 <strong>New Role</strong>: <?= $role ?>

    <p>You may now have different access to view or conduct certain actions based on the updated permissions.</p>

    <p>If you were not expecting this change or have any questions, please contact the tournament organizer/host.</p>

    <p>Best regards,</p>
    <p>🏆 <?= esc($tournamentCreatorName) ?> Team</p>
    <br />
    <p>Disclaimer: To opt out of these emails, <a href="<?= base_url('login') ?>">login</a> and adjust the notification setting from the "bell" icon.</p>
</body>

</html>