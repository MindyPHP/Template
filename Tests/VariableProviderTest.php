<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\VariableProviderInterface;

class TestVariableProvider implements VariableProviderInterface
{
    /**
     * @return array
     */
    public function getData()
    {
        return [
            'user' => [
                'id' => 1
            ]
        ];
    }
}

class VariableProviderTest extends AbstractTemplateTestCase
{
    protected function getTemplatePaths(): array
    {
        return [
            'variable_provider.html' => '{{ user.id }}'
        ];
    }

    public function testVariableProvider()
    {
        $this->templateEngine->addVariableProvider(new TestVariableProvider);
        $this->assertSame('1', $this->templateEngine->render('variable_provider.html'));
    }
}
