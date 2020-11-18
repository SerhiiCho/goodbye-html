<?php

declare(strict_types=1);

namespace Serhii\GoodbyeHtml\Syntax;

use Serhii\GoodbyeHtml\Regex;

trait ReplacesLoops
{
    private function replaceLoopsFromHtml(): void
    {
        preg_match_all(Regex::INLINE_LOOP, $this->html_content, $inline_matches);
        preg_match_all(Regex::BLOCK_LOOP, $this->html_content, $block_matches);

        $raw = $inline_matches[0] + $block_matches[0];
        $loop_froms = $inline_matches[1] + $block_matches[2];
        $loop_tos = $inline_matches[2] + $block_matches[3];
        $contents = $inline_matches[3] + $block_matches[4];

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