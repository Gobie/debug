<?php

namespace Gobie\Test\Debug\Dumpers;

use Gobie\Debug\Dumpers\AbstractDumper;

/**
 * Test for AbstractDumper.
 */
class AbstractDumperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $setTypeArguments
     * @param $expectedTypes
     * @dataProvider provideTypes
     */
    public function testSetAndGetTypes($setTypeArguments, $expectedTypes)
    {
        /** @var $dumper AbstractDumper */
        $dumper      = self::getMockForAbstractClass('\Gobie\Debug\Dumpers\AbstractDumper');
        $actualTypes = call_user_func_array(array($dumper, 'setTypes'), $setTypeArguments)->getTypes();

        self::assertEquals($expectedTypes, $actualTypes);
    }

    /**
     * @return array
     */
    public function provideTypes()
    {
        return array(
            'scalar argument'    => array(
                array('type1'),
                array('type1')
            ),
            'scalar arguments'   => array(
                array('type1', 'type2'),
                array('type1', 'type2')
            ),
            'array argument'     => array(
                array(array('type1')),
                array('type1')
            ),
            'array arguments'    => array(
                array(array('type1', 'type2')),
                array('type1', 'type2')
            ),
            'mixed arguments #1' => array(
                array(array('type1'), 'ignored'),
                array('type1')
            ),
            'mixed arguments #2' => array(
                array(array('type1', 'type2'), array('ignored')),
                array('type1', 'type2')
            ),
        );
    }

    public function testSetManager()
    {
        /** @var $dumper AbstractDumper */
        $dumper        = self::getMockForAbstractClass('\Gobie\Debug\Dumpers\AbstractDumper');
        $dumperManager = self::getMock('\Gobie\Debug\DumperManager\IDumperManager');
        $dumper->setManager($dumperManager);

        self::assertSame($dumperManager, $dumper->getManager());
    }

    public function testSetSameManagerMultipleTimes()
    {
        /** @var $dumper AbstractDumper */
        $dumper        = self::getMockForAbstractClass('\Gobie\Debug\Dumpers\AbstractDumper');
        $dumperManager = self::getMock('\Gobie\Debug\DumperManager\IDumperManager');
        $dumper->setManager($dumperManager)
               ->setManager($dumperManager);

        self::assertSame($dumperManager, $dumper->getManager());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Dumper has already different DumperManager set.
     */
    public function testSetDifferentManagers()
    {
        /** @var $dumper AbstractDumper */
        $dumper         = self::getMockForAbstractClass('\Gobie\Debug\Dumpers\AbstractDumper');
        $dumperManager1 = self::getMock('\Gobie\Debug\DumperManager\IDumperManager');
        $dumperManager2 = self::getMock('\Gobie\Debug\DumperManager\IDumperManager');

        $dumper->setManager($dumperManager1)
               ->setManager($dumperManager2);
    }

}
