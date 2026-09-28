<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WACHAAR — Profile Review Update</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#111;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5; padding:40px 15px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0"
                   style="max-width:600px; width:100%; background:#ffffff;">

                <tr>
                    <td style="padding:40px; text-align:center; border-bottom:1px solid #eeeeee;">

                        <div style="font-size:28px; font-weight:bold; letter-spacing:5px;">
                            WACHAAR
                        </div>

                    </td>
                </tr>

                <tr>
                    <td style="padding:45px 40px;">

                        <h1 style="font-size:24px; margin:0 0 25px;">
                            Update regarding your artist profile
                        </h1>

                        <p style="font-size:16px; line-height:1.7; margin:0 0 20px;">
                            Hello {{ $artist->first_name }} {{ $artist->last_name }},
                        </p>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            Thank you for submitting your artist profile to WACHAAR.
                            After reviewing your submission, our team was unable
                            to approve the profile at this time.
                        </p>

                        <div style="margin:35px 0; padding:20px; background:#f7f7f7;">
                            <strong>Status:</strong>
                            <span style="margin-left:8px;">
                                Not Approved
                            </span>
                        </div>

                        @if(!empty($artist->rejection_reason))

                            <div style="margin:30px 0;">

                                <div style="font-size:14px; font-weight:bold; margin-bottom:10px;">
                                    Review Note
                                </div>

                                <div style="padding:20px; background:#f7f7f7; color:#555; line-height:1.8;">
                                    {{ $artist->rejection_reason }}
                                </div>

                            </div>

                        @endif

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            You may update your profile and submit it again
                            according to the review feedback.
                        </p>

                    </td>
                </tr>

                <tr>
                    <td style="padding:25px 40px; background:#111; color:#fff; text-align:center;">

                        <div style="font-size:12px; letter-spacing:2px;">
                            WACHAAR
                        </div>

                        <div style="font-size:12px; color:#aaa; margin-top:10px;">
                            Magazine & Creative Platform
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
