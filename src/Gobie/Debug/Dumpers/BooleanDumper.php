<?php

namespace Gobie\Debug\Dumpers;

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
        $this->setType(DumperManager::T_BOOLEAN);
    }

    public function dump(&$var, $level, $depth)
    {
        return '<span class="dump_arg_bool">' . ($var ? 'TRUE' : 'FALSE') . '</span>';
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}
