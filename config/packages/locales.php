<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

// Not in framework.yaml: config/schema.json refuses a parameter where it expects an array,
// so "lint:yaml --check-schema" would fail on it.
return App::config([
    'framework' => [
        'enabled_locales' => '%app.locales%',
    ],
]);
