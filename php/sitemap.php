<?php
header('Content-Type: application/xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');

$urls = [
    ['loc' => $base . '/', 'changefreq' => 'daily', 'priority' => '1.0'],
    ['loc' => $base . '/services.php', 'changefreq' => 'weekly', 'priority' => '0.9'],
    ['loc' => $base . '/track.php', 'changefreq' => 'hourly', 'priority' => '0.9'],
    ['loc' => $base . '/quote.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['loc' => $base . '/pricing.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
    ['loc' => $base . '/about.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
    ['loc' => $base . '/help.php', 'changefreq' => 'weekly', 'priority' => '0.7'],
    ['loc' => $base . '/news.php', 'changefreq' => 'daily', 'priority' => '0.6'],
    ['loc' => $base . '/careers.php', 'changefreq' => 'weekly', 'priority' => '0.6'],
    ['loc' => $base . '/contact.php', 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => $base . '/sustainability.php', 'changefreq' => 'monthly', 'priority' => '0.5'],
    ['loc' => $base . '/legal.php', 'changefreq' => 'monthly', 'priority' => '0.3'],
    ['loc' => $base . '/privacy.php', 'changefreq' => 'monthly', 'priority' => '0.3'],
    ['loc' => $base . '/terms.php', 'changefreq' => 'monthly', 'priority' => '0.3'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq><?= htmlspecialchars($u['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></changefreq>
        <priority><?= htmlspecialchars($u['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8') ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
