<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\Finder\FinderInterface;
use Mindy\Template\Finder\TemplateFinder;
use Mindy\Template\LoaderMode;
use Mindy\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTemplateTestCase extends TestCase
{
    /**
     * @var TemplateEngine
     */
    protected $templateEngine;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @void
     */
    protected function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->templateEngine = $this->getTemplateEngine();
    }

    /**
     * @void
     */
    public function tearDown()
    {
        $this->filesystem->remove(__DIR__ . '/cache');
        $this->templateEngine = null;
    }

    /**
     * @return array
     */
    protected function getTemplatePaths(): array
    {
        return [
            __DIR__ . '/templates'
        ];
    }

    /**
     * @return FinderInterface
     */
    protected function getTemplateFinder(): FinderInterface
    {
        return new TemplateFinder($this->getTemplatePaths());
    }

    /**
     * @return TemplateEngine
     */
    protected function getTemplateEngine(): TemplateEngine
    {
        return new TemplateEngine(
            $this->getTemplateFinder(),
            __DIR__ . '/cache',
            LoaderMode::RECOMPILE_ALWAYS
        );
    }
}
