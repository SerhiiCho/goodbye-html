<?php declare(strict_types=1);

namespace Serhii\HtmlParser;

use Exception;

final class Parser
{
    /** @var string What to parse */
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

        try {
            $parsed_html = $this->getPhpCodeFromHtml($this->html_string);
            $replacement = $this->replaceVarNamesWithValues($parsed_html->var_names);

            return preg_replace($parsed_html->regex_patterns, $replacement, $this->html_string);
        } catch (Exception $e) {
            //
        }

        return $this->errorMessage() . $this->html_string;
    }

    private function getPhpCodeFromHtml(string $html_context)
    {
        preg_match_all('/{{ ?\$([_a-z0-9]+)? ?}}/', $html_context, $variables);

        $regex_patters = array_map(function ($item) {
            $item = str_replace('$', '\$', $item);
            return "/{$item}/";
        }, $variables[0]);

        return (object) [
            'regex_patterns' => $regex_patters,
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

            $var_values[] = "{$this->variables[$var_name]}";
        }

        return $var_values;
    }

    private function errorMessage(): string
    {
        return '<h3 class="mb-5">Произошла ошибка при попытке загузить контент</h3><br>';
    }
}
