<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Message\Dump\DumperManager\IDumperManager;

/**
 * Dumper desetinného čísla.
 */
class DoubleDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'double'.
     */
    public function __construct()
    {
        $this->setType(IDumperManager::T_DOUBLE);
    }

    public function dump(&$var, $level, $depth)
    {
        $out = var_export($var, true);

        return "<span class='dump_arg_number'>" . $out . (strpos($out, '.') !== false ? '' : '.0') . '</span>';
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}