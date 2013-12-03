<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper DOMDocument.
 */
class DomDocumentDumper extends ObjectDumper
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

        $dom               = clone $var;
        $dom->formatOutput = true;
        $xml               = Helpers::escape(trim($dom->saveXML()));

        $out[] = PHP_EOL . Helpers::wrapLines($xml, $indentation . '<span class="dump_arg_expanded">', '</span>');

        return true;
    }

    protected function verifyCustomCondition($var)
    {
        return $var instanceof \DOMDocument;
    }
}
