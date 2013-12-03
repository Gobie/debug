<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

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
