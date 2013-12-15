<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper SplObjectStorage.
 */
class SplObjectStorageDumper extends ObjectDumper
{

    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $indentation = Helpers::indent($level);

        /* @var $var \SplObjectStorage */
        foreach (clone $var as $key => $value) {
            $input = array(
                'object' => $value,
                'data'   => $var[$value]
            );

            $dKey   = Helpers::encodeKey($key);
            $dValue = $this->getManager()->dump($input, $level + 1, $depth - 1);
            $out[]  = PHP_EOL . $indentation . $dKey . '<span class="dump_arg_keyword"> =&gt; </span>' . $dValue;
        }

        return true;
    }

    public function canDump($var)
    {
        return $var instanceof \SplObjectStorage;
    }
}
