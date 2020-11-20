<?php

declare(strict_types=1);

namespace Serhii\Tests;

use Serhii\GoodbyeHtml\Parser;

class MixedTest extends TestCase
{
    public function test_for_mixed_bootstrap_footer(): void
    {
        $parser = new Parser(self::getPath("mixed/not-parsed/bootstrap-footer"), [
            'title' => 'Here is the title of the page',
            'content' => 'Hello people, this is the content of this page!',
            'show_content' => true,
            'links_number' => 4,
            'show_copy' => true,
            'footer_title_is_nice' => false,
            'first_step' => 1,
            'current_step' => 4,
            'site' => 'https://serhii.io',
            'link' => 'Link',
            'show_hr' => false,
            'show_big_title' => true,
            'margin_to_title' => false,
            'small_font' => true,
            'lang' => 'en',
        ]);

        $expect = file_get_contents(self::getPath("mixed/parsed/bootstrap-footer"));

        $this->assertEquals($expect, $parser->parseHtml());
    }

    public function test_for_mixed_page(): void
    {
        $parser = new Parser(self::getPath("mixed/not-parsed/show-page-is-not-available"), [
            'title' => 'Here is the title of the page',
            'message' => "<h1>Don't have permission to view the page</h1>",
            'has_access' => false,
            'show_title' => false,
            'no_title' => 'No title',
        ]);

        $expect = file_get_contents(self::getPath("mixed/parsed/show-page-is-not-available"));

        $this->assertEquals($expect, $parser->parseHtml());
    }
}