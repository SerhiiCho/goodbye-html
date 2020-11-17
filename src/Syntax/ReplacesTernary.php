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

        $new_replacements = [];

        foreach ($with_raw['replacements'] as $key => $item) {
            $first_symbol = $item[0] ?? null;
            $new_replacements[$key] = $first_symbol === '$' ? "{{ $item }}" : trim($item, "'\"");
        }

        $with_raw['replacements'] = $new_replacements;

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