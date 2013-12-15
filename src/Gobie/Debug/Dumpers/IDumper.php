<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Rozhraní pro dumper.
 */
interface IDumper
{

    /**
     * Dumpne proměnnou.
     *
     * @param mixed   $var   Proměnná
     * @param integer $level Počáteční odsazení
     * @param integer $depth Maximální hloubka zanoření objektů výpisu
     * @return string
     */
    public function dump(&$var, $level, $depth);

    /**
     * Nastaví manažera pro dumpování proměnných.
     *
     * @param IDumperManager $manager Manažer pro dumpování proměnných
     * @return self
     */
    public function setManager(IDumperManager $manager);

    /**
     * Vrátí manažera pro dumpování proměnných.
     *
     * @return IDumperManager
     */
    public function getManager();

    /**
     * Sets types of variables dumper can dump.
     *
     * @param string $type Typ proměnné
     * @return self
     */
    public function setType($type);

    /**
     * Returns types of variables dumper can dump.
     *
     * @return array
     */
    public function getType();

    /**
     * Checks if variable can be dumped by this dumper.
     *
     * It should be called before IDumper::dump().
     *
     * It should not explicitly check for variable type but for the requirements specific for the dumper.
     * Variable types it can dump are set through IDumper::setTypes();
     *
     * @param mixed $var Proměnná
     * @return boolean
     */
    public function canDump($var);
}
