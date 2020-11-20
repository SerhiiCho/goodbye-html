<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

final class Regex
{
    public const VARIABLES = '/{{ ?\$([_a-z0-9]+)? ?}}/u';
    public const IF_STATEMENTS= '/{{ ?if ?\$([_A-z0-9]+) ?}}(((?!{{ ?else ?}})(?!{{ ?end ?}})[\s\S])*){{ ?end ?}}/u';
    public const IF_ELSE_STATEMENTS = '/{{ ?if ?\$([_A-z0-9]+) ?}}((?!{{ ?end ?}})[\s\S])*{{ ?else ?}}([\s\S]+?){{ ?end ?}}/u';
    public const TERNARY_VARIABLES = '/{{ ?\$([_A-z0-9]+) ?\? ?((\$([_a-z0-9]+)?)|(.*?)) ?: ?((\$([_a-z0-9]+)?)|(.*?)) ?}}/u';
    public const TERNARY_STATEMENTS = '/{{ ?\$([_A-z0-9]+) ?\? ?((\'|")(.*?)(\'|")) ?: ?(\'|")(.*?)(\'|") ?}}/u';
    public const BLOCK_LOOP = '/([^\S\n]+)?{{ ?loop ([0-9]+), ?([0-9]+) ?}}\n([\s\S]+?)([^\S\n]+)?{{ ?end ?}}\n?/u';
    public const INLINE_LOOP = '/{{ ?loop ([0-9]+), ?([0-9]+) ?}}(.*?){{ ?end ?}}/u';
    public const LOOP_ARGUMENT_VARIABLES = '/{{ ?loop ([0-9]+|\$([_a-z0-9]+)?), ?([0-9]+|\$([_a-z0-9]+)?) ?}}/';
    public const INDEX_VARIABLE = '/{{ ?\$index ?}}/';
}
