<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

final class Regex
{
    public const MATCH_VARIABLES = '/{{ ?\$([_a-z0-9]+)? ?}}/u';
    public const MATCH_IF_STATEMENTS = '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?end ?}}/u';
    public const MATCH_IF_ELSE_STATEMENTS = '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?else ?}}([\s\S]+?){{ ?end ?}}/u';
    public const MATCH_TERNARY_STATEMENTS = '/{{ ?\$([_A-z0-9]+) ?\? ?(\'|")(.*)(\'|") ?: ?(\'|")(.*)(\'|") ?}}/u';
}
