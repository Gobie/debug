<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;
use Gobie\Debug\Options;

/**
 * Dumper objektů.
 */
class ObjectDumper extends AbstractDumper
{

    /**
     * Nastaví typ proménné na 'object'.
     */
    public function __construct()
    {
        $this->setType(DumperManager::T_OBJECT);
    }

    public function dump(&$var, $level, $depth)
    {
        static $recursion = array();

        $objHash      = spl_object_hash($var);
        $shortObjHash = substr(md5($objHash), 0, 4);
        if (isset($recursion[$objHash])) {
            return
                '**RECURSION** <span class="dump_arg_class">'
                . get_class($var)
                . '</span> <span class="dump_arg_desc">#'
                . $shortObjHash
                . '</span>';
        }
        $recursion[$objHash] = true;

        $out   = array();
        $out[] = '<span class="dump_arg_class">' . get_class($var) . '</span>';
        $out[] = ' <span class="dump_arg_desc">#' . $shortObjHash . '</span>';

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

    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $indentation = Helpers::indent($level);
        $skipModifiers = $this->getManager()->getDebug()->getOptions()->get(Options::SKIP_PROPERTY_MODIFIERS);

        $reflector       = new \ReflectionObject($var);
        $properties      = $reflector->getProperties(~$skipModifiers);
        $propertiesCount = count($properties);
        $out[]           = ' <span class="dump_arg_desc">(' . $propertiesCount . ')</span>';
        if (!$propertiesCount) {
            return true;
        }

        $pos   = 1;
        $out[] = PHP_EOL;
        foreach ($properties as $property) {
            $modifiers = $property->getModifiers();
            if ($modifiers & $skipModifiers) {
                continue;
            }

            /* @var $property \ReflectionProperty */
            $property->setAccessible(true);
            $value         = $this->getManager()->dump($property->getValue($var), $level + 1, $depth - 1);
            $propertyName  = Helpers::encodeKey($property->getName());
            $modifierNames = implode(' ', \Reflection::getModifierNames($modifiers));

            $out[] = $indentation . $propertyName . ':<span class="dump_arg_access">' . $modifierNames . '</span>';
            $out[] = '<span class="dump_arg_keyword"> => </span>' . $value;

            // Ošetření posledního řádku výpisu
            if ($pos !== $propertiesCount || ($pos === $propertiesCount && $depth === 0)) {
                $out[] = PHP_EOL;
            }
            ++$pos;
        }

        return true;
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}
