<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesTernary
{
    private function replaceTernaryVariablesFromHtml(): void
    {
        preg_match_all(Regex::TERNARY_VARIABLES, $this->html_content, $matches);

        [$raw, $var_names, $true_block,,,,$false_block] = $matches;

        $with_raw = $this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block);

        $with_raw['replacements'] = array_map(function ($item) {
            return ($item[0] ?? null) === '$' ? "{{ $item }}" : trim($item, "'\"");
        }, $with_raw['replacements']);

        $this->replaceStatements($with_raw);
    }

    private function replaceTernaryStatementsFromHtml(): void
    {
        preg_match_all(Regex::TERNARY_STATEMENTS, $this->html_content, $matches);

        [$raw, $var_names,,, $true_block,,, $false_block] = $matches;

        $this->replaceStatements($this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block));
        $this->replaceTernaryVariablesFromHtml();
    }
}