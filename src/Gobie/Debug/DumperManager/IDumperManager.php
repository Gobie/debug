<?php

namespace Gobie\Debug\DumperManager;

use Gobie\Debug\Dumpers\IDumper;

/**
 * DumperManager interface.
 */
interface IDumperManager
{
    /**
     * Known variable types.
     */
    const
        T_BOOLEAN = 'boolean',
        T_INTEGER = 'integer',
        T_DOUBLE = 'double',
        T_STRING = 'string',
        T_ARRAY = 'array',
        T_OBJECT = 'object',
        T_RESOURCE = 'resource',
        T_NULL = 'NULL',
        T_UNKNOWN = 'unknown type';

    /**
     * Adds dumper.
     *
     * @param IDumper $dumper Dumper
     */
    public function addDumper(IDumper $dumper);

    /**
     * Returns all added dumpers.
     *
     * @return array
     */
    public function getDumpers();

    /**
     * Dumps any kind of variable to string representation using corresponding Dumper.
     *
     * @param mixed   $var   Variable
     * @param integer $level Current level of indentation
     * @param integer $depth Max depth of variable dump
     * @return string
     */
    public function dump(&$var, $level, $depth);
}
