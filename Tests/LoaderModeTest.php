<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;


use Mindy\Template\LoaderMode;
use PHPUnit\Framework\TestCase;

class LoaderModeTest extends TestCase
{
    public function testConsts()
    {
        $this->assertSame(-1, LoaderMode::RECOMPILE_NEVER);
        $this->assertSame(0, LoaderMode::RECOMPILE_NORMAL);
        $this->assertSame(1, LoaderMode::RECOMPILE_ALWAYS);
    }
}
