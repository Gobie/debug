<?php

namespace Gobie\Debug;

use Gobie\Debug\Message\Dump\DumperManager\IDumperManager;
use Gobie\Debug\Message\Dump\DumpMessage;
use Gobie\Debug\Message\ErrorMessage;
use Gobie\Debug\Message\IMessage;
use Gobie\Debug\Message\Sql\AbstractMessage;
use Gobie\Debug\Message\Start\GlobalDumpers\IGlobalDumper;
use Gobie\Debug\Message\Start\StartMessage;
use Gobie\Debug\Message\TimerMessage;
use Gobie\Debug\Presenters\Bar\AjaxTemplate;
use Gobie\Debug\Presenters\Bar\ITemplate;
use Gobie\Debug\Presenters\Bar\JsTemplate;

/**
 * Třída Debug umožňující debugování libovolného systému.
 *
 * Správa Debugu
 * <pre>
 * // Získání instance
 * $debug = \Gobie\Debug\Debug::getInstance();
 *
 * // Zapnutí
 * $debug->enable();
 *
 * // Podmíněné zapnutí callbackem
 * $debug->enableOnCallback(function ($message) {
 *    // Např. dle výskytu nějaké chybové zprávy či dumpu
 *    return in_array(get_class($message), array(
 *          "\Gobie\Debug\Message\ErrorMessage",
 *          "\Gobie\Debug\Message\DumpMessage",
 *          "\Gobie\Debug\Message\Sql\Error\ConnectionMessage",
 *          "\Gobie\Debug\Message\Sql\Error\NativeMySQLMessage"
 *      ));
 * });
 *
 * // Je Debug zaplý?
 * $debug->isEnabled();
 * </pre>
 *
 * Dumpování proměnných
 * <pre>
 * // DumpMessage proměnných s callstackem do toolbaru
 * $debug->dump($var1, $var2[, ...]);
 *
 * // Pojmenovaný dump proměnných s callstackem do toolbaru
 * $debug->dump('context', $context[, ...]);
 *
 * // Dumpnutí vlastní zprávy
 * $debug->addMessage(new \Gobie\Debug\Message\TimerMessage("test", 1, 10));
 *
 * // Ve Startu se zobrazí "Context"
 * $debug->addGlobalDumper(new \Gobie\Debug\Message\Start\GlobalDumpers\ContextGlobalDumper());
 * </pre>
 *
 * Debugování a profilování SQL dotazů
 * <pre>
 * // Debugování SQL dotazu po jeho vykonání
 * $debug->query($sqlQuery, $mysql_resource/$connection[, $timer]);
 * </pre>
 *
 * Měření doby běhu skriptu a paměťové náročnosti
 * <pre>
 * // Spustí pojmenovaný timer
 * $debug->timerStart("test");
 *
 * // Získá data z timeru, dle názvu
 * $data = $debug->timerGet("test");
 *
 * // Vypíše data timeru i s využitím paměti do toolbaru
 * $debug->timerEnd("test");
 *
 * // Spustí timer a vrací jeho vygenerované jméno
 * $timerName = $debug->timerStart();
 *
 * // Ukončí timer s vygenrovaným jménem
 * $debug->timerEnd($timerName);
 * </pre>
 *
 * Nastavení Debugu
 * <pre>
 * // Základní nastavení
 * $debug->getOptions()->set(array(
 *      Options::SHOW_CALLSTACK_SOURCE => true,
 *      Options::IGNORE_ERRORS => E_STRICT | E_DEPRECATED,
 *      Options::SKIP_PROPERTY_MODIFIERS => \ReflectionProperty::IS_PRIVATE
 * ));
 *
 * // Získání nastavení
 * $debug->getOptions()->get(Options::SHOW_CALLSTACK_SOURCE);
 *
 * // Nastavení DumperManageru
 * $dumperManager = new \Gobie\Debug\Message\Dump\Dumpers\DumperManager();
 * $dumperManager->addDumper(...);
 * $debug->setDumperManager($dumperManager);
 *
 * // Nastavení šablony
 * $debug->setTemplate(new \Gobie\Debug\Presenters\Bar\JsTemplate());
 * </pre>
 *
 * @author Michal Brašna
 */
class Debug
{

