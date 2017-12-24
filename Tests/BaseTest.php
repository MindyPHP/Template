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

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 01/08/14.08.2014 13:51
 */
class BaseTest extends AbstractTemplateTestCase
{
    public function providerBase()
    {
        return [
            ['{{ a }}', ['a' => 'b'], 'b'],
            // Concat
            ['{{ a ~ b }}', ['a' => 'a', 'b' => 'b'], 'ab'],
            // Cycles
            ['{% for i in data %}{{ i }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
            ['{% for t, i in data %}{% if t > 1 %}{% break %}{% endif %}{{ i }}{% endfor %}', ['data' => [1, 2, 3]], '12'],
            // Cycles loop helper
            ['{% for i in data %}{{ loop.counter }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
            ['{% for i in data %}{{ loop.counter0 }}{% endfor %}', ['data' => [1, 2, 3]], '012'],
            ['{% for i in data %}{{ forloop.counter }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
            ['{% for i in data %}{{ forloop.counter0 }}{% endfor %}', ['data' => [1, 2, 3]], '012'],
            // Math
            ['{{ a / b }}', ['a' => 10, 'b' => 2], '5'],
            ['{{ a * b }}', ['a' => 10, 'b' => 2], '20'],
            ['{{ a + b }}', ['a' => 10, 'b' => 2], '12'],
            ['{{ a - b }}', ['a' => 10, 'b' => 2], '8'],
            ['{{ a % b }}', ['a' => 10, 'b' => 2], '0'],
        ];
    }

    /**
     * @dataProvider providerBase
     *
     * @param $template
     * @param array $data
     * @param $result
     */
    public function testTemplate($template, array $data, $result)
    {
        $this->assertEquals(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }
}
