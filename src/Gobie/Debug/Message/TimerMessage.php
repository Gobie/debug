<?php

namespace Gobie\Debug\Message;

use Gobie\Debug\Debug;

/**
 * Třída pro zprávy timeru.
 */
class TimerMessage extends Message
{

    /**
     * Název časovače.
     *
     * @var string
     */
    private $name;

    /**
     * Doba trvání časovače.
     *
     * @var integer
     */
    private $time;

    /**
     * Využitá paměť.
     *
     * @var string
     */
    private $memory;

    /**
     * Maximální využití paměti.
     *
     * @var string
     */
    private $peak;

    /**
     * Nastaví název timeru, dobu trvání a spotřebovanou paměť.
     *
     * @param Debug   $debug  Debug
     * @param string  $name   Název časovače
     * @param integer $time   Doba trvání
     * @param integer $memory Využitá paměť
     */
    public function __construct(Debug $debug, $name, $time, $memory)
    {
        parent::__construct($debug);

        $this->name   = $name;
        $this->time   = number_format($time, 6, ',', ' ');
        $this->memory = number_format($memory, 0, ',', ' ');
        $this->peak   = number_format(memory_get_peak_usage(true), 0, ',', ' ');
    }

    public function dump()
    {
        $pattern  = '<b>%s</b> loaded in %ss; memory used %sb; memory peak %sb';
        $settings = array(
            'message' => sprintf($pattern, $this->getName(), $this->getTime(), $this->getMemory(), $this->getPeak())
        );

        return array_merge(parent::dump(), $settings);
    }

    /**
     * Vrátí název.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Vrátí dobu trvání časovače.
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Vrátí využití paměti.
     *
     * @return string
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * Vrátí maximální využití paměti.
     *
     * @return string
     */
    public function getPeak()
    {
        return $this->peak;
    }
}
