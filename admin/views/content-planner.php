<div class="wrap">
    <h1><?php esc_html_e( 'Content Planner', 'ia-agent-content-platform' ); ?></h1>
    <p><?php esc_html_e( 'Generate ideas, create articles, and check their virality potential.', 'ia-agent-content-platform' ); ?></p>

    <div id="content-planner-wrapper">
        <div id="col-left">
            <div class="col-wrap">
                <h2><?php esc_html_e( 'Idea Generator', 'ia-agent-content-platform' ); ?></h2>
                <form id="idea-generator-form" method="post">
                    <div class="form-field">
                        <label for="idea-keywords"><?php esc_html_e( 'Keywords', 'ia-agent-content-platform' ); ?></label>
                        <input type="text" id="idea-keywords" name="idea-keywords" placeholder="<?php esc_attr_e( 'e.g., artificial intelligence, content creation', 'ia-agent-content-platform' ); ?>" required>
                    </div>
                    <p class="submit">
                        <input type="submit" name="submit" id="submit-idea" class="button button-primary" value="<?php esc_attr_e( 'Generate Ideas', 'ia-agent-content-platform' ); ?>">
                    </p>
                </form>

                <hr>

                <h2><?php esc_html_e( 'Content Generation Workflow', 'ia-agent-content-platform' ); ?></h2>
                <form id="content-generator-form" method="post">
                    <div class="form-field">
                        <label for="content-title"><?php esc_html_e( 'Title', 'ia-agent-content-platform' ); ?></label>
                        <input type="text" id="content-title" name="content-title" required>
                    </div>
                    <div class="form-field">
                        <label for="content-theme"><?php esc_html_e( 'Main Theme / Abstract', 'ia-agent-content-platform' ); ?></label>
                        <textarea id="content-theme" name="content-theme" rows="4" required></textarea>
                    </div>

                    <div class="workflow-step">
                        <h4><?php esc_html_e( 'Step 1: Initial Draft', 'ia-agent-content-platform' ); ?></h4>
                        <label for="draft-agent-selector"><?php esc_html_e( 'Select Writing Agent', 'ia-agent-content-platform' ); ?></label>
                        <select id="draft-agent-selector" name="draft-agent-selector" class="agent-selector" required>
                            <option value=""><?php esc_html_e( 'Select an Agent...', 'ia-agent-content-platform' ); ?></option>
                        </select>
                    </div>

                    <div class="workflow-step">
                        <h4><?php esc_html_e( 'Step 2: SEO Optimization (Optional)', 'ia-agent-content-platform' ); ?></h4>
                        <label for="seo-agent-selector"><?php esc_html_e( 'Select SEO Agent', 'ia-agent-content-platform' ); ?></label>
                        <select id="seo-agent-selector" name="seo-agent-selector" class="agent-selector">
                            <option value=""><?php esc_html_e( 'Skip this step...', 'ia-agent-content-platform' ); ?></option>
                        </select>
                    </div>

                    <div class="workflow-step">
                        <h4><?php esc_html_e( 'Step 3: Copywriting Review (Optional)', 'ia-agent-content-platform' ); ?></h4>
                        <label for="copy-agent-selector"><?php esc_html_e( 'Select Copywriting Agent', 'ia-agent-content-platform' ); ?></label>
                        <select id="copy-agent-selector" name="copy-agent-selector" class="agent-selector">
                            <option value=""><?php esc_html_e( 'Skip this step...', 'ia-agent-content-platform' ); ?></option>
                        </select>
                    </div>

                    <div class="workflow-step">
                        <h4><?php esc_html_e( 'Step 4: Image Prompt Generation (Optional)', 'ia-agent-content-platform' ); ?></h4>
                        <label for="image-agent-selector"><?php esc_html_e( 'Select Image Agent', 'ia-agent-content-platform' ); ?></label>
                        <select id="image-agent-selector" name="image-agent-selector" class="agent-selector">
                            <option value=""><?php esc_html_e( 'Skip this step...', 'ia-agent-content-platform' ); ?></option>
                        </select>
                    </div>

                    <div class="workflow-step">
                        <h4><?php esc_html_e( 'Step 5: Alternative Titles (Optional)', 'ia-agent-content-platform' ); ?></h4>
                        <label for="title-agent-selector"><?php esc_html_e( 'Select Title Agent', 'ia-agent-content-platform' ); ?></label>
                        <select id="title-agent-selector" name="title-agent-selector" class="agent-selector">
                            <option value=""><?php esc_html_e( 'Skip this step...', 'ia-agent-content-platform' ); ?></option>
                        </select>
                    </div>

                    <input type="hidden" id="iacp_virality_score" name="iacp_virality_score" value="0">
                    <input type="hidden" id="iacp_content_status" name="iacp_content_status" value="draft">
                    <p class="submit">
                        <input type="submit" name="submit" id="submit-content" class="button button-primary" value="<?php esc_attr_e( 'Generate Content', 'ia-agent-content-platform' ); ?>">
                    </p>
                </form>
            </div>
        </div>
        <div id="col-right">
            <div class="col-wrap">
                <h2><?php esc_html_e( 'Generated Ideas', 'ia-agent-content-platform' ); ?></h2>
                <div id="generated-ideas-container">
                    <p><?php esc_html_e( 'Generated ideas will appear here.', 'ia-agent-content-platform' ); ?></p>
                </div>

                <hr>

                <h2><?php esc_html_e( 'Generated Content', 'ia-agent-content-platform' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Title', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Virality Score', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Views', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Status', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Actions', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Delete', 'ia-agent-content-platform' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-content-list">
                        <!-- Generated content will be populated here via AJAX -->
                        <tr>
                            <td colspan="6"><?php esc_html_e( 'No content generated yet.', 'ia-agent-content-platform' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Content Modal -->
<div id="view-content-modal" class="iacp-view-content-modal">
    <div class="iacp-view-content-modal-content">
        <span id="view-content-modal-close" class="iacp-view-content-modal-close">&times;</span>
        <h2 id="view-content-modal-title"></h2> <!-- Title is set dynamically via JS -->
        <hr>
        <div id="view-content-modal-body">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Content History Modal -->
<div id="content-history-modal" class="iacp-view-content-modal"> <!-- Reusing modal styles -->
    <div class="iacp-view-content-modal-content">
        <span id="content-history-modal-close" class="iacp-view-content-modal-close">&times;</span>
        <h2><?php esc_html_e( 'Content Version History', 'ia-agent-content-platform' ); ?></h2>
        <hr>
        <div id="content-history-modal-body">
            <!-- Version history will be loaded here -->
        </div>
    </div>
</div>
