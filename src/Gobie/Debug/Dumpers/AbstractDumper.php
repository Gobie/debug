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
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array_flip($this->type);
    }

    /**
     * {@inheritdoc}
     */
    public function setTypes($types)
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
     * Removes DumperManager to break cyclic reference.
     */
    public function __destruct()
    {
        unset($this->manager);
    }
}
