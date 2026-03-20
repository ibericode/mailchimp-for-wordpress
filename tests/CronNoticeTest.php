<?php

class CronNoticeTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var MC4WP_Admin_Cron_Notice
     */
    private $notice;

    protected function setUp(): void
    {
        global $mock_wp_next_scheduled, $mock_current_user_can, $mock_user_meta;
        $mock_wp_next_scheduled = false;
        $mock_current_user_can  = true;
        $mock_user_meta         = [];

        // Use a partial mock of MC4WP_Admin_Tools to control on_plugin_page and is_user_authorized
        $tools = $this->createPartialMock(MC4WP_Admin_Tools::class, ['on_plugin_page', 'is_user_authorized']);
        $tools->method('on_plugin_page')->willReturn(true);
        $tools->method('is_user_authorized')->willReturn(true);

        $this->notice = new MC4WP_Admin_Cron_Notice($tools);
    }

    public function test_not_behind_schedule_when_no_event_scheduled(): void
    {
        global $mock_wp_next_scheduled;
        $mock_wp_next_scheduled = false;

        $this->assertFalse($this->notice->is_cron_behind_schedule());
    }

    public function test_not_behind_schedule_when_event_is_in_future(): void
    {
        global $mock_wp_next_scheduled;
        $mock_wp_next_scheduled = time() + 3600;

        $this->assertFalse($this->notice->is_cron_behind_schedule());
    }

    public function test_not_behind_schedule_when_event_is_slightly_past(): void
    {
        global $mock_wp_next_scheduled;
        // 30 minutes ago — within the 1 hour threshold
        $mock_wp_next_scheduled = time() - 1800;

        $this->assertFalse($this->notice->is_cron_behind_schedule());
    }

    public function test_behind_schedule_when_event_is_over_one_hour_past(): void
    {
        global $mock_wp_next_scheduled;
        // 2 hours ago — well past the threshold
        $mock_wp_next_scheduled = time() - 7200;

        $this->assertTrue($this->notice->is_cron_behind_schedule());
    }

    public function test_show_outputs_notice_when_behind_schedule(): void
    {
        global $mock_wp_next_scheduled;
        $mock_wp_next_scheduled = time() - 7200;

        ob_start();
        $this->notice->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('notice-warning', $output);
        $this->assertStringContainsString('dismiss_cron_notice', $output);
    }

    public function test_show_outputs_nothing_when_on_schedule(): void
    {
        global $mock_wp_next_scheduled;
        $mock_wp_next_scheduled = time() + 3600;

        ob_start();
        $this->notice->show();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_show_outputs_nothing_when_dismissed(): void
    {
        global $mock_wp_next_scheduled, $mock_user_meta;
        $mock_wp_next_scheduled   = time() - 7200;
        $mock_user_meta['1:_mc4wp_cron_notice_dismissed'] = 1;

        ob_start();
        $this->notice->show();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_dismiss_sets_user_meta(): void
    {
        global $mock_user_meta;

        $this->notice->dismiss();
        $this->assertEquals(1, $mock_user_meta['1:_mc4wp_cron_notice_dismissed']);
    }

    public function test_show_outputs_nothing_when_not_on_plugin_page(): void
    {
        global $mock_wp_next_scheduled;
        $mock_wp_next_scheduled = time() - 7200;

        $tools = $this->createPartialMock(MC4WP_Admin_Tools::class, ['on_plugin_page', 'is_user_authorized']);
        $tools->method('on_plugin_page')->willReturn(false);
        $tools->method('is_user_authorized')->willReturn(true);

        $notice = new MC4WP_Admin_Cron_Notice($tools);

        ob_start();
        $notice->show();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }

    public function test_show_outputs_nothing_when_user_not_authorized(): void
    {
        global $mock_wp_next_scheduled;
        $mock_wp_next_scheduled = time() - 7200;

        $tools = $this->createPartialMock(MC4WP_Admin_Tools::class, ['on_plugin_page', 'is_user_authorized']);
        $tools->method('on_plugin_page')->willReturn(true);
        $tools->method('is_user_authorized')->willReturn(false);

        $notice = new MC4WP_Admin_Cron_Notice($tools);

        ob_start();
        $notice->show();
        $output = ob_get_clean();

        $this->assertEmpty($output);
    }
}
