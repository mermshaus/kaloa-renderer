<?php

require_once '../bootstrap.php';

$mp = Kaloa\Renderer\Factory::createRenderer(null, 'markdown');

echo $mp->render(file_get_contents(__DIR__ . '/php-markdown-readme.text'));
