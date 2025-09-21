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
    <p>Dear <?= esc($username) ?>,</p>
    <p>We received a request to log into your account. To ensure your security, we have sent you a temporary login link that will grant access to your account.</p>
    <p>Please click the link below to log in:</p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-radius: 6px; border-collapse: separate !important;">
        <tbody>
            <tr>
                <td style="line-height: 24px; font-size: 16px; border-radius: 6px; margin: 0;" align="center" bgcolor="#0d6efd">
                    <a href="<?= url_to('verify-magic-link') ?>?token=<?= $token ?>" style="color: #ffffff; font-size: 16px; font-family: Helvetica, Arial, sans-serif; text-decoration: none; border-radius: 6px; line-height: 20px; display: inline-block; font-weight: normal; white-space: nowrap; background-color: #0d6efd; padding: 8px 12px; border: 1px solid #0d6efd;"><?= lang('Auth.login') ?></a>
                </td>
            </tr>
        </tbody>
    </table>
    <br />
    <p>This link will remain valid for the next 60 minutes. If you did not request this <a href="<?= base_url('login') ?>">login</a>, please disregard this email. </p>
    <br />
    <p>Best regards,</p>
    <p><?= esc($sendername) ?></p>
</body>

</html>