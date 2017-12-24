<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template;

/**
 * Class LoaderMode
 */
final class LoaderMode
{
    const RECOMPILE_NEVER = -1;
    const RECOMPILE_NORMAL = 0;
    const RECOMPILE_ALWAYS = 1;

    /**
     * @return array
     */
    final public static function getModes(): array
    {
        return [
            self::RECOMPILE_ALWAYS,
            self::RECOMPILE_NORMAL,
            self::RECOMPILE_NEVER
        ];
    }
}
