<?php

namespace Gobie\Debug\GlobalDumpers;

/**
 * Dumper pro $_SESSION.
 */
class SessionGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        if (!isset($_SESSION)) {
            ini_set('session.gc_probability', '0');
            @session_start();
            @session_write_close();
        }

        return isset($_SESSION) ? $_SESSION : null;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'SESSION';
    }
}
