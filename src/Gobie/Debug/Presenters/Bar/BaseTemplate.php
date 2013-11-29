<?php

namespace Gobie\Debug\Presenters\Bar;

use Gobie\Debug\Debug;

/**
 * Abstraktní šablona pro Debug.
 */
abstract class BaseTemplate implements ITemplate
{

    /**
     * Pole zpráv.
     *
     * @var array
     */
    private $messages = array();

    private $debug;

    /**
     * Zkomprimuje data pomocí JSONu, gzipu a base64.
     *
     * @param string $data Data ke komprimaci
     * @return string
     */
    protected static function compressData($data)
    {
        return base64_encode(gzcompress(static::jsonEncode($data), 7));
    }

    /**
     * Vyescapuje proměnnou pro výstup do JS.
     *
     * @param mixed $val Hodnota k vyescapování
     * @return string Vyescapovaná hodnota
     * @throws \UnexpectedValueException Při chybě během převodu do JSONu
     */
    protected static function jsonEncode($val)
    {
        $out = json_encode($val);
        if ($out === false) {
            $error = json_last_error();
            switch ($error) {
                case JSON_ERROR_UTF8:
                    throw new \UnexpectedValueException('Vstup není v UTF-8');
                case JSON_ERROR_SYNTAX:
                    throw new \UnexpectedValueException('Syntaktická chyba');
                case JSON_ERROR_CTRL_CHAR:
                    throw new \UnexpectedValueException('Nekorektně vyescapovaná kontrolní sekvence');
                case JSON_ERROR_STATE_MISMATCH:
                    throw new \UnexpectedValueException('Nekorektní vstup');
                case JSON_ERROR_DEPTH:
                    throw new \UnexpectedValueException('Byla dosažena maximální hloubka zanoření pro výpis');
                default:
                    throw new \UnexpectedValueException('Neznámá chyba při parsování JSONu');
            }
        }

        return $out;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @return Debug
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param Debug $debug
     * @return $this
     */
    public function setDebug(Debug $debug)
    {
        $this->debug = $debug;

        return $this;
    }

}
