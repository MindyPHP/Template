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

class IncludeTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'example.html' => 'just a text',
            'example1.html' => 'example1',
            'example2.html' => '{{ foo }}'
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
                '{% include "example.html" %}',
                'just a text',
                [],
            ],
            [
                '{% include "example1.html" %}',
                'example1',
                [],
            ],
            [
                '{% include "example2.html" %}',
                '',
                [],
            ],
            [
                '{% include "example2.html" with ["foo" => "foobar"] %}',
                'foobar',
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
