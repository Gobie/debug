<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\DumperManager\IDumperManager;
use Gobie\Debug\Helpers;

/**
 * Dumper resource.
 */
class ResourceDumper extends AbstractDumper
{

    private static $resources = array(
        'stream'         => 'stream_get_meta_data',
        'stream-context' => 'stream_context_get_options',
        'curl'           => 'curl_getinfo'
    );

    /**
     * Nastaví typ proménné na 'resource'.
     */
    public function __construct()
    {
        $this->setType(IDumperManager::T_RESOURCE);
    }

    public function dump(&$var, $level, $depth)
    {
        $indentation = Helpers::indent($level);
        $resType     = get_resource_type($var);

        $out   = array();
        $out[] = '<b>resource #' . Helpers::escape($resType) . '</b>';

        if (isset(self::$resources[$resType])) {
            $result = call_user_func_array(self::$resources[$resType], array($var));

            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $key   = Helpers::encodeKey($key);
                    $value = $this->getManager()->dump($value, $level + 1, $depth - 1);
                    $out[] = PHP_EOL . $indentation . $key . '<span class="dump_arg_keyword"> =&gt; </span>' . $value;
                }
            } else {
                $out[] = PHP_EOL . $indentation . $this->getManager()->dump($result);
            }
        }

        return implode('', $out);
    }

    protected function verifyCustomCondition($var)
    {
        return true;
    }
}
