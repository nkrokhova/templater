<?php

spl_autoload_register(function ($class) {
    if (strpos($class, 'Templater\\') === 0) {
        $dir = strcasecmp(substr($class, -4), 'Test') ? 'src' : 'tests';

        $name = substr($class, strlen('Templater'));

        require __DIR__ . '/../' . $dir . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});
