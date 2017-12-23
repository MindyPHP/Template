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
 * Interface FinderInterface
 */
interface FinderInterface
{
    /**
     * @param string $template
     *
     * @return string|null
     */
    public function find(string $template);

    /**
     * @param string $path
     *
     * @return string
     */
    public function getContents(string $path): string;

    /**
     * @param string $path
     *
     * @return int|null
     */
    public function lastModified(string $path);

    /**
     * @return array
     */
    public function getPaths(): array;
}
