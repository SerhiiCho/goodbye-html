<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesIf
{
    private function replaceIfStatementsFromHtml(): void
    {
        preg_match_all(Regex::BLOCK_IF_STATEMENTS, $this->html_content, $block_matches);
        preg_match_all(Regex::INLINE_IF_STATEMENTS, $this->html_content, $inline_matches);

        $raw = array_merge($inline_matches[0], $block_matches[0]);
        $var_names = array_merge($inline_matches[1], $block_matches[2]);
        $contents = array_merge($inline_matches[2], $block_matches[3]);

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $replacements[] = $this->variables[$var_names[$i]] ? $contents[$i] : '';
        }

        $this->replaceStatements(compact('raw', 'replacements', 'var_names'));
    }
}