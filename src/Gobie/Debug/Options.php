<?php

namespace Gobie\Debug;

/**
 * Nastavení Debugu.
 *
 * @author mbrasna
 */
class Options
{

    const
        IS_AJAX = 'isAjax',
        LOG_SUCCESSFUL_QUERIES = 'logSuccessfulQueries',
        SHOW_CALLSTACK_SOURCE = 'showCallstackSource',
        CALLSTACK_SOURCE_LINES = 'callstackSourceLines',
        DUMP_BUFFER_ON_FATAL_ERROR = 'dumpBufferOnFatalError',
        IGNORE_ERRORS = 'ignoreErrors',
        IGNORE_EXCEPTIONS = 'ignoreExceptions',
        SKIP_PROPERTY_MODIFIERS = 'skipPropertyModifiers',
        GLOBAL_DUMP_DEPTH = 'globalDumpDepth',
        CALLSTACK_ARGUMENT_DUMP_DEPTH = 'callstackArgumentDumpDepth',
        HIDE_EMPTY_GLOBAL_DUMPERS = 'hideEmptyGlobalDumpers',
        DB_MAP = 'dbMap',
        SQL_LINKS = 'sqlLinks',
        DOCUMENT_ROOT_PATH = 'docRootPath';

    /**
     * Nastavení Debugu.
     *
     * Popis jednotlivých nastavení je u {@see Options::set}.
     *
     * @var array
     */
    private $options = array(
        self::IS_AJAX                       => false,
        self::LOG_SUCCESSFUL_QUERIES        => true,
        self::SHOW_CALLSTACK_SOURCE         => false,
        self::CALLSTACK_SOURCE_LINES        => 5,
        self::DUMP_BUFFER_ON_FATAL_ERROR    => false,
        self::IGNORE_ERRORS                 => 0,
        self::IGNORE_EXCEPTIONS             => array(),
        self::SKIP_PROPERTY_MODIFIERS       => 0,
        self::GLOBAL_DUMP_DEPTH             => 3,
        self::CALLSTACK_ARGUMENT_DUMP_DEPTH => 2,
        self::HIDE_EMPTY_GLOBAL_DUMPERS     => true,
        self::DB_MAP                        => array(),
        self::SQL_LINKS                     => array(
            'query' => '',
            'table' => ''
        ),
        self::DOCUMENT_ROOT_PATH            => array(
            'from' => '',
            'to'   => ''
        ),
    );

