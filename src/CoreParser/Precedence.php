<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\CoreParser;

enum Precedence: int
{
    case LOWEST = 0;
    case TERNARY = 1; // a ? b : c
    case EQUALS = 2; // == and ===
    case LESS_GREATER = 3; // > or <
    case SUM = 4; // +
    case PRODUCT = 5; // *
    case PREFIX = 6; // -X or !X
    case CALL = 7; // myFunction(X)
    case INDEX = 8; // array[index]
}
