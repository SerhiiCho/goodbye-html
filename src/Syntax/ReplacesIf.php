<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesIf
{
    private function replaceIfStatementsFromHtml(): void
    {
        preg_match_all(Regex::IF_STATEMENTS, $this->html_content, $matches);

        [$raw, $var_names, $contents] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $replacements[] = $this->variables[$var_names[$i]] ? trim($contents[$i]) : '';
        }

        $this->replaceStatements(compact('raw', 'replacements', 'var_names'));
    }
}