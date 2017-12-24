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
 * Class ChainFinder
 */
class ChainFinder implements FinderInterface
{
    /**
     * @var array|FinderInterface[]
     */
    protected $finders;

    /**
     * ChainFinder constructor.
     *
     * @param array $finders
     */
    public function __construct(array $finders)
    {
        $this->finders = $finders;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $template)
    {
        foreach ($this->finders as $finder) {
            if ($path = $finder->find($template)) {
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
        foreach ($this->finders as $finder) {
            if ($content = $finder->getContents($path)) {
                return $content;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path)
    {
        foreach ($this->finders as $finder) {
            if ($stamp = $finder->lastModified($path)) {
                return $stamp;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        $paths = [];
        foreach ($this->finders as $finder) {
            $paths = array_merge($paths, $finder->getPaths());
        }

        return $paths;
    }
}
