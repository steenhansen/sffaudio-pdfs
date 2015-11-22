<?php

/*

To view the working pdfs table without WordPress and a shortcode, make htdocs/public_html/test_pdfs.php with below code completely self-contained

<?php
    include dirname($_SERVER['DOCUMENT_ROOT']) . '/pdfs/tests/show_pdf_table_no_wordpress.php';

*/

include dirname(__DIR__) . "/shortcode_html_pdf_table.php";

function is_user_logged_in(){       // WordPress function
    return false;                   // if true then load pdf books from google, if false then show the pdfs
}

$jquery_1_11_1_js = '<script>' . file_get_contents(dirname(__DIR__)  . '/js/jquery.min.1.11.1.js') . '</script>';
$table_sorter_js = '<script>' . file_get_contents(dirname(__DIR__)  . '/js/jquery.tablesorter.js') . '</script>';
$theme_blue_css = '<style>' . file_get_contents(dirname(__DIR__)  . '/css/theme.blue.css') . '</style>';

$pdf_html = make_html_pdf_table(true);

$entire_page = <<<EOS

    $jquery_1_11_1_js
    $table_sorter_js
    $theme_blue_css

    $pdf_html
EOS;

echo $entire_page;