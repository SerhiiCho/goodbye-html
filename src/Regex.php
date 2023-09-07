<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

final class Regex
{
    public const VARIABLES = '/{{ ?\$([_a-z0-9]+)? ?}}/u';
    public const BLOCK_IF_STATEMENTS = '/([^\S\n]+)?{{ ?if ?\$([_A-z0-9]+) ?}}\n([\s\S]+?)([^\S\n]+)?{{ ?end ?}}\n?/u';
    public const INLINE_IF_STATEMENTS = '/{{ ?if ?\$([_A-z0-9]+) ?}}(.*?){{ ?end ?}}/u';
    public const BLOCK_IF_ELSE_STATEMENTS = '/([^\S\n]+)?{{ ?if ?\$([_A-z0-9]+) ?}}\n(((?!{{ ?end ?}})[\s\S])*)(((^\s+)|^){{ ?else ?}})\n([\s\S]+?)(((^\s+)|^){{ ?end ?}})\n?/um';
    public const INLINE_IF_ELSE_STATEMENTS = '/{{ ?if ?\$([_A-z0-9]+) ?}}(.*?){{ ?else ?}}(.*?){{ ?end ?}}/u';
    public const TERNARY_VARIABLES = '/{{ ?\$([_A-z0-9]+) ?\? ?((\$([_a-z0-9]+)?)|(.*?)) ?: ?((\$([_a-z0-9]+)?)|(.*?)) ?}}/u';
    public const TERNARY_STATEMENTS = '/{{ ?\$([_A-z0-9]+) ?\? ?((\'|")(.*?)(\'|")) ?: ?(\'|")(.*?)(\'|") ?}}/u';
    public const BLOCK_LOOP = '/([^\S\n]+)?{{ ?loop ([0-9]+), ?([0-9]+) ?}}\n([\s\S]+?)([^\S\n]+)?{{ ?end ?}}\n?/u';
    public const INLINE_LOOP = '/{{ ?loop ([0-9]+), ?([0-9]+) ?}}(.*?){{ ?end ?}}/u';
    public const LOOP_ARGUMENT_VARIABLES = '/{{ ?loop ([0-9]+|\$([_a-z0-9]+)?), ?([0-9]+|\$([_a-z0-9]+)?) ?}}/';
    public const INDEX_VARIABLE = '/{{ ?\$index ?}}/';
}
