<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WACHAAR — Password Reset</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#111;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5; padding:40px 15px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0"
                   style="max-width:600px; width:100%; background:#ffffff;">

                <!-- Header -->
                <tr>
                    <td style="padding:40px; text-align:center; border-bottom:1px solid #eeeeee;">

                        <div style="font-size:28px; font-weight:bold; letter-spacing:5px;">
                            WACHAAR
                        </div>

                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:45px 40px;">

                        <h1 style="font-size:24px; margin:0 0 25px;">
                            Password Reset
                        </h1>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            We received a request to reset your WACHAAR account password.
                        </p>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            Use the verification code below to reset your password:
                        </p>

                        <!-- Code -->
                        <div style="margin:35px 0; padding:25px; background:#f7f7f7; text-align:center;">

                            <div style="font-size:12px; color:#777; margin-bottom:10px;">
                                YOUR VERIFICATION CODE
                            </div>

                            <div style="font-size:32px; font-weight:bold; letter-spacing:8px;">
                                {{ $code }}
                            </div>

                        </div>

                        <p style="font-size:14px; line-height:1.7; color:#777;">
                            This code is valid for 10 minutes.
                        </p>

                        <p style="font-size:14px; line-height:1.7; color:#777;">
                            If you did not request a password reset, you can safely ignore this email.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:25px 40px; background:#111; color:#fff; text-align:center;">

                        <div style="font-size:12px; letter-spacing:2px;">
                            WACHAAR
                        </div>

                        <div style="font-size:12px; color:#aaa; margin-top:10px;">
                            Magazine &amp; Creative Platform
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
