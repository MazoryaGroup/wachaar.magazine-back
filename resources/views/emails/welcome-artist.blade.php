<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Welcome to Wachaar</title>
</head>

<body style="margin:0; padding:0; background:#f4f4f4; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f4; padding:40px 20px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" border="0"
                   style="max-width:600px; width:100%; background:#ffffff;">

                <!-- Header -->
                <tr>
                    <td style="padding:40px 40px 20px; text-align:center;">

                        <h1 style="
                            margin:0;
                            font-size:32px;
                            letter-spacing:4px;
                            color:#111111;
                            font-weight:600;
                        ">
                            WACHAAR
                        </h1>

                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:20px 40px 40px;">

                        <h2 style="
                            margin:0 0 20px;
                            font-size:24px;
                            color:#111111;
                            font-weight:500;
                        ">
                            Welcome, {{ $client->first_name }}
                        </h2>

                        <p style="
                            margin:0 0 16px;
                            color:#555555;
                            font-size:15px;
                            line-height:1.7;
                        ">
                            Your Artist account has been successfully created.
                        </p>

                        <p style="
                            margin:0 0 25px;
                            color:#555555;
                            font-size:15px;
                            line-height:1.7;
                        ">
                            Welcome to Wachaar. You can now complete your artist
                            profile, build your portfolio, and share your work
                            with the Wachaar community.
                        </p>

                        <!-- Button -->
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background:#111111;">

                                    <a href="https://wachaar.com"
                                       style="
                                            display:inline-block;
                                            padding:14px 28px;
                                            color:#ffffff;
                                            text-decoration:none;
                                            font-size:14px;
                                            letter-spacing:1px;
                                       ">
                                        ENTER WACHAAR
                                    </a>

                                </td>
                            </tr>
                        </table>

                        <p style="
                            margin:30px 0 0;
                            color:#777777;
                            font-size:13px;
                            line-height:1.6;
                        ">
                            If you did not create this account, please contact
                            the Wachaar team.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="
                        padding:20px 40px;
                        border-top:1px solid #eeeeee;
                        text-align:center;
                    ">

                        <p style="
                            margin:0;
                            color:#999999;
                            font-size:12px;
                        ">
                            © {{ date('Y') }} Wachaar. All rights reserved.
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