    /**
     * Mapování chybových konstant na jejich textovou podobu.
     *
     * @var array
     */
    private static $errorsMap = array(
        E_PARSE             => 'E_PARSE',
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED'
    );

    /**
     * Pole nezachytitelných typů chyb.
     *
     * @var array
     */
    private static $uncatchableErrors = array(
        E_PARSE,
        E_ERROR,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING
    );

    /**
     * Instance Debugu.
     *
     * @var Debug
     */
    private static $instance;

    /**
     * Pole všech zpráv.
     *
     * @var array
     */
    private $messages = array();

    /**
     * Pole dumperů globálně přístupných objektů a struktur.
     *
     * Vykreslují se ve zprávě {@see Message\StartMessage}.
     *
     * @var array
     */
    private $globalDumpers = array();

    /**
     * Je Debug zaplý.
     *
     * @var boolean
     */
    private $enabled = false;

    /**
     * Callback, který pokud alespoň alespoň jednou vrátí true, tak se Debug zobrazí, jinak ne.
     *
     * Vzor je function ($message) {}.
     *
     * @var callable
     */
    private $enableOnCallback = null;

    /**
     * Objekt timeru.
     *
     * @var Timer
     */
    private $timer;

    /**
     * Název výchozího timeru pro měření doby běhu celé stránky.
     *
     * @var string
     */
    private $pageTimerName = '[Page]';

    /**
     * Manažer pro dumpování objektů.
     *
     * @var IDumperManager
     */
    private $dumperManager;

    /**
     * Šablona Debug baru.
     *
     * @var ITemplate
     */
    private $template;

    /**
     * Nastavení debugu.
     *
     * @var Options
     */
    private $options;

    /**
     * Privátní konstruktor kvůli Singletonu.
     */
    private function __construct(Options $options)
    {
        $this->timer   = new Timer();
        $this->options = $options;

        // Nastaví výchozí šablonu
        $this->setTemplate($this->getOptions()->get(Options::IS_AJAX) ? new AjaxTemplate() : new JsTemplate());
    }

    /**
     * Vrátí objekt nastavení pro další manipulaci.
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Vrátí unikátní instanci Debugu na stránce.
     *
     * @return Debug
     */
    public static function getInstance(Options $options)
    {
        return self::$instance ? : self::$instance = new self($options);
    }

