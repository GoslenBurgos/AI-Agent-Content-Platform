<?php
use PHPUnit\Framework\TestCase;

// The unified bootstrap file (tests/bootstrap.php) now handles all mock functions and autoloading.

require_once IACP_PLUGIN_DIR . 'includes/class-iacp-security-helper.php';
require_once IACP_PLUGIN_DIR . 'includes/class-iacp-gemini-client.php';

class IACP_Gemini_Client_Test extends TestCase
{
    // Helper to reset globals before each test
    protected function setUp(): void
    {
        global $mock_get_option_return, $mock_get_transient_return, $mock_wp_remote_post_return, $mock_delete_transient_called, $mock_set_transient_values;
        $mock_get_option_return = [];
        $mock_get_transient_return = [];
        $mock_wp_remote_post_return = [];
        $mock_delete_transient_called = [];
        $mock_set_transient_values = [];
    }

    public function test_generate_text_returns_error_if_api_key_is_missing()
    {
        global $mock_get_option_return;
        $mock_get_option_return['iacp_gemini_api_key'] = '';

        $result = IACP_Gemini_Client::generate_text('some prompt');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('api_key_missing', $result->get_error_code());
    }

    public function test_generate_text_returns_error_if_circuit_is_open()
    {
        global $mock_get_transient_return;
        $mock_get_transient_return['iacp_gemini_api_circuit_open'] = true;

        $result = IACP_Gemini_Client::generate_text('some prompt');

        $this->assertInstanceOf(WP_Error::class, $result);
        $this->assertEquals('circuit_breaker', $result->get_error_code());
    }

    public function test_generate_text_returns_text_on_successful_api_call()
    {
        global $mock_get_option_return, $mock_wp_remote_post_return, $mock_delete_transient_called;

        $mock_get_option_return['iacp_gemini_api_key'] = IACP_Security_Helper::encrypt('fake-api-key');

        $gemini_response_body = json_encode([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'This is the generated text.']
                        ]
                    ]
                ]
            ]
        ]);
        $mock_wp_remote_post_return = [
            'body' => $gemini_response_body,
            'response' => ['code' => 200]
        ];

        $result = IACP_Gemini_Client::generate_text('a good prompt');

        $this->assertEquals('This is the generated text.', $result);
        $this->assertTrue(isset($mock_delete_transient_called['iacp_gemini_api_failures']));
    }

    public function test_generate_text_retries_on_server_error_and_succeeds()
    {
        global $mock_get_option_return, $mock_wp_remote_post_return, $mock_set_transient_values;

        $mock_get_option_return['iacp_gemini_api_key'] = IACP_Security_Helper::encrypt('fake-api-key');

        $server_error_response = ['response' => ['code' => 503]];
        $successful_response = [
            'body' => json_encode([
                'candidates' => [['content' => ['parts' => [['text' => 'Success after retry.']]]]]
            ]),
            'response' => ['code' => 200]
        ];
        $mock_wp_remote_post_return = [$server_error_response, $successful_response];

        $result = IACP_Gemini_Client::generate_text('a prompt that will fail once');

        $this->assertEquals('Success after retry.', $result);

        $this->assertNotEmpty($mock_set_transient_values['iacp_gemini_api_failures']);
        $this->assertCount(1, $mock_set_transient_values['iacp_gemini_api_failures']);
    }
}