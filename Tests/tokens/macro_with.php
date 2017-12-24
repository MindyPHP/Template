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
    new Token(Token::NAME, 'macro', 1, 4),
    new Token(Token::NAME, 'foobar', 1, 10),
    new Token(Token::OPERATOR, '(', 1, 16),
    new Token(Token::NAME, 'name', 1, 17),
    new Token(Token::OPERATOR, '=', 1, 21),
    new Token(Token::STRING, 'none', 1, 22),
    new Token(Token::OPERATOR, ')', 1, 28),
    new Token(Token::BLOCK_END, '%}', 1, 30),
    new Token(Token::TEXT, "\nMacro: ", 1, 32),

    new Token(Token::OUTPUT_BEGIN, '{{', 2, 8),
    new Token(Token::NAME, 'name', 2, 11),
    new Token(Token::OUTPUT_END, '}}', 2, 16),
    new Token(Token::TEXT, "\n", 2, 18),

    new Token(Token::BLOCK_BEGIN, '{%', 3, 1),
    new Token(Token::NAME, 'assign', 3, 4),
    new Token(Token::NAME, 'foo', 3, 11),
    new Token(Token::OPERATOR, '=', 3, 15),
    new Token(Token::STRING, 'BAR', 3, 17),
    new Token(Token::BLOCK_END, '%}', 3, 23),
    new Token(Token::TEXT, "\n", 3, 25),

    new Token(Token::BLOCK_BEGIN, '{%', 4, 1),
    new Token(Token::NAME, 'yield', 4, 4),
    new Token(Token::OPERATOR, '(', 4, 9),
    new Token(Token::NAME, 'i', 4, 10),
    new Token(Token::OPERATOR, '=', 4, 11),
    new Token(Token::NAME, 'i', 4, 12),
    new Token(Token::OPERATOR, '+', 4, 13),
    new Token(Token::NUMBER, '1', 4, 14),
    new Token(Token::OPERATOR, ')', 4, 15),
    new Token(Token::BLOCK_END, '%}', 4, 17),
    new Token(Token::TEXT, "\n", 4, 19),

    new Token(Token::BLOCK_BEGIN, '{%', 5, 1),
    new Token(Token::NAME, 'assign', 5, 4),
    new Token(Token::NAME, 'i', 5, 11),
    new Token(Token::OPERATOR, '=', 5, 13),
    new Token(Token::NUMBER, '42', 5, 15),
    new Token(Token::BLOCK_END, '%}', 5, 18),
    new Token(Token::TEXT, "\n", 5, 20),

    new Token(Token::BLOCK_BEGIN, '{%', 6, 1),
    new Token(Token::NAME, 'yield', 6, 4),
    new Token(Token::BLOCK_END, '%}', 6, 10),
    new Token(Token::TEXT, "\n", 6, 12),

    new Token(Token::OUTPUT_BEGIN, '{{', 7, 1),
    new Token(Token::CONSTANT, 'false', 7, 4),
    new Token(Token::OUTPUT_END, '}}', 7, 10),
    new Token(Token::TEXT, "\n", 7, 12),

    new Token(Token::BLOCK_BEGIN, '{%', 8, 1),
    new Token(Token::NAME, 'endmacro', 8, 4),
    new Token(Token::BLOCK_END, '%}', 8, 13),
    new Token(Token::TEXT, "\n\n", 8, 15),

    new Token(Token::BLOCK_BEGIN, '{%', 10, 1),
    new Token(Token::NAME, 'assign', 10, 4),
    new Token(Token::NAME, 'foo', 10, 11),
    new Token(Token::OPERATOR, '=', 10, 15),
    new Token(Token::STRING, 'bar', 10, 17),
    new Token(Token::BLOCK_END, '%}', 10, 23),
    new Token(Token::TEXT, "\n\n", 10, 25),

    new Token(Token::BLOCK_BEGIN, '{%', 12, 1),
    new Token(Token::NAME, 'for', 12, 4),
    new Token(Token::NAME, 'i', 12, 8),
    new Token(Token::OPERATOR, 'in', 12, 10),
    new Token(Token::OPERATOR, '[', 12, 13),
    new Token(Token::NUMBER, '1', 12, 14),
    new Token(Token::OPERATOR, ',', 12, 15),
    new Token(Token::NUMBER, '2', 12, 16),
    new Token(Token::OPERATOR, ',', 12, 17),
    new Token(Token::NUMBER, '3', 12, 18),
    new Token(Token::OPERATOR, ']', 12, 19),
    new Token(Token::BLOCK_END, '%}', 12, 21),
    new Token(Token::TEXT, "\n", 12, 23),

    new Token(Token::BLOCK_BEGIN, '{%', 13, 1),
    new Token(Token::NAME, 'call', 13, 4),
    new Token(Token::NAME, 'foobar', 13, 9),
    new Token(Token::OPERATOR, '(', 13, 15),
    new Token(Token::NAME, 'age', 13, 16),
    new Token(Token::OPERATOR, '=', 13, 19),
    new Token(Token::NUMBER, '10', 13, 20),
    new Token(Token::OPERATOR, ')', 13, 22),
    new Token(Token::NAME, 'with', 13, 24),
    new Token(Token::BLOCK_END, '%}', 13, 29),
    new Token(Token::TEXT, "\n", 13, 31),

    new Token(Token::OUTPUT_BEGIN, '{{', 14, 1),
    new Token(Token::STRING, 'inside block', 14, 4),
    new Token(Token::OPERATOR, '..', 14, 19),
    new Token(Token::NAME, 'age', 14, 22),
    new Token(Token::OPERATOR, '..', 14, 26),
    new Token(Token::NAME, 'foo', 14, 29),
    new Token(Token::OPERATOR, '..', 14, 33),
    new Token(Token::STRING, '=', 14, 36),
    new Token(Token::OPERATOR, '..', 14, 40),
    new Token(Token::NAME, 'i', 14, 43),
    new Token(Token::NAME, 'if', 14, 45),
    new Token(Token::CONSTANT, 'true', 14, 48),
    new Token(Token::OUTPUT_END, '}}', 14, 53),
    new Token(Token::TEXT, "\n", 14, 55),

    new Token(Token::BLOCK_BEGIN, '{%', 15, 1),
    new Token(Token::NAME, 'endcall', 15, 4),
    new Token(Token::BLOCK_END, '%}', 15, 12),
    new Token(Token::TEXT, "\n", 15, 14),

    new Token(Token::BLOCK_BEGIN, '{%', 16, 1),
    new Token(Token::NAME, 'endfor', 16, 4),
    new Token(Token::BLOCK_END, '%}', 16, 11),
    new Token(Token::TEXT, "\n", 16, 13),

    new Token(Token::EOF, null, 17, 1),
];
