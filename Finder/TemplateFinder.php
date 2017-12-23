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
     * @param string|array $paths
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

    protected function iteratePaths($template): \Generator
    {
        foreach ($this->paths as $path) {
            $result = sprintf(
                '%s/%s',
                rtrim($path, '/'),
                $template
            );
            yield $result;
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
     * @param string $path
     *
     * @return string
     */
    public function getContents(string $path): string
    {
        return file_get_contents($path);
    }
}
