<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Abstract class for dumpers.
 */
abstract class AbstractDumper implements IDumper
{

    /**
     * Variable type which this dumper can dump.
     *
     * @var string
     * @see IDumperManager
     */
    private $type = IDumperManager::T_UNKNOWN;

    /**
     * DumperManager used for dumping subvalues.
     *
     * @var IDumperManager
     */
    private $manager;

    /**
     * @return IDumperManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param IDumperManager $manager
     * @return $this
     */
    public function setManager(IDumperManager $manager)
    {
        if ($this->manager && $this->manager !== $manager) {
            throw new \LogicException('Dumper has already different DumperManager set.');
        }
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return array
     */
    public function getType()
    {
        return array_flip($this->type);
    }

    /**
     * @param string $types
     * @return $this
     */
    public function setType($types)
    {
        if (!is_array($types)) {
            $types = func_get_args();
        }

        $this->type = array_flip($types);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function canDump($var)
    {
        return true;
    }

    /**
     * Odstraní manažeru kvůli cyklickému propojení.
     */
    public function __destruct()
    {
        unset($this->manager);
    }
}
