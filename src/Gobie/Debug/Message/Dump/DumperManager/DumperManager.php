<?php

namespace Gobie\Debug\Message\Dump\DumperManager;

use Gobie\Debug\Message\Dump\Dumpers\IDumper;

/**
 * Manažer pro dumpování proměnných.
 *
 * Povolené typy:
 * <pre>
 * boolean
 * integer
 * double
 * string
 * array
 * object
 * resource
 * NULL
 * unknown type
 * </pre>
 *
 * Vytvoření a nastavení objektu.
 * <code>
 * $dumperManager = new \Gobie\Debug\Message\Dump\Dumpers\DumperManager();
 * $dumperManager->addDumper(\Gobie\Debug\Message\Dump\Dumpers\ArrayDumper());
 * </code>
 *
 * Dumpnutí proměnné
 * <code>
 * echo $dumperManager->dump($_SERVER[, $level][, $depth]);
 * </code>
 */
class DumperManager implements IDumperManager
{

    /**
     * Povolené datové typy.
     *
     * @var array
     */
    protected static $allowedTypes = array(
        self::T_BOOLEAN  => true,
        self::T_INTEGER  => true,
        self::T_DOUBLE   => true,
        self::T_STRING   => true,
        self::T_ARRAY    => true,
        self::T_OBJECT   => true,
        self::T_RESOURCE => true,
        self::T_NULL     => true,
        self::T_UNKNOWN  => true
    );

    /**
     * Seznam aktuálně namapovaných Dumperů.
     *
     * @var array
     */
    protected $dumpers = array();

    private $isHtml;

    public function __construct()
    {
        $this->isHtml = PHP_SAPI !== 'cli'
                        && !preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list()));
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException Pokud není typ dumperu jedním z výčtu povolených
     */
    public function addDumper(IDumper $dumper)
    {
        $varTypeArr = $dumper->getType();

        foreach ($varTypeArr as $varType) {
            if (!isset(self::$allowedTypes[$varType])) {
                throw new \InvalidArgumentException(sprintf('Typ "%s" není povolen.', $varType));
            }

            $dumper->setManager($this);

            if (!isset($this->dumpers[$varType])) {
                $this->dumpers[$varType] = array();
            }
            $this->dumpers[$varType][] = $dumper;
        }

        return $this;
    }

    public function dump($var, $level = 1, $depth = 4)
    {
        $out         = array();
        $varType     = gettype($var);
        $dumpers     = isset($this->dumpers[$varType]) ? $this->dumpers[$varType] : array();
        $usedDumpers = array();

        /** @var $dumper IDumper */
        foreach ($dumpers as $dumper) {
            if (!$dumper->verify($var, $varType, $usedDumpers)) {
                continue;
            }

            $out[]       = $dumper->dump($var, $level, $depth);
            $usedDumpers = array_merge($usedDumpers, $dumper->getReplacedClasses());
        }

        if (count($out) === 0) {
            $out[] = 'Pro datový typ "' . $varType . '" není definován žádný Dumper.';
        }

        if ($this->isHtml) {
            return implode('', $out);
        }

        return htmlspecialchars_decode(strip_tags(implode('', $out)), ENT_QUOTES);
    }
}
