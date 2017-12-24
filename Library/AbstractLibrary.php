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
 * Class Library.
 */
abstract class AbstractLibrary implements LibraryInterface
{
    /**
     * @var \Mindy\Template\Parser
     */
    protected $parser;
    /**
     * @var \Mindy\Template\TokenStream
     */
    protected $stream;

    /**
     * {@inheritdoc}
     */
    abstract public function getHelpers();

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setParser(Parser $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStream(TokenStream $stream)
    {
        $this->stream = $stream;

        return $this;
    }
}
