<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Debug;

/**
 * Rozhraní manažeru pro dumpování proměnných.
 */
interface IDumperManager
{

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

    /**
     * Vrátí Debug.
     *
     * @return Debug
     */
    public function getDebug();

    /**
     * Nastaví Debug.
     *
     * @param Debug $debug Debug.
     * @return self
     */
    public function setDebug(Debug $debug);
}
