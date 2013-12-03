<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Helpers;

/**
 * MD5 hashed string dumper.
 */
class MD5Dumper extends StringDumper
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
               . $indentation . '<span class="dump_arg_desc">guessing MD5 hash</span>';
    }

    protected function verifyCustomCondition($var)
    {
        return $var && strlen($var) === 32 && !preg_match('@[^0-9a-f]@Si', $var);
    }
}
