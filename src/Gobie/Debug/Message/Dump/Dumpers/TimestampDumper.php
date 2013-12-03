<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Helpers;
use Gobie\Debug\Message\Dump\DumperManager\IDumperManager;

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

    public function getReplacedClasses()
    {
        return array(
            '\Gobie\Debug\Message\Dump\Dumpers\StringDumper'  => true,
            '\Gobie\Debug\Message\Dump\Dumpers\IntegerDumper' => true
        );
    }

    public function dump(&$var, $level, $depth)
    {
        $indentation = Helpers::indent($level);
        $class       = is_string($var) ? 'dump_arg_string' : 'dump_arg_number';

        return '<span class="' . $class . '">' . $var . '</span>' . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing Unix timestamp</span>' . PHP_EOL
               . $indentation . '<span class="dump_arg_expanded">' . date('Y-m-d H:i:s', $var) . '</span>';
    }

    protected function verifyCustomCondition($var)
    {
        $range = ((int) $var) / 1e9;

        return $range >= 1 && $range <= 2;
    }
}
