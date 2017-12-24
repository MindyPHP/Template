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

use Mindy\Template\Finder\TemplateFinder;
use PHPUnit\Framework\TestCase;

class TemplateFinderTest extends TestCase
{
    public function testFinder()
    {
        $path = __DIR__.'/data/templates';
        $finder = new TemplateFinder($path);

        $this->assertSame([$path], $finder->getPaths());

        $this->assertSame($path.'/example.html', $finder->find('example.html'));
        $this->assertNull($finder->find('foobar.html'));

        $this->assertSame(filemtime($path.'/example.html'), $finder->lastModified('example.html'));
        $this->assertNull($finder->lastModified('foobar.html'));

        $this->assertSame('{{ data }}', $finder->getContents($finder->find('example.html')));
    }
}
