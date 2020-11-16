<?php declare(strict_types=1);

return (object) [

    'match_variables' => '/{{ ?\$([_a-z0-9]+)? ?}}/u',
    'match_if_statements' => '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?end ?}}/u',
    'match_if_else_statements' => '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?else ?}}([\s\S]+?){{ ?end ?}}/u',
    'match_ternary_statements' => '/{{ ?\$([_A-z0-9]+) ?\? ?(\'|")(.*)(\'|") ?: ?(\'|")(.*)(\'|") ?}}/u',

];
