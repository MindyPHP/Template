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

class SetNodeTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'macroses.html' => '{% macro example() %}hello world{% endmacro %}',
        ];
    }

    public function providerInclude()
    {
        return [
            [
                '{% set x = 1 %}{{ x }}',
                '1',
                [],
            ],
            [
                '{% set x = ["x" => 1, "y" => 2] %}{{ x.x }}{{ x.y }}',
                '12',
                [],
            ],
            [
                '{% set x = y %}{{ x }}',
                '1',
                ['y' => 1],
            ],
            [
                '{% set x = y %}{{ x }}',
                '',
                [],
            ],
            [
                '{% set x = y ? 1 : 2 %}{{ x }}',
                '2',
                ['y' => false],
            ],
        ];
    }

    /**
     * @dataProvider providerInclude
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testInclude(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }
}
