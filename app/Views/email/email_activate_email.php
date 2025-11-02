<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.emailActivateSubject') ?></title>
</head>

<body>
    <div class="logo" style="text-align: center;">
        <a href="<?= base_url() ?>"><img src="<?= base_url('images/logo.jpg') ?>" style="max-height: 120px;"></a>
    </div>
    <p>Welcome to <b><a href="<?= base_url() ?>"><?= setting('Email.fromName') ?></a></b>! Before you can start creating/administering tournaments, we need to verify your account. </p>
    <p>
        <b>Why verify?</b> <br />
        ✔️ Secure your account <br />
        ✔️ Access all features, including custom tournaments, historical logging, and more!<br />
        ✔️ Confirms you're not a bot 🤖
    </p>
    <p><?= lang('Auth.emailActivateMailBody') ?></p>
    <div>
        <h1><?= $code ?></h1>
    </div>
    <br />
    <p>If you didn’t sign up for <b><?= setting('Email.fromName') ?></b>, you can disregard this email.</p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="width: 100%;" width="100%">
        <tbody>
            <tr>
                <td style="line-height: 20px; font-size: 20px; width: 100%; height: 20px; margin: 0;" align="left" width="100%" height="20">
                    &#160;
                </td>
            </tr>
        </tbody>
    </table>
    <p>🏆 See you in the tournament arena!</p>
    <p><?= setting('Email.fromName') ?> Team</p>
</body>

</html>