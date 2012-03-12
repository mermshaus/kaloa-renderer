<?php

namespace org\example\Contact;

/**
 * Removes Magic Quotes from GET, POST and COOKIE variables
 *
 * @see http://www.phpforum.de/forum/showthread.php?t=217421
 */
function sanitizeMagicQuotes()
{
    // Magic Quotes will be removed in PHP6
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        if (!function_exists('array_stripslashes')) {            
            // Instead of this, array_walk_recursive could be used in PHP5+
            function array_stripslashes(&$var) {
                if (is_string($var)) {
                    $var = stripslashes($var);
                } else if (is_array($var)) {
                    foreach($var as $key => $value) {
                        array_stripslashes($var[$key]);
                    }
                }
            }
        }

        array_stripslashes($_GET);
        array_stripslashes($_POST);
        array_stripslashes($_COOKIE);
    }
}

/**
 * Escapes strings for output in HTML context
 *
 * @param string $data
 * @return string
 */
function escape($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}