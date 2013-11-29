<?php

namespace Gobie\Debug\GlobalDumpers;

/**
 * Dumper pro $_FILES.
 */
class FilesGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        return $_FILES;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'FILES';
    }
}
