<?php

namespace Tests\Feature;

use Tests\TestCase;

class PageFaqBlockTest extends TestCase
{
    /** @test */
    public function it_renders_the_shared_faq_block_and_schema_markup()
    {
        $faqs = collect([
            new class {
                public function getTranslatedAttribute($field)
                {
                    return $field === 'question'
                        ? 'How long does production take?'
                        : 'Usually 3-5 business days.';
                }
            },
        ]);

        $html = view('theme.viar.pages.index.faq9', ['faqs' => $faqs])->render();

        $this->assertStringContainsString('<section class="faq">', $html);
        $this->assertStringContainsString('How long does production take?', $html);
        $this->assertStringContainsString('Usually 3-5 business days.', $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
    }
}
