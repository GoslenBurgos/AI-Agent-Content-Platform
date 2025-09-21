<?php
use PHPUnit\Framework\TestCase;

if (!defined('IACP_PLUGIN_DIR')) {
    define('IACP_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}

// Mock WordPress functions used in the class under test
if (!function_exists('__')) {
    function __($text, $domain) {
        return $text;
    }
}
if (!function_exists('wp_trim_words')) {
    function wp_trim_words($text, $num_words, $more) {
        return 'trimmed content';
    }
}

require_once IACP_PLUGIN_DIR . 'includes/class-iacp-social-media-planner.php';

class IACP_Social_Media_Planner_Test extends TestCase
{
    private $wpdb_mock;

    protected function setUp(): void
    {
        $this->wpdb_mock = $this->getMockBuilder(stdClass::class)
                                 ->addMethods(['insert', 'prepare', 'get_results', 'delete', 'update'])
                                 ->getMock();
        $this->wpdb_mock->prefix = 'wp_';
        global $wpdb;
        $wpdb = $this->wpdb_mock;
    }

    public function test_schedule_post()
    {
        $this->wpdb_mock->expects($this->once())
            ->method('insert')
            ->with(
                'wp_iacp_social_media',
                [
                    'content_id' => 1,
                    'platform' => 'twitter',
                    'message' => 'Hello World',
                    'publish_date' => '2025-10-01 10:00:00',
                ]
            );
        $this->wpdb_mock->insert_id = 123;

        $result = IACP_Social_Media_Planner::schedule_post(1, 'twitter', 'Hello World', '2025-10-01 10:00:00');
        $this->assertEquals(123, $result);
    }

    public function test_get_all_scheduled_posts()
    {
        $mock_blog_posts = [
            ['id' => 1, 'platform' => 'Blog', 'message' => 'Blog post excerpt', 'publish_date' => '2025-10-02 10:00:00', 'content_title' => 'Blog Title']
        ];
        $mock_social_posts = [
            ['id' => 10, 'platform' => 'Twitter', 'message' => 'Tweet text', 'publish_date' => '2025-10-01 12:00:00', 'content_title' => 'Original Content Title']
        ];

        $this->wpdb_mock->method('prepare')->willReturn('prepared_sql');
        $this->wpdb_mock->expects($this->exactly(2))
            ->method('get_results')
            ->willReturnOnConsecutiveCalls($mock_blog_posts, $mock_social_posts);

        $result = IACP_Social_Media_Planner::get_all_scheduled_posts();

        $this->assertCount(2, $result);
        // Check that it's sorted DESC by date
        $this->assertEquals('Blog', $result[0]['platform']);
        $this->assertEquals('Twitter', $result[1]['platform']);
    }

    public function test_delete_scheduled_post()
    {
        $this->wpdb_mock->expects($this->once())
            ->method('delete')
            ->with('wp_iacp_social_media', ['id' => 55], ['%d']);

        IACP_Social_Media_Planner::delete_scheduled_post(55);
    }

    public function test_update_scheduled_post_date()
    {
        $new_date = '2026-01-01 00:00:00';
        $this->wpdb_mock->expects($this->once())
            ->method('update')
            ->with(
                'wp_iacp_social_media',
                ['publish_date' => $new_date],
                ['id' => 77],
                ['%s'],
                ['%d']
            );

        IACP_Social_Media_Planner::update_scheduled_post_date(77, $new_date);
    }

    public function test_generate_social_post_suggestion_success()
    {
        $content_retriever = fn($id) => (object)['title' => 'Test Title', 'content' => 'Test content here'];
        $text_generator = fn($prompt) => '  Generated Tweet! #cool  ';

        $result = IACP_Social_Media_Planner::generate_social_post_suggestion(1, $content_retriever, $text_generator);

        $this->assertEquals('Generated Tweet! #cool', $result);
    }

    public function test_generate_social_post_suggestion_content_not_found()
    {
        $content_retriever = fn($id) => null;
        $result = IACP_Social_Media_Planner::generate_social_post_suggestion(99, $content_retriever);
        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('content_not_found', $result->get_error_code());
    }

    public function test_generate_social_post_suggestion_api_error()
    {
        $content_retriever = fn($id) => (object)['title' => 'Test Title', 'content' => 'Test content here'];
        $text_generator = fn($prompt) => new WP_Error('api_error', 'Gemini failed');

        $result = IACP_Social_Media_Planner::generate_social_post_suggestion(1, $content_retriever, $text_generator);

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('api_error', $result->get_error_code());
    }
}
