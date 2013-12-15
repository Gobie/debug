<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;
use Gobie\Debug\Helpers;

/**
 * Dumper timestampu.
 */
class TimestampDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'integer' nebo 'string'.
     */
    public function __construct()
    {
        $this->setType(array(IDumperManager::T_INTEGER, IDumperManager::T_STRING));
    }

    public function dump(&$var, $level, $depth)
    {
        $indentation = Helpers::indent($level);
        $class       = is_string($var) ? 'dump_arg_string' : 'dump_arg_number';

        return '<span class="' . $class . '">' . $var . '</span>' . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing Unix timestamp</span>' . PHP_EOL
               . $indentation . '<span class="dump_arg_expanded">' . date('Y-m-d H:i:s', $var) . '</span>';
    }

    public function canDump($var)
    {
        $range = ((int) $var) / 1e9;

        return $range >= 1 && $range <= 2;
    }
}
