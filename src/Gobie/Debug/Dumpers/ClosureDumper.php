<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * Dumper Closure.
 */
class ClosureDumper extends ObjectDumper
{

    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $indentation = Helpers::indent($level);

        $params    = array();
        $rFunction = new \ReflectionFunction($var);
        foreach ($rFunction->getParameters() as $param) {
            $params[] = '$' . $param->getName();
        }

        $input = array(
            'file'             => $rFunction->getFileName() . ':' . $rFunction->getStartLine(),
            'params'           => $params,
            'static variables' => $rFunction->getStaticVariables()
        );

        foreach ($input as $key => $value) {
            $key   = Helpers::encodeKey($key);
            $value = $this->getManager()->dump($value, $level + 1, $depth - 1);
            $out[] = PHP_EOL . $indentation . $key . '<span class="dump_arg_keyword"> =&gt; </span>' . $value;
        }

        return true;
    }

    public function canDump($var)
    {
        return $var instanceof \Closure;
    }
}
