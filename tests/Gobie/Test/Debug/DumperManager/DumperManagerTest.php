<?php

namespace Gobie\Test\Debug\DumperManager;

use Gobie\Debug\DumperManager\DumperManager;
use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Class DumperManagerTest.
 */
class DumperManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There is no registered dumper for type 'string'.
     */
    public function testNoDumpers()
    {
        $dumperManager = new DumperManager();
        $dumperManager->dump('no dumper');
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testAddingInvalidTypeDumpers()
    {
        $dumperManager = new DumperManager();
        $dumperManager->addDumper(new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage IDumper::getType must return array of types it can dump.
     */
    public function testAddingDumperWithInvalidType()
    {
        $dumperMock = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock->expects($this->exactly(1))
                   ->method('getType')
                   ->will($this->returnValue(null));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Type 'test' is unknown.
     */
    public function testAddingDumperWithUnknownType()
    {
        $dumperMock = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock->expects($this->exactly(1))
                   ->method('getType')
                   ->will($this->returnValue(array('test')));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
    }

    public function testAddingSameDumperMultipleTimes()
    {
        $dumperMock = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock->expects($this->exactly(3))
                   ->method('getType')
                   ->will($this->returnValue(array(IDumperManager::T_NULL)));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock)
                      ->addDumper($dumperMock)
                      ->addDumper($dumperMock);

        self::assertCount(1, $dumperManager->getDumpers());
    }

    public function testAddingMultipleDumpers()
    {
        $dumperMock1 = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock1->expects($this->exactly(1))
                    ->method('getType')
                    ->will($this->returnValue(array(IDumperManager::T_NULL)));

        $dumperMock2 = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock2->expects($this->exactly(1))
                    ->method('getType')
                    ->will($this->returnValue(array(IDumperManager::T_BOOLEAN)));

        $dumperManager = new DumperManager(array($dumperMock1));
        $dumperManager->addDumper($dumperMock2);

        self::assertCount(2, $dumperManager->getDumpers());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There is no registered dumper for type 'NULL'.
     */
    public function testUnverifiableDumpers()
    {
        $dumperMock = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock->expects($this->exactly(1))
                   ->method('getType')
                   ->will($this->returnValue(array(IDumperManager::T_NULL)));
        $dumperMock->expects($this->exactly(1))
                   ->method('verify')
                   ->will($this->returnValue(false));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
        $dumperManager->dump(null);
    }

    public function testDumping()
    {
        $dumperMock = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock->expects($this->exactly(1))
                   ->method('getType')
                   ->will($this->returnValue(array(IDumperManager::T_NULL)));
        $dumperMock->expects($this->exactly(1))
                   ->method('verify')
                   ->will($this->returnValue(true));
        $dumperMock->expects($this->exactly(1))
                   ->method('dump')
                   ->will($this->returnValue('NULL'));
        $dumperMock->expects($this->exactly(1))
                   ->method('getReplacedClasses')
                   ->will($this->returnValue(array()));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
        $out = $dumperManager->dump(null);

        self::assertEquals('NULL', $out);
    }
}
