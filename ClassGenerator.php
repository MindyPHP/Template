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

final class ClassGenerator
{
    const CLASS_PREFIX = '__MindyTemplate_';

    public static function generateName($name): string
    {
        return hash('crc32', $name);
    }

    public static function generateClass($name): string
    {
        return self::CLASS_PREFIX.self::generateName($name);
    }
}
