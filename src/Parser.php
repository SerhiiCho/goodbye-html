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
     * Parser constructor.
     *
     * @param string $file_path Absolute or relative path to an html file
     * @param array|null $variables Associative array ['var_name' => 'will be inserted']
     */
    public function __construct(string $file_path, ?array $variables = null)
    {
        $this->html_string = file_get_contents($file_path);
        $this->variables = $variables;
    }

    /**
     * Takes html and replaces all embedded variables with values
     *
     * @return string Parsed html with replaced php variables
     * @throws \Exception Throws exception if variable in html doesn't have value
     */
    public function parseHtml(): string
    {
        if ($this->thereAreNoVariables()) {
            return $this->html_string;
        }

        $this->replaceIfStatements()
            ->replaceVariables();

        return $this->html_string;
    }

    private function thereAreNoVariables(): bool
    {
        return !is_array($this->variables);
    }

    private function replaceVariables(): self
    {
        $parsed = $this->getPhpCodeFromHtml($this->html_string);
        $this->html_string = str_replace($parsed['raw'], $parsed['replacements'], $this->html_string);

        return $this;
    }

    private function replaceIfStatements(): self
    {
        $parsed = $this->getIfStatementsFromHtml($this->html_string);
        $this->html_string = str_replace($parsed['raw'], $parsed['replacements'], $this->html_string);

        return $this;
    }

    private function getIfStatementsFromHtml(string $html_context): array
    {
        preg_match_all('/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?end ?}}/', $html_context, $if_statements);

        [$raw, $var_names, $if_contents] = $if_statements;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            if ($this->variables[$var_names[$i]]) {
                $replacements[] = trim($if_contents[$i]);
            }
        }

        $this->removeUsedVariables($var_names);

        return compact('raw', 'replacements');
    }

    private function removeUsedVariables(array $used_vars): void
    {
        $this->variables = array_filter($this->variables, function ($key) use ($used_vars) {
            return !in_array($key, $used_vars);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function getPhpCodeFromHtml(string $html_context): array
    {
        preg_match_all('/{{ ?\$([_a-z0-9]+)? ?}}/', $html_context, $variables);

        [$raw, $var_names] = $variables;

        $replacements = $this->replaceVarNamesWithValues($var_names);

        return compact('raw', 'replacements');
    }

    private function replaceVarNamesWithValues(array $var_names): array
    {
        $var_values = [];
        $var_keys = array_keys($this->variables);

        foreach ($var_names as $var_name) {
            if (!in_array($var_name, $var_keys)) {
                throw new Exception("Undefined variable \${$var_name}");
                continue;
            }

            $var_values[] = $this->variables[$var_name];
        }

        return $var_values;
    }
}
