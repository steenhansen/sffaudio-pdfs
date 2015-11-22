<?php

/*

htdocs/sffaudio/pdfs> phpunit --bootstrap autoload_pdfs.php tests/PdfsTest

*/

class PdfsTest extends PHPUnit_Framework_TestCase
{
    public function testCheckWikipediaLink()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyPdf_checkWikipediaLink.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "BAD WIKIPEDIA LINK EXCEPTION: (D-Author Wikipedia Entry, 2, httX://cnn.com)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCheckCharacters()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyPdf_checkCharacters.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "BAD CHARACTER EXCEPTION: (A-Title, 2, Charon&amp;)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCheckNumber()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyPdf_checkNumber.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "BAD NUMBER EXCEPTION: (?-PDF Page Count 1, 2, asdf)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCheckColumnCount()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyPdf_checkColumnCount.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "WRONG NUMBER OF COLUMNS EXCEPTION: (8, 2)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCheckPdfLink_A()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyBook_checkPdfLink_A.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "BAD % CHARACTER IN PDF URL EXCEPTION: (?-PDF Link 1, 2, CharonBy%20LordDunsany.pdf)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCheckPdfLink_B()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyBook_checkPdfLink_B.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "BAD PDF URL EXCEPTION: (?-PDF Link 1, 2, CharonByLordDunsany)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCheckPdfLink_C()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_verifyBook_checkPdfLink_C.csv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "BAD CHARACTER IN PDF NAME EXCEPTION: (?-PDF Link 1, 2, my_test_#_file.pdf)";
        $this->assertEquals($expected_error, $error);
    }

    public function testCompleteRow()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_uploadCsv_completeRow.tsv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "INCOMPLETE CSV ROW EXCEPTION: (0, pdf_page_count_1)";
        $this->assertEquals($expected_error, $error);
    }

    public function testValidateColumnNames()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_uploadCsv_validateColumnNames.tsv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "WRONG FIELDNAME IN HEADER EXCEPTION: (author_wikipedia_entry, wrong_name)";
        $this->assertEquals($expected_error, $error);
    }

    public function testFieldsValid()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_uploadCsv_fieldsValid.tsv');
        $at_least_1_book = 1;
        $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "WRONG NUMBER OF FIELDS IN HEADER EXCEPTION: (30, 31)";
        $this->assertEquals($expected_error, $error);
    }

    public function testEnoughRows()
    {
        $pdf_table = new PdfTable(__DIR__ . '/test_uploadCsv_enoughRows.tsv');
        $at_least_42_books = 42;
        $pdf_table->readCurrentPdfData($at_least_42_books, true);
        $error = $pdf_table->exceptionError();
        $expected_error = "NOT ENOUGH BOOK ROWS EXCEPTION: (42, 1)";
        $this->assertEquals($expected_error, $error);
    }

    public function testEntirePdfTable()
    {
        $at_least_1_book = 1;
        $pdf_table = new PdfTable(__DIR__ . '/test_pdfTable_start.csv');
        $rows_only_html_bom = $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $rows_only_html = Helpers::stripUtf8Bom($rows_only_html_bom);
        $expected_html_bom = file_get_contents(__DIR__ . '/test_pdfTable_end.html');
        $expected_html = Helpers::stripUtf8Bom($expected_html_bom);
        $this->assertEquals($expected_html, $rows_only_html);
    }

    public function testSpecialChars()
    {
        $at_least_1_book = 1;
        $pdf_table = new PdfTable(__DIR__ . '/test_special_chars_start.csv');
        $rows_only_html_bom = $pdf_table->readCurrentPdfData($at_least_1_book, true);
        $rows_only_html = Helpers::stripUtf8Bom($rows_only_html_bom);
        $expected_html_bom = file_get_contents(__DIR__ . '/test_special_chars_end.html');
        $expected_html = Helpers::stripUtf8Bom($expected_html_bom);
        $this->assertEquals($expected_html, $rows_only_html);
    }

    public function testAuthorsLastNames()
    {
        $author_test_names_to_last = array(
            "Madeline L'Engel " => "L'Engel",
            "Fitz-James O'Brien " => "O'Brien",
            "Sheridan Le Fanu " => "Le Fanu",
            "Lester del Rey " => "del Rey",
            "James De Mille " => "De Mille",
            "Charles De Vet " => "De Vet",
            ' Kurt Vonnegut Jr. ' => 'Vonnegut',
            " Gene Cross aka 'Arthur Jean Cox' " => 'Cox',
            ' Arthur Quiller-Couch ' => 'Quiller-Couch',
            ' Sam    Merwin     Jr. ' => 'Merwin',
            " jim Garis' " => 'Garis',
            'Emily Brontë' => 'Brontë',
            'Gottfried August Bürger ' => 'Bürger',
            'hansen )' => 'hansen',
            'Hansen)' => 'Hansen',
            'H.G.Wells' => 'Wells',
            "Robert W.Chambers" => 'Chambers');
        $authors = new Authors();
        foreach ($author_test_names_to_last as $test_name => $expected_last) {
            $found_last = $authors->lastAuthorName($test_name);
            $this->assertEquals($found_last, $expected_last);
        }
    }

}

