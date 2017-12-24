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

class SpacelessNodeTest extends AbstractTemplateTestCase
{
    public function providerInclude()
    {
        return [
            [
                '{% spaceless %}<div>123</div>  <div>Hello world</div>{% endspaceless %}',
                '<div>123</div><div>Hello world</div>',
                [],
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
