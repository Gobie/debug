<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper DOMNode.
 */
class DomNodeDumper extends ObjectDumper
{

    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $indentation = Helpers::indent($level);

        $dom               = new \DOMDocument();
        $dom->formatOutput = true;
        $node              = $dom->importNode($var, true);
        $dom->appendChild($node);

        $xml = Helpers::escape(trim($dom->saveXML($node)));

        $out[] = PHP_EOL . Helpers::wrapLines($xml, $indentation . '<span class="dump_arg_expanded">', '</span>');

        return true;
    }

    public function canDump($var)
    {
        return $var instanceof \DOMNode && !($var instanceof \DOMDocument);
    }
}
