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

class ForElseTest extends AbstractTemplateTestCase
{
    public function providerInclude()
    {
        return [
            [
                '{% for x in y %}{{ x }}{% else %}else condition{% endfor %}',
                'else condition',
                ['y' => []],
            ],
            [
                '{% for x in y %}{{ x }}{% else %}else condition{% endfor %}',
                '12',
                ['y' => [1, 2]],
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
