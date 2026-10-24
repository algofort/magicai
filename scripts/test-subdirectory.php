<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/vendor/igaster/laravel-theme/src/Helpers/helpers.php';

use App\Support\SubdirectoryUrl;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

function same($expected, $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

$base = 'https://products.example.com/magicai';
same($base . '/themes/default/app.css', SubdirectoryUrl::asset('/themes/default/app.css', $base, $base));
same($base . '/upload/logo.png', SubdirectoryUrl::asset('/upload/logo.png', $base, '/'));
same($base . '/themes/app.js?v=2', SubdirectoryUrl::asset('/magicai/themes/app.js?v=2', $base));
same('https://cdn.example.com/logo.png', SubdirectoryUrl::asset('https://cdn.example.com/logo.png', $base));
same('//cdn.example.com/logo.png', SubdirectoryUrl::asset('//cdn.example.com/logo.png', $base));
same('data:image/png;base64,abc', SubdirectoryUrl::asset('data:image/png;base64,abc', $base));
same('https://cdn.example.com/static/upload/logo.png', SubdirectoryUrl::asset('/upload/logo.png', $base, 'https://cdn.example.com/static'));
same('https://products.example.com/static/logo.png', SubdirectoryUrl::asset('/logo.png', $base, '/static'));
same('http://localhost:8020/themes/app.js', SubdirectoryUrl::asset('/themes/app.js', 'http://localhost:8020', '/'));
same('', SubdirectoryUrl::asset('', $base));

// Exercise the real shared Blade helper without booting the database/application.
$container = new Container;
Container::setInstance($container);
$container->instance('config', new Repository(['app' => ['url' => $base, 'asset_url' => $base]]));
$container->instance('igaster.themes', new class {
    public function url($path): string
    {
        return '/themes/default/' . ltrim($path, '/');
    }
});
same($base . '/themes/default/assets/js/app.js', custom_theme_url('assets/js/app.js'));
same($base . '/upload/logo.png', custom_theme_url('/upload/logo.png', true));
same($base . '/upload/logo.png', custom_theme_url('upload/logo.png', true));
same('MagicAI', custom_theme_url('MagicAI'));
same('https://cdn.example.com/logo.png', custom_theme_url('https://cdn.example.com/logo.png', true));
echo "Asset URL and shared helper checks passed.\n";

// Check the generated PHP too: complex expressions inside @json can compile
// incorrectly even when the original Blade file looks syntactically valid.
$blade = new \Illuminate\View\Compilers\BladeCompiler(new \Illuminate\Filesystem\Filesystem, sys_get_temp_dir());
$template = file_get_contents(dirname(__DIR__) . '/resources/views/default/panel/layout/scripts.blade.php');
$compiled = tempnam(sys_get_temp_dir(), 'magicai-blade-');
try {
    file_put_contents($compiled, $blade->compileString($template));
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($compiled), $output, $status);
    if ($status !== 0) {
        throw new RuntimeException('Compiled auth Blade PHP failed syntax check.');
    }
} finally {
    unlink($compiled);
}
echo "Compiled authentication Blade syntax check passed.\n";
