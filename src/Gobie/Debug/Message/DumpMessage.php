<?php

namespace Gobie\Debug\Message;

use Gobie\Debug\Debug;

/**
 * Zpráva obsahující název a dump předaných proměnných s callstackem.
 */
class DumpMessage extends CallstackMessage
{

    /**
     * Název dumpu.
     *
     * @var string
     */
    private $name;

    /**
     * Pole proměnných.
     *
     * @var array
     */
    private $variables;

    /**
     * Dumpnuté pole proměnných.
     *
     * @var string
     */
    private $variableDumps;

    /**
     * Nastaví název dumpu, callstack a až bude nasetován Debug, dumpne proměné.
     *
     * @param Debug $debug     Debug
     * @param mixed $name      Název dumpu
     * @param array $variables Pole proměných k dumpnutí
     * @param array $callstack CallstackMessage
     */
    public function __construct(Debug $debug, $name, array $variables, array $callstack)
    {
        parent::__construct($debug, $callstack);

        $this->name          = $name;
        $this->variables     = $variables;
        $this->variableDumps = $this->dumpVariables($variables);
    }

    /**
     * @param $variables
     * @return array
     */
    private function dumpVariables($variables)
    {
        $variableDumps = array();
        foreach ($variables as $variable) {
            $variableDumps[] = $this->getDebug()->dumpVariable($variable);
        }

        return $variableDumps;
    }

    /**
     * Vrátí pole hodnot pro prezentační vrstvu.
     *
     * @see Message::dump
     * @return array
     */
    public function dump()
    {
        $name = $this->getName();
        if ($name === null) {
            $callstack = $this->getCallstack();
            $trace     = reset($callstack);
            $name      = $trace['file'] . ':' . $trace['line'];
        }

        $settings = array(
            'message' => static::translateFilePath($name) . ' (dumps: ' . count($this->getVariableDumps()) . ')',
            'content' => array(
                'variables' => $this->getVariableDumps(),
                'callstack' => $this->getCallstackDump()
            )
        );

        return array_merge(parent::dump(), $settings);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return string
     */
    public function getVariableDumps()
    {
        return $this->variableDumps;
    }


}
