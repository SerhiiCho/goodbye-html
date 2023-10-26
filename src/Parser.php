<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Exception;

final class Parser
{
    /**
     * @var string The content that is being parsed
     */
    private $html_content;

    /**
     * @var string[]|null $variables Associative array ['var_name' => 'will be inserted']
     * of variable name and content that it holds
     */
    private $variables;

    /**
     * Parser constructor
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
     * @throws Exception Throws exception if variable is in html but doesn't have value
     */
    public function parseHtml(): string
    {
        if (!$this->hasVariables()) {
            return $this->html_content;
        }

        return '';
    }

    private function hasVariables(): bool
    {
        return is_array($this->variables) && !empty($this->variables);
    }
}
