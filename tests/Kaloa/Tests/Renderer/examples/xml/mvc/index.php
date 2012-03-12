<?php

/*function url($controller, $action, array $params = array())
{
    $tmp = array();

    if ($controller !== 'index' || $action !== 'index') {
        $tmp['controller'] = $controller;
        $tmp['action']     = $action;
    }

    $params = array_merge($tmp, $params);

    $queryPart = '';

    if (count($params) > 0) {
        $queryPart = '?' . http_build_query($params, '', '&amp;');
    }

    $url = './' . $queryPart;

    return $url;
}*/

function url($controller, $action, array $params = array())
{
    $basePath = rtrim(pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME), '/');

    if ($controller === 'index' && $action === 'index'
            && count($params) === 0
    ){
        // Auf Hinzufügen des Controllers und der Action verzichten, wenn es
        // sich um index.index handelt und keine weiteren Parameter angegeben
        // wurden
        return $basePath . '/';
    }

    $url = $basePath . '/' . $controller . '/' . $action;

    foreach ($params as $key => $value) {
        $url .= '/' . urlencode($key) . '/' . urlencode($value);
    }

    return $url;
}

function escape($string, $quoteStyle = ENT_QUOTES, $charset = 'UTF-8')
{   
    return htmlspecialchars($string, $quoteStyle, $charset);
}

function loadViewScript($controller, $action, array $vars = array())
{
    $scriptPath = './views/' . $controller . '/' . $action . '.phtml';

    extract($vars);
    ob_start();
    include $scriptPath;
    return ob_get_clean();
}

function requestUriToGetArray()
{
    $basePath     = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);
    $requestPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $relativePath = substr($requestPath, strlen($basePath));

    $pathParts = explode('/', trim($relativePath, '/'));

    $count = count($pathParts);

    if ($count > 1) {
        $_GET['controller'] = $pathParts[0];
        $_GET['action']     = $pathParts[1];

        for ($i = 2; $i < $count; $i += 2) {
            $key   = $pathParts[$i];
            $value = (isset($pathParts[$i + 1]))
                   ? $pathParts[$i + 1]
                   : '';
            $_GET[$key] = $value;
        }
    }
}



requestUriToGetArray();



$controller = (isset($_GET['controller']))
            ? trim((string) $_GET['controller'])
            : 'index';

$action = (isset($_GET['action']))
        ? trim((string) $_GET['action'])
        : 'index';

$controllerPath = realpath('./controllers/' . $controller . '.php');

if (strpos($controllerPath, realpath('./controllers')) !== 0) {
    throw new Exception('Invalid controller specified "' . $controller . '"');
}

require_once $controllerPath;

$actionFunctionName = $controller . '_' . $action . 'Action';

if (!function_exists($actionFunctionName)) {
    throw new Exception('Invalid controller action specified "'
            . $controller . '.' . $action . '"');
}

$appData['pageTitle'] = 'Meine Seite';

$tplVars = $actionFunctionName($appData);

if ($tplVars === null) {
    $tplVars = array();
}

?><!DOCTYPE html>

<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo escape($appData['pageTitle']); ?></title>
    </head>

    <body>
        <ul>
            <li><a href="<?php echo url('index', 'index'); ?>">Home</a></li>
            <li><a href="<?php echo url('index', 'about'); ?>">About</a></li>
            <li><a href="<?php echo url('book', 'index'); ?>">Themen</a></li>
        </ul>

        <?php echo loadViewScript($controller, $action, $tplVars); ?>
    </body>

</html>