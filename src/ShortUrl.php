<?php

declare(strict_types=1);

class ShortUrl
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $longUrl): string
    {
        $longUrl = $this->normalize($longUrl);

        if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL tidak valid.');
        }

        $code = $this->generateUniqueCode();
        $stmt = $this->pdo->prepare('INSERT INTO urls (code, original_url) VALUES (?, ?)');
        $stmt->execute([$code, $longUrl]);

        return $code;
    }

    public function find(string $code): ?string
    {
        $code = preg_replace('/[^a-zA-Z0-9]/', '', $code);
        if ($code === '') {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT original_url FROM urls WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch();

        return $row ? (string) $row['original_url'] : null;
    }

    private function normalize(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }

        if (!preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*://#', $url)) {
            $url = 'http://' . $url;
        }

        return $url;
    }

    private function generateUniqueCode(int $length = 6): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($alphabet) - 1;

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            $bytes = random_bytes($length);
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[ord($bytes[$i]) & $maxIndex];
            }

            $stmt = $this->pdo->prepare('SELECT 1 FROM urls WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
            if ($stmt->fetch() === false) {
                return $code;
            }
        }

        throw new RuntimeException('Gagal menghasilkan kode unik.');
    }
}
