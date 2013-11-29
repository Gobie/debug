<?php

namespace Gobie\Debug\Dumpers;

/**
 * Abstraktní třída pro všechny dumpery.
 */
abstract class AbstractDumper implements IDumper
{

    /**
     * Typ proměnné, pro kterou je tento objekt registrován.
     *
     * @var string
     * @see DumperManager::$allowedTypes
     */
    private $varType = DumperManager::T_UNKNOWN;

    /**
     * Manažer zpracování pro dumpování.
     *
     * @var DumperManager
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

    public function verify($var, $varType, array $usedDumperClasses = array())
    {
        return isset($this->varType[$varType])
               && !isset($usedDumperClasses['\\' . get_class($this)])
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
