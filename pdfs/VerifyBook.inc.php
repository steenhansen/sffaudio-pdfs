<?php

// Purpose: basis of checking pdf book data

class VerifyBook extends VerifyPdf {

    function __construct($valid_columns) {
        $this->valid_columns = $valid_columns;
        parent::__construct();
    }

    private function checkPdfLink($http_link, $column_name) {
        if ('' !== $http_link) {
            if (preg_match('~%~', $http_link)) {
                $exception_message = <<< EOS
                        BAD % CHARACTER IN PDF URL EXCEPTION: ($column_name, $this->row_number, $http_link) 
                    <br> -In column '$column_name'
                    <br> -In row '$this->row_number' 
                    <br> -Supplied value of '$http_link' has a '%20' for blank space    
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
            if ( (!preg_match('~.pdf$~i', $http_link)) and (!preg_match('~.can$~i', $http_link)) ) {
                $exception_message = <<< EOS
                             BAD PDF URL EXCEPTION: ($column_name, $this->row_number, $http_link)
                    <br> -In column '$column_name'               
                    <br> -In row '$this->row_number' 
                    <br> -Supplied value of '$http_link' EXCEPTION - does not end in '.pdf'
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
            if (!preg_match("~^[a-zA-Z0-9\.\-_! ',\(\)]+$~", $http_link)) {                              // May want to change to no [! ',\(\)] in the future?
                $exception_message = <<< EOS
                             BAD CHARACTER IN PDF NAME EXCEPTION: ($column_name, $this->row_number, $http_link)
                    <br> -In column '$column_name'
                    <br> -In row '$this->row_number' 
                    <br> -Supplied value of '$http_link' can only contain 'a-z' and 'A-Z' and '0-9' and '.-_! ',()' characters'    
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
        }
    }

    public function checkColumns($book, $row_number, CheckLinks &$check_links, Authors $authors) {
        $this->onRow($row_number + 2);
        $this->checkColumnCount($book);
        $book_title = $book['title'];
        $this->checkCharacters($book_title, 'A-Title');
        $book_author = $book['author'];
        $this->checkCharacters($book_author, 'B-Author');
        $story_link = $book['story_link_on_wikipedia'];
        $this->checkWikipediaLink($story_link, 'C-Story Link on Wikipedia');
        $check_links->addUrl($story_link);
        $author_link = $book['author_wikipedia_entry'];
        $this->checkWikipediaLink($author_link, 'D-Author Wikipedia Entry');
        $check_links->addUrl($author_link);
        $authors->addAuthorCollapsed($book_author, $author_link);
        $this->checkPdfColumns($book, $check_links);
    }

    private function checkPdfColumns($book, CheckLinks &$check_links) {
        $pdfs_only_book = Books::pdfsOnlyList($book);
        $book_count = 1;
        for ($i = 0; $i < count($pdfs_only_book); $i = $i + Books::$COLUMNS_OF_PDF_IN_BOOK) {
            $index_pdf_location = $i;
            $index_pdf_pages = $i + 1;
            $index_pdf_info = $i + 2;
            $pdf_location = $pdfs_only_book[$index_pdf_location];
            if (NULL === $pdf_location) {
                break;
            }
            $this->checkPdfLink($pdf_location, "?-PDF Link $book_count");
            $check_links->addUrl($pdf_location);
            $pdf_pages = $pdfs_only_book[$index_pdf_pages];
            $this->checkNumber($pdf_pages, "?-PDF Page Count $book_count");
            if (isset($pdfs_only_book[$index_pdf_info])) {
                $pdf_info = $pdfs_only_book[$index_pdf_info];
                $this->checkCharacters($pdf_info, "?-PDF INFO $book_count");
            }
            ++$book_count;
        }
    }

}
