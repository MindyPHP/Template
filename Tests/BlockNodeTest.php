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

class BlockNodeTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'block.html' => '{% block content %}{% endblock %}',
        ];
    }

    public function providerBlock()
    {
        return [
            [
                '{% extends "block.html" %}{% block content %}just a text{% endblock %}',
                'just a text',
                [],
            ],
            [
                '{% extends "block.html" %}{% block content %}hello {{ world }}{% endblock %}',
                'hello world',
                ['world' => 'world'],
            ],
        ];
    }

    /**
     * @dataProvider providerBlock
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testBlock(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }
}
