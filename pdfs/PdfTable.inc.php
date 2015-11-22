<?php

// Purpose: to decide what pdf html to show with regard to search, and possibly update cached html from Google spreadsheet

class PdfTable {

    private $edited_csv_pdf_book_spreadsheet;
    private $exception_message_reference;
    private $minimum_books_found;
    private $is_user_logged_in;
    public static $COMPRESSED_HTML_ROW_DATA = "cached_book_rows.html";
    public static $MISSING_HTML_ROW_DATA = "missing_back_up_display.html";
    public static $TEMPORARY_GOOGLE_ROW_DATA = "my_temp_books.csv";
    public static $PDF_FOLDER_URL = "http://www.sffaudio.com/podcasts/";
    private $local_new_temp_books_filename;
    private $compress;
    private $search_for_text;
    private $pdf_rows_html;

// in normal running called with - https://docs.google.com/spreadsheets/d/12L1xoLC38ypaxB77jKUgLSX3MBmaxmIRaHhV2XVeh0o/export?format=csv&id	
    function __construct($edited_csv_pdf_book_spreadsheet) {
        $this->edited_csv_pdf_book_spreadsheet = $edited_csv_pdf_book_spreadsheet;
        $this->exception_message_reference = '';
        $data_directory = dirname(__FILE__) . '/data/';
        $this->local_new_temp_books_filename = $data_directory . self::$TEMPORARY_GOOGLE_ROW_DATA;
        $current_cached_html_file = $data_directory . self::$COMPRESSED_HTML_ROW_DATA;
        $missing_cached_html_file = $data_directory . self::$MISSING_HTML_ROW_DATA;
        $this->compress = new Compress($current_cached_html_file, $missing_cached_html_file);
    }

    public function readCurrentPdfData($minimum_books_found, $is_user_logged_in) {
        $this->minimum_books_found = $minimum_books_found;
        $this->is_user_logged_in = $is_user_logged_in;
        $this->pdf_rows_html = '';
        if ($is_user_logged_in) {
            try {
                $this->pdf_rows_html = $this->readNewPdfData($this->edited_csv_pdf_book_spreadsheet);
            } catch (Exception $e) {
                $this->exception_message_reference = $e->getMessage();
            }
        }
        if ('' === $this->pdf_rows_html) {
            $this->search_for_text = Helpers::safeSearchText(@$_GET['search']);
            $this->pdf_rows_html = $this->compress->rowMatches($this->search_for_text);
        } else {
            $this->search_for_text = '';
        }
        return $this->pdf_rows_html;    // for the acceptance tests we return html pdf table
    }

    private function readNewPdfData($google_books_filename) {
        $this->saveGoogleDocData($google_books_filename);
        $upload_books = new UploadBooks($this->local_new_temp_books_filename);
        $upload_books->fieldsValid($this->minimum_books_found);
        $verify_book = New VerifyBook(UploadBooks::$BOOK_COLUMNS_CSV_ARRAY);
        $check_links = New CheckLinks(self::$PDF_FOLDER_URL);
        $authors = New Authors();
        $new_books_set = $upload_books->readBooks($verify_book, $check_links, $authors);
        $new_html = $this->compress->makeBookHtmlRows($new_books_set, $authors, self::$PDF_FOLDER_URL);
        $new_compressed_html = $this->compress->stripWhiteSpace($new_html);
        $current_pdf_rows_html_with_leading_bom = $this->compress->rowMatches();
        $current_pdf_rows_html = Helpers::stripUtf8Bom($current_pdf_rows_html_with_leading_bom);
        $new_pdf_rows_html = $this->checkButtonsPressed($check_links, $current_pdf_rows_html, $new_compressed_html);
        return $new_pdf_rows_html;          // if return '' then re-read cached data file
    }

    public function exceptionError() {
        $exception_lines = explode("<br>", $this->exception_message_reference);     // first line of exception is used to verify tests are ok
        $exception_header = trim($exception_lines[0]);
        return $exception_header;
    }

    public function addJavascript($test_link_to_css_js) {
        $show_save_button = $this->compress->showSaveButton($this->exception_message_reference);
        if ($test_link_to_css_js){
            $html = '';
        }else{
            $html = HtmlSortTable::tableSorterJavascript();
        }
        $html .= HtmlSortTable::javascriptAndHTML($this->exception_message_reference, $show_save_button, $this->search_for_text, $this->pdf_rows_html);
        return $html;
    }

    private function saveGoogleDocData($google_books_filename) {
        $google_docs_csv = file_get_contents($google_books_filename);
        Helpers::saveAsUtf8($this->local_new_temp_books_filename, $google_docs_csv);
    }

    private function checkButtonsPressed($check_links, $current_pdf_rows_html, $new_compressed_html) {
        if (HtmlSortTable::testAllUrls()) {
            $testing_check_5_links_only = 0;                                                 // Set to 0 for check ALL url and pdf links
            $check_output = $check_links->checkPdfsWikipedia($testing_check_5_links_only);   // User pressed 'Check All Links' to go to http://www.sffaudio.com/features/pdf-page/?check_urls=all
            echo $check_output;
            exit;
        } else if ($current_pdf_rows_html === $new_compressed_html) {  // Nothing to do as data has not changed, NO 'save' button
            return '';
        } else if (HtmlSortTable::saveNewBookYes()) {
            $this->compress->saveHtmlRows($new_compressed_html);
            $js_refresh_screen = HtmlSortTable::jsReloadPdfPage(); //  User pressed 'Save New Data' to go to http://www.sffaudio.com/features/pdf-page/?save_new_books=yes, which is a refresh
            echo $js_refresh_screen;
            exit;
        } else {
            $this->compress->dataFilesDifferent();           // Tell Compress we have new different data to show the 'save' button, later
        }
        return $new_compressed_html;
    }

}
