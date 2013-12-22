<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Null dumper.
 */
class NullDumper extends AbstractDumper
{

    /**
     * Sets types it can dump.
     */
    public function __construct()
    {
        $this->setTypes(IDumperManager::T_NULL);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(&$var, $level = 1, $depth = 4)
    {
        return '<span class="dump_arg_null">NULL</span>';
    }
}
