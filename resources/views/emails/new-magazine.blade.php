<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>New Magazine — WACHAAR</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, sans-serif;">

<div style="max-width:600px; margin:40px auto; background:#ffffff; padding:40px;">

    <h1 style="margin-top:0;">
        WACHAAR
    </h1>

    <h2>
        A new magazine is available.
    </h2>

    @php
        $translation = $magazine->translations
            ->firstWhere('locale', 'en');
    @endphp

    @if($translation?->title)
        <h3>
            {{ $translation->title }}
        </h3>
    @endif

    @if($translation?->description)
        <p>
            {{ $translation->description }}
        </p>
    @endif

    <p>
        A new issue of WACHAAR Magazine has just been added.
    </p>

    <p>
        Stay tuned and discover the latest issue.
    </p>

</div>

</body>
</html>
