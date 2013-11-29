<?php

namespace Gobie\Debug;

/**
 * Helpery.
 */
class Helpers
{

    /**
     * @param $var
     * @return string
     */
    public static function encodeKey($var)
    {
        return preg_match('@\s@S', $var) ? self::encodeString($var) : $var;
    }

    /**
     * Vyescapuje proměnnou.
     *
     * @param mixed $var Proměnná
     * @return string
     */
    public static function encodeString($var)
    {
        static $table;
        if ($table === null) {
            foreach (array_merge(range("\x00", "\x1F"), range("\x7F", "\xFF")) as $ch) {
                $table[$ch] = '\x' . str_pad(dechex(ord($ch)), 2, '0', STR_PAD_LEFT);
            }
            $table["\\"] = '\\\\';
            $table["\r"] = '\r';
            $table["\n"] = '\n';
            $table["\t"] = '\t';
        }

        if (preg_match('#[^\x20-\x7E\xA0-\x{10FFFF}]#uS', $var) || preg_last_error()) {
            $var = strtr($var, $table);
        }

        return '"' . self::escape($var) . '"';
    }

    /**
     * @param $var
     * @return string
     */
    public static function escape($var)
    {
        return htmlspecialchars($var, ENT_NOQUOTES, 'utf-8');
    }

    /**
     * Obalí řádky textu.
     *
     * @param string $text    Víceřádkový text
     * @param string $prepend Text, který se připojí na začátek každého řádku
     * @param string $append  Text, který se připojí na konec každého řádku
     * @return string
     */
    public static function wrapLines($text, $prepend, $append, $glue = PHP_EOL)
    {
        return implode(
            $glue,
            array_map(
                function ($line) use ($prepend, $append) {
                    return $prepend . $line . $append;
                },
                explode($glue, $text)
            )
        );
    }

    /**
     * @param $level
     * @return string
     */
    public static function indent($level)
    {
        return '<span class="dump_arg_indent">' . str_repeat('|  ', $level) . '</span>';
    }
}
