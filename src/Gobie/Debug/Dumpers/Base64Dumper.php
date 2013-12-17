<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper Base64.
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

    public function canDump($var)
    {
        return $var
               && strlen($var) % 4 === 0
               && !preg_match('@[^a-zA-Z0-9/+=]@S', $var)
               && preg_match('@\S@Su', base64_decode($var));
    }
}
