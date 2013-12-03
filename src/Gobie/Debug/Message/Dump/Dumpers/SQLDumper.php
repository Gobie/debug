<?php

namespace Gobie\Debug\Message\Dump\Dumpers;

use Gobie\Debug\Helpers;

/**
 * SQL dumper.
 */
class SQLDumper extends StringDumper
{

    private static $topLevelKeywords = array(
        // DML
        'CALL',
        'DELETE',
        'DO',
        'HANDLER',
        'INSERT',
        'LOAD',
        'REPLACE',
        'SELECT',
        'UPDATE',
        // DDL
        'ALTER',
        'CREATE',
        'DROP',
        'RENAME',
        'TRUNCATE',
        // DCL
        'COMMIT',
        'LOCK',
        'RELEASE',
        'ROLLBACK',
        'SAVEPOINT',
        'SET',
        'START',
        'UNLOCK',
    );

    public function __construct()
    {
        parent::__construct();
        $this->regex = '@^\s*(' . implode('|', self::$topLevelKeywords) . ')\b.@iS';
    }

    public function getReplacedClasses()
    {
        return array(
            '\Gobie\Debug\Message\Dump\Dumpers\StringDumper' => true
        );
    }

    public function dump(&$var, $level, $depth)
    {
        $sql = \SqlFormatter::format($var, false);

        $indentation = Helpers::indent($level);

        return parent::dump($var, $level, $depth) . PHP_EOL
               . $indentation . '<span class="dump_arg_desc">guessing SQL query</span>' . PHP_EOL
               . Helpers::wrapLines($sql, $indentation . '<span class="dump_arg_expanded">', '</span>');
    }

    protected function verifyCustomCondition($var)
    {
        return preg_match($this->regex, $var);
    }
}
