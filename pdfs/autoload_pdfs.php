<?php

spl_autoload_register('pdfAutoloader');

function pdfAutoloader($className)
{
    $include_filename = __DIR__ . "/$className.inc.php";
    if (file_exists($include_filename)) {
        include $include_filename;
    }
}

