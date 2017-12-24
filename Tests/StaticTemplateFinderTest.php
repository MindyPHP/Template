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

use Mindy\Template\Finder\StaticTemplateFinder;
use PHPUnit\Framework\TestCase;

class StaticTemplateFinderTest extends TestCase
{
    public function testFinder()
    {
        $finder = new StaticTemplateFinder([
            'example.html' => 'template content',
        ]);

        $this->assertSame(['example.html'], $finder->getPaths());

        $this->assertSame('example.html', $finder->find('example.html'));
        $this->assertNull($finder->find('foobar.html'));

        $this->assertSame(time(), $finder->lastModified('example.html'));
        $this->assertNull($finder->lastModified('foobar.html'));

        $this->assertSame('template content', $finder->getContents($finder->find('example.html')));
    }
}
