#!/usr/bin/env php
<?php
/**
 * Object Foundation - README auto-updater (multilang)
 * - Injects/updates top badges, contacts/company table, and footer badges
 * - Supports README.en.md and README.ru.md
 *
 * Markers:
 *   <!-- OF:TOP-BADGES START --> ... <!-- OF:TOP-BADGES END -->
 *   <!-- OF:CONTACTS START --> ... <!-- OF:CONTACTS END -->
 *   <!-- OF:FOOTER START --> ... <!-- OF:FOOTER END -->
 */

declare(strict_types=1);

function repoMeta(): array {
    // Allow override via env in CI, fallback to generic
    $ownerRepo = getenv('GITHUB_REPOSITORY') ?: 'MarketingAmericaCorp/object-foundation';
    $pkg = getenv('COMPOSER_PACKAGE') ?: 'marketingamericacorp/object-foundation';
    $defaultPhone = '+1 707-867-5833';
    $defaultEmail = 'taa0662621456@gmail.com';
    $author = 'Oleksandr Tishchenko';
    $org = 'Marketing America Corp';
    return [
        'ownerRepo' => $ownerRepo,
        'pkg' => $pkg,
        'author' => $author,
        'org' => $org,
        'phone' => getenv('ORG_PHONE') ?: $defaultPhone,
        'email' => getenv('ORG_EMAIL') ?: $defaultEmail,
        'brandA' => 'Smartresponsor',
        'brandB' => 'iSponsor',
        'site' => 'https://isponsor.dev',
    ];
}

function topBadges(string $ownerRepo, string $pkg): string {
    $parts = explode('/', $ownerRepo, 2);
    $owner = $parts[0] ?? 'MarketingAmericaCorp';
    $repo = $parts[1] ?? 'object-foundation';
    $build = "https://github.com/$ownerRepo/actions/workflows/ci-cd.yml/badge.svg";
    $buildUrl = "https://github.com/$ownerRepo/actions/workflows/ci-cd.yml";
    $apiUrl = "https://$owner.github.io/$repo/api/latest/";
    $covUrl = "https://$owner.github.io/$repo/coverage/latest/";
    $devUrl = "https://$owner.github.io/$repo/dev/latest/";
    $packagist = "https://packagist.org/packages/$pkg";
    $packagistBadge = "https://img.shields.io/packagist/v/$pkg.svg";
    $licenseUrl = "https://github.com/$ownerRepo/blob/master/LICENSE";

    return <<<MD
<!-- OF:TOP-BADGES START -->
<p align="center">
  <a href="$buildUrl"><img src="$build" alt="Build Status"></a>
  <a href="$apiUrl"><img src="https://img.shields.io/badge/API-Docs-blue?logo=swagger" alt="API Docs"></a>
  <a href="$covUrl"><img src="https://img.shields.io/badge/Coverage-Latest-brightgreen" alt="Coverage"></a>
  <a href="$devUrl"><img src="https://img.shields.io/badge/Developer%20Docs-Latest-orange" alt="Developer Docs"></a>
  <a href="$packagist"><img src="$packagistBadge" alt="Packagist Version"></a>
  <a href="$licenseUrl"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License MIT"></a>
</p>
<!-- OF:TOP-BADGES END -->
MD;
}

function contactsTable(string $lang, array $meta): string {
    $date = date('Y-m-d');
    if ($lang === 'ru') {
        $title = '## 📞 Контакты и Компания';
        $rows = [
            ['Поле','Значение'],
            ['Компания',$meta['org']],
            ['Владелец',$meta['author']],
            ['Телефон',$meta['phone']],
            ['Эл. почта', $meta['email']],
            ['Бренды', $meta['brandA'].', '.$meta['brandB']],
            ['Сайт', '['.$meta['site'].']('.$meta['site'].')'],
        ];
        $updated = "_Обновлено: $date";
    } else {
        $title = '## 📞 Contacts / Company Info';
        $rows = [
            ['Field','Value'],
            ['Company',$meta['org']],
            ['Owner',$meta['author']],
            ['Phone',$meta['phone']],
            ['Email', $meta['email']],
            ['Brands', $meta['brandA'].', '.$meta['brandB']],
            ['Website', '['.$meta['site'].']('.$meta['site'].')'],
        ];
        $updated = "_Last updated: $date";
    }
    // Build Markdown table
    $md = $title."\n\n";
    $md .= '| '.$rows[0][0].' | '.$rows[0][1]." |\n";
    $md .= '|'.str_repeat('-', strlen($rows[0][0])+2).'|'.str_repeat('-', strlen($rows[0][1])+2)."|\n";
    for ($i=1; $i<count($rows); $i++) {
        $md .= '| '.$rows[$i][0].' | '.$rows[$i][1]." |\n";
    }
    $md .= "\n$updated\n";
    return <<<MD
<!-- OF:CONTACTS START -->
$md
<!-- OF:CONTACTS END -->
MD;
}

