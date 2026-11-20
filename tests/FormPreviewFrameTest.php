<?php

class FormPreviewFrameTest extends PHPUnit\Framework\TestCase
{
    /**
     * @return string[]
     */
    private function get_preview_frame_sandbox_tokens(): array
    {
        $html      = file_get_contents(MC4WP_PLUGIN_DIR . '/includes/forms/views/tabs/form-fields.php');
        $processor = new WP_HTML_Tag_Processor($html);
        while ($processor->next_tag('iframe')) {
            if ($processor->get_attribute('id') !== 'mc4wp-form-preview') {
                continue;
            }

            $sandbox = $processor->get_attribute('sandbox');
            $this->assertIsString($sandbox, 'Form preview iframe has no sandbox attribute.');
            return preg_split('/\s+/', trim($sandbox), -1, PREG_SPLIT_NO_EMPTY);
        }

        $this->fail('Form preview iframe not found.');
    }

    public function test_preview_frame_is_sandboxed(): void
    {
        $tokens = $this->get_preview_frame_sandbox_tokens();

        // the form editor reads the preview document, the form needs its scripts and must still submit
        $this->assertContains('allow-same-origin', $tokens);
        $this->assertContains('allow-scripts', $tokens);
        $this->assertContains('allow-forms', $tokens);
    }

    public function test_preview_frame_can_not_navigate_top_window(): void
    {
        $tokens = $this->get_preview_frame_sandbox_tokens();

        $this->assertNotContains('allow-top-navigation', $tokens);
        $this->assertNotContains('allow-top-navigation-by-user-activation', $tokens);
        $this->assertNotContains('allow-popups-to-escape-sandbox', $tokens);
    }
}
