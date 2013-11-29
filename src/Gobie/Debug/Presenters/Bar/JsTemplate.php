<?php

namespace Gobie\Debug\Presenters\Bar;

/**
 * JS šablona pro Debug.
 */
class JsTemplate extends BaseTemplate
{

    /**
     * Je komprese zapnuta.
     *
     * @var boolean
     */
    private $compress = true;

    public function render()
    {
        $this->initialize();

        echo '<script type="text/javascript">' . PHP_EOL;
        echo '(function(D) {' . PHP_EOL;

        foreach ($this->getMessages() as $message) {
            if ($this->compress) {
                $message = "'" . static::compressData($message) . "'";
            } else {
                $message = static::jsonEncode($message);
            }

            echo 'D.addMessages(' . $message . ');' . PHP_EOL;
        }

        echo 'D.render();' . PHP_EOL;
        echo '})(Debugger);' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }

    /**
     * Nastaví kompresi zpráv při vykreslování.
     *
     * @param boolean $enable Zapnout/Vypnout
     * @return self
     */
    public function enableCompression($enable)
    {
        $this->compress = (bool) $enable;

        return $this;
    }

    /**
     * Inicializace a vypsání backendu pro Debug (HTML markup, JS, CSS).
     */
    protected function initialize()
    {
        echo '<style type="text/css">' . PHP_EOL;
        include_once 'assets/debugger.css';
        echo '</style>' . PHP_EOL;
        ?>

        <div id="debug_toolbar">
            <div id="debug_toolbar_menu" class="clearfix clickable" onclick="Debugger.toggle();">
                <div id="debug_toolbar_status" title="Created by Michal Brašna">Debug</div>
                <div id="debug_toolbar_actions" onclick="event.cancelBubble = true;">
                    <span title='Sbal všechny zprávy' class='clickable' onclick='Debugger.collapseAll();'>&#8892;</span>
                    <span title='Otoč pořadí zpráv' class='clickable' onclick='Debugger.reverseOrder();'>&#8693;</span>
                    <span title='Vymaž všechny zprávy' class='clickable' onclick='Debugger.deleteAll();'>&#8709;</span>
                </div>
                <div id="debug_toolbar_filter" onclick="event.cancelBubble = true;"></div>
                <div id="debug_toolbar_paging" onclick="event.cancelBubble = true;"></div>
            </div>
            <div id="debug_toolbar_messages"></div>
            <div id="debug_toolbar_bottom" class="clickable" onclick="Debugger.toggle();"></div>
        </div>

        <?php
        echo '<script type="text/javascript">' . PHP_EOL;
        include_once 'assets/ie_support.min.js';
        include_once 'assets/base64binary_decode.min.js';
        include_once 'assets/zlib_inflate.min.js';
        include_once 'assets/debugger.js';
        echo '</script>' . PHP_EOL;
    }
}
