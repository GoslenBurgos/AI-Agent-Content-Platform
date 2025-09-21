<div class="wrap">
    <h1><?php esc_html_e( 'Agent Management', 'ia-agent-content-platform' ); ?></h1>
    <p><?php esc_html_e( 'Create, customize, and manage your content creation agents.', 'ia-agent-content-platform' ); ?></p>

    <div id="agent-management-wrapper">
        <div id="col-left">
            <div class="col-wrap">
                <h2><?php esc_html_e( 'Add New Agent', 'ia-agent-content-platform' ); ?></h2>
                <form id="add-agent-form" method="post">
                    <div class="form-field">
                        <label for="agent-name"><?php esc_html_e( 'Name', 'ia-agent-content-platform' ); ?></label>
                        <input type="text" id="agent-name" name="agent-name" required>
                    </div>
                    <div class="form-field">
                        <label for="agent-role"><?php esc_html_e( 'Role', 'ia-agent-content-platform' ); ?></label>
                        <textarea id="agent-role" name="agent-role" rows="3" required></textarea>
                    </div>
                    <div class="form-field">
                        <label for="agent-experience"><?php esc_html_e( 'Experience', 'ia-agent-content-platform' ); ?></label>
                        <textarea id="agent-experience" name="agent-experience" rows="3" required></textarea>
                    </div>
                    <div class="form-field">
                        <label for="agent-tasks"><?php esc_html_e( 'Tasks', 'ia-agent-content-platform' ); ?></label>
                        <textarea id="agent-tasks" name="agent-tasks" rows="5" required></textarea>
                    </div>
                    <div class="form-field">
                        <label for="agent-prompt"><?php esc_html_e( 'Prompt', 'ia-agent-content-platform' ); ?></label>
                        <textarea id="agent-prompt" name="agent-prompt" rows="7" required></textarea>
                    </div>
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add New Agent', 'ia-agent-content-platform' ); ?>">
                    </p>
                </form>
            </div>
        </div>
        <div id="col-right">
            <div class="col-wrap">
                <h2><?php esc_html_e( 'Existing Agents', 'ia-agent-content-platform' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Name', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Role', 'ia-agent-content-platform' ); ?></th>
                            <th scope="col" class="manage-column"><?php esc_html_e( 'Actions', 'ia-agent-content-platform' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-list">
                        <!-- Agent list will be populated here via AJAX -->
                        <tr>
                            <td colspan="3"><?php esc_html_e( 'No agents found.', 'ia-agent-content-platform' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Agent Modal -->
<div id="edit-agent-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; margin:10% auto; padding:20px; width:50%; max-width:600px; border-radius:5px;">
        <h2><?php esc_html_e( 'Edit Agent', 'ia-agent-content-platform' ); ?></h2>
        <form id="edit-agent-form" method="post">
            <input type="hidden" id="edit-agent-id" name="edit-agent-id">
            <div class="form-field">
                <label for="edit-agent-name"><?php esc_html_e( 'Name', 'ia-agent-content-platform' ); ?></label>
                <input type="text" id="edit-agent-name" name="edit-agent-name" required>
            </div>
            <div class="form-field">
                <label for="edit-agent-role"><?php esc_html_e( 'Role', 'ia-agent-content-platform' ); ?></label>
                <textarea id="edit-agent-role" name="edit-agent-role" rows="3" required></textarea>
            </div>
            <div class="form-field">
                <label for="edit-agent-experience"><?php esc_html_e( 'Experience', 'ia-agent-content-platform' ); ?></label>
                <textarea id="edit-agent-experience" name="edit-agent-experience" rows="3" required></textarea>
            </div>
            <div class="form-field">
                <label for="edit-agent-tasks"><?php esc_html_e( 'Tasks', 'ia-agent-content-platform' ); ?></label>
                <textarea id="edit-agent-tasks" name="edit-agent-tasks" rows="5" required></textarea>
            </div>
            <div class="form-field">
                <label for="edit-agent-prompt"><?php esc_html_e( 'Prompt', 'ia-agent-content-platform' ); ?></label>
                <textarea id="edit-agent-prompt" name="edit-agent-prompt" rows="7" required></textarea>
            </div>
            <p class="submit">
                <input type="submit" name="submit" id="submit-edit-agent" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'ia-agent-content-platform' ); ?>">
                <button type="button" id="cancel-edit-agent" class="button"><?php esc_html_e( 'Cancel', 'ia-agent-content-platform' ); ?></button>
            </p>
        </form>
    </div>
</div>
