<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * SHA1 hashed string dumper.
 */
class SHA1Dumper extends StringDumper
{

    public function dump(&$var, $level, $depth)
    {
        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing SHA1 hash</span>';
    }

    protected function verifyCustomCondition($var)
    {
        return $var && strlen($var) === 40 && !preg_match('@[^0-9a-f]@Si', $var);
    }
}
