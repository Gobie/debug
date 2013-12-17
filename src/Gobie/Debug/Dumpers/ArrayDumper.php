<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;
use Gobie\Debug\Helpers;

/**
 * Array dumper.
 */
class ArrayDumper extends AbstractDumper
{

    /**
     * Marker for recognizing array recursion.
     *
     * Inconsistent, compare results of following code with var_dump output:
     * <pre>
     * $a = array();
     * $a[] = &$a;
     * $a[0][] = $a;
     * </pre>
     *
     * @var string
     */
    private $recursionMarker;

    /**
     * Sets types it can dump and recursion marker.
     */
    public function __construct()
    {
        $this->setTypes(IDumperManager::T_ARRAY);
        $this->recursionMarker = uniqid("\x00", true);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(&$var, $level = 1, $depth = 4)
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

            // Last line fix
            if ($pos !== $count || ($pos === $count && $depth === 0)) {
                $out[] = PHP_EOL;
            }
            ++$pos;
        }
        unset($var[$this->recursionMarker]);

        return implode('', $out);
    }
}
