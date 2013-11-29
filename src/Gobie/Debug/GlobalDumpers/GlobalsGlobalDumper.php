<?php

namespace Gobie\Debug\GlobalDumpers;

class GlobalsGlobalDumper implements IGlobalDumper
{

    public function getData()
    {
        return $GLOBALS;
    }

    public function getName()
    {
        return 'GLOBALS';
    }
}
