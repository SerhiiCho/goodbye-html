<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesIfElse
{
    private function replaceIfElseStatementsFromHtml(): void
    {
        preg_match_all(Regex::BLOCK_IF_ELSE_STATEMENTS, $this->html_content, $block);
        preg_match_all(Regex::INLINE_IF_ELSE_STATEMENTS, $this->html_content, $inline);

        $raw = array_merge($block[0], $inline[0]);
        $var_names = array_merge($block[2], $inline[1]);
        $true_block = array_merge($block[3], $inline[2]);
        $false_block = array_merge($block[8], $inline[3]);

        $this->replaceStatements($this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block));
    }
}
