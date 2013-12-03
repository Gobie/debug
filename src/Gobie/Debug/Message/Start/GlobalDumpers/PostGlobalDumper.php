<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Dumper pro $_POST.
 */
class PostGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        return $_POST;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'POST';
    }
}
