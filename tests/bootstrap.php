<?php

if ((!$loader = @include __DIR__.'/../../../.composer/autoload.php')
        && (!$loader = @include __DIR__.'/../vendor/.composer/autoload.php')
) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

$loader->add('Kaloa\\Tests', __DIR__);
$loader->register();
