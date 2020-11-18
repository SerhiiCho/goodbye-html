<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Serhii\GoodbyeHtml\Syntax\ReplacesIf;
use Serhii\GoodbyeHtml\Syntax\ReplacesIfElse;
use Serhii\GoodbyeHtml\Syntax\ReplacesTernary;
use Serhii\GoodbyeHtml\Syntax\ReplacesLoops;
use Serhii\GoodbyeHtml\Syntax\ReplacesVariables;

final class Parser
{
    use ReplacesIfElse;
    use ReplacesIf;
    use ReplacesTernary;
    use ReplacesLoops;
    use ReplacesVariables;

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
        $this->replaceLoopsFromHtml();

        if ($this->hasVariables()) {
            // Order of method calls matters
            $this->replaceIfElseStatementsFromHtml();
            $this->replaceIfStatementsFromHtml();
            $this->replaceTernaryStatementsFromHtml();
            $this->replaceVariablesFromHtml();
        }

        return $this->html_content;
    }

    private function hasVariables(): bool
    {
        return is_array($this->variables) && !empty($this->variables);
    }

    /**
     * @param array $parsed
     */
    private function replaceStatements(array $parsed): void
    {
        $this->html_content = str_replace($parsed['raw'], $parsed['replacements'], $this->html_content);

        if (!isset($parsed['var_names']) || empty($parsed['var_names'])) {
            return;
        }

        $this->variables = array_filter($this->variables, function ($var_name) use ($parsed) {
            return !in_array($var_name, $parsed['var_names']);
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
