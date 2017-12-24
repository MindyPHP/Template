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

class IfElseNodeTest extends AbstractTemplateTestCase
{
    public function providerInclude()
    {
        return [
            [
                '{% if x %}1{% else %}2{% endif %}',
                '1',
                ['x' => true],
            ],
            [
                '{% if x %}1{% else %}2{% endif %}',
                '2',
                ['x' => false],
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
