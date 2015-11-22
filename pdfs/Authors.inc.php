<?php

// Purpose: to handle author names 

class Authors {

    protected $author_wikipedia_hash;
    protected $last_name_steps;

    function __construct() {
        $this->author_wikipedia_hash = array();
        $this->last_name_steps = '';
    }

    public function lastNameSteps() {
        return $this->last_name_steps;
    }

    public function addAuthorCollapsed($book_author, $author_link) {
        $link_trimmed = trim($author_link);
        if ('' !== $link_trimmed) {
            $author_collapsed = Helpers::collapseIntoVarName($book_author);
            if ('' !== $author_collapsed) {
                $this->author_wikipedia_hash [$author_collapsed] = $link_trimmed;
            }
        }
    }

    public function getAuthorCollapsed($book_author) {
        $author_hash = Helpers::collapseIntoVarName($book_author);
        if (isset($this->author_wikipedia_hash [$author_hash])) {
            return $this->author_wikipedia_hash [$author_hash];
        } else {
            return '';
        }
    }

    public function lastAuthorName($author_punctuation) {
        $irish_o_apostraphe = '_Irish_O_Brien_apostraphe_';
        $author_irish_o = preg_replace("~ O'([A-za-z])~", " $irish_o_apostraphe$1", $author_punctuation);

        $french_l_apostraphe = '_French_L_apostraphe_';
        $author_french_l = preg_replace("~ L'([A-za-z])~", " $french_l_apostraphe$1", $author_irish_o);

        $author_french_de = preg_replace("~ De ([A-za-z])~", ' De&nbsp;$1', $author_french_l);
        $author_french_le = preg_replace("~ Le ([A-za-z])~", ' Le&nbsp;$1', $author_french_de);

        $author_del_rey = preg_replace("~ del ([A-za-z])~", ' del&nbsp;$1', $author_french_le);

        $bad_punctuations = array("'", '"', '.', ')',);
        $author_double_spaces = str_ireplace($bad_punctuations, ' ', $author_del_rey);
        $author_no_punc = preg_replace('~\s\s+~', ' ', $author_double_spaces);
        $bad_end_pieces = array(' Jr ', ' Sr ');
        $author_no_jr_sr = str_ireplace($bad_end_pieces, ' ', "$author_no_punc ");
        $author_chars_only = trim($author_no_jr_sr);

        $author_irish_o_ok = str_replace($irish_o_apostraphe, "O'", $author_chars_only);
        $author_french_l_ok = str_replace($french_l_apostraphe, "L'", $author_irish_o_ok);

        $author_words = explode(' ', $author_french_l_ok);
        $last_nbsp_name = array_pop($author_words);
        $last_name = str_replace('&nbsp;', ' ', $last_nbsp_name);

        $lastname_steps = compact('author_punctuation', 'author_irish_o', 'author_french_l', 'author_french_de', 'author_french_le', 'author_del_rey', 'author_double_spaces', 'author_no_punc', 'author_no_jr_sr', 'author_chars_only', 'author_irish_o_ok', 'author_french_l_ok', 'last_nbsp_name', 'last_name');
        $this->last_name_steps .= self::test_show_last_name_steps($lastname_steps);
        return $last_name;
    }

    public static function test_show_last_name_steps($lastname_steps) {
        extract($lastname_steps);
        $output = <<<EOS
          <br> author_punctuation   ---$author_punctuation---
          <br> author_irish_o       ---$author_irish_o---
          <br> author_french_l      ---$author_french_l---
          <br> author_french_de     ---$author_french_de---
          <br> author_french_le     ---$author_french_le---
          <br> author_del_rey       ---$author_del_rey---
          <br> author_double_spaces ---$author_double_spaces---
          <br> author_no_punc       ---$author_no_punc---
          <br> author_no_jr_sr      ---$author_no_jr_sr---
          <br> author_chars_only    ---$author_chars_only---
          <br> author_irish_o_ok    ---$author_irish_o_ok---
          <br> author_french_l_ok   ---$author_french_l_ok---
          <br> last_nbsp_name       ---$last_nbsp_name---
          <br> last_name            ---$last_name---
EOS;
        return $output;
    }

}
