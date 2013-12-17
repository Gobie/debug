<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper JSON.
 */
class JsonDumper extends StringDumper
{

    public function dump(&$var, $level = 1, $depth = 4)
    {
        if (defined('JSON_PRETTY_PRINT')) {
            $json = json_encode(json_decode($var), JSON_PRETTY_PRINT);
        } else {
            $json = $this->jsonFormat($var);
        }
        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing JSON</span>' . PHP_EOL
               . Helpers::wrapLines($json, $indentation . '<span class="dump_arg_expanded">', '</span>');
    }

    public function canDump($var)
    {
        return $var
               && (($var{0} === '{' && $var{strlen($var) - 1} === '}')
                   || ($var{0} === '[' && $var{strlen($var) - 1} === ']'))
               && json_decode($var) !== null;
    }

    /**
     * Format a flat JSON string to make it more human-readable
     *
     * @param string $json The original JSON string to process
     *                     When the input is not a string it is assumed the input is RAW
     *                     and should be converted to JSON first of all.
     * @return string Indented version of the original JSON string
     * @link https://github.com/GerHobbelt/nicejson-php/blob/master/nicejson.php
     */
    protected function jsonFormat($json)
    {
        $result      = '';
        $pos         = 0; // indentation level
        $strLen      = strlen($json);
        $levelStr    = "  ";
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i = 0; $i < $strLen; $i++) {
            // Grab the next character in the string
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            } // If this character is the end of an element,
            // output a new line and indent the next line
            else if (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $levelStr;
                }
            } // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
            else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
                continue;
            }

            // Add the character to the result string
            $result .= $char;
            // always add a space after a field colon:
            if ($char == ':' && $outOfQuotes) {
                $result .= ' ';
            }

            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $levelStr;
                }
            }
            $prevChar = $char;
        }

        return $result;
    }
}