    /**
     * Zapne Debug pouze pokud callback vrátí alespoň jednou true.
     *
     * Callback je volán nad každou zprávou v bufferu zpráv v shutdown handleru.
     *
     * @param callable $callback Callback [function ($message) {}]
     * @return Debug
     * @throws \InvalidArgumentException Pokud argument není platný callback
     */
    public function enableOnCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Argument není callback');
        }

        $this->enableOnCallback = $callback;

        return $this->enable();
    }

    /**
     * Zapnutí Debugu.
     *
     * Nakonfiguruje se PHP.
     * Nastaví se error a exception handler.
     * Zaregistruje se shutdown handler.
     * Spustí se časovač pro měření celkového běhu skriptu.
     * Zaregistruje se Start zpráva.
     *
     * Funguje pouze, pokud není Debug zaplý.
     *
     * @return Debug
     */
    public function enable()
    {
        if ($this->isEnabled()) {
            return $this;
        }

        ob_start();

        // Nastavení reportování
        error_reporting(-1);
        ini_set('display_errors', 'stderr');
        ini_set('html_errors', '0');
        ini_set('log_errors', '1');
        ini_set('log_errors_max_len', '0');

        // xdebug
        if (extension_loaded('xdebug')) {
            ini_set('xdebug.collect_params', 1);
        }

        // Zaregistrování handlerů (shutdown, error a exception)
        register_shutdown_function(array($this, 'shutdownHandler'));
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));

        // Označí Debug jako zaplý
        $this->enabled = true;

        // Spustí globální timer
        $this->timerStart($this->pageTimerName);

        // Zaregistruje Start zprávu
        $this->addMessage(new StartMessage($this, $this->globalDumpers));
        unset($this->globalDumpers);

        return $this;
    }

    /**
     * Je Debug zapnutý.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Spustí timer.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @param string $name Název timeru, když nebude zadáno, vygeneruje se náhodný název
     * @return string Název timeru
     */
    public function timerStart($name = null)
    {
        if (!$this->isEnabled()) {
            return $name;
        }

        $timer = $this->getTimer();
        $name  = $timer->start($name);
        $timer->watchMemory($name);

        return $name;
    }

    /**
     * Vrátí Timer.
     *
     * @return Timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * Přidá zprávu do toolbaru.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @param IMessage $message Zpráva
     * @return Debug
     */
    public function addMessage(IMessage $message)
    {
        if (!$this->isEnabled()) {
            return $this;
        }

        $this->messages[] = $message;

        return $this;
    }

    /**
     * Dumpne proměnnou a vrátí ji ve formátované podobě.
     *
     * Náhrada za var_dump(), var_export(), print_r() apod.
     *
     * @param mixed   $var   Dumpovaná proměnná
     * @param integer $level Počáteční odsazení [defaultně 1]
     * @param integer $depth Maximální hloubka zanoření výpisu [defaultně dle Options::GLOBAL_DUMP_DEPTH]
     * @return string
     * @throws \LogicException Pokud nebyl zaregistrován žádný manažer pro dumpování proměnných
     * @internal
     */
    public function dumpVariable($var, $level = 1, $depth = null)
    {
        if (!$this->getDumperManager()) {
            throw new \LogicException('Nebyl zaregistrován žádný manažer pro dumpování proměnných.');
        }
        $depth = $depth !== null ? $depth : $this->getOptions()->get(Options::GLOBAL_DUMP_DEPTH);

        return $this->getDumperManager()->dump($var, $level, $depth);
    }

    /**
     * Vrátí manažer pro dumpování objektů.
     *
     * @return IDumperManager
     */
    public function getDumperManager()
    {
        return $this->dumperManager;
    }

    /**
     * Nastaví manažer pro dumpování objektů.
     *
     * @param IDumperManager $dumperManager
     * @return Debug
     */
    public function setDumperManager(IDumperManager $dumperManager)
    {
        $dumperManager->setDebug($this);
        $this->dumperManager = $dumperManager;

        return $this;
    }

    /**
     * Zaregistruje globální dumper do {@see Message\StartMessage} zprávy.
     *
     * Funguje pouze, pokud není Debug zaplý.
     *
     * @param IGlobalDumper $globalDumper Globální dumper
     * @return Debug
     * @throws \LogicException Pokud již byl Debug spuštěn
     */
    public function addGlobalDumper(IGlobalDumper $globalDumper)
    {
        if ($this->isEnabled()) {
            throw new \LogicException('Nelze přidat globální dumper, Debug již byl spuštěn.');
        }

        $this->globalDumpers[] = $globalDumper;

        return $this;
    }

    /**
     * Dumpne proměnné v argumentu do toolbaru.
     *
     * Pokud bude první argument string a dump bude volán s alespoň 2 argumenty, bere se první argument za název dumpu.
     * V ostatních případech se zobrazuje jako název dumpu soubor a řádek, kde k dumpu došlo.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @return Debug
     */
    public function dump()
    {
        if (!$this->isEnabled()) {
            return $this;
        }

        $name = null;
        $argv = func_get_args();
        if (func_num_args() > 1 && is_string($argv[0])) {
            $name = array_shift($argv);
        }

        return $this->addMessage(new DumpMessage($this, $name, $argv, $this->getCallstack()));
    }

    /**
     * Vrátí vyfiltrovaný callstack v místě volání bez Debugu.
     *
     * @return array
     */
    private function getCallstack()
    {
        $debugMethods = array('query' => true, 'getCallstack' => true, 'errorHandler' => true);
        $class        = get_class($this);
        $backtrace    = debug_backtrace(false);

        // use xdebug if available for better callstacks from within shutdown handler
        if (function_exists('xdebug_get_function_stack')) {
            // transform xdebug backtrace to php-like backtrace
            $transformCallback = function ($item) {
                $item['args'] = isset($item['params']) ? $item['params'] : array();
                unset($item['params']);

                if (isset($item['type'])) {
                    $item['type'] = $item['type'] === 'static'
                        ? '::'
                        : ($item['type'] === 'dynamic' ? '->' : $item['type']);
                }

                return $item;
            };

            // filter out top-level trace item: {main}
            $filterCallback = function ($item) {
                return !isset($item['function']) || (isset($item['function']) && $item['function'] !== '{main}');
            };

            // transform xdebug backtrace, filter out top-level trace item, revert call order
            $xdebugBacktrace = array_reverse(
                array_filter(
                    array_map(
                        $transformCallback,
                        xdebug_get_function_stack()
                    ),
                    $filterCallback
                )
            );

            // use backtrace with more trace items, this happens only with backtraces from within shutdown handler
            $backtrace = count($xdebugBacktrace) > count($backtrace) ? $xdebugBacktrace : $backtrace;
        }

        // filter out Debug methods to get more accurate callstack
        return array_filter(
            $backtrace,
            function ($trace) use ($class, $debugMethods) {
                $isDebug = isset($trace['class'])
                           && $trace['class'] === $class
                           && isset($trace['function'])
                           && isset($debugMethods[$trace['function']]);

                return isset($trace['file']) && !$isDebug;
            }
        );
    }

    /**
     * Zpracuje SQL dotaz.
     *
     * Zpracovává chybné i úspěšné SQL dotazy.
     * Musí být voláno až po vykonaném dotazu.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @param string $sql      SQL dotaz
     * @param mixed  $resource Jakýkoli resource z {@see Options::DB_MAP}
     * @param array  $timer    Informace z timeru
     * @return mixed Pokud došlo k chybě v SQL dotazu true, jinak false. Při chybě nebo vypnutém Debugu null.
     * @throws \LogicException Pokud nemá resource nastavené mapování v Options::DB_MAP
     * @throws \InvalidArgumentException Pokud checkCallback daného typu není callback {@see Options::DB_MAP}
     */
    public function query($sql, $resource, array $timer = null)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        static $highlighters = array();

        foreach ($this->getOptions()->get(Options::DB_MAP) as $type => $mapping) {
            if (!is_a($resource, $type) && $type !== 'default') {
                continue;
            }

            /** @var $message AbstractMessage */
            $message       = null;
            $errorOccurred = call_user_func($mapping['checkCallback'], $resource);
            if ($errorOccurred) {
                $message = new $mapping['errorClass']($this, $resource, $sql, $this->getCallstack());
            } elseif ($this->getOptions()->get(Options::LOG_SUCCESSFUL_QUERIES)) {
                $message = new $mapping['successClass']($this, $resource, $sql, $this->getCallstack(), $timer);
            }

            if (isset($message)) {
                if (isset($mapping['highlighter'])) {
                    $resourceHash = is_object($resource) ? spl_object_hash($resource) : (string) $resource;
                    if (!isset($highlighters[$resourceHash])) {
                        $highlighters[$resourceHash] = call_user_func($mapping['highlighter'], $this, $resource);
                    }
                    $message->setHighlighter($highlighters[$resourceHash]);
                }

                $this->addMessage($message);
            }

            return !$errorOccurred;
        }

        $resourceName = is_object($resource) ? get_class($resource) : gettype($resource);
        throw new \LogicException(sprintf('DB spojení "%s" nemá nastavené mapování v Options::DB_MAP.', $resourceName));
    }

    /**
     * Exception handler pro zachycení výjimek.
     *
     * @param \Exception $exc Zachycená výjimka
     * @internal
     */
    public function exceptionHandler(\Exception $exc)
    {
        try {
            if (in_array(get_class($exc), $this->getOptions()->get(Options::IGNORE_EXCEPTIONS))) {
                return;
            }

            $previousException = $exc->getPrevious();
            if ($previousException instanceof \Exception) {
                $this->exceptionHandler($previousException);
            }

            $msg   = '[' . $exc->getCode() . '] ' . $exc->getMessage();
            $error = new ErrorMessage($this, get_class($exc), $exc->getFile(), $exc->getLine(), $msg, $exc->getTrace());
            $this->addMessage($error);
        } catch (\Exception $e) {
            $msg = 'Exception  "' . $e->getMessage() . '" occurred within exception handler line ' . $e->getLine();
            trigger_error($msg, E_USER_ERROR);
        }
    }

    /**
     * Shutdown handler, zaloguje nezachytitelné Fatal erory.
     *
     * Pokud nastal Fatal error, dosavadní obsah stránky se vyprázdní nebo ponechá.
     * Chování závisí na {@see Options::DUMP_BUFFER_ON_FATAL_ERROR}.
     * Na konci se dle šablony vypíše Debug Toolbar a všechny zprávy do něj.
     *
     * @internal
     */
    public function shutdownHandler()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], self::$uncatchableErrors)) {
            $this->errorHandler($error['type'], $error['message'], $error['file'], $error['line']);

            if (!$this->getOptions()->get(Options::DUMP_BUFFER_ON_FATAL_ERROR)) {
                ob_clean();
            }
        }

        $this->timerEnd($this->pageTimerName);

        if ($this->isDebugRendered()) {
            @session_write_close();
            @session_start();
            $session = & $_SESSION['__debug'];

            /** @var $message IMessage */
            while ($message = array_shift($this->messages)) {
                $session[] = $message->dump();
            }

            if (preg_match('@^Location:@im', implode("\n", headers_list()))) {
                @session_write_close();

                return;
            }

            $messages = $session;
            unset($_SESSION['__debug']);
            @session_write_close();

            $this->getTemplate()->setMessages($messages);
            $this->getTemplate()->render();
        }

        ob_end_flush();
    }

    /**
     * Error handler pro zachycení PHP chyb.
     *
     * @param integer $errNo   Číslo chyby
     * @param string  $errStr  Chybová zpráva
     * @param string  $errFile Soubor, kde se chyba vyskytla
     * @param integer $errLine Řádek, kde se chyba vyskytla
     * @return boolean Zda má pokračovat ve zpracování další Error handler
     * @internal
     */
    public function errorHandler($errNo, $errStr, $errFile, $errLine)
    {
        if (($errNo & $this->getOptions()->get(Options::IGNORE_ERRORS)) !== $errNo) {
            $msg = new ErrorMessage($this, self::$errorsMap[$errNo], $errFile, $errLine, $errStr, $this->getCallstack());
            $this->addMessage($msg);
        }

        return false;
    }

    /**
     * Vypíše uběhlý čas a informace o využití paměti do toolbaru.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @param string $name Název timeru
     * @return Debug
     * @see Debug::timerGet()
     */
    public function timerEnd($name)
    {
        if (!$this->isEnabled()) {
            return $this;
        }

        list($time, $memory) = $this->timerGet($name);

        return $this->addMessage(new TimerMessage($this, $name, $time, $memory));
    }

    /**
     * Vrátí uběhlý čas a informace o využití paměti pojmenovaného timeru.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @param string $name Název timeru
     * @return array array(uběhlý čas, informace o využité paměťi)
     */
    public function timerGet($name)
    {
        if (!$this->isEnabled()) {
            return array();
        }

        $timer  = $this->getTimer();
        $time   = $timer->stop($name);
        $memory = $timer->unwatchMemory($name);

        return array($time, $memory);
    }

    /**
     * Vrátí, zda se má debug vyrenderovat.
     *
     * Pokud neexistuje callback, vrací true.
     * Pokud existuje, ověří, zda callback vrátí na některou ze zpráv v toolbaru true-like hodnotu.
     *
     * @return boolean
     */
    private function isDebugRendered()
    {
        if (!$this->enableOnCallback) {
            return true;
        }

        foreach ($this->messages as $message) {
            if (call_user_func($this->enableOnCallback, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vrátí šablonu Debug baru.
     *
     * @return ITemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Nastaví šablonu Debug baru.
     *
     * @param ITemplate $template Šablona
     * @return Debug
     */
    public function setTemplate(ITemplate $template)
    {
        $template->setDebug($this);
        $this->template = $template;

        return $this;
    }

    /**
     * Vrátí všechny dosud uložené zprávy.
     *
     * Funguje pouze, pokud je Debug zaplý.
     *
     * @return array
     */
    public function getMessages()
    {
        if (!$this->isEnabled()) {
            return array();
        }

        return $this->messages;
    }

    /**
     * Vrátí nahrazený parameter v odkazech.
     *
     * @param string $param Parameter k nahrazení ve tvaru '%neco'
     * @param string $what  Nahrazovaný řetězec
     * @return string Nahrazený text
     */
    public function replaceLinkParam($param, $what)
    {
        $settings = $this->getOptions()->get(Options::SQL_LINKS);
        if (isset($settings[$param])) {
            return str_replace('%' . $param, $what, $settings[$param]);
        }

        return $what;
    }
}
