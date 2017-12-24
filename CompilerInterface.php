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

use Mindy\Template\Node\Node;

interface CompilerInterface
{
    /**
     * @param Node $node
     * @param int  $indent
     * @param bool $line
     */
    public function addTraceInfo(Node $node, int $indent = 0, bool $line = true);

    /**
     * @param bool $export
     *
     * @return mixed
     */
    public function getTraceInfo(bool $export = false);

    /**
     * @void
     */
    public function compile();

    /**
     * @param mixed $repr
     * @param int   $indent
     *
     * @return $this
     */
    public function repr($repr, int $indent = 0);

    /**
     * @param string $raw
     * @param int    $indent
     *
     * @return $this
     */
    public function raw(string $raw, int $indent = 0);
}
