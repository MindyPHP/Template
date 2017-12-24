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
    new Token(Token::OUTPUT_BEGIN, '{{', 1, 1),
    new Token(Token::STRING, 'this will be printed', 1, 4),
    new Token(Token::NAME, 'unless', 1, 27),
    new Token(Token::CONSTANT, 'false', 1, 34),
    new Token(Token::OUTPUT_END, '}}', 1, 40),
    new Token(Token::TEXT, "\n", 1, 42),

    new Token(Token::OUTPUT_BEGIN, '{{', 2, 1),
    new Token(Token::STRING, 'this will not be printed', 2, 4),
    new Token(Token::NAME, 'unless', 2, 31),
    new Token(Token::CONSTANT, 'true', 2, 38),
    new Token(Token::OUTPUT_END, '}}', 2, 43),
    new Token(Token::TEXT, "\n", 2, 45),

    new Token(Token::EOF, null, 3, 1),
];
