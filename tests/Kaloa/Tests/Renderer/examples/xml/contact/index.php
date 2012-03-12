<?php

use org\example\Contact as H;
use org\example\Contact\ContactModel;

error_reporting(-1);
session_start();

require_once './library/org/example/Contact/inc.helpers.php';

header('Content-Type: text/html; charset=utf-8');

// Magic quotes wizardry
H\sanitizeMagicQuotes();

// Autoloading for namespaces
spl_autoload_register(function ($class) {
    require_once './library/' . str_replace('\\', '/', $class) . '.php';
});

// Determine active action
$action = (isset($_POST['action']))
        ? trim((string) $_POST['action']) : 'index';

// Default to "index" action
if (!in_array($action, array('index', 'submit'))) {
    $action = 'index';
}

// This is the "controller"
switch ($action) {
    case 'index':
    default:
        // Show contact form
        $model = new ContactModel();

        // Make sure, session variable is set
        $_SESSION['contact']['lastEmailSent'] = 0;

        $tpl = array();
        $tpl['action'] = $action;
        $tpl['errors'] = array();
        $tpl['titles'] = $model->getTitles();
        $tpl['formIsSent'] = false;

        include './scripts/contact.phtml';
        break;

    case 'submit':
        // Try to send mail
        $model = new ContactModel();

        $errors = $model->sendEmailFromPOST();

        $tpl = array();
        $tpl['action'] = $action;
        $tpl['errors'] = $errors;
        $tpl['titles'] = $model->getTitles();
        $tpl['formIsSent'] = true;

        include './scripts/contact.phtml';
        break;
}