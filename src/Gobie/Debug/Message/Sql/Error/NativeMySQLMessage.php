<?php

namespace Gobie\Debug\Message\Sql\Error;

use Gobie\Debug\Debug;

/**
 * Třída pro zprávy chybného SQL dotazu v nativním ovladači.
 */
class NativeMySQLMessage extends AbstractMessage
{

    /**
     * Nastaví databázové spojení, SQL dotaz a callstack.
     *
     * @param Debug  $debug     Debug
     * @param mixed  $resource  Databázové spojení
     * @param string $sql       SQL dotaz
     * @param array  $callstack CallstackMessage
     */
    public function __construct(Debug $debug, $resource, $sql, array $callstack)
    {
        parent::__construct($debug, $resource, $sql, $callstack);

        $this->errno = mysql_errno($this->resource);
        $this->error = mysql_error($this->resource);
    }
}
