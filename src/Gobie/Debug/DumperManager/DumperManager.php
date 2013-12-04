<?php

namespace Gobie\Debug\DumperManager;

use Gobie\Debug\Dumpers\IDumper;

/**
 * DumperManager for variables.
 *
 * Allowed types:
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
 * DumperManager initialization.
 * <code>
 * $dumperManager = new \Gobie\Debug\Dumpers\DumperManager();
 * $dumperManager->addDumper(\Gobie\Debug\Dumpers\StringDumper());
 * $dumperManager->addDumper(\Gobie\Debug\Dumpers\IntegerDumper());
 * $dumperManager->addDumper(\Gobie\Debug\Dumpers\ArrayDumper());
 * </code>
 *
 * Variable dump
 * <code>
 * echo $dumperManager->dump($_GET);
 * </code>
 */
class DumperManager implements IDumperManager
{

    /**
     * Allowed data types.
     *
     * Array of DumperManager::T_* constants.
     *
     * @var array
     */
    protected $allowedTypes;

    /**
     * List of available dumpers.
     *
     * @var array
     */
    protected $dumpers;

    /**
     * Output is NOT cli, so hopefully HTML-like with support of CSS & JS.
     *
     * @var boolean
     */
    protected $isHtml;

    /**
     * Sets allowedTypes, dumpers and detects CLI/CGI.
     *
     * @param IDumper[] $dumpers Array of dumpers
     */
    public function __construct(array $dumpers = array())
    {
        $this->allowedTypes = array_flip(
            array(
                 self::T_BOOLEAN,
                 self::T_INTEGER,
                 self::T_DOUBLE,
                 self::T_STRING,
                 self::T_ARRAY,
                 self::T_OBJECT,
                 self::T_RESOURCE,
                 self::T_NULL,
                 self::T_UNKNOWN
            )
        );

        $this->isHtml = PHP_SAPI !== 'cli'
                        && !preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list()));

        $this->dumpers = $dumpers;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException If dumper is not one of allowed types.
     */
    public function addDumper(IDumper $dumper)
    {
        $varTypeArr = $dumper->getType();

        foreach ($varTypeArr as $varType) {
            if (!isset($this->allowedTypes[$varType])) {
                throw new \InvalidArgumentException("Type '{$varType}' isn't allowed.");
            }

            $dumper->setManager($this);

            if (!isset($this->dumpers[$varType])) {
                $this->dumpers[$varType] = array();
            }
            $this->dumpers[$varType][] = $dumper;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
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
            $out[] = "There is no registered dumper for type '{$varType}'.";
        }

        if ($this->isHtml) {
            return implode('', $out);
        }

        return htmlspecialchars_decode(strip_tags(implode('', $out)), ENT_QUOTES);
    }
}