    /**
     * Nastaví Debug.
     *
     * Volá se s polem jako argumentem, nebo dvojicí argumentů reprezentující klíč a hodnotu.
     *
     * <pre>
     * $debug->set(array(
     *      'key' => $value,
     *      ...
     * ));
     *
     * $debug->set('key', $value);
     * </pre>
     *
     * <strong>IS_AJAX</strong>
     *
     * Nastaví, zda se jedná o zpracování požadavku AJAXem.
     * Využívá se u automatického výběru šablony.
     *
     * <pre>
     * $debug->set(Options::IS_AJAX, false);
     * </pre>
     *
     * <strong>LOG_SUCCESSFUL_QUERIES</strong>
     *
     * Nastaví, zda se mají logovat i úspěšné SQL dotazy.
     * Chybné SQL dotazy se logují automaticky, pokud je nastaveno <strong>DB_MAP</strong>.
     *
     * <pre>
     * $debug->set(Options::LOG_SUCCESSFUL_QUERIES, true);
     * </pre>
     *
     * <strong>SHOW_CALLSTACK_SOURCE</strong>
     *
     * Nastaví, zda se mají zobrazovat úryvky zdrojového kódu spolu u každé položky callstacku.
     *
     * <pre>
     * $debug->set(Options::SHOW_CALLSTACK_SOURCE, true);
     * </pre>
     *
     * <strong>CALLSTACK_SOURCE_LINES</strong>
     *
     * Nastaví, kolik řádků okolo se má zobrazovat u každé položky callstacku.
     *
     * <pre>
     * $debug->set(Options::CALLSTACK_SOURCE_LINES, 10);
     * </pre>
     *
     * <strong>DUMP_BUFFER_ON_FATAL_ERROR</strong>
     *
     * Nastaví, zda se má vypsat stávající obsah stránky, pokud dojde k fatální chybě PHP.
     *
     * <pre>
     * $debug->set(Options::DUMP_BUFFER_ON_FATAL_ERROR, false);
     * </pre>
     *
     * <strong>IGNORE_ERRORS</strong>
     *
     * Nastaví bitovou masku neodchytávaných PHP chyb, 0 znamená odchytávat vše.
     *
     * <pre>
     * $debug->set(Options::IGNORE_ERRORS, E_STRICT | E_DEPRECATED);
     * </pre>
     *
     * <strong>IGNORE_EXCEPTIONS</strong>
     *
     * Nastaví pole názvů neodchytávaných výjimek, prázdné pole znamená odchytávat vše.
     *
     * <pre>
     * $debug->set(Options::IGNORE_EXCEPTIONS, array('\LogicException', '\InvalidArgumentException'));
     * </pre>
     *
     * <strong>SKIP_PROPERTY_MODIFIERS</strong>
     *
     * Nastaví bitovou masku nezobrazovaných atributů objektů při dumpování.
     *
     * <pre>
     * $debug->set(Options::SKIP_PROPERTY_MODIFIERS, \ReflectionProperty::IS_PRIVATE);
     * </pre>
     *
     * <strong>GLOBAL_DUMP_DEPTH</strong>
     *
     * Nastaví hloubku výpisu u všech dumpů mimo argumentů callstacku, na ty je CALLSTACK_ARGUMENT_DUMP_DEPTH.
     *
     * <pre>
     * $debug->set(Options::GLOBAL_DUMP_DEPTH, 3);
     * </pre>
     *
     * <strong>CALLSTACK_ARGUMENT_DUMP_DEPTH</strong>
     *
     * Nastaví hloubku výpisu u argumentů callstacku.
     *
     * <pre>
     * $debug->set(Options::CALLSTACK_ARGUMENT_DUMP_DEPTH, 3);
     * </pre>
     *
     * <strong>HIDE_EMPTY_GLOBAL_DUMPERS</strong>
     *
     * Nastaví, zda se mají skrýt globální dumpery, které jsou prázdné.
     *
     * <pre>
     * $debug->set(Options::HIDE_EMPTY_GLOBAL_DUMPERS, true);
     * </pre>
     *
     * <strong>SQL_LINKS</strong>
     *
     * Placeholdery:
     * - %table se nahradí za urlencoded název tabulky
     * - %query se nahradí za urlencoded SQL dotaz
     *
     * <pre>
     * // Nastaví odkazy pro ruční vykonání dotazu (např. v Admineru)
     * $debug->set(
     *     Options::SQL_LINKS,
     *     array(
     *         // Url k zobrazení tabulky
     *         'table' => 'http://localhost/adminer/?username=root&db=database&select=%table',
     *         // Url k vykonání dotazu
     *         'query' => 'http://localhost/adminer/?username=root&db=database&sql=%query',
     *     )
     * );
     * </pre>
     *
     * <strong>DB_MAP</strong>
     *
     * Klíčem je třída obsluhující databázové spojení, nebo default jako nějaký nativní driver.
     * Pole pak obsahuje:
     * - successClass -> třída od níž se vytvoří instance při úspěšném dotazu
     * - errorClass -> třída od níž se vytvoří instance při neúspěšném dotazu
     * - checkCallback -> callback, jenž provede na objektu/resourcu spojení kontrolu, zda se jedná o chybný SQL dotaz
     * - highlighter -> zvýrazňovač SQL dotazů
     *
     * <pre>
     * // DB_MAP - Nastaví mapování databázových zpráv (nativní)
     * $debug->set(
     *     Options::DB_MAP, array(
     *         'default' => array(
     *             'successClass' => '\Gobie\Debug\Message\Sql\Success\NativeMySQLMessage',
     *             'errorClass' => '\Gobie\Debug\Message\Sql\Error\NativeMySQLMessage',
     *             'checkCallback' => function ($resource) {
     *                 return mysql_errno($resource);
     *             },
     *             'highlighter' => function (\Gobie\Debug\Debug $debug, $resource) {
     *                 return new \Gobie\Debug\Highlighters\NativeHighlighter($debug);
     *             }
     *         )
     *     )
     * );
     * </pre>
     *
     * <strong>DOCUMENT_ROOT_PATH</strong>
     *
     * Nastaví mapování cest v aplikaci na lokální filesystem kvůli prokliku souborů do Netbeans.
     *
     * <pre>
     * $debug->set(Options::DOCUMENT_ROOT_PATH, array(
     *      'from' => '/home/user/projects',
     *      'to' => 'c:\\xampp\\htdocs'
     * ));
     * </pre>
     *
     * @param mixed $arg,... Nastavení v poli nebo formou název a hodnota
     * @return Debug
     * @throws \InvalidArgumentException Nepovolené argumenty
     */
    public function set()
    {
        $argc = func_num_args();
        $argv = func_get_args();
        if ($argc === 1 && is_array($argv[0])) {
            $this->options = array_merge($this->options, $argv[0]);
        } elseif ($argc === 2 && is_string($argv[0])) {
            $this->options[$argv[0]] = $argv[1];
        } else {
            throw new \InvalidArgumentException('Povolené argumenty jsou pole nastavení nebo klíč a hodnota.');
        }

        return $this;
    }

    /**
     * Vrátí hodnotu pojmenovaného nastavení Debugu.
     *
     * @param string $name Název nastavení
     * @return mixed Hodnota nastavení
     * @throws \InvalidArgumentException Pokud požadované nastavení neexistuje
     */
    public function get($name)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(sprintf('Přístup k neexistujícímu nastavení %s.', $name));
        }

        return $this->options[$name];
    }
}
