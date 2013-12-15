<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Dumper booleovské proměnné.
 */
class BooleanDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'boolean'.
     */
    public function __construct()
    {
        $this->setType(IDumperManager::T_BOOLEAN);
    }

    public function dump(&$var, $level, $depth)
    {
        return '<span class="dump_arg_bool">' . ($var ? 'TRUE' : 'FALSE') . '</span>';
    }
}
