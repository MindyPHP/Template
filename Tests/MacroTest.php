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

class MacroTest extends AbstractTemplateTestCase
{
    public function providerLoadFromString()
    {
        return [
            [
                '{% macro example() %}hello world{% endmacro %}{{ @example() }}',
                'hello world',
                [],
            ],
            [
                '{% macro example() %}hello world{% endmacro %}{{ @example }}',
                'hello world',
                [],
            ],
            [
                '{% macro example() %}{{ variable }}{% endmacro %}{{ @example }}',
                'hello world',
                ['variable' => 'hello world'],
            ],
            [
                '{% macro example(foo, bar) %}{{ foo }}:{{ bar }}{% endmacro %}{{ @example("foo","bar") }}',
                'foo:bar',
                [
                    'foo' => 'hello',
                    'bar' => 'world',
                ],
            ],
            [
                '{% macro example(foo, bar) %}{{ foo }}:{{ bar }}{% endmacro %}{{ @example(foo, bar) }}',
                'hello:world',
                [
                    'foo' => 'hello',
                    'bar' => 'world',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerLoadFromString
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testLoadFromString(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }
}
