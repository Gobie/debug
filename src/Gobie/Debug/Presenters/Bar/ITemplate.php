<?php

namespace Gobie\Debug\Presenters\Bar;

use Gobie\Debug\Debug;

/**
 * Rozhraní pro šablony Debugu.
 */
interface ITemplate
{

    /**
     * Nastaví šabloně zprávy.
     *
     * @param array $messages Zprávy
     * @return self
     */
    public function setMessages(array $messages);

    /**
     * Vrátí zprávy.
     *
     * @return array
     */
    public function getMessages();

    /**
     * Vykreslí šablonu.
     */
    public function render();

    public function setDebug(Debug $debug);

    public function getDebug();
}
