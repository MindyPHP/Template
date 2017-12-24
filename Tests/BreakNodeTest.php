<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

class BreakNodeTest extends AbstractTemplateTestCase
{
    public function providerBreak()
    {
        return [
            [
                '{% for x in y %}{% if loop.counter0 > 0 %}{% break %}{% endif %}{{ x }}{% endfor %}',
                '1',
                ['y' => [1, 2, 3, 4, 5]],
            ],
        ];
    }

    /**
     * @dataProvider providerBreak
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testBreak(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }

    /**
     * @expectedException \Mindy\Template\SyntaxError
     * @expectedExceptionMessage unexpected break, not in for loop
     */
    public function testBreakException()
    {
        $this->templateEngine->renderString('{% break %}');
    }
}
