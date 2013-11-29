<?php

namespace Gobie\Debug\GlobalDumpers;

/**
 * Dumper pro $_SERVER.
 */
class ServerGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        return $_SERVER;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'SERVER';
    }
}
