<?php

namespace Gobie\Debug\Message;

use Gobie\Debug\Debug;

/**
 * Abstraktní třída pro všechny zprávy.
 */
abstract class Message implements IMessage
{

    /**
     * Unikátní identifikátor HTTP požadavku.
     *
     * @var string
     */
    private static $uniqueId;

    /**
     * Čas vytvoření zprávy.
     *
     * @var string
     */
    private $timestamp;

    /**
     * Stránka, ze které byl požadavek spuštěn.
     *
     * @var string
     */
    private $page;

    /**
     * Debug.
     *
     * @var Debug
     */
    private $debug;

    /**
     * Vytvoří unikátní id pro objekt zprávy z tohoto požadavku, zjistí čas vykonání a nastaví stránku.
     *
     * @param Debug $debug Debug
     * @throws \RuntimeException Pokud není zavedena mbstring extenze
     */
    public function __construct(Debug $debug)
    {
        if (!extension_loaded('mbstring')) {
            throw new \RuntimeException('mbstring extenze není načtena');
        }

        $this->debug = $debug;

        if (self::$uniqueId === null) {
            self::$uniqueId = md5(uniqid('debug_', true) . microtime(true));
        }

        $this->timestamp = isset($_SERVER['REQUEST_TIME_FLOAT'])
            ? $_SERVER['REQUEST_TIME_FLOAT']
            : sprintf('%.6f', microtime(true));

        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $this->page = $_SERVER['REQUEST_URI'];
        } elseif (array_key_exists('argv', $_SERVER)) {
            $this->page = implode(' ', $_SERVER['argv']);
        } else {
            $this->page = 'unknown';
        }
    }

    /**
     * Upraví odkazy do PHP dokumentace, aby fungovaly na proklik.
     *
     * @param string $val Chybová zpráva PHP
     * @return string Upravená chybová zpráva
     */
    protected static function fixLinksToManual($val)
    {
        return preg_replace("~(<a href=['\"])(.*?['\"])~", '\\1http://php.net/manual/en/\\2', $val);
    }

    /**
     * Odstraní z cesty kořenový adresář aplikace a převede ji na UNIX-like.
     *
     * @param string $path Cesta ke skriptu
     * @return string
     */
    protected static function translateFilePath($path)
    {
        $filePath = str_replace("\\", "/", $path);
        // @TODO REQUEST_URI for console
//        if (strpos($filePath, '/') !== false) {
//            $docRoot = realpath($_SERVER['DOCUMENT_ROOT']) . $_SERVER['REQUEST_URI'];
//            do {
//                $filePath    = str_replace($docRoot, "", $filePath, $count);
//                $lastDocRoot = $docRoot;
//                $docRoot     = realpath(dirname($docRoot));
//            } while (!$count && $lastDocRoot !== $docRoot);
//        }

        return $filePath;
    }

    /**
     * Převede řetězec do UTF-8.
     *
     * @staticvar array $validEncodings Pole platných cílových kódování
     * @staticvar array $allEncodings Pole všech detekovatelných kódování
     * @param string $text Text
     * @return string
     */
    protected static function toUtf8($text)
    {
        static $validEncodings = array('UTF-8', 'ASCII');
        static $allEncodings = null;
        if ($allEncodings === null) {
            $allEncodings = mb_list_encodings();
        }

        $encoding = mb_detect_encoding($text, $allEncodings);
        if (!in_array($encoding, $validEncodings)) {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }

        return $text;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function dump()
    {
        $page = $this->getPage();
        if (preg_match('@/[^/]*$@', $page, $matches)) {
            $page = $matches[0];
        }

        $namespaces = explode('\\', get_class($this));
        $className  = preg_replace('@^(\w+)Message$@', '\\1', end($namespaces));

        return array(
            'id'        => $this->getId(),
            'classType' => strtolower($className),
            'timestamp' => $this->getTimestamp(),
            'type'      => strtoupper($className),
            'message'   => '',
            'page'      => $page,
            'content'   => ''
        );
    }

    /**
     * Vrátí stránku, ze které byl požadavek spuštěn.
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Vrátí unikátní identifikátor HTTP požadavku.
     *
     * @return string
     */
    public function getId()
    {
        return self::$uniqueId;
    }

    /**
     * Vrátí čas vytvoření zprávy.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
