<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Dumper pro $_COOKIE.
 */
class CookieGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        return $_COOKIE;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'COOKIE';
    }
}
