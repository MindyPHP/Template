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

use Mindy\Template\Finder\FinderInterface;
use Mindy\Template\Finder\StaticTemplateFinder;

class ExtendNodeTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'base.html' => 'Yo',
            'clean.html' => 'No',
            'params.html' => '{{ foo }}',
        ];
    }

    protected function getTemplateFinder(): FinderInterface
    {
        return new StaticTemplateFinder($this->getTemplatePaths());
    }

    public function providerInclude()
    {
        return [
            [
                '{% extends "base.html" %}',
                'Yo',
                [],
            ],
            [
                '{% extends "base.html" if x %}',
                'Yo',
                ['x' => true],
            ],
            [
                '{% extends "base.html" if x %}',
                '',
                ['x' => false],
            ],
            [
                '{% extends x ? "base.html" : "clean.html" %}',
                'Yo',
                ['x' => true],
            ],
            [
                '{% extends x ? "base.html" : "clean.html" %}',
                'No',
                ['x' => false],
            ],
            [
                '{% extends "params.html" with ["foo" => "hello world"] %}',
                'hello world',
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
