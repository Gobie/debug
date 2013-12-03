<?php

namespace Gobie\Debug\Message\Start;

use Gobie\Debug\Debug;
use Gobie\Debug\Message\Message;
use Gobie\Debug\Message\Start\GlobalDumpers\IGlobalDumper;
use Gobie\Debug\Options;

/**
 * Třída pro zprávy, které oznamují začátek zpracování stránky.
 *
 * Možnost zobrazit globální dumpy ve StartMessage zprávě ve formě IGlobalDumper.
 */
class StartMessage extends Message
{

    private $globalDumpers;

    private $globalDumpersDump;

    /**
     * Nastaví dumpy, které by se neměli zobrazovat.
     *
     * @param Debug $debug         Debug
     * @param array $globalDumpers Pole globálních dumperů
     */
    public function __construct(Debug $debug, array $globalDumpers = array())
    {
        parent::__construct($debug);

        $this->globalDumpers     = $globalDumpers;
        $this->globalDumpersDump = $this->dumpGlobalDumpers($globalDumpers);
    }

    /**
     * @return array
     */
    private function dumpGlobalDumpers($globalDumpers)
    {
        $hideEmpty = $this->getDebug()->getOptions()->get(Options::HIDE_EMPTY_GLOBAL_DUMPERS);

        $messageData = $contentData = array();
        /* @var $globalDumper IGlobalDumper */
        foreach ($globalDumpers as $globalDumper) {
            $data = $globalDumper->getData();
            if ($hideEmpty && !$data) {
                continue;
            }
            $messageData[] = $globalDumper->getName() . ' (' . count($data) . ')';
            $contentData[] = $this->getDebug()->dumpVariable($data);
        }

        return array('message' => $messageData, 'content' => $contentData);
    }

    public function getGlobalDumpers()
    {
        return $this->globalDumpers;
    }

    public function dump()
    {
        return array_merge(parent::dump(), $this->globalDumpersDump);
    }
}
