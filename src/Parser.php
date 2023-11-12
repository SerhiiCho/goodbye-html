<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml;

use Exception;
use Serhii\GoodbyeHtml\Obj\Env;
use Serhii\GoodbyeHtml\Obj\Obj;
use Serhii\GoodbyeHtml\Ast\Program;
use Serhii\GoodbyeHtml\Lexer\Lexer;
use Serhii\GoodbyeHtml\Obj\ErrorObj;
use Serhii\GoodbyeHtml\Evaluator\Evaluator;
use Serhii\GoodbyeHtml\CoreParser\CoreParser;
use Serhii\GoodbyeHtml\Exceptions\EvaluatorException;
use Serhii\GoodbyeHtml\Exceptions\CoreParserException;
use Serhii\GoodbyeHtml\Exceptions\ParserException;

final readonly class Parser
{
    /**
     * @var string The content that is being parsed
     */
    private string $html_content;

    /**
     * @param string $file_path Absolute file path or the file content itself
     * @param string[]|null $variables Associative array ['var_name' => 'will be inserted']
     */
    public function __construct(
        private string $file_path,
        private ?array $variables = null,
    ) {
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
        $this->setHtmlContent();

        if (!$this->hasVariables()) {
            return $this->html_content;
        }

        $lexer = new Lexer($this->html_content);
        $parser = new CoreParser($lexer);
        $program = $parser->parseProgram();

        if (count($parser->errors()) > 0) {
            throw new CoreParserException($parser->errors()[0]);
        }

        $evaluated = $this->evaluate($program);

        if ($evaluated instanceof ErrorObj) {
            throw new EvaluatorException($evaluated->value());
        }

        return $evaluated->value();
    }

    /**
     * @throws ParserException
     */
    private function setHtmlContent(): void
    {
        if (!str_starts_with($this->file_path, '/')) {
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

        foreach ($this->variables as $name => $value) {
            $envVariables[$name] = Obj::fromNative($value, $name);
        }

        $env = Env::fromArray($envVariables);

        return (new Evaluator())->eval($program, $env);
    }
}
