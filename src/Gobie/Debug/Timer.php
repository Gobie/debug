<?php

namespace Gobie\Debug;

/**
 * Třída pro měření času.
 *
 * Použití
 * <pre>
 * $t = new Timer();
 *
 * // Sledování paměti
 * $mem = $t->watchMemory();
 * $t->unwatchMemory($mem)
 *
 * // Měření času
 * $name = 'timer_1';
 * $t->start($name);
 * foreach ($array as $key => $value) {
 *  // něco, co trvá dlouho
 *  $t->lap($name);
 * }
 * $t->stop($name);
 *
 * // Countdown - nejjednodušší použití
 * // Vyhodí výjimku, pokud běh skriptu trval více než 10s.
 * $name = 'countdown_1';
 * $t->startCountdown($name, array(
 *  function ($time) {
 *      if ($time > 10) {
 *          throw new \RangeException('Běh skriptu trval přes 10s');
 *      }
 *  }
 *
 * ));
 * $t->stopCountdown($name);
 *
 * // Countdown - vlastní data do callbacků
 * // Zjistí rozdíl peaků na začátku a na konci.
 * // Tedy maximální využití paměti za dobu běhu skriptu.
 * $name = 'countdown_2';
 * $t->startCountdown($name, array(
 *  function ($time, $data) {
 *      echo 'Za ' . $time . 's se využilo ' . memory_get_peak_usage() - $data .  'B';
 *  }
 * ), array(memory_get_peak_usage()));
 * $t->stopCountdown($name);
 *
 * // Countdown
 * //
 * $name = 'countdown_3';
 * $t->startCountdown($name, array(
 *  function ($time) use ($logger) {
 *      if ($time > 50) {
 *          $logger->log($time);
 *      }
 *  },
 *  function ($time) use ($bar) {
 *      if ($time < 5) {
 *          $bar->notify($time);
 *      }
 *  }
 * ));
 * $t->stopCountdown($name);
 *
 * // Získání interních dat pro další zpracování
 * $t->getData(Timer::TIMER, $name);
 *
 * </pre>
 *
 * Nativní struktury jsou:
 * <b>Timer::TIMER</b>
 * <pre>
 * array(
 *  'start' => (double)
 *  'laps' =>
 *      array(
 *          0 => (double)
 *          * => (double)
 *      )
 *  'end' => (double)
 * )
 * </pre>
 *
 * <b>Timer::COUNTDOWN</b>
 * <pre>
 * array(
 *  'start' => (double)
 *  'callbacks' => (array)
 *  'userData' => (array)
 *  'end' => (double)
 * )
 * </pre>
 *
 * <b>Timer::MEMORY</b>
 * <pre>
 * array(
 *  'start' => (integer)
 *  'end' => (integer)
 * )
 * </pre>
 */
class Timer
{
    /**
     * Typy datových struktur.
     *
     * @var string
     */
    const
        TIMER = 'timer',
        COUNTDOWN = 'countdown',
        MEMORY = 'memory';

    /**
     * Interní datová struktura.
     *
     * @var array
     */
    protected $data;

    /**
     * Vytvoří interní datovou strukturu.
     */
    public function __construct()
    {
        $this->data = array(
            self::TIMER     => array(),
            self::COUNTDOWN => array(),
            self::MEMORY    => array()
        );
    }

    /**
     * Spustí timer.
     *
     * Když nebude zadán název, vygeneruje se náhodný identifikátor a ten se vrátí.
     *
     * @param string $name Název timer
     * @return string Název timer
     * @see Timer::lap()
     * @see Timer::stop()
     */
    public function start($name = null)
    {
        if ($name === null) {
            $name = uniqid(self::TIMER . '_');
        }

        $time                           = microtime(true);
        $this->data[self::TIMER][$name] = array(
            'start' => $time,
            'laps'  => array(),
            'end'   => null
        );

        return $name;
    }

    /**
     * Ukončí timer a vrátí uběhlý čas od jeho spuštění.
     *
     * @param string $name Pojmenovaný timer
     * @return double Uběhlý čas
     * @throws \InvalidArgumentException Pokud timer se zadaným názvem neexistuje
     * @see Timer::start()
     * @see Timer::lap()
     */
    public function stop($name)
    {
        if (!isset($this->data[self::TIMER][$name])) {
            throw new \InvalidArgumentException('Timer "' . $name . '" neexistuje');
        }

        $timer        = & $this->data[self::TIMER][$name];
        $timer['end'] = microtime(true);

        return $timer['end'] - $timer['start'];
    }

