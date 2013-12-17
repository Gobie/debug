<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Base64 dumper.
 *
 * Expects utf-8 strings.
 */
class Base64Dumper extends StringDumper
{

    /**
     * {@inheritdoc}
     */
    public function dump(&$var, $level = 1, $depth = 4)
    {
        $indentation = Helpers::indent($level);
        $value       = Helpers::encodeString(base64_decode($var));

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing Base64 encoded string</span>' . PHP_EOL
               . $indentation . '<span class="dump_arg_expanded">' . $value . '</span>';
    }

    /**
     * {@inheritdoc}
     * String must be non-empty.
     * String must have length divisible by four.
     * String must contain only of characters [a-zA-Z0-9/+=].
     * String must after base64_decode contain only valid utf-8 characters.
     * @link http://en.wikipedia.org/wiki/Base64
     */
    public function canDump($var)
    {
        return $var
               && strlen($var) % 4 === 0
               && !preg_match('@[^a-zA-Z0-9/+=]@S', $var)
               && preg_match('@@Su', base64_decode($var));
    }
}
