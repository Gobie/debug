<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper XML.
 */
class XmlDumper extends StringDumper
{

    public function dump(&$var, $level = 1, $depth = 4)
    {
        $dom               = new \DOMDocument();
        $dom->formatOutput = true;
        $dom->loadXML($var);

        $xml         = Helpers::escape($dom->saveXML($dom->documentElement));
        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing XML</span>' . PHP_EOL
               . Helpers::wrapLines($xml, $indentation . '<span class="dump_arg_expanded">', '</span>');
    }

    public function canDump($var)
    {
        return $var
               && $var{0} === '<'
               && $var{strlen($var) - 1} === '>'
               && ($dom = new \DOMDocument())
               && $dom->loadXML($var);
    }
}
