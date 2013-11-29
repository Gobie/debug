<?php

namespace Gobie\Debug\GlobalDumpers;

/**
 * Dumper pro načtené soubory, třídy, rozhraní, funkce a konstanty.
 */
class LoadedGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        $functions = get_defined_functions();
        $constants = get_defined_constants(true);

        return array(
            'files'      => get_included_files(),
            'classes'    => get_declared_classes(),
            'interfaces' => get_declared_interfaces(),
            'functions'  => $functions['user'],
            'constants'  => $constants['user']
        );
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'Loaded';
    }
}
