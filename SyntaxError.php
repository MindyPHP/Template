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

use Exception;

/**
 * Class SyntaxError.
 */
class SyntaxError extends Exception
{
    /**
     * @var Token
     */
    protected $token;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $content;

    /**
     * SyntaxError constructor.
     *
     * @param string $message
     * @param Token  $token
     */
    public function __construct($message, Token $token)
    {
        $this->token = $token;

        parent::__construct(sprintf(
            '%s in line %s char %s',
            $message,
            $token->getLine(),
            $token->getChar()
        ));
    }

    public function __toString()
    {
        return (string) $this->message;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setTemplateFile(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setTemplateContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getTemplateFile(): string
    {
        return $this->path;
    }

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }
}
