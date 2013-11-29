<?php

namespace Gobie\Debug\Message\Sql\Success;

/**
 * Abstraktní třída pro zprávy obsahující úspěšný SQL dotaz.
 */
class SimpleMessage extends \Gobie\Debug\Message\Sql\AbstractMessage
{

    public function dump()
    {
        $explainUrl = $this->getUrlForSqlExplain();
        $content    = "<div class='debug_box sql_info border'>" .
                      $this->getSql() .
                      "<div class='exec_query'>" .
                      "<a href='" . $this->getUrlForSqlExecution() . "' target='_blank'>Vykonat dotaz</a><br>" .
                      ($this->isSelect ? "<a href='" . $explainUrl . "' target='_blank'>Vysvětlit dotaz</a><br>" : '') .
                      "<a href='" . $this->getUrlForSqlProfiling() . "' target='_blank'>Profilovat dotaz</a>" .
                      "</div>" .
                      "</div>" .
                      $this->getCallstackDump();
        $settings   = array(
            'classType' => 'sql_query',
            'type'      => $this->renderSqlType(),
            'message'   => "<div class='cell sql'>" . $this->getSQL() . "</div>"
                           . "<div class='cell time'>" . $this->getTime() . " ms</div>",
            'content'   => $content
        );

        return array_merge(parent::dump(), $settings);
    }
}
