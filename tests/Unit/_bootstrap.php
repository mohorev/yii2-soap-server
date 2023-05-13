<?php

// add unit testing specific bootstrap code here

// register Composer autoloader
require(__DIR__ . '/../../vendor/autoload.php');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

// include Yii class file
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');

// load application configuration
$config = require(__DIR__ . '/../Yii2_config/test.php');

// create, configure and run application
$application = new yii\console\Application($config);
