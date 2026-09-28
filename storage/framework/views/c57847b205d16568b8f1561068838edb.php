<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    ```
    <title>WACHAAR — Project Under Review</title>
    ```

</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#111;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5; padding:40px 15px;">
    <tr>
        <td align="center">

            ```
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
                            Your project is under review
                        </h1>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            Thank you for submitting your project to WACHAAR.
                        </p>

                        <?php
                            $translation = $project->translations
                                ->firstWhere('locale', 'en');
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($translation): ?>
                            <p style="font-size:16px; line-height:1.7;">
                                <strong>
                                    <?php echo e($translation->title); ?>

                                </strong>
                            </p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            Our team is currently reviewing your project.
                            We will notify you once the review process has been completed.
                        </p>

                        <!-- Status -->
                        <div style="margin:35px 0; padding:20px; background:#f7f7f7;">

                            <strong>Status:</strong>

                            <span style="margin-left:8px;">
                            Under Review
                        </span>

                        </div>

                        <p style="font-size:14px; line-height:1.7; color:#777;">
                            Thank you for being part of WACHAAR.
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
    ```

</table>

</body>
</html>
<?php /**PATH D:\xampp\htdocs\wachaar.magazine-back\resources\views/emails/project-draft.blade.php ENDPATH**/ ?>