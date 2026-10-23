<?php
/*
 * LOCAL DEVELOPMENT ONLY - do not upload to the live server.
 *
 * PHP's built-in server ignores .htaccess, so this file gives it the same
 * behaviour: existing files are served normally, and any missing URL returns
 * the custom 404 page WITH a real 404 status code.
 *
 * Start the site with:   php -S localhost:8000 router.php
 */

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// Files that must never be public (same as .htaccess) -> 404
if (preg_match('#^/(vendor/|composer\.(json|lock)$|router\.php$|\.htaccess$)#', $path)) {
    send_404();
}

// 301 redirects for moved pages - mirror the ones in .htaccess
$redirects = [
    // '/old-page.html' => '/new-page.html',
];
if (isset($redirects[$path])) {
    header('Location: ' . $redirects[$path], true, 301);
    exit;
}

// Home page and real files: let PHP's server handle them (html, css, js, images, contact.php)
$file = realpath(__DIR__ . $path);
if ($path === '/' || ($file !== false && strpos($file, __DIR__) === 0 && is_file($file))) {
    return false;
}

send_404();

function send_404(): void
{
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    readfile(__DIR__ . '/404.html');
    exit;
}
