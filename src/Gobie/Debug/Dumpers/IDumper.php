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
     * Nastaví typ proměnné, pro kterou je objekt registrován.
     *
     * @param string $type Typ proměnné
     * @return self
     */
    public function setType($type);

    /**
     * Vrátí typ proměnné, pro kterou je objekt registrován.
     *
     * @return array
     */
    public function getType();

    /**
     * Ověří, zda proměnná má být tímto objektem zpracována.
     *
     * @param mixed  $var         Proměnná
     * @param string $varType     Datový typ proměnné
     * @param array  $usedDumperClasses Použité dumpery
     * @return boolean
     */
    public function verify($var, $varType, array $usedDumperClasses = array());


    /**
     * @return array
     */
    public function getReplacedClasses();
}
