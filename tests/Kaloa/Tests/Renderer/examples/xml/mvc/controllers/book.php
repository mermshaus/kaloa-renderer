<?php

function book_getTopicList()
{
    $topicsRootPath = './data/topics';

    $topics = array();

    $topics['Informatik'] = array(
        'Sortierverfahren' => $topicsRootPath
                . '/informatik/sortierverfahren.phtml',
        'Kontrollstrukturen' => $topicsRootPath
                . '/informatik/kontrollstrukturen.phtml',
    );

    return $topics;
}

function book_indexAction(&$appData)
{
    $vars['topics'] = book_getTopicList();

    return $vars;
}

function book_viewAction(&$appData)
{
    $topics = book_getTopicList();

    $vars['topics'] = $topics;
    
    $vars['page'] = (isset($_GET['page']))
                  ? $_GET['page']
                  : null;

    $appData['pageTitle'] = $_GET['section'];

    if ($vars['page'] !== null) {
        $vars['content'] = file_get_contents(
                $topics[$_GET['section']][$_GET['page']]);
        $appData['pageTitle'] = $vars['page'];
    }

    $vars['title'] = ($vars['page'] !== null)
                   ? $vars['page']
                   : $_GET['section'];

    return $vars;
}
