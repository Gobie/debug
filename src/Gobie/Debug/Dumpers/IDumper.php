<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Dumper interface.
 */
interface IDumper
{

    /**
     * Dumps variable to string representation.
     *
     * @param mixed   $var   Variable
     * @param integer $level Current level of indentation
     * @param integer $depth Max depth of variable dump
     * @return string
     */
    public function dump(&$var, $level, $depth);

    /**
     * Sets DumperManager.
     *
     * Dumper uses DumperManager for dumping subvalues.
     *
     * @param IDumperManager $manager DumperManager
     * @return $this
     */
    public function setManager(IDumperManager $manager);

    /**
     * Returns DumperManager.
     *
     * @return IDumperManager
     */
    public function getManager();

    /**
     * Sets types of variables dumper can dump.
     *
     * @param string $types Variable types
     * @return $this
     */
    public function setTypes($types);

    /**
     * Returns types of variables dumper can dump.
     *
     * @return array
     */
    public function getTypes();

    /**
     * Checks if variable can be dumped by this dumper.
     *
     * It should be called before IDumper::dump().
     *
     * It should not explicitly check for variable type but for the requirements specific for the dumper.
     * Variable types it can dump are set through IDumper::setTypes();
     *
     * @param mixed $var Variable
     * @return boolean
     */
    public function canDump($var);
}
