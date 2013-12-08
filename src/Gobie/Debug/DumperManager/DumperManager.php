<?php

namespace Gobie\Debug\DumperManager;

use Gobie\Debug\Dumpers\IDumper;

/**
 * DumperManager for variables.
 *
 * Known types:
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
     * Known data types.
     *
     * Array of DumperManager::T_* constants.
     *
     * @var array
     */
    protected $knownTypes = array(
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
     * Sets known types, dumpers and detects CLI/CGI.
     *
     * @param array $dumpers Array of dumpers
     */
    public function __construct(array $dumpers = array())
    {
        $this->isHtml = PHP_SAPI !== 'cli'
                        && !preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list()));

        foreach ($dumpers as $dumper) {
            $this->addDumper($dumper);
        }

    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException If dumper is not one of known types.
     */
    public function addDumper(IDumper $dumper)
    {
        $varTypeArr = $dumper->getType();

        if (!is_array($varTypeArr)) {
            throw new \InvalidArgumentException("IDumper::getType must return array of types it can dump.");
        }

        foreach ($varTypeArr as $varType) {
            if (!isset($this->knownTypes[$varType])) {
                throw new \InvalidArgumentException("Type '{$varType}' is unknown.");
            }

            $dumper->setManager($this);

            if (!isset($this->dumpers[$varType])) {
                $this->dumpers[$varType] = array();
            }
            if (in_array($dumper, $this->dumpers[$varType], true)) {
                break;
            }
            $this->dumpers[$varType][] = $dumper;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDumpers()
    {
        $out = array();
        array_walk_recursive(
            $this->dumpers,
            function ($value) use (&$out) {
                $out[] = $value;
            }
        );

        return $out;
    }

    /**
     * {@inheritdoc}
     */
    public function dump($var, $level = 1, $depth = 4)
    {
        $out             = array();
        $varType         = gettype($var);
        $dumpers         = isset($this->dumpers[$varType]) ? $this->dumpers[$varType] : array();
        $replacedClasses = array();

        /** @var $dumper IDumper */
        foreach ($dumpers as $dumper) {
            if (!$dumper->verify($var, $varType, $replacedClasses)) {
                continue;
            }

            $out[] = $dumper->dump($var, $level, $depth);
            $replacedClasses += $dumper->getReplacedClasses();
        }

        if (count($out) === 0) {
            throw new \RuntimeException("There is no registered dumper for type '{$varType}'.");
        }

        if ($this->isHtml) {
            return implode('', $out);
        }

        return htmlspecialchars_decode(strip_tags(implode('', $out)), ENT_QUOTES);
    }
}
