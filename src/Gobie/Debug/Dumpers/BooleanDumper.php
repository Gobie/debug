<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Boolean dumper.
 */
class BooleanDumper extends AbstractDumper
{

    /**
     * Sets types it can dump.
     */
    public function __construct()
    {
        $this->setTypes(IDumperManager::T_BOOLEAN);
    }

    /**
     * {@inheritdoc}
     */
    public function dump(&$var, $level = 1, $depth = 4)
    {
        return '<span class="dump_arg_bool">' . ($var ? 'TRUE' : 'FALSE') . '</span>';
    }
}
