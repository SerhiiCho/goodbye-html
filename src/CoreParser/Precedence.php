<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\CoreParser;

enum Precedence: int
{
    case LOWEST = 0;
    case EQUALS = 1; // == and ===
    case TERNARY = 2; // a ? b : c
    case LESS_GREATER = 3; // > or <
    case SUM = 4; // +
    case PRODUCT = 5; // *
    case PREFIX = 6; // -X or !X
    case CALL = 7; // myFunction(X)
    case INDEX = 8; // array[index]
}
