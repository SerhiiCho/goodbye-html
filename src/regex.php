<?php declare(strict_types=1);

return (object) [

    'match_variables' => '/{{ ?\$([_a-z0-9]+)? ?}}/',
    'match_if_statements' => '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?end ?}}/',
    'match_if_else_statements' => '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?else ?}}([\s\S]+?){{ ?end ?}}/',

];
