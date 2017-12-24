<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\Library\AbstractLibrary;

class TestLibrary extends AbstractLibrary
{
    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'hello_world' => function () {
                return 'hello_world';
            }
        ];
    }
}

class LibraryTest extends AbstractTemplateTestCase
{
    public function testLibraries()
    {
        $this->templateEngine->addLibrary(new TestLibrary);
        $this->assertSame(1, count($this->templateEngine->getHelpers()));

        $this->assertSame('hello_world', $this->templateEngine->renderString('{{ hello_world() }}'));
    }
}
