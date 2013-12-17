<?php

namespace Gobie\Test\Debug\Dumpers;


use Gobie\Debug\Dumpers\Base64Dumper;

/**
 * Test for Base64Dumper.
 */
class Base64DumperTest extends BaseDumperTest
{

    /**
     * @dataProvider provideDump
     */
    public function testDump($input, $expectedOutput)
    {
        $this->dump(new Base64Dumper(), $input, $expectedOutput);
    }

    /**
     * return array
     */
    public function provideDump()
    {
        return array(
            'short' => array(
                'dGVzdA==',
                '<span class="dump_arg_string">"dGVzdA=="</span> <span class="dump_arg_desc">(8)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span><span class="dump_arg_desc">guessing Base64 encoded string</span>'
                . PHP_EOL .
                '<span class="dump_arg_indent">|  </span><span class="dump_arg_expanded">"test"</span>'
            )
        );
    }

    /**
     * @dataProvider provideCanDump
     */
    public function testCanDump($input, $expectedOutput)
    {
        $this->canDump(new Base64Dumper(), $input, $expectedOutput);
    }

    /**
     * @return array
     */
    public function provideCanDump()
    {
        return array(
            'empty string'                      => array(
                '',
                false
            ),
            'length not divisible by four'      => array(
                'tests',
                false
            ),
            'non-base64 characters'             => array(
                '12 45678',
                false
            ),
            'non-utf-8 characters after decode' => array(
                'dGVzdaa=',
                false
            ),
        );
    }
}
