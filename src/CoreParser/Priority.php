<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\CoreParser;

enum Priority: int
{
    case LOWEST = 0;
    case EQUALS = 1; // == and ===
    case LESS_GREATER = 2; // > or <
    case SUM = 3; // +
    case PRODUCT = 4; // *
    case PREFIX = 5; // -X or !X
    case CALL = 6; // myFunction(X)
    case INDEX = 7; // array[index]
}
