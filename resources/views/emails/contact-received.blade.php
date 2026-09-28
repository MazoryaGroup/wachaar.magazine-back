<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Message Received</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f4f4;
    font-family:Arial, Helvetica, sans-serif;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="background:#f4f4f4; padding:40px 20px;"
>
    <tr>
        <td align="center">

            <table
                width="600"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    max-width:600px;
                    width:100%;
                    background:#ffffff;
                "
            >

                <!-- Logo -->
                <tr>
                    <td style="
                        padding:40px 40px 20px;
                        text-align:center;
                    ">

                        <h1 style="
                            margin:0;
                            font-size:32px;
                            letter-spacing:5px;
                            color:#111111;
                            font-weight:600;
                        ">
                            WACHAAR
                        </h1>

                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="
                        padding:20px 40px 40px;
                    ">

                        <h2 style="
                            margin:0 0 20px;
                            font-size:24px;
                            color:#111111;
                            font-weight:500;
                        ">
                            Thank you, {{ $contact->name }}
                        </h2>

                        <p style="
                            margin:0 0 18px;
                            color:#555555;
                            font-size:15px;
                            line-height:1.7;
                        ">
                            We have received your message successfully.
                        </p>

                        <p style="
                            margin:0 0 25px;
                            color:#555555;
                            font-size:15px;
                            line-height:1.7;
                        ">
                            Our team will review your request and get back
                            to you as soon as possible.
                        </p>

                        <!-- Request Details -->

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-top:25px;
                                border-top:1px solid #eeeeee;
                                border-bottom:1px solid #eeeeee;
                            "
                        >

                            <tr>
                                <td style="
                                    padding:14px 0;
                                    color:#888888;
                                    font-size:13px;
                                    width:140px;
                                ">
                                    Service
                                </td>

                                <td style="
                                    padding:14px 0;
                                    color:#111111;
                                    font-size:14px;
                                ">
                                    {{ $contact->service }}
                                </td>
                            </tr>

                            @if($contact->company_name)

                                <tr>
                                    <td style="
                                    padding:14px 0;
                                    color:#888888;
                                    font-size:13px;
                                ">
                                        Company
                                    </td>

                                    <td style="
                                    padding:14px 0;
                                    color:#111111;
                                    font-size:14px;
                                ">
                                        {{ $contact->company_name }}
                                    </td>
                                </tr>

                            @endif

                            <tr>
                                <td style="
                                    padding:14px 0;
                                    color:#888888;
                                    font-size:13px;
                                    vertical-align:top;
                                ">
                                    Message
                                </td>

                                <td style="
                                    padding:14px 0;
                                    color:#111111;
                                    font-size:14px;
                                    line-height:1.7;
                                ">
                                    {{ $contact->message }}
                                </td>
                            </tr>

                        </table>

                        <p style="
                            margin:30px 0 0;
                            color:#777777;
                            font-size:13px;
                            line-height:1.6;
                        ">
                            Please keep this email for your records.
                            There is no need to submit your request again.
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
