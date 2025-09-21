<div class="wrap">
    <h1><?php esc_html_e( 'Social Media Planner', 'ia-agent-content-platform' ); ?></h1>
    <p><?php esc_html_e( 'Schedule and manage your content distribution on social media.', 'ia-agent-content-platform' ); ?></p>

    <div id="social-media-planner-wrapper">
        <div id="col-left">
            <div class="col-wrap">
                <h2><?php esc_html_e( 'Schedule Post', 'ia-agent-content-platform' ); ?></h2>
                <form id="schedule-post-form" method="post">
                    <div class="form-field">
                        <label for="post-content-id"><?php esc_html_e( 'Select Content', 'ia-agent-content-platform' ); ?></label>
                        <select id="post-content-id" name="post-content-id" required>
                            <!-- Content options will be populated here -->
                            <option value=""><?php esc_html_e( 'Select an article', 'ia-agent-content-platform' ); ?></option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e( 'Platforms', 'ia-agent-content-platform' ); ?></label>
                        <div class="platforms-checkboxes">
                            <label><input type="checkbox" name="platforms[]" value="blog" checked> <?php esc_html_e( 'Blog (WordPress Post)', 'ia-agent-content-platform' ); ?></label><br>
                            <label><input type="checkbox" name="platforms[]" value="facebook"> <?php esc_html_e( 'Facebook', 'ia-agent-content-platform' ); ?></label><br>
                            <label><input type="checkbox" name="platforms[]" value="instagram"> <?php esc_html_e( 'Instagram', 'ia-agent-content-platform' ); ?></label><br>
                            <label><input type="checkbox" name="platforms[]" value="linkedin"> <?php esc_html_e( 'LinkedIn', 'ia-agent-content-platform' ); ?></label><br>
                            <label><input type="checkbox" name="platforms[]" value="twitter"> <?php esc_html_e( 'X (Twitter)', 'ia-agent-content-platform' ); ?></label><br>
                            <label><input type="checkbox" name="platforms[]" value="youtube"> <?php esc_html_e( 'YouTube', 'ia-agent-content-platform' ); ?></label><br>
                            <label><input type="checkbox" name="platforms[]" value="tiktok"> <?php esc_html_e( 'TikTok', 'ia-agent-content-platform' ); ?></label><br>
                        </div>
                    </div>
                    <div class="form-field">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label for="post-message"><?php esc_html_e( 'Message', 'ia-agent-content-platform' ); ?></label>
                            <button type="button" id="generate-social-message" class="button button-small">✨ Generate with AI</button>
                        </div>
                        <textarea id="post-message" name="post-message" rows="5" placeholder="<?php esc_attr_e( 'Or generate one with AI...', 'ia-agent-content-platform' ); ?>"></textarea>
                    </div>
                    <div class="form-field">
                        <label for="post-publish-date"><?php esc_html_e( 'Publish Date', 'ia-agent-content-platform' ); ?></label>
                        <input type="datetime-local" id="post-publish-date" name="post-publish-date" required>
                    </div>
                    <p class="submit">
                        <input type="submit" name="submit" id="submit-schedule" class="button button-primary" value="<?php esc_attr_e( 'Schedule Post', 'ia-agent-content-platform' ); ?>">
                    </p>
                </form>
            </div>
        </div>
        <div id="col-right">
            <div class="col-wrap">
                <h2><?php esc_html_e( 'Scheduled Posts', 'ia-agent-content-platform' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Article Title', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Platform', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Message', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Publish Date', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Actions', 'ia-agent-content-platform' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-schedule-list">
                        <!-- Scheduled posts will be populated here via AJAX -->
                        <tr>
                            <td colspan="5"><?php esc_html_e( 'No posts scheduled yet.', 'ia-agent-content-platform' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>