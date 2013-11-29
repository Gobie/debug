<?php

namespace Gobie\Debug\Message;

use Gobie\Debug\Debug;

/**
 * Třída pro zprávy XHProfu.
 */
class XHProfMessage extends Message
{

    /**
     * Data z XHProfu.
     *
     * @var array
     */
    private $data = array();

    /**
     * Cesta k XHProf libs.
     *
     * /usr/share/php5-xhprof/xhprof_lib/utils/
     *
     * @var string
     */
    private $xhprofLibPath;

    /**
     * URL k XHProf aplikaci.
     *
     * @var string
     */
    private $xhprofAppUrl;

    /**
     * Inicializace XHProfu.
     *
     * @param Debug  $debug         Debug
     * @param string $xhprofLibPath Cesta k XHProf libs
     * @param string $xhprofAppUrl  URL k XHProf aplikaci
     * @throws \RuntimeException Pokud nenní zavedena xhprof extenze
     */
    public function __construct(Debug $debug, $xhprofLibPath, $xhprofAppUrl)
    {
        if (!extension_loaded('xhprof')) {
            throw new \RuntimeException('XHProf extenze není načtena');
        }

        parent::__construct($debug);

        $this->xhprofLibPath = $xhprofLibPath;
        $this->xhprofAppUrl  = $xhprofAppUrl;
    }

    /**
     * Spustí XHProf.
     *
     * @return self
     */
    public function start()
    {
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

        return $this;
    }

    /**
     * Ukončí XHProf a uloží data.
     *
     * @return self
     */
    public function stop()
    {
        $this->setData(xhprof_disable());

        return $this;
    }

    public function dump()
    {
        include_once $this->xhprofLibPath . 'xhprof_lib.php';
        include_once $this->xhprofLibPath . 'xhprof_runs.php';

        // @TODO REQUEST_URI for console
        $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (preg_match('@link=([^&]*)@', $_SERVER['QUERY_STRING'], $matches)) {
            $request = $matches[1];
        }

        $xhprofRuns = new \XHProfRuns_Default();
        $runId      = $xhprofRuns->save_run($this->getData(), $request);

        $url      = $this->xhprofAppUrl . '?run=' . $runId . '&source=' . $request;
        $settings = array(
            'message' => sprintf('<a target="_blank" href="%s">XHProf results</a>', $url)
        );

        return array_merge(parent::dump(), $settings);
    }

    /**
     * Vrátí data z XHProfu.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Nastaví data z XHProfu.
     *
     * @param array $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
