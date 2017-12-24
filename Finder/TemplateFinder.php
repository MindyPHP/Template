<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Finder;

/**
 * Class TemplateFinder
 */
class TemplateFinder implements FinderInterface
{
    /**
     * @var array
     */
    protected $paths;

    /**
     * TemplateFinder constructor.
     *
     * @param $paths
     */
    public function __construct($paths)
    {
        $this->paths = (array) $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param $template
     *
     * @return \Generator
     */
    protected function iteratePaths($template): \Generator
    {
        foreach ($this->paths as $path) {
            yield sprintf('%s/%s', rtrim($path, '/'), ltrim($template, '/'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $template)
    {
        foreach ($this->iteratePaths($template) as $path) {
            if (is_file($path)) {
                return filemtime($path);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $template)
    {
        foreach ($this->iteratePaths($template) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(string $path)
    {
        if (is_file($path)) {
            return file_get_contents($path);
        }

        return null;
    }
}
