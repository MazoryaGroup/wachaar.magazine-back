<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WACHAAR — Project Review Update</title>
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
                            Update regarding your project
                        </h1>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            Thank you for submitting your project to WACHAAR.
                        </p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->translations->firstWhere('locale', 'en')): ?>
                            <div style="margin:30px 0; padding:20px; background:#f7f7f7;">

                                <div style="font-size:12px; color:#888; margin-bottom:8px;">
                                    PROJECT
                                </div>

                                <div style="font-size:18px; font-weight:bold;">
                                    <?php echo e($project->translations->firstWhere('locale', 'en')->title); ?>

                                </div>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            After reviewing your project, our team was unable
                            to approve it at this time.
                        </p>

                        <div style="margin:35px 0; padding:20px; background:#f7f7f7;">
                            <strong>Status:</strong>
                            <span style="margin-left:8px;">
                                Not Approved
                            </span>
                        </div>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            You may review your project information and
                            submit it again for review.
                        </p>

                        <p style="font-size:15px; line-height:1.8; color:#555;">
                            Thank you for your interest in WACHAAR.
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
<?php /**PATH D:\xampp\htdocs\wachaar.magazine-back\resources\views/emails/project-rejected.blade.php ENDPATH**/ ?>