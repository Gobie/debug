<?php

namespace Gobie\Debug\Message\Sql\Highlighters;

use Gobie\Debug\Debug;

/**
 * SQL syntax highlighting and formatting using \SqlFormatter.
 *
 * @link https://github.com/jdorn/sql-formatter
 */
class SqlFormatterHighlighter extends NativeHighlighter
{
    /**
     * Nastavení databázového spojení.
     *
     * @param Debug  $debug        Debug
     * @param string $databaseType Typ databáze
     */
    public function __construct(Debug $debug, $databaseType, $replacePatterns = self::PATTERN_ALL)
    {
        if (!class_exists('\SqlFormatter')) {
            throw new \RuntimeException('\SqlFormatter class cannot be found');
        }

        if ($replacePatterns === self::PATTERN_ALL) {
            $replacePatterns = self::PATTERN_ALL & ~self::PATTERN_REMOVE_INDENTATION;
        }

        parent::__construct($debug, $databaseType, $replacePatterns);
    }

    /**
     * {@inheritdoc}
     * @param string $sql SQL query
     */
    public function highlight($sql)
    {
        return parent::highlight(\SqlFormatter::format($sql, false));
    }
}
