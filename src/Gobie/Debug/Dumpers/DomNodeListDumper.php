<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper DOMNodeListu.
 */
class DomNodeListDumper extends ObjectDumper
{

    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $indentation = Helpers::indent($level);

        $dom               = new \DOMDocument();
        $dom->formatOutput = true;
        /** @var $var \DOMNodeList */
        for ($i = 0; $i < $var->length; ++$i) {
            $node = $dom->importNode($var->item($i), true);
            $dom->appendChild($node);
        }

        $xml = Helpers::escape(trim($dom->saveXML()));

        $out[] = PHP_EOL . Helpers::wrapLines($xml, $indentation . '<span class="dump_arg_expanded">', '</span>');

        return true;
    }

    protected function verifyCustomCondition($var)
    {
        return $var instanceof \DOMNodeList;
    }
}
