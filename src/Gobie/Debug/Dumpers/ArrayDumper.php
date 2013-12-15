<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;
use Gobie\Debug\Helpers;

/**
 * Dumper pole.
 */
class ArrayDumper extends AbstractDumper
{

    /**
     * Značka pro zjištění referenční rekurze polí.
     *
     * Velice nespolehlivé.
     * <pre>
     * $a = array();
     * $a[] = &$a;
     * $a[0][] = $a;
     * </pre>
     * Porovnejte výstupy z var_dump a z této funkce.
     *
     * @var string
     */
    private $recursionMarker;

    /**
     * Nastaví typ proménné na 'array'.
     *
     * Definice značky pro zjištění referenční rekurze polí.
     */
    public function __construct()
    {
        $this->setType(IDumperManager::T_ARRAY);
        $this->recursionMarker = uniqid("\x00", true);
    }

    public function dump(&$var, $level, $depth)
    {
        if (isset($var[$this->recursionMarker])) {
            return '**RECURSION**';
        }

        $out   = array();
        $count = count($var);
        $out[] = '<b>array</b> <span class="dump_arg_desc">(' . $count . ')</span>';
        if (!$count) {
            return implode('', $out);
        }

        $indentation = Helpers::indent($level);
        if ($depth == 0) {
            $out[] = ' ...';

            return implode('', $out);
        }

        $out[] = PHP_EOL;
        $pos   = 1;

        $var[$this->recursionMarker] = true;
        foreach ($var as $key => &$value) {
            if ($key === $this->recursionMarker) {
                continue;
            }

            $dKey   = Helpers::encodeKey($key);
            $dValue = $this->getManager()->dump($value, $level + 1, $depth - 1);
            $out[]  = $indentation . $dKey . '<span class="dump_arg_keyword"> =&gt; </span>' . $dValue;

            // Ošetření posledního řádku
            if ($pos !== $count || ($pos === $count && $depth === 0)) {
                $out[] = PHP_EOL;
            }
            ++$pos;
        }
        unset($var[$this->recursionMarker]);

        return implode('', $out);
    }
}
