<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Dumper pro $_ENV.
 */
class EnvGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        return $_ENV;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'ENV';
    }
}
