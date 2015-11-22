<?php

// Purpose: to generate and return pdf books 


define("CANADIAN_PDF_EXTENSION", ".can");
define("CANADIAN_PDF_FOLDER_URL", "http://www.coquitlamwebsolutions.ca/sffaudio_can_pdfs/");

class Books {

    public static $NO_ROMAN_NUMBERS_AFTER_PART = 'no_roman_numbers_after_part';
    public static $COLUMNS_OF_PDF_IN_BOOK = 3;
    private static $IGNORE_START_ARTICLES_IN_TITLES = array('the', 'an', 'a');
    private $pdf_authors;
    private $pdf_books;
    private $current_multipart_title = 'no_match_on_first_try';
    private $current_book_pages;                                                // for sorting on number of pages stored in id

    function __construct($pdf_authors, $pdf_books) {
        $this->pdf_authors = $pdf_authors;
        $this->pdf_books = $pdf_books;
    }

    private function bookInSearch($pdf, $search) {
        $search = trim($search);
        if ('' === $search) {
            return TRUE;
        }
        foreach ($pdf as $column) {
            if (Helpers::stringContainsInsensitive($column, $search)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    // Idea here is to have the first "PART" title as a normal title, but then the following titles will have the repeating parts in gray. Uppercase "PART"
    private function multipartTitle($title) {
        if (strpos($title, ' PART ') > 0) {                                     // "Fungi From Yuggoth PART VI: The Lamp"
            $title_split = explode(' PART ', $title, 2);                        // "Fungi From Yuggoth", "VI: The Lamp" 
            $start_multipart_name = $title_split[0];                            // "Fungi From Yuggoth"
            if ($this->current_multipart_title === $start_multipart_name) {
                $after_part = $title_split[1];                                  //  "VI: The Lamp"  
                $title = <<<EOS
                        <span class="pdf_part_title_not_first">$start_multipart_name</span>&nbsp;Part $after_part
EOS;
            } else {
                $this->current_multipart_title = $start_multipart_name;
            }
        }
        return $title;
    }

    private function checkIfToCanada($pdf_url_folder, $pdf_name){
        $lower_pdf_name = strtolower($pdf_name);
        if (Helpers::stringEndsWith($lower_pdf_name, CANADIAN_PDF_EXTENSION)) {
            $canadian_pdf_name = str_ireplace(CANADIAN_PDF_EXTENSION, '.pdf', $pdf_name);
            $canadian_pdf_url = CANADIAN_PDF_FOLDER_URL  . $canadian_pdf_name;
            return $canadian_pdf_url;
        }
        $usa_pdf_url = $pdf_url_folder . $pdf_name;
        return $usa_pdf_url;
    }

    private function pdfsHtml($pdf_url_folder, $pdf_list) {
        $this->current_book_pages = 0;
        $books = array();
        for ($i = 0; $i < count($pdf_list); $i = $i + self::$COLUMNS_OF_PDF_IN_BOOK) {
            $index_pdf_location = $i;
            $index_pdf_pages = $i + 1;
            $index_pdf_info = $i + 2;
            $pdf_name = $pdf_list[$index_pdf_location];
            if (NULL === $pdf_name) {
                break;                                          // we end the loop when we run out of pdfs
            }
            $pdf_url = $this->checkIfToCanada($pdf_url_folder, $pdf_name);
            $number_pages = $pdf_list[$index_pdf_pages];
            if (isset($pdf_list[$index_pdf_info])) {
                $pdf_text = trim($pdf_list[$index_pdf_info]);
            } else {
                $pdf_text = '';
            }
            $books [] = <<<EOS
                    <a href="$pdf_url" target='_blank' class="p"></a>$number_pages $pdf_text 
EOS;
            if ($this->current_book_pages < $number_pages) {
                $this->current_book_pages = $number_pages;
            }
        }
        $pdfs_html = implode('<br>', $books);
        return $pdfs_html;
    }

    public static function pdfsOnlyList($book) {
        unset($book['title']);
        unset($book['author']);
        unset($book['story_link_on_wikipedia']);
        unset($book['author_wikipedia_entry']);
        $pdfs_by_integer_set = array_values($book);                             // so can traverse by 3s
        return $pdfs_by_integer_set;
    }

    public function matchingBooks($pdf_url_folder, $search = '') {
        $filtered_search = $this->filterSearch($search);
        $initial_pdf_books = $this->sortableTitleKeys($this->pdf_books);
        $authors = new Authors();
        $html_books = array();
        foreach ($initial_pdf_books as $sort_title => $book) {
            if ($this->bookInSearch($book, $filtered_search)) {
                $this->title_html = $this->titleHtml($book['title'], $book['story_link_on_wikipedia']);
                $sort_author_lastname = $authors->lastAuthorName($book['author']);
                $this->author_title_sort = "$sort_author_lastname-$sort_title";
                $this->author_html = $this->authorHtml($book['author'], $sort_author_lastname);
                $pdfs_book = self::pdfsOnlyList($book);
                $this->pdfs_html = $this->pdfsHtml($pdf_url_folder, $pdfs_book);
                $html_books [] = $this->tableRow($sort_title);
            }
        }
        $html = implode("\n", $html_books);
        return $html;
    }

    private function tableRow($sort_title) {
        $sort_pages = 1000 + $this->current_book_pages;         // No ending tags, like </td>, http://www.w3.org/TR/html5/syntax.html#syntax-tag-omission
        $book_row = <<<EOS
                <tr> 
                    <td id="$sort_title">$this->title_html 
                    <td id="$this->author_title_sort">$this->author_html 
                    <td id="$sort_pages">$this->pdfs_html 
EOS;
        return $book_row;
    }

    private function authorHtml($author, $sort_author_lastname) {
        $author_text = str_replace($sort_author_lastname, "<b>$sort_author_lastname</b>", $author);
        $author_url = $this->pdf_authors->getAuthorCollapsed($author);
        if ('' !== $author_url) {
            $author_link = <<<EOS
                <a href="$author_url">$author_text</a>
EOS;
            return $author_link;
        } else {
            return $author_text;
        }
    }

    private function romanToDecialPart($title) {
        $title_split = explode(' PART ', $title, 2);                                // Fungi From Yuggoth PART VI: The Lamp 
        if (1 === count($title_split)) {
            return $title;                                                          // "Fungi From Yuggoth", "VI: The Lamp" 
        }
        $roman_values = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $possible_roman_numerals = explode(' ', $title_split[1], 2);                // "VI:", "The Lamp" 
        $roman_number = $possible_roman_numerals[0];                                // VI:
        $first_roman_char = $roman_number{0};                                       // V
        if (isset($roman_values[$first_roman_char])) {
            $roman = $roman_number;
            $decimal_result = 0;
            foreach ($roman_values as $key => $value) {
                while (strpos($roman, $key) === 0) {
                    $decimal_result += $value;
                    $roman = substr($roman, strlen($key));
                }
            }
            $sortable_decimal = 100 + $decimal_result;
            $new_sortable_title = str_replace($roman_number, $sortable_decimal, $title);
            return $new_sortable_title;                                             // Fungi From Yuggoth Part 1006: The Lamp 
        }
        return self::$NO_ROMAN_NUMBERS_AFTER_PART;             //'no_roman_numbers_after_part';
    }

    private function titleHtml($title, $story_link) {
        $possible_multipart_title = $this->multipartTitle($title);
        if ('http' === substr($story_link, 0, 4)) {
            $linked_title = <<<EOS
                <a href="$story_link">$possible_multipart_title</a>
EOS;
            return $linked_title;
        }
        return $possible_multipart_title;
    }

    private function sortableTitleKeys($pdf_books) {
        $sortable_titles = array();
        foreach ($pdf_books as $key=>$pdf_row) {
            $title = $pdf_row['title'];
            $sort_title = $this->killStartTitleArticles($title);
            $sort_title_decimal = $this->romanToDecialPart($sort_title);
            if (self::$NO_ROMAN_NUMBERS_AFTER_PART === $sort_title_decimal) {
                $sortable_titles["$sort_title-$key"] = $pdf_row;
            } else {
                $sortable_titles["$sort_title_decimal-$key"] = $pdf_row;        // in case of duplicate name like 'Derelict'
            }
        }
        return $sortable_titles;
    }

    private function killStartTitleArticles($long_title) {
        $long_title = preg_replace("~[^A-Za-z0-9 ]~", '', $long_title);
        $title_words = explode(' ', $long_title);
        $first_word = array_shift($title_words);
        $first_lower_case_word = strtolower($first_word);
        if (in_array($first_lower_case_word, self::$IGNORE_START_ARTICLES_IN_TITLES)) {
            $shorter_title = implode(' ', $title_words);
            return $shorter_title;
        }
        return $long_title;
    }

    private function filterSearch($search) {
        $search = trim($search);
        $filtered_search = filter_var($search, FILTER_SANITIZE_URL);
        return $filtered_search;
    }

}
