<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Exception;
use Serhii\GoodbyeHtml\Ast\Statements\Program;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\Exceptions\CoreParserException;
use Serhii\GoodbyeHtml\Exceptions\EvaluatorException;
use Serhii\GoodbyeHtml\Exceptions\ParserException;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Obj\Obj;

class Parser
{
    /**
     * @var string The content that is being parsed
     */
    private string $html_content;

    /**
     * @param string $file_path Absolute file path
     * @param array<non-empty-string, int|string|bool|float|null>|null $variables
     * @param ParserOption|null $options
     */
    public function __construct(
        private readonly string $file_path,
        private readonly array|null $variables = null,
        private readonly ParserOption|null $options = null,
    ) {
        $this->html_content = '';
    }

    /**
     * Takes HTML and replaces all embedded variables with values
     *
     * @return string Parsed HTML with replaced PHP variables
     * @throws Exception
     * @throws ParserException
     * @throws CoreParserException
     * @throws EvaluatorException
     */
    public function parseHtml(): string
    {
        if ($this->file_path === '') {
            return '';
        }

        $this->setHtmlContent();

        if (!$this->hasVariables()) {
            return $this->html_content;
        }

        $lexer = new Lexer($this->html_content);
        $parser = new CoreParser($lexer);
        $program = $parser->parseProgram();

        $evaluated = $this->evaluate($program);

        if ($evaluated instanceof ErrorObj) {
            throw new EvaluatorException($evaluated->value());
        }

        return (string) $evaluated->value();
    }

    /**
     * @throws ParserException
     */
    private function setHtmlContent(): void
    {
        if ($this->options && $this->options === ParserOption::PARSE_TEXT) {
            $this->html_content = $this->file_path;
            return;
        }

        $content = file_get_contents($this->file_path);

        if ($content === false) {
            $msg = "File {$this->file_path} doesn't exist. Make sure the path is absolute and starts with '/'";
            throw new ParserException($msg);
        }

        $this->html_content = $content;
    }

    private function hasVariables(): bool
    {
        return is_array($this->variables) && !empty($this->variables);
    }

    /**
     * @throws ParserException
     */
    private function evaluate(Program $program): Obj
    {
        $envVariables = [];

        foreach ($this->variables ?? [] as $name => $value) {
            $envVariables[$name] = Obj::fromNative($value, $name);
        }

        $env = Env::fromArray($envVariables);

        return (new Evaluator())->eval($program, $env);
    }
}
