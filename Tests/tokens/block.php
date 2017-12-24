<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Template\Tests;

use Mindy\Template\Token;

return [
    new Token(Token::BLOCK_BEGIN, '{%', 1, 1),
    new Token(Token::NAME, 'extends', 1, 4),
    new Token(Token::STRING, 'layouts/base.html', 1, 12),
    new Token(Token::BLOCK_END, '%}', 1, 32),
    new Token(Token::TEXT, "\n\n", 1, 34),

    new Token(Token::BLOCK_BEGIN, '{%', 3, 1),
    new Token(Token::NAME, 'block', 3, 4),
    new Token(Token::NAME, 'main', 3, 10),
    new Token(Token::BLOCK_END, '%}', 3, 15),

    new Token(Token::TEXT, "\n<h1>block.html</h1>\n", 3, 17),

    new Token(Token::BLOCK_BEGIN, '{%', 5, 1),
    new Token(Token::NAME, 'endblock', 5, 4),
    new Token(Token::BLOCK_END, '%}', 5, 13),
    new Token(Token::TEXT, "\n", 5, 15),

    new Token(Token::EOF, null, 6, 1),
];
