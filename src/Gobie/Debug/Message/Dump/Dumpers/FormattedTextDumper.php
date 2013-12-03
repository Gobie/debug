<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Formatted text dumper.
 */
class FormattedTextDumper extends StringDumper
{

    public function getReplacedClasses()
    {
        return array(
            '\Gobie\Debug\Message\Dump\Dumpers\StringDumper' => true
        );
    }

    public function dump(&$var, $level, $depth)
    {
        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing formatted text</span>' . PHP_EOL
               . Helpers::wrapLines($var, $indentation . '<span class="dump_arg_expanded">', '</span>');
    }

    protected function verifyCustomCondition($var)
    {
        return $var && preg_match('@(\\n|\\t)@S', $var);
    }
}
