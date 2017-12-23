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

class ImportTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'macroses.html' => '{% macro example() %}hello world{% endmacro %}',
        ];
    }

    protected function getTemplateFinder(): FinderInterface
    {
        return new StaticTemplateFinder($this->getTemplatePaths());
    }

    public function providerImport()
    {
        return [
            [
                '{% import "macroses.html" as macro %}{{ @macro.example() }}',
                'hello world',
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerImport
     *
     * @param string $template
     * @param string $result
     * @param array  $data
     */
    public function testImport(string $template, string $result, array $data)
    {
        $this->assertSame(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }
}