function footerBadges(string $lang, array $meta): string {
    $ownerRepo = $meta['ownerRepo'];
    $parts = explode('/', $ownerRepo, 2);
    $owner = $parts[0] ?? 'MarketingAmericaCorp';
    $repo = $parts[1] ?? 'object-foundation';
    $packagist = "https://packagist.org/packages/".$meta['pkg'];
    $packagistBadge = "https://img.shields.io/packagist/v/".$meta['pkg'].".svg";
    $licenseUrl = "https://github.com/$ownerRepo/blob/master/LICENSE";
    $apiUrl = "https://$owner.github.io/$repo/api/latest/";

    $createdBy = ($lang === 'ru') ? "Создано" : "Created by";

    return <<<MD
<!-- OF:FOOTER START -->
---

<p align="center">
  <strong>© {YEAR} {$meta['org']}</strong><br/>
  <em>$createdBy <a href="https://github.com/taa0662621456">{$meta['author']}</a></em><br/>
  <a href="https://smartresponsor.com">{$meta['brandA']}</a> • <a href="https://isponsor.dev">{$meta['brandB']}</a><br/>
  📞 {$meta['phone']} 📧 <a href="mailto:{$meta['email']}">{$meta['email']}</a><br/><br/>
  <a href="$packagist"><img src="$packagistBadge" alt="Packagist Version"></a>
  <a href="$licenseUrl"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License MIT"></a>
  <a href="$apiUrl"><img src="https://img.shields.io/badge/API-Docs-blue?logo=swagger" alt="API Docs"></a>
</p>

---
<!-- OF:FOOTER END -->
MD;
}

function upsertBetweenMarkers(string $content, string $start, string $end, string $replacement): string {
    $pattern = '/'.preg_quote($start,'/').'(.*?)'.preg_quote($end,'/').'/s';
    if (preg_match($pattern, $content)) {
        return preg_replace($pattern, $replacement, $content);
    }
    // Not found: append at the end
    return rtrim($content)."\n\n".$replacement."\n";
}

function processFile(string $path, string $lang, array $meta): void {
    $exists = file_exists($path);
    $content = $exists ? file_get_contents($path) : "# Object Foundation\n\n";

    // Build sections
    $tb = topBadges($meta['ownerRepo'], $meta['pkg']);
    $ct = contactsTable($lang, $meta);
    $fb = footerBadges($lang, $meta);
    $fb = str_replace('{YEAR}', date('Y'), $fb);

    // Ensure TOP badges at very top
    if (strpos($content, '<!-- OF:TOP-BADGES START -->') === false) {
        $content = $tb."\n\n".$content;
    } else {
        $content = upsertBetweenMarkers($content, '<!-- OF:TOP-BADGES START -->', '<!-- OF:TOP-BADGES END -->', $tb);
    }

    // Contacts block (insert before footer if exists)
    if (strpos($content, '<!-- OF:CONTACTS START -->') === false) {
        if (strpos($content, '<!-- OF:FOOTER START -->') !== false) {
            $content = str_replace('<!-- OF:FOOTER START -->', $ct."\n\n".'<!-- OF:FOOTER START -->', $content);
        } else {
            $content = rtrim($content)."\n\n".$ct."\n";
        }
    } else {
        $content = upsertBetweenMarkers($content, '<!-- OF:CONTACTS START -->', '<!-- OF:CONTACTS END -->', $ct);
    }

    // Footer block
    if (strpos($content, '<!-- OF:FOOTER START -->') === false) {
        $content = rtrim($content)."\n\n".$fb."\n";
    } else {
        $content = upsertBetweenMarkers($content, '<!-- OF:FOOTER START -->', '<!-- OF:FOOTER END -->', $fb);
    }

    file_put_contents($path, $content);
    echo "Updated: $path\n";
}

function main(): void {
    $meta = repoMeta();
    processFile('README.en.md', 'en', $meta);
    processFile('README.ru.md', 'ru', $meta);
    echo "Done.\n";
}

main();
