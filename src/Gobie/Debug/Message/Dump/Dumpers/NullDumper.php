<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Message\Dump\DumperManager\IDumperManager;

/**
 * Dumper nullu.
 */
class NullDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'NULL'.
     */
    public function __construct()
    {
        $this->setType(IDumperManager::T_NULL);
    }

    public function dump(&$var, $level, $depth)
    {
        return "<span class='dump_arg_null'>NULL</span>";
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}
