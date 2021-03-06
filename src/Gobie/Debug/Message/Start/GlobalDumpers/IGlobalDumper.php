<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Rozhraní pro globální dumpery.
 */
interface IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName();
}
