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

use Mindy\Template\LoaderMode;
use Mindy\Template\SyntaxError;

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 01/08/14.08.2014 13:51
 */
class BaseTest extends AbstractTemplateTestCase
{
    public function providerBase()
    {
        return [
//            ['{{ a }}', ['a' => 'b'], 'b'],
//            // Concat
//            ['{{ a ~ b }}', ['a' => 'a', 'b' => 'b'], 'ab'],
//            // Cycles
//            ['{% for i in data %}{{ i }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
//            ['{% for t, i in data %}{% if t > 1 %}{% break %}{% endif %}{{ i }}{% endfor %}', ['data' => [1, 2, 3]], '12'],
//            // Cycles loop helper
//            ['{% for i in data %}{{ loop.counter }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
//            ['{% for i in data %}{{ loop.counter0 }}{% endfor %}', ['data' => [1, 2, 3]], '012'],
//            ['{% for i in data %}{{ forloop.counter }}{% endfor %}', ['data' => [1, 2, 3]], '123'],
//            ['{% for i in data %}{{ forloop.counter0 }}{% endfor %}', ['data' => [1, 2, 3]], '012'],
//            // Math
//            ['{{ a / b }}', ['a' => 10, 'b' => 2], '5'],
//            ['{{ a * b }}', ['a' => 10, 'b' => 2], '20'],
//            ['{{ a + b }}', ['a' => 10, 'b' => 2], '12'],
//            ['{{ a - b }}', ['a' => 10, 'b' => 2], '8'],
//            ['{{ a % b }}', ['a' => 10, 'b' => 2], '0'],
//            // Helper functions
            ['{{ "test"|contains("tes") }}', [], '1'],
            ['{{ "<div>123</div>"|raw }}', [], '<div>123</div>'],
            ['{{ "<div>123</div>"|escape }}', [], '&lt;div&gt;123&lt;/div&gt;'],
            // Escape by default test
            ['{{ "<div>123</div>" }}', [], '&lt;div&gt;123&lt;/div&gt;'],
        ];
    }

    /**
     * @dataProvider providerBase
     *
     * @param $template
     * @param array $data
     * @param $result
     */
    public function testTemplate($template, array $data, $result)
    {
        $this->assertEquals(
            $this->templateEngine->renderString($template, $data),
            $result
        );
    }

    protected function getTemplatePaths(): array
    {
        return [
            'mode.html' => 'mode.html',
        ];
    }

    public function testMode()
    {
        $finder = $this->templateEngine->getFinder();
        $path = $finder->find('mode.html');
        $cacheClass = $this->templateEngine->generateTemplateCacheClass($path);
        $cachePath = $this->templateEngine->resolveCacheTemplatePath($cacheClass);

        $this->assertSame('mode.html', $this->templateEngine->render('mode.html'));

        // Always mode
        $this->templateEngine->setMode(LoaderMode::RECOMPILE_ALWAYS);
        $this->assertTrue(
            $this->templateEngine->isNeedRecompile($cacheClass, $cachePath)
        );

        // Normal mode
        $this->templateEngine->setMode(LoaderMode::RECOMPILE_NORMAL);
        unlink($cachePath);
        $this->assertTrue(
            $this->templateEngine->isNeedRecompile($cacheClass, $cachePath)
        );

        touch($cachePath);
        $this->assertTrue(
            $this->templateEngine->isNeedRecompile($cacheClass, $cachePath)
        );

        // Never mode (production)
        $this->templateEngine->setMode(LoaderMode::RECOMPILE_NEVER);
        unlink($cachePath);
        $this->assertTrue(
            $this->templateEngine->isNeedRecompile($cacheClass, $cachePath)
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown mode 2, available 1,0,-1
     */
    public function testModeException()
    {
        $this->templateEngine->setMode(2);
    }

    public function testResolveCachedTemplatePath()
    {
        $this->assertSame('mode.html', $this->templateEngine->render('mode.html'));
        $this->assertSame('mode.html', $this->templateEngine->render('mode.html'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage unreadable.html is not a valid readable template
     */
    public function testUnreadableTemplate()
    {
        $this->templateEngine->render('unreadable.html');
    }

    /**
     * @expectedException \Mindy\Template\SyntaxError
     * @expectedExceptionMessageRegExp /malformed if statement in line \d+ char \d+/
     */
    public function testSyntaxErrorInTemplate()
    {
        $this->templateEngine->renderString('{% if x %}');
    }
}
