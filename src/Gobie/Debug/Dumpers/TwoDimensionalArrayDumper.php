<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * TwoDimensionalArrayDumper.
 */
class TwoDimensionalArrayDumper extends ArrayDumper
{
    public function getReplacedClasses()
    {
        return array(
            '\Gobie\Debug\Dumpers\ArrayDumper' => true
        );
    }

    public function dump(&$var, $level, $depth)
    {
        $out = array();

        $out[] = '<table><tr><th><b>array[' . count($var) . '][' . count(reset($var)) . ']</b></th>';
        foreach (reset($var) as $key => $_) {
            $out[] = '<th>' . Helpers::encodeKey($key) . '</th>';
        }
        $out[] = '</tr>';

        foreach ($var as $key => &$arr) {
            $out[] = '<tr><th>' . Helpers::encodeKey($key) . '</th>';
            foreach ($arr as &$value) {
                $dValue = $this->getManager()->dump($value, 1, $depth - 1);
                $out[]  = '<td style="white-space: pre-wrap;">' . $dValue . '</td>';
            }
            $out[] = '</tr>';
        }

        $out[] = '</table>';

        return implode('', $out);
    }

    protected function verifyCustomCondition($var)
    {
        $keys = null;
        foreach ($var as $row) {
            if (!is_array($row)) {
                return false;
            }

            if (isset($keys)) {
                return $keys === array_keys($row);
            }

            foreach ($row as $col) {
                if ($col !== null && !is_scalar($col)) {
                    break 2;
                }
            }

            $keys = array_keys($row);
        }

        return false;
    }
}