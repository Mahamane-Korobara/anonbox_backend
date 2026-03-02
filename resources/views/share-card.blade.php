<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $card['title'] ?? 'AnonBox' }}</title>
    <meta name="description" content="{{ $card['description'] ?? 'Réponds anonymement sur AnonBox.' }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $card['title'] ?? 'AnonBox' }}">
    <meta property="og:description" content="{{ $card['description'] ?? 'Réponds anonymement sur AnonBox.' }}">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:image" content="{{ $card['image_url'] ?? '' }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $card['title'] ?? 'AnonBox' }}">
    <meta name="twitter:description" content="{{ $card['description'] ?? 'Réponds anonymement sur AnonBox.' }}">
    <meta name="twitter:image" content="{{ $card['image_url'] ?? '' }}">

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f1f2f6;
            color: #1e272e;
        }
        main {
            max-width: 560px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .card {
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #dfe4ea;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }
        img {
            display: block;
            width: 100%;
            height: auto;
        }
        .btn {
            display: block;
            margin-top: 16px;
            text-align: center;
            text-decoration: none;
            background: #ff4757;
            color: #fff;
            padding: 14px 18px;
            border-radius: 14px;
            font-weight: 700;
        }
    </style>
</head>
<body>
<main>
    <div class="card">
        <img src="{{ $card['image_url'] ?? '' }}" alt="Carte de partage AnonBox">
    </div>

    @if(!empty($card['target_url']))
        <a class="btn" href="{{ $card['target_url'] }}">Répondre anonymement</a>
    @endif
</main>
</body>
</html>
