<?php

namespace Gobie\Debug\Dumpers;

/**
 * Dumper celého čísla.
 */
class IntegerDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'integer'.
     */
    public function __construct()
    {
        $this->setType(DumperManager::T_INTEGER);
    }

    public function dump(&$var, $level, $depth)
    {
        return "<span class='dump_arg_number'>" . $var . '</span>';
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}
