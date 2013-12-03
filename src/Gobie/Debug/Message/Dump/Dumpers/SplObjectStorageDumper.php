<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper SplObjectStorage.
 */
class SplObjectStorageDumper extends ObjectDumper
{
    public function getReplacedClasses()
    {
        return array(
            '\Gobie\Debug\Message\Dump\Dumpers\ObjectDumper' => true
        );
    }

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

    protected function verifyCustomCondition($var)
    {
        return $var instanceof \SplObjectStorage;
    }
}
