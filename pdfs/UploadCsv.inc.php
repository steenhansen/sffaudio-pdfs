<?php

// Purpose: basis of reading a csv file

class UploadCsv {

    protected $filename;
    protected $delimiter;

    function __construct($filename, $delimiter = ",") {
        $this->filename = $filename;
        $this->delimiter = $delimiter;
    }

    protected function enoughRows($at_least_this_many_rows) {
        $rows_found = -1;
        if (($handle = fopen($this->filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                ++$rows_found;
            }
            fclose($handle);
        }
        if ($at_least_this_many_rows > $rows_found) {
            $exception_message = <<< EOS
                            NOT ENOUGH BOOK ROWS EXCEPTION: ($at_least_this_many_rows, $rows_found)   
                    <br> -There has to be at least $at_least_this_many_rows book rows, only $rows_found rows were found              
EOS;
            throw new Exception($exception_message . __METHOD__);
        }
    }

    public function fieldsValid($at_least_this_many_rows) {
        $this->enoughRows($at_least_this_many_rows);
        $file_field_names = $this->firstLineVariables();
        $header_count = count($file_field_names);
        $expected_count = count($this->FIELD_NAMES);
        if ($expected_count !== $header_count) {
            $exception_message = <<< EOS
                            WRONG NUMBER OF FIELDS IN HEADER EXCEPTION: ($header_count, $expected_count)   
                    <br> -Expected $expected_count columns in header but read $header_count columns instead              
EOS;
            throw new Exception($exception_message . __METHOD__);
        }
        $this->validateColumnNames($file_field_names);
    }

    private function validateColumnNames($file_field_names) {
        foreach ($this->FIELD_NAMES as $column => $class_field_name) {
            $class_field_name_lower = trim(strtolower($class_field_name));
            $field_name_from_file = trim(strtolower($file_field_names[$column]));
            if ($class_field_name_lower !== $field_name_from_file) {
                $exception_message = <<< EOS
                            WRONG FIELDNAME IN HEADER EXCEPTION: ($class_field_name_lower, $field_name_from_file)  
                    <br> -Expected $class_field_name_lower but found $field_name_from_file instead              
EOS;
                throw new Exception($exception_message . __METHOD__);
            }
        }
    }

    protected function trimRow($row) {
        foreach ($row as $column => $value) {
            $row[$column] = trim($value);
        }
        return $row;
    }

    protected function deleteEmptyCellsFromRight($row) {
        $preserve_keys = TRUE;
        $reverse = array_reverse($row, $preserve_keys);
        foreach ($reverse as $column => $value) {
            if ('' === $value) {
                unset($reverse[$column]);
            } else {
                break;
            }
        }
        $normal = array_reverse($reverse, $preserve_keys);
        return $normal;
    }

    protected function firstLineVariables() {
        if (($handle = fopen($this->filename, "r")) !== FALSE) {
            while (($data_types = fgetcsv($handle, 0, ",")) !== FALSE) {
                break;
            }
            fclose($handle);
        }
        $variable_names = array();
        foreach ($data_types as $a_data_type) {
            $var_name = Helpers::collapseIntoVarName($a_data_type);
            if ('' !== $var_name) {
                $variable_names [] = $var_name;
            }
        }
        return $variable_names;
    }

    protected function completeRow($row, $row_number) {
        $first_column_name = $this->NON_BLANK_FIELDS[0];
        if (NULL !== $row[$first_column_name]) {
            foreach ($this->NON_BLANK_FIELDS as $non_blank_field) {
                if ('' === $row[$non_blank_field]) {
                    $exception_message = <<< EOS
                            INCOMPLETE CSV ROW EXCEPTION: ($row_number, $non_blank_field)  
                    <br> -On row $row_number we have an incomplete csv row in column $non_blank_field              
EOS;
                    throw new Exception($exception_message . __METHOD__);
                }
            }
        }
    }

}
