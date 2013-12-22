<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;
use Gobie\Debug\Helpers;

/**
 * Default object dumper.
 */
class ObjectDumper extends AbstractDumper
{

    /**
     * Bit mask of \ReflectionProperty constants.
     *
     * @var integer
     */
    protected $skipModifiers;

    /**
     * Sets type and property modifiers to skip.
     *
     * e.g. \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED
     *
     * @param integer $skipModifiers Bit mask of \ReflectionProperty constants; defaults to 0 = show all properties.
     */
    public function __construct($skipModifiers = 0)
    {
        $this->setTypes(IDumperManager::T_OBJECT);
        $this->skipModifiers = (int) $skipModifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(&$var, $level = 1, $depth = 4)
    {
        static $recursion = array();

        $objHash      = spl_object_hash($var);
        $shortObjHash = substr(md5($objHash), 0, 4);

        $out   = array();
        $out[] =
            '<span class="dump_arg_class">'
            . get_class($var)
            . '</span> <span class="dump_arg_desc">#'
            . $shortObjHash
            . '</span>';

        if (isset($recursion[$objHash])) {
            return '**RECURSION** ' . implode('', $out);
        }
        $recursion[$objHash] = true;

        $reflector       = new \ReflectionObject($var);
        $parentReflector = $reflector;
        while ($parentReflector = $parentReflector->getParentClass()) {
            $out[] = ' extends <span class="dump_arg_class">' . $parentReflector->getName() . '</span>';
        }

        $interfaces = $reflector->getInterfaceNames();
        if ($interfaces) {
            $out[] =
                ' implements <span class="dump_arg_interface">'
                . implode('</span>, <span class="dump_arg_interface">', $interfaces)
                . '</span>';
        }

        if (method_exists($reflector, 'getTraitNames')) {
            $traits = $reflector->getTraitNames();
            if ($traits) {
                $out[] =
                    ' uses <span class="dump_arg_trait">'
                    . implode('</span>, <span class="dump_arg_trait">', $traits)
                    . '</span>';
            }
        }

        if ($depth === 0) {
            $out[] = ' ...';
        } else {
            $this->dumpBody($var, $level, $depth, $out);
        }

        unset($recursion[$objHash]);

        return implode('', $out);
    }

    /**
     * Dumps body of the object.
     *
     * It is intended to be overridden in subclasses.
     *
     * @param mixed   $var   Variable
     * @param integer $level Current level of indentation
     * @param integer $depth Max depth of variable dump
     * @param array   $out   Array of output
     */
    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $reflector       = new \ReflectionObject($var);
        $properties      = $reflector->getProperties(~$this->skipModifiers);
        $propertiesCount = count($properties);
        $out[]           = ' <span class="dump_arg_desc">(' . $propertiesCount . ')</span>';

        if (!$propertiesCount) {
            return;
        }

        $pos         = 1;
        $out[]       = PHP_EOL;
        $indentation = Helpers::indent($level);

        /* @var $property \ReflectionProperty */
        foreach ($properties as $property) {
            $modifiers = $property->getModifiers();
            if ($modifiers & $this->skipModifiers) {
                continue;
            }

            $property->setAccessible(true);
            $value         = $this->getManager()->dump($property->getValue($var), $level + 1, $depth - 1);
            $propertyName  = Helpers::encodeKey($property->getName());
            $modifierNames = implode(' ', \Reflection::getModifierNames($modifiers));

            $out[] = $indentation . $propertyName . ':<span class="dump_arg_access">' . $modifierNames . '</span>';
            $out[] = '<span class="dump_arg_keyword"> => </span>' . $value;

            // Last line fix
            if ($pos !== $propertiesCount || ($pos === $propertiesCount && $depth === 0)) {
                $out[] = PHP_EOL;
            }
            ++$pos;
        }
    }
}
