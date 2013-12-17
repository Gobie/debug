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
     * Array of IDumperManager::T_* constants.
     *
     * @var array
     */
    protected static $knownTypes = array(
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
        $types = $dumper->getTypes();

        if (!is_array($types)) {
            throw new \InvalidArgumentException("IDumper::getType must return array of types it can dump.");
        }

        foreach ($types as $type) {
            if (!isset(self::$knownTypes[$type])) {
                throw new \InvalidArgumentException("Type '{$type}' is unknown.");
            }
            if (!isset($this->dumpers[$type])) {
                $this->dumpers[$type] = array();
            }
            if (in_array($dumper, $this->dumpers[$type], true)) {
                break;
            }

            $this->dumpers[$type][] = $dumper;
        }

        $dumper->setManager($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDumpers()
    {
        $dumpers = array();
        array_walk_recursive(
            $this->dumpers,
            function ($value) use (&$dumpers) {
                $dumpers[] = $value;
            }
        );

        return $dumpers;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(&$var, $level = 1, $depth = 4)
    {
        $type = gettype($var);
        if (!isset($this->dumpers[$type])) {
            throw new \RuntimeException("There is no registered dumper for type '{$type}'.");
        }

        $out = array();
        /** @var $dumper IDumper */
        foreach ($this->dumpers[$type] as $dumper) {
            if (!$dumper->canDump($var)) {
                continue;
            }

            $out[] = $dumper->dump($var, $level, $depth);
        }

        if (count($out) === 0) {
            throw new \RuntimeException("No dumper capable of dumping variable '{$var}' of type '{$type}' found.");
        }

        if ($this->isHtml) {
            return implode('', $out);
        }

        return htmlspecialchars_decode(strip_tags(implode('', $out)), ENT_QUOTES);
    }
}
