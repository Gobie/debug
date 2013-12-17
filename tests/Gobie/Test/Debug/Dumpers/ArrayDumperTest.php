<?php

namespace Gobie\Test\Debug\Dumpers;

use Gobie\Debug\Dumpers\ArrayDumper;

/**
 * Test for ArrayDumper.
 */
class ArrayDumperTest extends BaseDumperTest
{

    /**
     * @dataProvider provideDump
     */
    public function testDump($input, $expectedOutput)
    {
        $this->dump(new ArrayDumper(), $input, $expectedOutput);
    }

    /**
     * @return array
     */
    public function provideDump()
    {
        $a   = array();
        $a[] = & $a;

        $b   = array();
        $b[] = array();
        $b[] = & $b;

        $c      = array();
        $c[]    = & $c;
        $c[0][] = $c;

        return array(
            'simple recursion'        => array(
                $a,
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**'
            ),
            'complex recursion'       => array(
                $b,
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(0)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(0)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**'
            ),
            'different from var_dump' => array(
                $c,
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span> ...' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span> ...' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span> ...' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span> ...' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(2)</span> ...'
            ),
            'deep structure'          => array(
                array(
                    array(
                        array(
                            array(
                                array(
                                    array()
                                )
                            )
                        )
                    ),
                    array()
                ),
                '<b>array</b> <span class="dump_arg_desc">(2)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  |  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(1)</span> ...' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>1<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(0)</span>'
            ),
            'empty array'             => array(
                array(),
                '<b>array</b> <span class="dump_arg_desc">(0)</span>'
            )
        );
    }
}
