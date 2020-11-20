<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesIf
{
    private function replaceIfStatementsFromHtml(): void
    {
        preg_match_all(Regex::BLOCK_IF_STATEMENTS, $this->html_content, $block);
        preg_match_all(Regex::INLINE_IF_STATEMENTS, $this->html_content, $inline);

        $raw = array_merge($inline[0], $block[0]);
        $var_names = array_merge($inline[1], $block[2]);
        $contents = array_merge($inline[2], $block[3]);

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $replacements[] = $this->variables[$var_names[$i]] ? $contents[$i] : '';
        }

        $this->replaceStatements(compact('raw', 'replacements', 'var_names'));
    }
}