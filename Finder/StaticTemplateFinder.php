<?php

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
     * MockTemplateFinder constructor.
     * @param array $templates
     */
    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }

    /**
     * @param string $template
     *
     * @return string|null
     */
    public function find(string $template)
    {
        if (array_key_exists($template, $this->templates)) {
            return $template;
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
        return $this->templates[$path];
    }

    /**
     * @param string $path
     *
     * @return int|null
     */
    public function lastModified(string $path)
    {
        return time();
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return array_keys($this->templates);
    }
}
