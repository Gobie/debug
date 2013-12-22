<?php

namespace Gobie\Test\Debug\Dumpers;

use Gobie\Debug\Dumpers\BooleanDumper;

/**
 * Test for BooleanDumper.
 */
class BooleanDumperTest extends BaseDumperTest
{
    /**
     * @dataProvider provideDump
     */
    public function testDump($input, $expectedOutput)
    {
        $this->dump(new BooleanDumper(), $input, $expectedOutput);
    }

    /**
     * @return array
     */
    public function provideDump()
    {
        return array(
            'false' => array(
                false,
                '<span class="dump_arg_bool">FALSE</span>'
            ),
            'true'  => array(
                true,
                '<span class="dump_arg_bool">TRUE</span>'
            )
        );
    }
}
