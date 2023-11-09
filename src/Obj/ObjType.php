<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Obj;

enum ObjType: string
{
    case INTEGER_OBJ = 'INTEGER';
    case ERROR_OBJ = 'ERROR';
    case STRING_OBJ = 'STRING';
    case HTML_OBJ = 'HTML';
    case BLOCK_OBJ = 'BLOCK';
}
