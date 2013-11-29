<?php

namespace Gobie\Debug\Message\Sql;

use Gobie\Debug\Debug;
use Gobie\Debug\Highlighters\IHighlighter;
use Gobie\Debug\Message\CallstackMessage;

/**
 * Třída pro zprávy obsahující SQL dotaz.
 */
abstract class AbstractMessage extends CallstackMessage
{

    /**
     * SQL dotaz.
     *
     * @var string
     */
    protected $sql;

    /**
     * Pokud je dotaz SELECT.
     *
     * @var boolean
     */
    protected $isSelect;

    /**
     * SQL resource spojení.
     *
     * @var mixed
     */
    protected $resource;

    /**
     * Doba zpracování v mikrosekundách.
     *
     * @var string
     */
    private $time;

    /**
     * Zvýrazňovač syntaxe.
     *
     * @var IHighlighter
     */
    private $highlighter;

    /**
     * Nastaví SQL dotaz, callstack a čas zpracování (z timeru), pokud byl nějaký zadán.
     *
     * @param Debug  $debug     Debug
     * @param mixed  $resource  Databázové spojení
     * @param string $sql       SQL dotaz
     * @param array  $callstack CallstackMessage
     * @param array  $time      Čas zpracování {@see \Gobie\Debug\Debug::timerGet)
     */
    public function __construct(Debug $debug, $resource, $sql, array $callstack, array $time = null)
    {
        parent::__construct($debug, $callstack);
        $this->resource = $resource;
        $this->sql      = trim($sql);
        $this->isSelect = substr($this->sql, 0, 6) === 'SELECT';
        if ($time !== null) {
            $this->time = $time[0];
        }
    }

    /**
     * Nastaví zvýrazňovač syntaxe.
     *
     * @param IHighlighter $highlighter Zvýrazňovač syntaxe
     * @return AbstractMessage
     */
    public function setHighlighter(IHighlighter $highlighter)
    {
        $this->highlighter = $highlighter;

        return $this;
    }

    /**
     * Vrátí dobu trvání SQL dotazu.
     *
     * Pod 1ms -> ~0ms, řešit rychlost takovýchto dotazů je plýtvání časem!
     *
     * @return mixed
     */
    protected function getTime()
    {
        return intval(round($this->time * 1000)) ? : '<1';
    }

    /**
     * Vrátí zvýrazněný SQL dotaz.
     *
     * @return string
     */
    protected function getSql()
    {
        return $this->highlighter->highlight($this->sql);
    }

    /**
     * Vrátí URL pro vykonání dotazu.
     *
     * @return string
     */
    protected function getUrlForSqlExecution()
    {
        return $this->getDebug()->replaceLinkParam('query', urlencode($this->sql));
    }

    /**
     * Vrátí URL pro vysvětlení dotazu.
     *
     * Funguje, pouze pro SELECTy.
     *
     * @return string
     */
    protected function getUrlForSqlExplain()
    {
        if (!$this->isSelect) {
            return '';
        }

        return $this->getDebug()->replaceLinkParam('query', urlencode('EXPLAIN EXTENDED ' . $this->sql));
    }

    /**
     * Vrátí URL pro profilování dotazu.
     *
     * Funguje, pouze pro SELECTy.
     *
     * @return string
     */
    protected function getUrlForSqlProfiling()
    {
        $sql = $this->sql;
        if ($this->isSelect) {
            $sql = 'SELECT SQL_NO_CACHE ' . substr($sql, 6);
        }
        $sql .= $sql[mb_strlen($sql) - 1] !== ';' ? ';' : '';
        $encodedQuery = urlencode("SET profiling = 1;\n" . $sql . "\nSHOW PROFILES;\nSHOW PROFILE;");

        return $this->getDebug()->replaceLinkParam('query', $encodedQuery);
    }

    /**
     * Vrací textovou podobu typu databázového spojení.
     *
     * @return string
     */
    protected function renderSqlType()
    {
        $dbConfig = $this->resource->getConfig();
        $driver   = strtolower($dbConfig['driver']);

        if ($driver === 'pdo') {
            $dsnInfo = explode(':', $dbConfig['dsn']);
            $driver .= '-' . $dsnInfo[0];
        }

        return 'SQL [' . $driver . ']';
    }
}
