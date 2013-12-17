<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

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
        $this->setTypes(IDumperManager::T_INTEGER);
    }

    public function dump(&$var, $level = 1, $depth = 4)
    {
        return "<span class='dump_arg_number'>" . $var . '</span>';
    }
}
