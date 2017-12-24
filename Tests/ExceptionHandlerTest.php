<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\Finder\StaticTemplateFinder;
use Mindy\Template\LoaderMode;
use Mindy\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    public function testException()
    {
        $errTemplate = '{% qwe';
        $templateEngine = new TemplateEngine(
            new StaticTemplateFinder([
                'example.html' => $errTemplate
            ]), __DIR__ . '/cache',
            LoaderMode::RECOMPILE_ALWAYS,
            true
        );
        $errorOutput = $templateEngine->renderString($errTemplate);
        $this->assertContains('unexpected "qwe", expecting a valid tag in line 1 char 4', $errorOutput);

        $errorOutput = $templateEngine->render('example.html');
        $this->assertContains('unexpected "qwe", expecting a valid tag in line 1 char 4', $errorOutput);
    }

    /**
     * @expectedException \Mindy\Template\SyntaxError
     */
    public function testExceptionThrowString()
    {
        $errTemplate = '{% qwe';
        $templateEngine = new TemplateEngine(
            new StaticTemplateFinder([
                'example.html' => $errTemplate
            ]), __DIR__ . '/cache',
            LoaderMode::RECOMPILE_ALWAYS);

        $templateEngine->renderString($errTemplate);
    }

    /**
     * @expectedException \Mindy\Template\SyntaxError
     */
    public function testExceptionThrowFile()
    {
        $errTemplate = '{% qwe';
        $templateEngine = new TemplateEngine(
            new StaticTemplateFinder([
                'example.html' => $errTemplate
            ]), __DIR__ . '/cache',
            LoaderMode::RECOMPILE_ALWAYS);

        $templateEngine->render('example.html');
    }
}
