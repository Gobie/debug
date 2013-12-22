<?php

namespace Gobie\Test\Debug\Dumpers;

use Gobie\Debug\Dumpers\NullDumper;

/**
 * Test for NullDumper.
 */
class NullDumperTest extends BaseDumperTest
{
    /**
     * @dataProvider provideDump
     */
    public function testDump($input, $expectedOutput)
    {
        $this->dump(new NullDumper(), $input, $expectedOutput);
    }

    /**
     * @return array
     */
    public function provideDump()
    {
        return array(
            'null' => array(
                null,
                '<span class="dump_arg_null">NULL</span>'
            )
        );
    }
}
