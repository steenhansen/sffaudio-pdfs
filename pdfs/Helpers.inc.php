<?php

// Purpose: string and BOM routines

class Helpers {

    public static function show_test_error_results($error_line, $have_leading_hr = 'leading_hr') {
        if (self::stringContainsInsensitive($error_line, "PASS")) {
            $line_color = 'green';
        } else if (self::stringContainsInsensitive($error_line, "fail")) {
            $line_color = 'red';
        } else {
            $line_color = 'black';
        }
        if ('leading_hr' === $have_leading_hr) {
            $an_hr_br = '<hr>';
        } else {
            $an_hr_br = '<br>';
        }
        $html = "$an_hr_br<font color='$line_color'>$error_line</font>";
        return $html;
    }

    public static function string_differences($string_1, $string_2) {
        $differences = array();
        if (Helpers::hasUtf8Bom($string_1)) {
            $differences [] = "String 1 has Utf8 Bom, now stripped";
            $string_1 = Helpers::stripUtf8Bom($string_1);
        }
        if (Helpers::hasUtf8Bom($string_2)) {
            $differences [] = "String 2 has Utf8 Bom, now stripped";
            $string_2 = Helpers::stripUtf8Bom($string_2);
        }

        $string_1 = htmlspecialchars($string_1);
        $string_2 = htmlspecialchars($string_2);
        $len_str_1 = strlen($string_1);
        $len_str_2 = strlen($string_2);
        if ($len_str_1 !== $len_str_2) {
            $differences [] = "String Lengths '$len_str_1' <> '$len_str_2' are different";
        }

        $first_20_chars_1 = substr($string_1, 0, 20);
        $first_20_chars_2 = substr($string_2, 0, 20);
        if ($first_20_chars_1 !== $first_20_chars_2) {
            $differences [] = "First 20 characters of strings '$first_20_chars_1' !== '$first_20_chars_2' are different";
        }

        $last_20_chars_1 = substr($string_1, -20);
        $last_20_chars_2 = substr($string_2, -20);
        if ($last_20_chars_1 !== $last_20_chars_2) {
            $differences [] = "Last 20 characters of strings '$last_20_chars_1' <> '$last_20_chars_2' are different";
        }
        $error_html = implode('<br>', $differences);
        return $error_html;
    }

    public static function safeSearchText($search_text) {
        $search_decoded = urldecode($search_text);
        $safe_search = preg_replace("~[^A-Za-z0-9 '\,\.]~", '', $search_decoded);
        return $safe_search;
    }

    public static function hasUtf8Bom($string_with_bom) {
        if (self::stringContains($string_with_bom, "\xEF\xBB\xBF")) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function stripUtf8Bom($string_with_bom) {
        $string_no_bom = trim(str_replace("\xEF\xBB\xBF", '', $string_with_bom));
        return $string_no_bom;
    }

    public static function saveAsUtf8($filename, $data) {
        $f = fopen($filename, "w");
        fwrite($f, "\xEF\xBB\xBF");
        fwrite($f, $data);
        fclose($f);
    }

    public static function collapseIntoVarName($data) {
        $var_name = trim(strtolower($data));
        $var_name = str_replace(' ', '_', $var_name);
        $var_name = preg_replace("~[^a-z0-9_]~", '', $var_name);
        if (('0' <= $var_name{0}) and ( $var_name{0} <= '9')) {
            $var_name = "_$var_name";
        }
        return $var_name;
    }

    public static function stringEndsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return TRUE;
        }
        return (substr($haystack, -$length) === $needle);
    }

    public static function stringContains($haystack, $needles) {       // From Laravel
        foreach ((array) $needles as $needle) {
            if (($needle != '') && (strpos($haystack, $needle) !== FALSE)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public static function stringContainsInsensitive($haystack, $needles) {
        foreach ((array) $needles as $needle) {
            if (($needle != '') && (stripos($haystack, $needle) !== FALSE)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
