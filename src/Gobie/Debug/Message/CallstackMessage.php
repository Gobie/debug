<?php

namespace Gobie\Debug\Message;

use Gobie\Debug\Debug;
use Gobie\Debug\Options;

/**
 * Abstraktní třída pro zprávy s callstackem.
 */
abstract class CallstackMessage extends Message
{

    /**
     * Vyfiltrovaný callstack z debug_backtrace().
     *
     * @var array
     */
    private $callstack;

    /**
     * CallstackMessage dump.
     *
     * @var string
     */
    private $callstackDump;

    /**
     * Nastaví callstack, po nasetování Debugu se callstack dumpne.
     *
     * @param Debug $debug     Debug
     * @param array $callstack CallstackMessage
     */
    public function __construct(Debug $debug, array $callstack)
    {
        parent::__construct($debug);

        $this->callstack     = $callstack;
        $this->callstackDump = $this->dumpCallstack($callstack);
    }

    /**
     * Dumps callstack.
     *
     * @param array $callstack CallstackMessage
     * @return string
     */
    public function dumpCallstack(array $callstack)
    {
        $out = array();

        $out[] = '<ol class="debug_box debug_callstack">';
        foreach ($callstack as $trace) {
            $out[] = $this->dumpTrace($trace);
        }
        $out[] = '</ol>';

        return implode('', $out);
    }

    /**
     * Dumpne řádek callstacku.
     *
     * @param array $trace Položka callstacku
     * @return string
     */
    private function dumpTrace($trace)
    {
        $message = $this->getTraceMessage($trace);
        if ($message) {
            $argsLink = '()';
            $argsDump = '';
            if (isset($trace['args']) && $trace['args']) {
                $onClickAction = ' onclick="Debugger.toggleArguments(this); return false;"';
                $argCount      = count($trace['args']);
                $argsLink      =
                    '<a class="arguments_toggle clickable"' . $onClickAction . ' href="#">(' . $argCount . ')</a>';
                $argsDump      = $this->dumpArgs($trace);
            }

            $messageHtml = '<b>' . $message . '</b>' . $argsLink . ' ';
        }

        $line = isset($trace['line']) ? (int) $trace['line'] : '';

        $editorLink = $this->createEditorLink($trace, $line);
        $sourceDump = $this->dumpSource($trace);

        return '<li>' . $messageHtml . $editorLink . $argsDump . $sourceDump . '</li>';
    }

    /**
     * @param $trace
     * @return string
     */
    private function getTraceMessage($trace)
    {
        if (isset($trace['message'])) {
            $message = $trace['message'];
        } elseif (isset($trace['class'])) {
            $message = $trace['class'] . $trace['type'] . $trace['function'];
        } elseif (isset($trace['function'])) {
            $message = $trace['function'];
        } else {
            $message = '';
        }

        return $message;
    }

    /**
     * Dumpne argumenty volání.
     *
     * @param array $trace Položka callstacku
     * @return string
     */
    private function dumpArgs($trace)
    {
        try {
            $reflector = isset($trace['class'])
                ? new \ReflectionMethod($trace['class'], $trace['function'])
                : new \ReflectionFunction('\\' . $trace['function']);
            $params    = $reflector->getParameters();
        } catch (\Exception $e) {
            $params = array();
        }

        $depth = $this->getDebug()->getOptions()->get(Options::CALLSTACK_ARGUMENT_DUMP_DEPTH);
        $out   = array();
        $out[] = '<table class="arguments" style="display: none">';
        foreach ($trace['args'] as $key => $value) {
            $paramName  = isset($params[$key]) ? '$' . $params[$key]->getName() : '#' . $key;
            $paramValue = $this->getDebug()->dumpVariable($value, 1, $depth);
            $out[]      = sprintf('<tr><th>%s</th><td><pre>%s</pre></td></tr>', $paramName, $paramValue);
        }
        $out[] = '</table>';

        return implode('', $out);
    }

    /**
     * @param $trace
     * @param $line
     * @return string
     */
    private function createEditorLink($trace, $line)
    {
        $editorLink = '';
        if (isset($trace['file'])) {
            $docRootPath = $this->getDebug()->getOptions()->get(Options::DOCUMENT_ROOT_PATH);
            $file        = preg_replace('@' . $docRootPath['from'] . '@', $docRootPath['to'], $trace['file']);
            $filePath    = static::translateFilePath($trace['file']);
            $url         = 'editor://open/?file=' . $file . ($line ? '&line=' . $line : '');
            $editorLink  = '<a class="editor" href="' . $url . '">' . $filePath . '</a>' . ($line ? ':' . $line : '');
        }

        return $editorLink;
    }

    /**
     * Z položky callstacku získá soubor spuštění a dumpne vyříznutý zdrojový kód souboru.
     *
     * @param array $trace Položka callstacku
     * @return string
     */
    private function dumpSource($trace)
    {
        $out                 = array();
        $showCallstackSource = $this->getDebug()->getOptions()->get(Options::SHOW_CALLSTACK_SOURCE);

        if ($showCallstackSource && is_readable($trace['file'])) {
            $callstackSourceLines = $this->getDebug()->getOptions()->get(Options::CALLSTACK_SOURCE_LINES);

            $lineBeginOrg = $trace['line'] - 1 - $callstackSourceLines;
            $lineBegin    = $lineBeginOrg < 0 ? 0 : $lineBeginOrg;
            $lineCount    = 1 + 2 * $callstackSourceLines + ($lineBeginOrg < 0 ? $lineBeginOrg : 0);
            $sourceCode   = array_slice(file($trace["file"], FILE_IGNORE_NEW_LINES), $lineBegin, $lineCount, true);

            $preg    = array(
                'from' => array('/\n/', '@(<span style="color: #0000BB">)&lt;\?php&nbsp;(.*?</span>)@'),
                'to'   => array('', '$1$2')
            );
            $counter = $lineBegin + 1;
            $out[]   = '<table><tr><td><ol start="' . $counter . '">';
            foreach ($sourceCode as $line) {
                $highlightedLine = preg_replace($preg['from'], $preg['to'], highlight_string('<?php ' . $line, true));
                $style           = $counter === $trace['line'] ? ' style="background-color:rgba(0,0,0,0.1)"' : '';
                $out[]           = '<li' . $style . '>' . $highlightedLine . '</li>';
                ++$counter;
            }
            $out[] = '</ol></td></tr></table>';
        }

        return implode('', $out);
    }

    /**
     * @return string
     */
    public function getCallstackDump()
    {
        return $this->callstackDump;
    }

    /**
     * Vrátí callstack.
     *
     * @return array
     */
    public function getCallstack()
    {
        return $this->callstack;
    }
}
