<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Dumper pro HTTP response.
 */
class ResponseGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        $response = $matches = array();
        foreach (headers_list() as $value) {
            preg_match('~^([^:]+):\s*(.+)$~', $value, $matches);
            $response[$matches[1]] = $matches[2];
        }
        ksort($response);

        return $response;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'Response';
    }
}
