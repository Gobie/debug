<?php

namespace Gobie\Debug\Message;

use Gobie\Debug\Debug;

/**
 * Třída pro zprávy obsahující PHP chyby či výjimky.
 */
class ErrorMessage extends CallstackMessage
{

    /**
     * Typ chyby nebo název výjimky.
     *
     * @var string
     */
    private $type;

    /**
     * Soubor, kde se chyba vyskytla.
     *
     * @var string
     */
    private $file;

    /**
     * Číslo řádku.
     *
     * @var integer
     */
    private $line;

    /**
     * Zpráva s chybou.
     *
     * @var array
     */
    private $message;

    /**
     * Nastaví typ chybové zprávy, soubor a řádek s chybou, zprávu a callstack.
     *
     * @param Debug   $debug     Debug
     * @param string  $type      Typ chyby
     * @param string  $file      Soubor s chybou
     * @param integer $line      Číslo řádku s chybou
     * @param string  $message   Zpráva s chybou
     * @param array   $callstack CallstackMessage
     */
    public function __construct(Debug $debug, $type, $file, $line, $message, array $callstack)
    {
        parent::__construct($debug, $callstack);

        $this->type    = $type;
        $this->file    = $file;
        $this->line    = $line;
        $this->message = $message;
    }

    /**
     * Vrátí soubor, kde se chyba vyskytla.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Vrátí číslo řádku.
     *
     * @return integer
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Vrátí callstack případně obohacený o řádek, kde byla chyba vyvolána.
     *
     * @return array
     */
    public function getCallstack()
    {
        $callstack  = parent::getCallstack();
        $firstTrace = reset($callstack);
        $firstFile  = isset($firstTrace['file']) ? $firstTrace['file'] : '';
        $firstLine  = isset($firstTrace['line']) ? $firstTrace['line'] : '';
        if ($firstFile != $this->file || $firstLine != $this->line) {
            $callstackItem = array(
                'file'    => $this->file,
                'line'    => $this->line,
                'message' => $this->type
            );
            array_unshift($callstack, $callstackItem);
        }

        return $callstack;
    }

    /**
     * Vrátí pole hodnot pro prezentační vrstvu.
     *
     * @see Message::dump
     * @return array
     */
    public function dump()
    {
        $settings = array(
            'type'    => $this->getType(),
            'message' => static::toUtf8(static::fixLinksToManual($this->getMessage())),
            'content' => static::toUtf8($this->getCallstackDump())
        );

        return array_merge(parent::dump(), $settings);
    }

    /**
     * Vrátí typ chyby nebo název výjimky.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Vrátí zprávu s chybou.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
