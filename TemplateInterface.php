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
 * Interface TemplateInterface
 */
interface TemplateInterface
{
    /**
     * @param array $context
     * @param array $blocks
     * @param array $macros
     * @param array $imports
     *
     * @return string
     */
    public function render(array $context = [], array $blocks = [], array $macros = [], array $imports = []): string;
}
