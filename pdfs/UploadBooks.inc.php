<?php

// Purpose: basis of reading a book csv file

define("BOOK_COLUMNS_1_NO_INFO_COL", 6);
define("BOOK_COLUMNS_1_INFO_COL", 7);
define("BOOK_COLUMNS_2_NO_INFO_COL", 9);
define("BOOK_COLUMNS_2_INFO_COL", 10);
define("BOOK_COLUMNS_3_NO_INFO_COL", 12);
define("BOOK_COLUMNS_3_INFO_COL", 13);
define("BOOK_COLUMNS_4_NO_INFO_COL", 15);
define("BOOK_COLUMNS_4_INFO_COL", 16);
define("BOOK_COLUMNS_5_NO_INFO_COL", 18);
define("BOOK_COLUMNS_5_INFO_COL", 19);
define("BOOK_COLUMNS_6_NO_INFO_COL", 21);
define("BOOK_COLUMNS_6_INFO_COL", 22);
define("BOOK_COLUMNS_7_NO_INFO_COL", 24);
define("BOOK_COLUMNS_7_INFO_COL", 25);
define("BOOK_COLUMNS_8_NO_INFO_COL", 27);
define("BOOK_COLUMNS_8_INFO_COL", 28);
define("BOOK_COLUMNS_9_NO_INFO_COL", 30);
define("BOOK_COLUMNS_9_INFO_COL", 31);

class UploadBooks extends UploadCsv {

    public static $BOOK_COLUMNS_CSV_ARRAY = array(
        BOOK_COLUMNS_1_NO_INFO_COL, BOOK_COLUMNS_1_INFO_COL,
        BOOK_COLUMNS_2_NO_INFO_COL, BOOK_COLUMNS_2_INFO_COL,
        BOOK_COLUMNS_3_NO_INFO_COL, BOOK_COLUMNS_3_INFO_COL,
        BOOK_COLUMNS_4_NO_INFO_COL, BOOK_COLUMNS_4_INFO_COL,
        BOOK_COLUMNS_5_NO_INFO_COL, BOOK_COLUMNS_5_INFO_COL,
        BOOK_COLUMNS_6_NO_INFO_COL, BOOK_COLUMNS_6_INFO_COL,
        BOOK_COLUMNS_7_NO_INFO_COL, BOOK_COLUMNS_7_INFO_COL,
        BOOK_COLUMNS_8_NO_INFO_COL, BOOK_COLUMNS_8_INFO_COL,
        BOOK_COLUMNS_9_NO_INFO_COL, BOOK_COLUMNS_9_INFO_COL,
    );
    protected $FIELD_NAMES = array('title', 'author', 'story_link_on_wikipedia', 'author_wikipedia_entry',
        'pdf_link_1', 'pdf_page_count_1', 'pdf_info_1',
        'pdf_link_2', 'pdf_page_count_2', 'pdf_info_2',
        'pdf_link_3', 'pdf_page_count_3', 'pdf_info_3',
        'pdf_link_4', 'pdf_page_count_4', 'pdf_info_4',
        'pdf_link_5', 'pdf_page_count_5', 'pdf_info_5',
        'pdf_link_6', 'pdf_page_count_6', 'pdf_info_6',
        'pdf_link_7', 'pdf_page_count_7', 'pdf_info_7',
        'pdf_link_8', 'pdf_page_count_8', 'pdf_info_8',
        'pdf_link_9', 'pdf_page_count_9', 'pdf_info_9',
    );
    protected $NON_BLANK_FIELDS = array('title', 'author', 'pdf_link_1', 'pdf_page_count_1');         // Pdf must have at least these fields non-blank to be ok

    function __construct($filename, $delimiter = ",") {
        parent::__construct($filename, $delimiter);
    }

    public function readBooks(verifyBook $verify_book, CheckLinks &$check_links, Authors $authors) {
        $data_types = $this->firstLineVariables();
        $row = 1;
        if (($handle = fopen($this->filename, "r")) !== FALSE) {
            fgetcsv($handle, 0, $this->delimiter);
            $csv_books = array();
            while (($data = fgetcsv($handle, 0, $this->delimiter)) !== FALSE) {
                $csv_books[] = array_combine($data_types, $data);
            }
            fclose($handle);
        }
        $good_books = array();
        foreach ($csv_books as $row_number => $book) {
            $trimmed_book = $this->trimRow($book);
            $thin_book = $this->deleteEmptyCellsFromRight($trimmed_book);
            $this->completeRow($thin_book, $row_number);
            $verify_book->checkColumns($thin_book, $row_number, $check_links, $authors);
            $good_books [] = $thin_book;
        }
        return $good_books;
    }

}
