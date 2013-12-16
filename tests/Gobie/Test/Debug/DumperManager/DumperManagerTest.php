<?php

namespace Gobie\Test\Debug\DumperManager;

use Gobie\Debug\DumperManager\DumperManager;
use Gobie\Debug\DumperManager\IDumperManager;

/**
 * Test for DumperManager.
 */
class DumperManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Dump with no registered Dumpers should throw \RuntimeException.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There is no registered dumper for type 'string'.
     */
    public function testNoDumpers()
    {
        $dumperManager = new DumperManager();
        $dumperManager->dump('no dumper');
    }

    /**
     * Dumper with invalid type should throw \InvalidArgumentException.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage IDumper::getType must return array of types it can dump.
     */
    public function testAddingDumperWithInvalidType()
    {
        $dumperMock = $this->createIDumperWithGetTypeNull(1, null);

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
    }

    /**
     * Dumper with unknown type should throw \InvalidArgumentException.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Type 'test' is unknown.
     */
    public function testAddingDumperWithUnknownType()
    {
        $dumperMock = $this->createIDumperWithGetTypeNull(1, array('test'));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
    }

    /**
     * Dumper must be registered only once.
     */
    public function testAddingSameDumperMultipleTimes()
    {
        $dumperMock = $this->createIDumperWithGetTypeNull(3, array(IDumperManager::T_NULL));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock)
                      ->addDumper($dumperMock)
                      ->addDumper($dumperMock);

        self::assertCount(1, $dumperManager->getDumpers());
    }

    /**
     * Multiple dumpers can be set through constructor or setter.
     */
    public function testAddingMultipleDumpers()
    {
        $dumperMock1 = $this->createIDumperWithGetTypeNull();
        $dumperMock2 = $this->createIDumperWithGetTypeNull();

        $dumperManager = new DumperManager(array($dumperMock1));
        $dumperManager->addDumper($dumperMock2);

        self::assertCount(2, $dumperManager->getDumpers());
    }

    /**
     * Dumper must verify against given data or else is skipped.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No dumper capable of dumping variable '' of type 'NULL' found.
     */
    public function testUnverifiableDumpers()
    {
        $dumperMock = $this->createIDumperWithGetTypeNull();
        $dumperMock->expects($this->exactly(1))
                   ->method('canDump')
                   ->will($this->returnValue(false));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
        $dumperManager->dump(null);
    }

    /**
     * Dump of variable.
     */
    public function testDumping()
    {
        $dumperMock = $this->createIDumperWithGetTypeNull();
        $dumperMock->expects($this->exactly(1))
                   ->method('canDump')
                   ->will($this->returnValue(true));
        $dumperMock->expects($this->exactly(1))
                   ->method('dump')
                   ->will($this->returnValue('NULL'));

        $dumperManager = new DumperManager();
        $dumperManager->addDumper($dumperMock);
        $out = $dumperManager->dump(null);

        self::assertEquals('NULL', $out);
    }

    /**
     * Create IDumper mock for later use.
     *
     * @param int   $called      Expected to be called
     * @param array $returnValue Return value of getType
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createIDumperWithGetTypeNull($called = 1, $returnValue = array(IDumperManager::T_NULL))
    {
        $dumperMock = self::getMock('\Gobie\Debug\Dumpers\IDumper');
        $dumperMock->expects($this->exactly($called))
                   ->method('getTypes')
                   ->will($this->returnValue($returnValue));

        return $dumperMock;
    }
}