    /**
     * Zaznamená jedno kolo a vrátí uběhlý čas od posledního kola nebo od začátku u prvního volání.
     *
     * @param string $name Název timeru
     * @return double Uběhlý čas od posledního kola nebo od začátku u prvního kola
     * @throws \InvalidArgumentException Pokud timer se zadaným názvem neexistuje
     * @see Timer::start();
     * @see Timer::stop();
     */
    public function lap($name)
    {
        if (!isset($this->data[self::TIMER][$name])) {
            throw new \InvalidArgumentException('Timer "' . $name . '" neexistuje');
        }

        $timer = & $this->data[self::TIMER][$name];
        if (empty($timer['laps'])) {
            $startTime = $timer['start'];
        } else {
            $startTime = end($timer['laps']);
        }

        $endTime         = microtime(true);
        $timer['laps'][] = $endTime;

        return $endTime - $startTime;
    }

    /**
     * Spustí countdown.
     *
     * Callbacky se vykonají při ukončení pomocí {@see Timer::stopCountdown()}.
     * Když nebude zadán název, vygeneruje se náhodný identifikátor a ten se vrátí.
     *
     * @param string $name      Název countdownu
     * @param array  $callbacks Pole callbacků function ($timer[, $userData[0], $userData[1], ...]) {}
     * @param array  $userData  Uživatelská data
     * @return string Název timeru
     * @see Timer::stopCountdown();
     */
    public function startCountdown($name = null, array $callbacks = array(), array $userData = array())
    {
        if ($name === null) {
            $name = uniqid(self::COUNTDOWN . '_');
        }

        $this->data[self::COUNTDOWN][$name] = array(
            'start'     => microtime(true),
            'callbacks' => $callbacks,
            'userData'  => $userData,
            'end'       => null
        );

        return $name;
    }

    /**
     * Ukončí countdown a vykoná veškeré uložené callbacky.
     *
     * @param string $name Název countdownu
     * @return array Pole výsledků callbacků, v pořadí v jakém byly definovány
     * @throws \InvalidArgumentException Pokud countdown se zadaným názvem neexistuje
     * @see Timer::startCountdown()
     */
    public function stopCountdown($name)
    {
        if (!isset($this->data[self::COUNTDOWN][$name])) {
            throw new \InvalidArgumentException('Countdown "' . $name . '" neexistuje');
        }

        $countdown        = & $this->data[self::COUNTDOWN][$name];
        $countdown['end'] = microtime(true);
        $duration         = $countdown['end'] - $countdown['start'];
        array_unshift($countdown['userData'], $duration);

        $callbackResults = array();
        foreach ($countdown['callbacks'] as $callback) {
            $callbackResults[] = call_user_func_array($callback, $countdown['userData']);
        }

        return $callbackResults;
    }

    /**
     * Spustí měření paměti.
     *
     * Když nebude zadán název, vygeneruje se náhodný identifikátor a ten se vrátí.
     *
     * @param string $name Název měření
     * @return string Název měření
     * @see Timer::unwatchMemory()
     */
    public function watchMemory($name = null)
    {
        if ($name === null) {
            $name = uniqid(self::MEMORY . '_');
        }

        $memory                          = memory_get_usage(true);
        $this->data[self::MEMORY][$name] = array(
            'start' => $memory,
            'end'   => null
        );

        return $name;
    }

    /**
     * Ukončí měření paměti a vrátí rozdíl od jeho spuštění.
     *
     * @param string $name Název měření
     * @return integer Spotřebovaná paměť
     * @throws \InvalidArgumentException Pokud měření se zadaným názvem neexistuje
     * @see Timer::watchMemory()
     */
    public function unwatchMemory($name)
    {
        if (!isset($this->data[self::MEMORY][$name])) {
            throw new \InvalidArgumentException('Memory watcher "' . $name . '" neexistuje');
        }

        $memory        = & $this->data[self::MEMORY][$name];
        $memory['end'] = memory_get_usage(true);

        return $memory['end'] - $memory['start'];
    }

    /**
     * Vrátí interní data pro daný typ a název měření.
     *
     * @param string $type Typ měření z výčtu [Timer::TIMERS, Timer::COUNTDOWNS, Timer::MEMORY]
     * @param string $name Název měření
     * @return array Data měření
     * @throws \InvalidArgumentException Pokud typ nebo měření daného typu neexistuje
     * @see Timer Definice nativních struktur
     */
    public function getData($type, $name)
    {
        if (!isset($this->data[$type])) {
            throw new \InvalidArgumentException('Typ "' . $type . '" není definován');
        }

        if (!isset($this->data[$type][$name])) {
            throw new \InvalidArgumentException('Měření "' . $type . ':' . $name . '" není definováno');
        }

        return $this->data[$type][$name];
    }
}
