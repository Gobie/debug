<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Abstraktní třída pro všechny dumpery.
 */
abstract class AbstractDumper implements IDumper
{

    /**
     * Typ proměnné, pro kterou je tento objekt registrován.
     *
     * @var string
     * @see IDumperManager
     */
    private $varType = IDumperManager::T_UNKNOWN;

    /**
     * Manažer zpracování pro dumpování.
     *
     * @var IDumperManager
     */
    private $manager;

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager(IDumperManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    public function setType($types)
    {
        if (!is_array($types)) {
            $types = array($types);
        }

        $this->varType = array_flip($types);

        return $this;
    }

    public function getType()
    {
        return array_flip($this->varType);
    }

    public function verify($var, $varType, array $replacedClasses = array())
    {
        return isset($this->varType[$varType])
               && !isset($replacedClasses['\\' . get_class($this)])
               && $this->verifyCustomCondition($var);
    }

    /**
     * Metoda k podědění, kde se nastaví dodatečná podmínka pro zpracování tímto objektem.
     *
     * Pokud vrátí true, tento objekt proměnnou zpracuje, jinak ne.
     *
     * @param mixed $var Variable
     * @return boolean
     */
    abstract protected function verifyCustomCondition($var);

    /**
     * Odstraní manažeru kvůli cyklickému propojení.
     */
    public function __destruct()
    {
        unset($this->manager);
    }

    /**
     * @return array
     */
    public function getReplacedClasses()
    {
        return array();
    }

}
