<div class="wrap">
    <h1><?php esc_html_e( 'Settings', 'ia-agent-content-platform' ); ?></h1>
    <form method="post" action="options.php">
        <?php
            settings_fields( 'iacp_settings_group' );
            do_settings_sections( 'iacp_settings_group' );
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Gemini API Key', 'ia-agent-content-platform' ); ?></th>
                <td><input type="text" name="iacp_gemini_api_key" value="<?php echo esc_attr( get_option('iacp_gemini_api_key') ); ?>" size="50"/></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'Gemini Model Name', 'ia-agent-content-platform' ); ?></th>
                <td>
                    <input type="text" name="iacp_gemini_model_name" value="<?php echo esc_attr( get_option('iacp_gemini_model_name', 'gemini-1.5-flash-latest') ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'e.g., gemini-1.5-flash-latest, gemini-1.5-pro-latest', 'ia-agent-content-platform' ); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e( 'Cache Management', 'ia-agent-content-platform' ); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php esc_html_e( 'API Cache', 'ia-agent-content-platform' ); ?></th>
                <td>
                    <button type="button" id="iacp-clear-api-cache" class="button"><?php esc_html_e( 'Clear API Cache', 'ia-agent-content-platform' ); ?></button>
                    <p class="description"><?php esc_html_e( 'This will clear all cached API responses.', 'ia-agent-content-platform' ); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
