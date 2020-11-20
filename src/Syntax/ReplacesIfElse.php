<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesIfElse
{
    private function replaceIfElseStatementsFromHtml(): void
    {
        preg_match_all(Regex::BLOCK_IF_ELSE_STATEMENTS, $this->html_content, $matches);

        [$raw,, $var_names, $true_block,,, $false_block] = $matches;

        $this->replaceStatements($this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block));
    }
}