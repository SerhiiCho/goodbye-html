<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Exception;

final class Parser
{
    /**
     * @var string The content that is being parsed.
     */
    private $html_content;

    /**
     * @var string[]|null $variables Associative array ['var_name' => 'will be inserted'] of variable name
     * and content that it holds.
     */
    private $variables;

    /**
     * Parser constructor.
     *
     * @param string $file_path Absolute or relative path to an html file
     * @param string[]|null $variables Associative array ['var_name' => 'will be inserted']
     */
    public function __construct(string $file_path, ?array $variables = null)
    {
        $this->html_content = file_get_contents($file_path);
        $this->variables = $variables;
    }

    /**
     * Takes html and replaces all embedded variables with values
     *
     * @return string Parsed html with replaced php variables
     * @throws \Exception Throws exception if variable is in html but doesn't have value
     */
    public function parseHtml(): string
    {
        if ($this->thereAreNoVariables()) {
            return $this->html_content;
        }

        $this->replaceIfElseStatementsFromHtml();
        $this->replaceIfStatementsFromHtml();
        $this->replaceTernaryStatementsFromHtml();
        $this->replaceTernaryVariablesFromHtml();
        $this->replaceVariablesFromHtml();

        return $this->html_content;
    }

    private function thereAreNoVariables(): bool
    {
        return !is_array($this->variables) || count($this->variables) === 0;
    }

    /**
     * @param array $parsed
     */
    private function replaceStatements(array $parsed): void
    {
        $this->html_content = str_replace($parsed['raw'], $parsed['replacements'], $this->html_content);
        $this->removeUsedVariables($parsed['var_names']);
    }

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

    private function replaceIfElseStatementsFromHtml(): void
    {
        preg_match_all(Regex::IF_ELSE_STATEMENTS, $this->html_content, $matches);

        [$raw, $var_names, $true_block, $false_block] = $matches;

        $this->replaceStatements($this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block));
    }

    private function replaceTernaryStatementsFromHtml(): void
    {
        preg_match_all(Regex::TERNARY_STATEMENTS, $this->html_content, $matches);

        [$raw, $var_names,,, $true_block,,, $false_block] = $matches;

        $this->replaceStatements($this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block));
    }

    private function replaceIfStatementsFromHtml(): void
    {
        preg_match_all(Regex::IF_STATEMENTS, $this->html_content, $matches);

        [$raw, $var_names, $contents] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            if ($this->variables[$var_names[$i]]) {
                $replacements[] = trim($contents[$i]);
            }
        }

        $this->replaceStatements(compact('raw', 'replacements', 'var_names'));
    }

    /**
     * @throws \Exception
     */
    private function replaceVariablesFromHtml(): void
    {
        preg_match_all(Regex::VARIABLES, $this->html_content, $matches);

        [$raw, $var_names] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $var_key = $var_names[$i];

            if ( ! in_array($var_key, array_keys($this->variables))) {
                throw new Exception("Undefined variable \${$var_key}");
            }

            $replacements[] = $this->variables[$var_key];
        }

        $this->replaceStatements(compact('raw', 'replacements', 'var_names'));
    }

    private function removeUsedVariables(array $used_vars): void
    {
        $filtered = [];

        foreach ($this->variables as $key => $item) {
            if (!in_array($key, $used_vars)) {
                $filtered[$key] = $item;
            }
        }

        $this->variables = $filtered;
    }

    /**
     * @param $raw
     * @param $var_names
     * @param $true_block
     * @param $false_block
     *
     * @return array[]
     */
    private function getVarNamesWithRaw($raw, $var_names, $true_block, $false_block): array
    {
        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            if ($this->variables[$var_names[$i]] === true) {
                $replacements[] = trim($true_block[$i]);
            }

            if ($this->variables[$var_names[$i]] === false) {
                $replacements[] = trim($false_block[$i]);
            }
        }

        return compact('raw', 'replacements', 'var_names');
    }
}
