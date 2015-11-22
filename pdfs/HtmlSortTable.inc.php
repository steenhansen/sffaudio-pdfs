<?php

// Purpose: HTML templates and Javascript handling

class HtmlSortTable {

    public static $SAVE_NEW_DATA_GET_KEY = 'save_new_books';
    public static $SAVE_NEW_DATA_GET_VALUE = 'yes';
    public static $CHECK_URLS_GET_KEY = 'check_urls';
    public static $CHECK_URLS_GET_VALUE = 'all';

    function __construct(Books $pdf_books) {
        $this->pdf_books = $pdf_books;
    }

    public static function saveNewBookYes() { //     &save_new_books=yes;
        if (isset($_GET[self::$SAVE_NEW_DATA_GET_KEY]) and ( self::$SAVE_NEW_DATA_GET_VALUE === $_GET[self::$SAVE_NEW_DATA_GET_KEY])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private static function getSaveButton() {
        $save_new_data_get_key = self::$SAVE_NEW_DATA_GET_KEY;
        $save_new_data_get_value = self::$SAVE_NEW_DATA_GET_VALUE;
        $save_url = "http://" . $_SERVER['SERVER_NAME'] . self::getPageId() . "?$save_new_data_get_key=$save_new_data_get_value";
        $save_button = <<<EOS
	<button id='save_google_docs_data' value='button'>Save New Data below</button>
	    <script>
		$("#save_google_docs_data").click(function(event){
		    window.location="$save_url";
	        });
	    </script>      
EOS;
        return $save_button;
    }

    public static function testAllUrls() { //     &check_urls=all;
        if (isset($_GET[self::$CHECK_URLS_GET_KEY]) and ( self::$CHECK_URLS_GET_VALUE === $_GET[self::$CHECK_URLS_GET_KEY])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private static function getCheckUrlsButton() {
        $check_urls_get_key = self::$CHECK_URLS_GET_KEY;
        $check_urls_get_value = self::$CHECK_URLS_GET_VALUE;
        $check_url = "http://" . $_SERVER['SERVER_NAME'] . self::getPageId() . "?$check_urls_get_key=$check_urls_get_value";
        $check_button = <<<EOS
	    <button id='check_all_links' value='button'>Check All Links</button>
	    <script>
	        $("#check_all_links").click(function(event){
		    window.location='$check_url';
	        });
</script>    
EOS;
        return $check_button;
    }

    public static function tableSorterJavascript() {
        $head_javascript = <<<'EOS'
        	<link rel="stylesheet" href="/css/theme.blue.css">
                <script src="/js/jquery.tablesorter.js"></script>
EOS;
        return $head_javascript;
    }

    private static function documentReadyJavascript() {
        $document_ready_javascript = <<<EOS
            <script>
                document.addEventListener("DOMContentLoaded", function(event) {
                    $("#tablesorter").tablesorter({
                        theme : 'blue',
                        headers: { 0: { sorter:'text' } , 
                                   1: { sorter:'text' } ,
                                   2: { sorter:'text' }      },
                        textExtraction: {
                            0: function(node, table, cellIndex){ return $(node).attr('id'); },
                            1: function(node, table, cellIndex){ return $(node).attr('id'); },
                            2: function(node, table, cellIndex){ return $(node).attr('id'); }       }
                    });
            });
            </script>
EOS;
        return $document_ready_javascript;
    }

    private static function getPageId() {
        $get_parts = explode('?', $_SERVER['REQUEST_URI']);
        $page_name = $get_parts[0];
        return $page_name;
    }

    private static function getSearch($safe_search) {
        $current_pdf_page = "http://" . $_SERVER['SERVER_NAME'] . self::getPageId();
        $search_box = <<<EOS
       <input type='text' id='search_pdf' name='search' value="$safe_search" placeholder="search pdfs for..." >
       <button onclick="
             var search_text = document.getElementById('search_pdf').value;
            var trim_search =  $.trim(search_text);
            if (''===trim_search){
                var action = '$current_pdf_page';
            }else{
                var action = '$current_pdf_page' + '?search=' + trim_search;
            }
            window.location=action;
       ">Search</button>
<script>
    $("#search_pdf").keydown(function(event){
        if (13==event.which){
            var search_text = document.getElementById('search_pdf').value;
            var trim_search =  $.trim(search_text);
            if (''===trim_search){
                var action = '$current_pdf_page';
            }else{
                var action = '$current_pdf_page' + '?search=' + trim_search;
            }
            window.location=action;
        }
    });
</script>      
EOS;
        return $search_box;
    }

    private static function sortingTableHtml($pdf_rows_html, $search_for_text) {          // From  tablesorter.com/docs/
        if (''===trim($search_for_text)) {
            $search_number_sentence ='';
        } else {
            if ('' === trim($pdf_rows_html)) {
                $num_matches = "no";
            } else {
                $html_row_set = explode('<tr', $pdf_rows_html);
                $num_matches = count($html_row_set);
            }
            if (1 === $num_matches) {
                $search_number_sentence = "<br> There is 1 matching poem, story, novel, comic, play, or essay. <br>";
            } else {
                $search_number_sentence = "<br> There are $num_matches matching poems, stories, novels, comics, plays, or essays. <br>";
            }
        }
        $sorting_pdf_table = <<<EOS
             $search_number_sentence
            <table id="tablesorter" class="tablesorter-blue">
                <thead>
                    <tr> 
                        <th class='header' width='50%'>Title </th>
                        <th class='header' width='20%'>Author</th> 
                        <th class='header' width='30%'>PDF Link/Page Count</th> 
                    </tr> 
                </thead> 
                <tbody>
                    $pdf_rows_html
                </tbody> 
            </table> 
EOS;
        return $sorting_pdf_table;
    }

    private static function errorMessage($error_mess) {
        $error_html = <<<EOS
            <br>
                <div style="color:red"><b>$error_mess</b></div>    
            <br>
EOS;
        return $error_html;
    }

    public static function javascriptAndHTML($pdf_error_message, $show_save_button, $search_for_text, $pdf_rows_html) {
        if ($show_save_button) {
            $html = self::getSaveButton();
        } else {
            $html = self::getSearch($search_for_text);
        }
        $html .= self::errorMessage($pdf_error_message);
        $html .= self::sortingTableHtml($pdf_rows_html, $search_for_text);
        $html .= self::documentReadyJavascript();
        if ($show_save_button) {
            $html .= self::getCheckUrlsButton();
        }
        return $html;
    }

    public static function jsReloadPdfPage() {
        $server_name = $_SERVER['SERVER_NAME'];
        $get_vars = explode('&', $_SERVER['REQUEST_URI']);
        $request_uri = $get_vars[0];
        $save_reload = <<<EOS
            <script>
                window.location='http://$server_name$request_uri';
            </script>      
EOS;
        return $save_reload;
    }

}
