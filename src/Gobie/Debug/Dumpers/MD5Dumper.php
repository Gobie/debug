<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * MD5 hashed string dumper.
 */
class MD5Dumper extends StringDumper
{

    public function dump(&$var, $level = 1, $depth = 4)
    {
        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing MD5 hash</span>';
    }

    public function canDump($var)
    {
        return $var && strlen($var) === 32 && !preg_match('@[^0-9a-f]@Si', $var);
    }
}
