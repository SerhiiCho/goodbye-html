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
     * @var array|null Key value pairs ['var_name_to_replace' => 'replace to what']
     */
    private $variables;

    public function __construct(string $file_path, ?array $variables = null)
    {
        $this->html_string = file_get_contents($file_path);
        $this->variables = $variables;
    }

    public function parseHtml(): string
    {
        if (!is_array($this->variables)) {
            return $this->html_string;
        }

        return $this
            ->replaceIfStatements()
            ->replaceVariables()
            ->done();
    }

    private function done(): string
    {
        return $this->html_string;
    }

    private function replaceVariables(): self
    {
        $parsed_variables = $this->getPhpCodeFromHtml($this->html_string);
        $replacement = $this->replaceVarNamesWithValues($parsed_variables['var_names']);

        $this->html_string = preg_replace($parsed_variables['regex'], $replacement, $this->html_string);

        return $this;
    }

    private function replaceIfStatements(): self
    {
        $parsed_ifs = $this->getIfStatementsFromHtml($this->html_string);
        $this->html_string = str_replace($parsed_ifs['raw_statements'], $parsed_ifs['replacements'], $this->html_string);

        return $this;
    }

    private function getIfStatementsFromHtml(string $html_context): array
    {
        preg_match_all('/{{ ?if ?\$([_A-z0-9]+) ?}}([\s\S]+?){{ ?end ?}}/', $html_context, $if_statements);

        [$raw_statements, $var_names, $if_contents] = $if_statements;

        $replacements = [];

        for ($i = 0; $i < count($raw_statements); $i++) {
            if ($this->variables[$var_names[$i]]) {
                $replacements[] = trim($if_contents[$i]);
            }
        }

        $this->removeUsedVariables($var_names);

        return compact('raw_statements', 'replacements');
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

        $regex_patters = array_map(function ($item) {
            $item = str_replace('$', '\$', $item);
            return "/{$item}/";
        }, $variables[0]);

        return [
            'regex' => $regex_patters,
            'var_names' => $variables[1]
        ];
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
