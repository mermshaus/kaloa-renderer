<?php

use Kaloa\Renderer\Factory;

require_once __DIR__ . '/../bootstrap.php';

$e = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };

$sets = array();

$sets['commonmark'] = array(
    __DIR__  . '/../../README.md'
);

$sets['inigo'] = array(
    __DIR__ . '/inigo.txt'
);

$sets['xml'] = array(
    __DIR__ . '/xml.txt'
);

$sets['xmllegacy'] = array(
    __DIR__ . '/xmllegacy.txt'
);

$renderers = array();

foreach (array_keys($sets) as $renderer) {
    $renderers[$renderer] = Factory::createRenderer($renderer);
}

?><!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <title>kaloa/renderer demo</title>
  <link rel="stylesheet" href="assets/shared.css">
  <link rel="stylesheet" href="assets/hljs-theme-zenburn.css">
  <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/highlight.min.js"></script>
  <script>hljs.initHighlightingOnLoad();</script>
</head>

<body>

    <?php foreach ($sets as $renderer => $files) : ?>

    <h2><?=$e($renderer)?></h2>

    <?php foreach ($files as $file) : ?>

    <div class="example-wrapper">
    <table class="example">
        <tr>
        <?php
        $input = file_get_contents($file);
        $output = $renderers[$renderer]->render($input);
        ?>
            <td class="input"><?=nl2br($e($input))?></td>
            <td class="output"><?=$output?></td>
        </tr>
    </table>
    </div>

    <?php endforeach; ?>

    <?php endforeach; ?>

</body>
</html>
