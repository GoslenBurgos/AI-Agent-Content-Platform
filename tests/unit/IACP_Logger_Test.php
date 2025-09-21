<?php
use PHPUnit\Framework\TestCase;

if (!defined('IACP_PLUGIN_DIR')) {
    define('IACP_PLUGIN_DIR', dirname(dirname(__DIR__)) . '/');
}
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-logger.php';

class IACP_Logger_Test extends TestCase
{
    public function test_format_log_entry_without_data() {
        $level = 'INFO';
        $message = 'This is a test message.';

        $formatted_entry = IACP_Logger::format_log_entry($level, $message);

        // Assert that the string contains the basic parts
        $this->assertStringContainsString('[INFO]', $formatted_entry);
        $this->assertStringContainsString('- This is a test message.', $formatted_entry);
        
        // Assert that it does NOT contain the data part
        $this->assertStringNotContainsString('| Data:', $formatted_entry);

        // Assert the overall structure with regex
        $timestamp_regex = '\[\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}\]'; // Matches [YYYY-MM-DD HH:MM:SS]
        $this->assertMatchesRegularExpression('/' . $timestamp_regex . ' \[INFO\] - This is a test message\.$/', $formatted_entry);
    }

    public function test_format_log_entry_with_data() {
        $level = 'DEBUG';
        $message = 'Debugging process.';
        $data = ['user_id' => 123, 'action' => 'save_post'];

        $formatted_entry = IACP_Logger::format_log_entry($level, $message, $data);

        // Assert that the string contains the basic parts
        $this->assertStringContainsString('[DEBUG]', $formatted_entry);
        $this->assertStringContainsString('- Debugging process.', $formatted_entry);
        $this->assertStringContainsString('| Data:', $formatted_entry);
        
        // Assert that the data is correctly printed
        $this->assertStringContainsString('[user_id] => 123', $formatted_entry);
        $this->assertStringContainsString('[action] => save_post', $formatted_entry);

        // Assert the overall structure with regex
        $timestamp_regex = '\[\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}\]';
        $this->assertMatchesRegularExpression('/' . $timestamp_regex . ' \[DEBUG\] - Debugging process\. \| Data: Array/', $formatted_entry);
    }
}
