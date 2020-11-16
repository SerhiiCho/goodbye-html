<?php declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Exception;

final class Parser
{
    /**
     * @var string What to parse
     */
    private $html_string;

    /**
     * @var array|null $variables Associative array ['var_name' => 'will be inserted']
     */
    private $variables;

    /**
     * @var object Regex patterns
     */
    private $regex;

    /**
     * Parser constructor.
     *
     * @param string $file_path Absolute or relative path to an html file
     * @param array|null $variables Associative array ['var_name' => 'will be inserted']
     */
    public function __construct(string $file_path, ?array $variables = null)
    {
        $this->html_string = file_get_contents($file_path);
        $this->variables = $variables;
        $this->regex = require __DIR__ . '/regex.php';
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
            return $this->html_string;
        }

        $this->removeUsedVariables($this->replaceTernaryStatements());
        $this->removeUsedVariables($this->replaceIfElseStatements());
        $this->removeUsedVariables($this->replaceIfStatements());
        $this->removeUsedVariables($this->replaceVariables());

        return $this->html_string;
    }

    private function thereAreNoVariables(): bool
    {
        return !is_array($this->variables) || count($this->variables) === 0;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function replaceVariables(): array
    {
        $parsed = $this->getVariablesFromHtml($this->html_string);
        $this->html_string = str_replace($parsed['raw'], $parsed['replacements'], $this->html_string);

        $this->removeUsedVariables($parsed['var_names']);

        return $parsed['var_names'];
    }

    private function replaceIfElseStatements(): array
    {
        $parsed = $this->getIfElseStatementsFromHtml($this->html_string);
        $this->html_string = str_replace($parsed['raw'], $parsed['replacements'], $this->html_string);

        $this->removeUsedVariables($parsed['var_names']);

        return $parsed['var_names'];
    }

    private function replaceTernaryStatements(): array
    {
        $parsed = $this->getTernaryStatementsFromHtml($this->html_string);
        $this->html_string = str_replace($parsed['raw'], $parsed['replacements'], $this->html_string);

        $this->removeUsedVariables($parsed['var_names']);

        return $parsed['var_names'];
    }

    private function replaceIfStatements(): array
    {
        $parsed = $this->getIfStatementsFromHtml($this->html_string);
        $this->html_string = str_replace($parsed['raw'], $parsed['replacements'], $this->html_string);

        $this->removeUsedVariables($parsed['var_names']);

        return $parsed['var_names'];
    }

    /**
     * @param string $html_context
     *
     * @return array[]
     */
    private function getIfElseStatementsFromHtml(string $html_context): array
    {
        preg_match_all($this->regex->match_if_else_statements, $html_context, $matches);

        [$raw, $var_names, $true_block, $false_block] = $matches;

        return $this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block);
    }

    /**
     * @param string $html_context
     *
     * @return array[]
     */
    private function getTernaryStatementsFromHtml(string $html_context): array
    {
        preg_match_all($this->regex->match_ternary_statements, $html_context, $matches);

        [$raw, $var_names,, $true_block,,, $false_block] = $matches;

        return $this->getVarNamesWithRaw($raw, $var_names, $true_block, $false_block);
    }

    private function getIfStatementsFromHtml(string $html_context): array
    {
        preg_match_all($this->regex->match_if_statements, $html_context, $matches);

        [$raw, $var_names, $contents] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            if ($this->variables[$var_names[$i]]) {
                $replacements[] = trim($contents[$i]);
            }
        }

        return compact('raw', 'replacements', 'var_names');
    }

    /**
     * @param string $html_context
     *
     * @return array
     * @throws \Exception
     */
    private function getVariablesFromHtml(string $html_context): array
    {
        preg_match_all($this->regex->match_variables, $html_context, $matches);

        [$raw, $var_names] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $var_key = $var_names[$i];

            if (!in_array($var_key, array_keys($this->variables))) {
                throw new Exception("Undefined variable \${$var_key}");
            }

            $replacements[] = $this->variables[$var_key];
        }

        return compact('raw', 'replacements', 'var_names');
    }

    private function removeUsedVariables(array $used_vars): void
    {
        $this->variables = array_filter($this->variables, function ($key) use ($used_vars) {
            return !in_array($key, $used_vars);
        }, ARRAY_FILTER_USE_KEY);
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
