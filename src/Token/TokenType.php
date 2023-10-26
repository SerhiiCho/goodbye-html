<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Token;

enum TokenType: string
{
    case EOF = 'EOF';
    case VARIABLE = 'VARIABLE';
}
