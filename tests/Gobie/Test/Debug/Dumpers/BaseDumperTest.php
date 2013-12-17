<?php

namespace Gobie\Test\Debug\Dumpers;

use Gobie\Debug\Dumpers\IDumper;

/**
 * Base class for all dumper tests.
 */
class BaseDumperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param IDumper $dumper         Dumper
     * @param mixed   $input          Variable
     * @param string  $expectedOutput Dump of variable
     */
    protected function dump(IDumper $dumper, $input, $expectedOutput)
    {
        $this->setDumperManager($dumper);
        $actualOutput = $dumper->dump($input);

        self::assertSame($expectedOutput, $actualOutput);
    }

    /**
     * @param IDumper $dumper Dumper
     */
    protected function setDumperManager(IDumper $dumper)
    {
        $dumperManager = self::getMock('\Gobie\Debug\DumperManager\IDumperManager');
        $dumperManager->expects($this->any())
                      ->method('dump')
                      ->will($this->returnCallback(
                                  function (&$value, $level, $depth) use ($dumper) {
                                      return $dumper->dump($value, $level, $depth);
                                  }
                      ));
        $dumper->setManager($dumperManager);
    }

    /**
     * @param IDumper $dumper         Dumper
     * @param mixed   $input          Variable
     * @param boolean $expectedOutput Can be dumped by this dumper?
     */
    protected function canDump(IDumper $dumper, $input, $expectedOutput)
    {
        $this->setDumperManager($dumper);
        $actualOutput = $dumper->canDump($input);

        self::assertSame($expectedOutput, $actualOutput);
    }
}
