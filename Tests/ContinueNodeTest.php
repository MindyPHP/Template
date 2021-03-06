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

class ContinueNodeTest extends AbstractTemplateTestCase
{
    public function providerContinue()
    {
        return [
            [
                '{% for x in y %}{% if loop.counter0 > 0 %}{% continue %}{% endif %}{{ x }}{% endfor %}',
                '1',
                ['y' => [1, 2, 3, 4, 5]],
            ],
        ];
    }

    /**
     * @dataProvider providerContinue
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testContinue(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }

    /**
     * @expectedException \Mindy\Template\SyntaxError
     * @expectedExceptionMessage unexpected continue, not in for loop
     */
    public function testContinueException()
    {
        $this->templateEngine->renderString('{% continue %}');
    }
}
