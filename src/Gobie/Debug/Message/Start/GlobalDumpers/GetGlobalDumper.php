<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Dumper pro $_GET.
 */
class GetGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        return $_GET;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'GET';
    }
}
