<?php

// Purpose: basis of checking pdf data

abstract class VerifyPdf {

    protected $valid_columns;
    protected $row_number;

    function __construct() {
        $this->row_number = 0;
    }

    public function onRow($row_number) {
        $this->row_number = $row_number;
    }

    function checkWikipediaLink($http_link, $column_name) {
        if (('' !== $http_link) and ( NULL !== $http_link)) {
            if (!preg_match('~http(s)?://~i', $http_link)) {
                $exception_message = <<< EOS
                            BAD WIKIPEDIA LINK EXCEPTION: ($column_name, $this->row_number, $http_link)  
                    <br> -In column '$column_name'
                    <br> -In row '$this->row_number' 
                    <br> -Supplied value of '$http_link' does not match 'http://' nor 'https://'    
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
        }
    }

    function checkCharacters($text, $column_name) {
        if (preg_match('~&[^ ]~', $text)) {
            $exception_message = <<< EOS
                            BAD CHARACTER EXCEPTION: ($column_name, $this->row_number, $text) 
                    <br> -In column '$column_name'
                    <br> -In row '$this->row_number' 
                    <br> -Supplied value of '$text' has a special character starting with &, please delete and use the real character via copy. For example do not use '&amp;'    
EOS;
            throw new Exception($exception_message . __METHOD__);
        }
    }

    function checkNumber($text, $column_name) {
        if (NULL !== $text) {
            if (!preg_match('~^[1234567890]+$~', $text)) {
                $exception_message = <<< EOS
                            BAD NUMBER EXCEPTION: ($column_name, $this->row_number, $text)
                    <br> -In column '$column_name'
                    <br> -In row '$this->row_number' 
                    <br> -Supplied value of '$text' does not match a valid number    
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
        }
    }

    function checkColumnCount($pdf_row) {
        $columns_in_row = count($pdf_row);
        if (0 < $columns_in_row) {
            if (!in_array($columns_in_row, $this->valid_columns)) {
                $exception_message = <<< EOS
                            WRONG NUMBER OF COLUMNS EXCEPTION: ($columns_in_row, $this->row_number) 
                    <br> -Found $columns_in_row, which is missing some more information like a Page Count
                    <br> -In row '$this->row_number' 
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
        }
    }

    abstract public function checkColumns($array, $row_number, CheckLinks &$check_links, Authors $authors);
}
