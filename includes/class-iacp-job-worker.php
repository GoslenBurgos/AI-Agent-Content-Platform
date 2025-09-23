<?php

class IACP_Job_Worker {

    public static function process_queue() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_jobs';

        // Find a pending job
        $job = $wpdb->get_row("SELECT * FROM $table_name WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1");

        if (!$job) {
            return; // No pending jobs
        }

        // Mark job as processing
        $wpdb->update(
            $table_name,
            array('status' => 'processing', 'processed_at' => current_time('mysql')),
            array('id' => $job->id)
        );

        $payload = json_decode($job->payload, true);
        $content = IACP_Content_Planner::execute_content_workflow(
            $payload['title'],
            $payload['theme'],
            $payload['draft_agent_id'],
            $payload['seo_agent_id'],
            $payload['copy_agent_id'],
            $payload['image_agent_id'],
            $payload['title_agent_id']
        );

        if (is_wp_error($content)) {
            // Job failed
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'failed',
                    'logs' => $content->get_error_message(),
                ),
                array('id' => $job->id)
            );
        } else {
            // Job completed
            $content_id = IACP_Content_Planner::save_content(
                $payload['title'],
                $payload['theme'],
                $content,
                $payload['virality_score'],
                $payload['status'],
                $payload['draft_agent_id']
            );

            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'content_id' => $content_id,
                ),
                array('id' => $job->id)
            );
        }
    }
}
