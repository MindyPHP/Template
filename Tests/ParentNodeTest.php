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

class ParentNodeTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'parent.html' => '{% block content %}hello world{% endblock %}',
        ];
    }

    public function providerParent()
    {
        return [
            [
                '{% extends "parent.html" %}{% block content %}{% parent %} 123{% endblock %}',
                'hello world 123',
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerParent
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
