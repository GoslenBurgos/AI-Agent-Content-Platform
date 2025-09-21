<div class="wrap">
    <h1><?php esc_html_e( 'IA Agent Content Platform Dashboard', 'ia-agent-content-platform' ); ?></h1>
    <p><?php esc_html_e( 'Welcome to the central hub for your automated content creation.', 'ia-agent-content-platform' ); ?></p>

    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e( 'Content Calendar', 'ia-agent-content-platform' ); ?></span></h2>
                        <div class="inside">
                            <div id="calendar">
                                <p><?php esc_html_e( 'Loading calendar...', 'ia-agent-content-platform' ); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e( 'Editorial Profile', 'ia-agent-content-platform' ); ?></span></h2>
                        <div class="inside">
                            <div id="editorial-profile-form-container">
                                <p><?php esc_html_e( 'Define the voice, tone, and style for the AI-generated content.', 'ia-agent-content-platform' ); ?></p>
                                <div class="form-field">
                                    <label for="target-audience"><?php esc_html_e( 'Target Audience', 'ia-agent-content-platform' ); ?></label>
                                    <textarea id="target-audience" name="target_audience" rows="2" style="width:100%;"><?php echo esc_textarea(get_option('iacp_editorial_profile_target_audience', '')); ?></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="voice-tone"><?php esc_html_e( 'Voice and Tone', 'ia-agent-content-platform' ); ?></label>
                                    <textarea id="voice-tone" name="voice_tone" rows="2" style="width:100%;"><?php echo esc_textarea(get_option('iacp_editorial_profile_voice_tone', '')); ?></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="style-guide"><?php esc_html_e( 'Style Guide (e.g., use active voice, avoid jargon)', 'ia-agent-content-platform' ); ?></label>
                                    <textarea id="style-guide" name="style_guide" rows="3" style="width:100%;"><?php echo esc_textarea(get_option('iacp_editorial_profile_style_guide', '')); ?></textarea>
                                </div>
                                <div class="form-field">
                                    <label for="banned-words"><?php esc_html_e( 'Banned Words (comma-separated)', 'ia-agent-content-platform' ); ?></label>
                                    <input type="text" id="banned-words" name="banned_words" value="<?php echo esc_attr(get_option('iacp_editorial_profile_banned_words', '')); ?>" style="width:100%;">
                                </div>
                                <p class="submit">
                                    <button type="button" name="submit" id="submit-editorial-profile" class="button button-primary"><?php esc_html_e( 'Save Profile', 'ia-agent-content-platform' ); ?></button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="postbox-container-2" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e( 'Content Planner', 'ia-agent-content-platform' ); ?></span></h2>
                        <div class="inside">
                            <p><?php esc_html_e( 'Plan your next articles, generate ideas and content.', 'ia-agent-content-platform' ); ?></p>
                            <a href="?page=ia-agent-content-platform-content-planner" class="button button-primary"><?php esc_html_e( 'Go to Content Planner', 'ia-agent-content-platform' ); ?></a>
                        </div>
                    </div>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e( 'Agent Management', 'ia-agent-content-platform' ); ?></span></h2>
                        <div class="inside">
                            <p><?php esc_html_e( 'Create, edit, and manage your content creation agents.', 'ia-agent-content-platform' ); ?></p>
                            <a href="?page=ia-agent-content-platform-agent-management" class="button button-primary"><?php esc_html_e( 'Go to Agent Management', 'ia-agent-content-platform' ); ?></a>
                        </div>
                    </div>
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e( 'Social Media Planner', 'ia-agent-content-platform' ); ?></span></h2>
                        <div class="inside">
                            <p><?php esc_html_e( 'Schedule your content for social media platforms.', 'ia-agent-content-platform' ); ?></p>
                            <a href="?page=ia-agent-content-platform-social-media-planner" class="button button-primary"><?php esc_html_e( 'Go to Social Media Planner', 'ia-agent-content-platform' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
