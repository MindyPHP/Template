<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Library;

use Mindy\Template\Parser;
use Mindy\Template\TokenStream;

/**
 * Interface LibraryInterface
 */
interface LibraryInterface
{
    /**
     * @return array
     */
    public function getHelpers();

    /**
     * @return array
     */
    public function getTags();

    /**
     * @param Parser $parser
     *
     * @return $this
     */
    public function setParser(Parser $parser);

    /**
     * @param TokenStream $stream
     *
     * @return $this
     */
    public function setStream(TokenStream $stream);
}
