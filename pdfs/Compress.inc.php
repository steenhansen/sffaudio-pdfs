<?php

// Purpose: compress html to smaller size

class Compress {

    private $filename;
    private $missing_filename;
    private $new_google_data;

    function __construct($filename, $missing_filename) {
        $this->filename = $filename;
        $this->missing_filename = $missing_filename;
        $this->new_google_data = FALSE;
    }

    public function dataFilesDifferent() {
        $this->new_google_data = TRUE;          // to signal that the 'save' button must be shown because there is new different data
    }

    public function showSaveButton($pdf_error_message) {
        if ($this->new_google_data and ( '' === $pdf_error_message)) {    // never update with an error
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function stripWhiteSpace($large_pdf_rows_html) {
        $smaller = preg_replace('~\s\s+~', ' ', $large_pdf_rows_html); // Note that there are no ending </tr> nor </td> in the output
        $smaller = str_replace('> <', '><', $smaller);
        $smaller = str_replace(' </', '</', $smaller);
        $smaller = str_replace(' <tr', '<tr', $smaller);
        $smaller = str_replace(' <td', '<td', $smaller);
        $smaller = str_replace(' <br', '<br', $smaller);
        $smaller = str_replace('<td id="">', '<td>', $smaller);
        $smaller = trim($smaller);
        return $smaller;
    }

    public function saveHtmlRows($pdf_rows_html) {
        Helpers::saveAsUtf8($this->filename, $pdf_rows_html);
    }

    public function rowMatches($search = '') {
        $pdf_rows_html = @file_get_contents($this->filename);
        if (!$pdf_rows_html) {
            $pdf_rows_html = file_get_contents($this->missing_filename);       // if something is wrong with the main pdf file, goto the backup
        }
        if ('' == $search) {
            return $pdf_rows_html;
        } else {
            $search = urldecode($search);
            $safe_search = preg_replace("~[^A-Za-z0-9 '\,\.]~", '', $search);
            $rows = explode('<tr>', $pdf_rows_html);
            $matches = array();
            foreach ($rows as $row) {
                $columns = explode('<td', $row);            // so don't have to strpos!==false
                foreach ($columns as $column) {
                    $text_column= strip_tags($column);
                    if (0 < stripos($text_column, $safe_search)) {
                        $matches [] = $row;
                        break;
                    }
                }
            }
            $search_rows = implode('<tr>', $matches);
            return $search_rows;
        }
    }

    public function makeBookHtmlRows($books_data, $authors, $pdf_url_folder) {
        $books = new Books($authors, $books_data);
        $books_html = $books->matchingBooks($pdf_url_folder);
        return $books_html;
    }

}
