<?php

/**
 * Application configuration shared by all test types
 */
return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@web' => '@app'
    ],
    'controllerNamespace' => 'app\tests'
];
