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
    new Token(Token::OPERATOR, '[', 1, 4),
    new Token(Token::NUMBER, '1', 1, 5),
    new Token(Token::OPERATOR, ',', 1, 6),
    new Token(Token::NUMBER, '2', 1, 8),
    new Token(Token::OPERATOR, ',', 1, 9),
    new Token(Token::NUMBER, '3', 1, 11),
    new Token(Token::OPERATOR, ']', 1, 12),
    new Token(Token::OPERATOR, '[', 1, 13),
    new Token(Token::NUMBER, '0', 1, 14),
    new Token(Token::OPERATOR, ']', 1, 15),
    new Token(Token::OUTPUT_END, '}}', 1, 17),
    new Token(Token::TEXT, "\n", 1, 19),

    new Token(Token::OUTPUT_BEGIN, '{{', 2, 1),
    new Token(Token::OPERATOR, '[', 2, 4),
    new Token(Token::NUMBER, '1', 2, 5),
    new Token(Token::OPERATOR, ',', 2, 6),
    new Token(Token::NUMBER, '2', 2, 8),
    new Token(Token::OPERATOR, ',', 2, 9),
    new Token(Token::NUMBER, '3', 2, 11),
    new Token(Token::OPERATOR, ',', 2, 12),
    new Token(Token::OPERATOR, ']', 2, 13),
    new Token(Token::OPERATOR, '[', 2, 14),
    new Token(Token::NUMBER, '1', 2, 15),
    new Token(Token::OPERATOR, ']', 2, 16),
    new Token(Token::OUTPUT_END, '}}', 2, 18),
    new Token(Token::TEXT, "\n", 2, 20),

    new Token(Token::OUTPUT_BEGIN, '{{', 3, 1),
    new Token(Token::OPERATOR, '[', 3, 4),
    new Token(Token::STRING, 'foo', 3, 5),
    new Token(Token::OPERATOR, '=>', 3, 11),
    new Token(Token::NUMBER, '1', 3, 14),
    new Token(Token::OPERATOR, ',', 3, 15),
    new Token(Token::STRING, 'bar', 3, 17),
    new token(Token::OPERATOR, '=>', 3, 23),
    new Token(Token::NUMBER, '2', 3, 26),
    new Token(Token::OPERATOR, ',', 3, 27),
    new Token(Token::STRING, 'baz', 3, 29),
    new Token(Token::OPERATOR, '=>', 3, 35),
    new Token(Token::NUMBER, '3', 3, 38),
    new Token(Token::OPERATOR, ',', 3, 39),
    new Token(Token::OPERATOR, ']', 3, 40),
    new Token(Token::OPERATOR, '[', 3, 41),
    new Token(Token::STRING, 'baz', 3, 42),
    new Token(Token::OPERATOR, ']', 3, 47),
    new Token(Token::OUTPUT_END, '}}', 3, 49),
    new Token(Token::TEXT, "\n", 3, 51),

    new Token(Token::OUTPUT_BEGIN, '{{', 4, 1),
    new Token(Token::OPERATOR, '[', 4, 4),
    new Token(Token::NUMBER, '1', 4, 5),
    new Token(Token::OPERATOR, ']', 4, 6),
    new Token(Token::OPERATOR, '[', 4, 7),
    new Token(Token::NUMBER, '1', 4, 8),
    new Token(Token::OPERATOR, ']', 4, 9),
    new Token(Token::OUTPUT_END, '}}', 4, 11),
    new Token(Token::TEXT, "\n", 4, 13),

    new Token(Token::EOF, null, 5, 1),
];