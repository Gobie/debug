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
        $this->setTypes(IDumperManager::T_BOOLEAN);
    }

    public function dump(&$var, $level = 1, $depth = 4)
    {
        return '<span class="dump_arg_bool">' . ($var ? 'TRUE' : 'FALSE') . '</span>';
    }
}
