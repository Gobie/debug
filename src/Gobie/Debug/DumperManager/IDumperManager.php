<?php

namespace Gobie\Debug\DumperManager;

use Gobie\Debug\Dumpers\IDumper;

/**
 * Rozhraní manažeru pro dumpování proměnných.
 */
interface IDumperManager
{
    const T_BOOLEAN = 'boolean';

    const T_INTEGER = 'integer';

    const T_DOUBLE = 'double';

    const T_STRING = 'string';

    const T_ARRAY = 'array';

    const T_OBJECT = 'object';

    const T_RESOURCE = 'resource';

    const T_NULL = 'NULL';

    const T_UNKNOWN = 'unknown type';

    /**
     * Zaregistruje Dumper dle jeho nastavení.
     *
     * @param IDumper $dumper Dumper
     * @return self
     */
    public function addDumper(IDumper $dumper);

    /**
     * Dumpne proměnnou.
     *
     * @param mixed   $var   Proměnná
     * @param integer $level Počáteční odsazení
     * @param integer $depth Maximální hloubka zanoření objektů výpisu
     * @return string
     */
    public function dump($var, $level, $depth);
}
