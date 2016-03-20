<?php

use Kaloa\Renderer\Factory;

require_once __DIR__ . '/../bootstrap.php';

echo '<table border=1>';


echo '<tr>';
echo '<td>inigo</td>';
echo '<td>';
echo Factory::createRenderer('inigo')->render(
    '[h1]Test[/h1] [url=http://www.example.org]Link[/url] [code]1+1=2[/code] foo[fn]
        [fnt]Test[/fnt] more content'
);
echo '</td>';
echo '</tr>';


echo '<tr>';
echo '<td>markdown</td>';
echo '<td>';
echo Factory::createRenderer('markdown')->render(
    file_get_contents(__DIR__ . '/php-markdown-readme.text')
);
echo '</td>';
echo '</tr>';


echo '<tr>';
echo '<td>xml</td>';
echo '<td>';
echo Factory::createRenderer('xml')->render(
    '<h2>Test</h2><k:toc/><h3>Foo</h3><listing>1+1=2</listing>'
);
echo '</td>';
echo '</tr>';


echo '</table>';
