<?php

namespace Gobie\Debug\Message;

/**
 * Rozhraní pro zprávy v Debug toolbaru.
 */
interface IMessage
{

    /**
     * Dumpne obsah zprávy a vrátí ji.
     *
     * @return array
     */
    public function dump();

}
