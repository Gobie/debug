<?php

namespace Gobie\Test\Debug\Dumpers;

use Gobie\Debug\Dumpers\ArrayDumper;

/**
 * Test for ArrayDumper.
 */
class ArrayDumperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provideDump
     */
    public function testDumpRecursion($input, $expectedOutput)
    {
        $dumper        = new ArrayDumper();
        $dumperManager = self::getMock('\Gobie\Debug\DumperManager\DumperManager');
        $dumperManager->expects($this->any())
                      ->method('dump')
                      ->will($this->returnCallback(
                                  function (&$value, $level, $depth) use ($dumper) {
                                      return $dumper->dump($value, $level, $depth);
                                  }
                      ));
        $dumper->setManager($dumperManager);
        $actualOutput = $dumper->dump($input);

        self::assertSame($expectedOutput, $actualOutput);
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
            // Simple recursion
            array(
                $a,
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '<b>array</b> <span class="dump_arg_desc">(1)</span>' . PHP_EOL .
                '<span class="dump_arg_indent">|  |  </span>0<span class="dump_arg_keyword"> =&gt; </span>' .
                '**RECURSION**'
            ),
            // Complex recursion
            array(
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
            // Different from var_dump
            array(
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
            // Deep structure
            array(
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
            // Empty array
            array(
                array(),
                '<b>array</b> <span class="dump_arg_desc">(0)</span>'
            )
        );
    }
}
