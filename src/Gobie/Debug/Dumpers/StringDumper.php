<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;
use Gobie\Debug\Helpers;

/**
 * Dumper řetězce.
 */
class StringDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'string'.
     *
     * Zkontroluje dostupnost potřebné extenze.
     */
    public function __construct()
    {
        $this->setType(IDumperManager::T_STRING);
    }

    public function dump(&$var, $level, $depth)
    {
        $varEnc = Helpers::encodeString($var);
        $varLen = strlen($var);

        return "<span class='dump_arg_string'>" . $varEnc . "</span>"
               . ($varLen ? " <span class='dump_arg_desc'>(" . $varLen . ')</span>' : '');
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}
