<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\Finder\ChainFinder;
use Mindy\Template\Finder\StaticTemplateFinder;
use Mindy\Template\Finder\TemplateFinder;
use PHPUnit\Framework\TestCase;

class ChainFinderTest extends TestCase
{
    public function testFinder()
    {
        $staticFinder = new StaticTemplateFinder([
            'example.html' => 'example.html'
        ]);
        $templateFinder = new TemplateFinder([
            __DIR__.'/data/templates'
        ]);
        $chainFinder = new ChainFinder([
            $staticFinder,
            $templateFinder
        ]);

        $this->assertSame('example.html', $chainFinder->getContents($chainFinder->find('example.html')));

        $this->assertSame(2, count($chainFinder->getPaths()));

        $this->assertSame(time(), $chainFinder->lastModified($chainFinder->find('example.html')));

        $this->assertNull($chainFinder->lastModified('foobar.html'));

        $this->assertNull($chainFinder->getContents('foobar.html'));
        
        $this->assertNull($chainFinder->find('foobar.html'));
    }
}
