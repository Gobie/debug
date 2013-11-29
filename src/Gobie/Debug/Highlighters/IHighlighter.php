<?php

namespace Gobie\Debug\Highlighters;

/**
 * Rozhraní pro zvýraznění syntaxe.
 */
interface IHighlighter
{

    /**
     * Zvýrazní SQL dotaz.
     *
     * @param string $sql SQL dotaz
     * @return string
     */
    public function highlight($sql);
}
