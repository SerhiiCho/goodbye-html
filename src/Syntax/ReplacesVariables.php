<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Exception;
use Serhii\GoodbyeHtml\Regex;

trait ReplacesVariables
{
    /**
     * @throws Exception
     */
    private function replaceVariablesFromHtml(): void
    {
        preg_match_all(Regex::VARIABLES, $this->html_content, $matches);

        [$raw, $var_names] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $var_key = $var_names[$i];

            if (!in_array($var_key, array_keys($this->variables))) {
                throw new Exception("Undefined variable \${$var_key}");
            }

            $replacements[] = $this->variables[$var_key];
        }

        $this->replaceStatements(compact('raw', 'replacements', 'var_names'));
    }
}
