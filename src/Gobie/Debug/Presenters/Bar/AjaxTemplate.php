<?php

namespace Gobie\Debug\Presenters\Bar;

use Gobie\Debug\Message\ErrorMessage;

/**
 * AJAXová šablona Debugu.
 */
class AjaxTemplate extends BaseTemplate
{

    /**
     * Maximální možná délka všech hlaviček.
     *
     * Maximum pro cross-browser funkcionalitu.
     * Nejmenší velikost povoluje Chrome.
     *
     * @var integer
     */
    protected $responseMaxLen = 256e3;

    /**
     * Maximální možná délka jedné hlavičky.
     *
     * Maximum pro cross-browser funkcionalitu.
     *
     * @var integer
     */
    protected $responseLineMaxLen = 5e3;

    public function render()
    {
        $msg = static::compressData($this->getMessages());

        $msgLength = strlen($msg);
        if ($msgLength > $this->responseMaxLen) {
            $pattern = 'Data překročila limit pro zaslání hlavičkami (%s/%s)b. Omezte výpisy argumentů/callstacků.';
            $exc     = new \RuntimeException(sprintf($pattern, $msgLength, $this->responseMaxLen));
            $err     = new ErrorMessage($this->getDebug(), get_class($exc), $exc->getFile(), $exc->getLine(), $exc->getMessage(), $exc->getTrace());
            $msg     = static::compressData(array($err->dump()));
        }

        $parts      = explode(PHP_EOL, chunk_split($msg, $this->responseLineMaxLen, PHP_EOL));
        $countParts = count($parts);
        for ($usedParts = 0, $i = 0; $i < $countParts; ++$i) {
            if ($parts[$i]) {
                $this->setHeader('X-Debug-' . $usedParts++, $parts[$i]);
            }
        }

        $this->setHeader('X-Debug-Count', $usedParts);
    }

    /**
     * Nastaví odpovědní hlavičku HTTP požadavku.
     *
     * @param string $name  Název hlavičky
     * @param string $value Hodnota
     * @return self
     */
    protected function setHeader($name, $value)
    {
        header($name . ': ' . $value);

        return $this;
    }
}
