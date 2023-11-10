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
     * @throws
     * @throws Exception Throws exception if variable is in html but doesn't have value
     */
    public function parseHtml(): string
    {
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
            throw new EvaluatorException($evaluated->inspect());
        }

        return $evaluated->inspect();
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
