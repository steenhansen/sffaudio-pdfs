<?php

// Purpose: replace WordPress short code [make_html_pdf_table] with pdf book data from Google spreadsheet


include "autoload_pdfs.php";

/*
  To make [show-pdf-table-shortcode] appear in http://www.sffaudio.com/features/pdf-page/
  You must add include_once('/pdfs/shortcode_html_pdf_table.php');
  To the top of /wp-content/themes/revolution-code-blue2/functions.php
 */

// Public PDF books spreadsheet https://docs.google.com/spreadsheets/d/12L1xoLC38ypaxB77jKUgLSX3MBmaxmIRaHhV2XVeh0o/edit#gid=0

function make_html_pdf_table($test_link_to_css_js=false) {
    define("GOOGLE_DOCS_BOOK_SPREADSHEET", "https://docs.google.com/spreadsheets/d/12L1xoLC38ypaxB77jKUgLSX3MBmaxmIRaHhV2XVeh0o/export?format=csv&id");
    $pdf_table = new PdfTable(GOOGLE_DOCS_BOOK_SPREADSHEET);
    $at_least_100_books = 100;
    $is_user_logged_in = is_user_logged_in();   // WordPress function
    $pdf_table->readCurrentPdfData($at_least_100_books, $is_user_logged_in);    // only check for new books if signed into WordPress, and there must be at least 100 books in the spreadsheet to update
    $html_with_javascript = $pdf_table->addJavascript($test_link_to_css_js);
    return $html_with_javascript;
}

function register_sffaudio_shortcodes() {
    add_shortcode('show-pdf-table-shortcode', 'make_html_pdf_table');      // this will make the function make_html_pdf_table() be executed when WordPress encounters '[show-pdf-table-shortcode]'
}

function modify_jquery() {      // sortable html table needs at least jquery 1.2.6+
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', '/js/jquery.min.1.11.1.js', FALSE, '1.11.1');     // Not using Google cdn
        wp_enqueue_script('jquery');
    }
}

if (function_exists('add_action')) {
    add_action('init', 'register_sffaudio_shortcodes');
    add_action('init', 'modify_jquery');
} 
