<?php

namespace Gobie\Debug\Message\Sql\Error;

/**
 * Abstraktní třída pro zprávy obsahující chybný SQL dotaz.
 */
abstract class AbstractMessage extends \Gobie\Debug\Message\Sql\AbstractMessage
{
    /**
     * Informace o chybě v SQL dotazu.
     *
     * @var array
     */
    protected $errno;

    /**
     * Informace o chybě v SQL dotazu.
     *
     * @var array
     */
    protected $error;

    public function dump()
    {
        $settings = array(
            'classType' => 'sql_error',
            'type'      => $this->renderSqlType(),
            'message'   => "[" . $this->errno . "] " . $this->error,
            'content'   => "<div class='debug_box sql_info border'>" .
                           $this->getSql() .
                           "<div class='exec_query'>" .
                           "<a href='" . $this->getUrlForSqlExecution() . "' target='_blank'>Vykonat dotaz</a><br>" .
                           "</div>" .
                           "</div>" .
                           $this->getCallstackDump()
        );

        return array_merge(parent::dump(), $settings);
    }
}
