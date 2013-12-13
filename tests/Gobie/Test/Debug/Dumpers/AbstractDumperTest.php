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
    public function testSettingAndGettingTypes($setTypeArguments, $expectedTypes)
    {
        /** @var $dumper AbstractDumper */
        $dumper      = self::getMockForAbstractClass('\Gobie\Debug\Dumpers\AbstractDumper');
        $actualTypes = call_user_func_array(array($dumper, 'setType'), $setTypeArguments)->getType();
        self::assertEquals($expectedTypes, $actualTypes);
    }

    /**
     * @return array
     */
    public function provideTypes()
    {
        return array(
            // multiple scalar arguments
            array(
                array('type1'),
                array('type1')
            ),
            array(
                array('type1', 'type2'),
                array('type1', 'type2')
            ),
            // array argument
            array(
                array(array('type1')),
                array('type1')
            ),
            array(
                array(array('type1', 'type2')),
                array('type1', 'type2')
            ),
            // mixed argument
            array(
                array(array('type1'), 'ignored'),
                array('type1')
            ),
            array(
                array(array('type1', 'type2'), array('ignored')),
                array('type1', 'type2')
            ),
        );
    }
}
