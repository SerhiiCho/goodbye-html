<?php declare(strict_types=1);

return (object) [

    'match_variables' => '/{{ ?\$([_a-z0-9]+)? ?}}/',
    'match_if_statements' => '/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?end ?}}/',

];
