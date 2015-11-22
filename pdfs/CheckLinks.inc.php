<?php

// Purpose: to check that all pdfs, and wikipedia links work

class CheckLinks {

    private static $CHARACTERS_TO_READ = 10;
    private static $TIME_OUT = 22;
    private static $RETRY_READS = 4;
    private $urls_to_check;
    private $pdf_folder_url;
    private $pdf_fail_count;
    private $pdf_pass_count;
    private $wikipedia_fail_count;
    private $wikipedia_pass_count;

    function __construct($pdf_folder_url) {
        $this->urls_to_check = array();
        $this->pdf_folder_url = $pdf_folder_url;
        $this->pdf_fail_count = 0;
        $this->pdf_pass_count = 0;
        $this->wikipedia_fail_count = 0;
        $this->wikipedia_pass_count = 0;
    }

    public function addUrl($url) {
        $trimmed_url = trim($url);
        if ('' !== $trimmed_url) {
            $this->urls_to_check [] = $trimmed_url;
        }
    }

    private function openTimeOutStream($url) {
        $url_quote_encoded = str_replace("'", '%27', $url);
        $url_comma_encoded = str_replace(',', '%2C', $url_quote_encoded);
        $url_blanks_encoded = str_replace(' ', '%20', $url_comma_encoded);
        $stream_options = array('http' => array('method' => 'GET',
                'timeout' => self::$TIME_OUT,
                'header' => "Accept-language: en-us,en\r\n" .
                "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0\n\r"));
        $context = stream_context_create($stream_options);
        for ($try = self::$RETRY_READS; 0 < $try; --$try) {
            $do_not_use_include_path = FALSE;
            $contents = file_get_contents($url_blanks_encoded, $do_not_use_include_path, $context, 0, self::$CHARACTERS_TO_READ);
            if (self::$CHARACTERS_TO_READ === strlen($contents)) {
                break;
            }
        }
        return $contents;
    }

    private function openWikipediaUrl($url) {
        $contents = $this->openTimeOutStream($url);
        if (0 === strlen($contents)) {
            $error_message = "Wikipedia fail $url is empty";
            ++$this->wikipedia_fail_count;
        } else if (Helpers::stringContainsInsensitive($contents, 'DOCTYPE')) {
            $error_message = "Wikipedia pass $url";
            ++$this->wikipedia_pass_count;
        } else {
            $error_message = "Wikipedia fail $url does not look like a webpage";
            ++$this->wikipedia_fail_count;
        }
        return Helpers::show_test_error_results($error_message, 'no_hr');
    }

    private function openPdf($url) {
        $contents = $this->openTimeOutStream($url);
        if (0 === strlen($contents)) {
            $error_message = "Pdf fail $url is empty";
            ++$this->pdf_fail_count;
        } else if (Helpers::stringContainsInsensitive($contents, 'DOCTYPE')) {
            $error_message = "Pdf fail $url is a webpage, possibly an error message";
            ++$this->pdf_fail_count;
        } else if (Helpers::stringContainsInsensitive($contents, 'PDF')) {
            $error_message = "Pdf pass pdf $url";
            ++$this->pdf_pass_count;
        } else {
            $content_length = strlen($contents);
            $error_message = "Pdf fail $url does not contain a pdf $contents :::: $content_length --";
            ++$this->pdf_fail_count;
        }
        return Helpers::show_test_error_results($error_message, 'no_hr');
    }

    public function checkingCounts() {
        $link_counts = array();
        $total_wikipedia = $this->wikipedia_pass_count + $this->wikipedia_fail_count;
        $total_pdf = $this->pdf_pass_count + $this->pdf_fail_count;
        $link_counts [] = "Wikipedia Links Fail : $this->wikipedia_fail_count";
        $link_counts [] = "Wikipedia Links Pass : $this->wikipedia_pass_count";
        $link_counts [] = "Wikipedia Links Total : $total_wikipedia ";
        $link_counts [] = "PDF Links Fail : $this->pdf_fail_count";
        $link_counts [] = "PDF Links Pass : $this->pdf_pass_count";
        $link_counts [] = "PDF Links Total : $total_pdf";
        return $link_counts;
    }

    public function checkPdfsWikipedia($number_links_to_check = 0) {     // if 0 then check ALL
        set_time_limit(0);
        error_reporting(E_ERROR | E_PARSE);
        $messages = array();
        foreach ($this->urls_to_check as $url) {
            if (Helpers::stringEndsWith($url, '.pdf')) {
                $pdf_url = $this->pdf_folder_url . $url;
                $message = $this->openPdf($pdf_url);
            } else {
                $message = $this->openWikipediaUrl($url);
            }
            $messages [] = $message;
            --$number_links_to_check;
            if (0 === $number_links_to_check) {
                break;
            }
        }
        $html = '';
        $link_counts = $this->checkingCounts();
        foreach ($link_counts as $link_count) {
            $html .= Helpers::show_test_error_results($link_count, 'a br');
        }

        $errors_html = implode('', $messages);
        $html .= "<br><hr><br>$errors_html";
        return $html;
    }

}
