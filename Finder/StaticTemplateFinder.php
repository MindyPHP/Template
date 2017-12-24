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

class StaticTemplateFinder implements FinderInterface
{
    /**
     * @var array
     */
    protected $templates = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $template)
    {
        if (array_key_exists($template, $this->templates)) {
            return $template;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(string $path)
    {
        if (array_key_exists($path, $this->templates)) {
            return $this->templates[$path];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path)
    {
        if (array_key_exists($path, $this->templates)) {
            return time();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        return array_keys($this->templates);
    }
}
