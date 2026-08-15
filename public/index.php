<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/ShortUrl.php';

$appUrl = rtrim((string) (getenv('APP_URL') ?: ''), '/');
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($requestPath === '/' || $requestPath === '') {
    $error = '';
    $shortLink = '';
    $originalUrl = '';

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $longUrl = trim((string) ($_POST['url'] ?? ''));
        try {
            $short = new ShortUrl(getDbConnection());
            $code = $short->create($longUrl);
            $shortLink = ($appUrl !== '' ? $appUrl : '') . '/' . $code;
            $originalUrl = $longUrl;
        } catch (Throwable $ex) {
            $error = 'Gagal membuat short URL: ' . $ex->getMessage();
        }
    }
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShortURL &middot; Pemendek Tautan</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .card {
            width: 100%;
            max-width: 480px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 50px rgba(0,0,0,.35);
        }
        h1 { margin: 0 0 .25rem; font-size: 1.6rem; }
        p.sub { margin: 0 0 1.5rem; color: #94a3b8; font-size: .9rem; }
        label { display: block; font-size: .8rem; margin-bottom: .4rem; color: #cbd5e1; }
        input[type="url"] {
            width: 100%;
            padding: .8rem 1rem;
            border-radius: 10px;
            border: 1px solid #334155;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 1rem;
        }
        input[type="url"]:focus { outline: 2px solid #6366f1; border-color: #6366f1; }
        button {
            margin-top: 1rem;
            width: 100%;
            padding: .8rem 1rem;
            border: 0;
            border-radius: 10px;
            background: #6366f1;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover { background: #4f46e5; }
        .result {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 10px;
            background: #064e3b;
            border: 1px solid #065f46;
            word-break: break-all;
        }
        .result a { color: #6ee7b7; font-weight: 600; text-decoration: none; }
        .result a:hover { text-decoration: underline; }
        .result small { display: block; margin-top: .4rem; color: #a7f3d0; }
        .error {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 10px;
            background: #7f1d1d;
            border: 1px solid #991b1b;
            color: #fecaca;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>🔗 ShortURL</h1>
        <p class="sub">Tempel tautan panjang, dapatkan tautan pendek.</p>

        <form method="post" action="/">
            <label for="url">Tautan asli</label>
            <input type="url" id="url" name="url" placeholder="https://contoh.com/tautan-yang-sangat-panjang" required value="<?php echo e($originalUrl); ?>">
            <button type="submit">Pendekkan</button>
        </form>

        <?php if ($shortLink !== ''): ?>
            <div class="result">
                Tautan pendek: <a href="<?php echo e($shortLink); ?>"><?php echo e($shortLink); ?></a>
                <small>Asal: <?php echo e($originalUrl); ?></small>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo e($error); ?></div>
        <?php endif; ?>
    </main>
</body>
</html>
    <?php
    exit;
}

$code = ltrim($requestPath, '/');
$short = new ShortUrl(getDbConnection());
$target = $short->find($code);

if ($target === null) {
    http_response_code(404);
    echo 'URL tidak ditemukan.';
    exit;
}

header('Location: ' . $target, true, 302);
exit;
