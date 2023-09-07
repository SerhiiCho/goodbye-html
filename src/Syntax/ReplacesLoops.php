<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Exception;
use Serhii\GoodbyeHtml\Regex;

trait ReplacesLoops
{
    /**
     * @throws Exception
     */
    private function replaceLoopsFromHtml(): void
    {
        $this->replaceArgumentVariables();

        preg_match_all(Regex::INLINE_LOOP, $this->html_content, $inline);
        preg_match_all(Regex::BLOCK_LOOP, $this->html_content, $block);

        $raw = array_merge($inline[0], $block[0]);
        $loop_froms = array_merge($inline[1], $block[2]);
        $loop_tos = array_merge($inline[2], $block[3]);
        $contents = array_merge($inline[3], $block[4]);

        $replacements = [];

        for ($i = 0, $count = count($raw); $i < $count; $i++) {
            $content = '';

            for ($j = (int) $loop_froms[$i]; $j <= (int) $loop_tos[$i]; $j++) {
                $content .= preg_replace(Regex::INDEX_VARIABLE, (string) $j, $contents[$i]);
            }

            $replacements[] = $content;
        }

        $this->replaceStatements(compact('raw', 'replacements'));
    }

    /**
     * @throws Exception
     */
    private function replaceArgumentVariables(): void
    {
        preg_match_all(Regex::LOOP_ARGUMENT_VARIABLES, $this->html_content, $matches);

        [$raw, $var_raw1, $var_names1, $var_raw2, $var_names2] = $matches;

        foreach ($raw as $key => $loop) {
            $raw1 = $var_raw1[$key];
            $raw2 = $var_raw2[$key];
            $val1 = $this->variables[$var_names1[$key]] ?? null;
            $val2 = $this->variables[$var_names2[$key]] ?? null;

            $this->replaceVariable($raw1, $val1);
            $this->replaceVariable($raw2, $val2);
        }
    }

    /**
     * @param string $raw
     * @param mixed $val
     *
     * @throws Exception
     */
    private function replaceVariable(string $raw, $val): void
    {
        $val = null === $val ? null : (int) $val;

        if ($raw[0] === '$' && null === $val) {
            throw new Exception("Undefined variable {$raw}");
        }

        if ($raw[0] === '$') {
            $this->html_content = str_replace($raw, (string) $val, $this->html_content);
        }
    }
}
