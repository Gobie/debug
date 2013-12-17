<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Formatted text dumper.
 */
class FormattedTextDumper extends StringDumper
{

    public function dump(&$var, $level = 1, $depth = 4)
    {
        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing formatted text</span>' . PHP_EOL
               . Helpers::wrapLines($var, $indentation . '<span class="dump_arg_expanded">', '</span>');
    }

    public function canDump($var)
    {
        return $var && preg_match('@(\\n|\\t)@S', $var);
    }
}
