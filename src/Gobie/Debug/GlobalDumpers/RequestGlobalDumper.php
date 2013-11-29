<?php

namespace Gobie\Debug\GlobalDumpers;

/**
 * Dumper pro HTTP request.
 */
class RequestGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        $request = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $request[substr($key, 5)] = $value;
            }
        }
        ksort($request);

        return $request;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'Request';
    }
}
