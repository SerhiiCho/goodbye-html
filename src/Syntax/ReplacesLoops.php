<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesLoops
{
    private function replaceLoopsFromHtml(): void
    {
        preg_match_all(Regex::LOOP, $this->html_content, $matches);

        [$raw,, $loop_froms, $loop_tos, $contents] = $matches;

        $replacements = [];

        for ($i = 0; $i < count($raw); $i++) {
            $content = '';

            for ($j = (int) $loop_froms[$i]; $j <= (int) $loop_tos[$i]; $j++) {
                $content .= preg_replace(Regex::INDEX_VARIABLE, $j, $contents[$i]);
            }

            $replacements[] = $content;
        }

        $this->replaceStatements(compact('raw', 'replacements'));
    }
}