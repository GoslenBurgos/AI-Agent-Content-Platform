<?php

class IACP_Social_Media_Planner {

    public static function schedule_post( $content_id, $platform, $message, $publish_date ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_social_media';

        $wpdb->insert(
            $table_name,
            array(
                'content_id' => $content_id,
                'platform' => $platform,
                'message' => $message,
                'publish_date' => $publish_date,
            )
        );
        return $wpdb->insert_id;
    }

    public static function get_all_scheduled_posts() {
        global $wpdb;

        // 1. Get scheduled WordPress blog posts (status = 'future')
        $posts_table = $wpdb->prefix . 'posts';
        $query_blog_posts = $wpdb->prepare("
            SELECT 
                ID as id, 
                'Blog' as platform, 
                post_excerpt as message, 
                post_date as publish_date, 
                post_title as content_title
            FROM {$posts_table}
            WHERE post_status = %s AND post_type = 'post'
        ", 'future');
        $blog_posts = $wpdb->get_results( $query_blog_posts, ARRAY_A );

        // 2. Get scheduled social media posts from our custom table
        $table_social = $wpdb->prefix . 'iacp_social_media';
        $table_content = $wpdb->prefix . 'iacp_content';
        $query_social_posts = "
            SELECT 
                sm.id, 
                sm.platform, 
                sm.message, 
                sm.publish_date, 
                c.title as content_title
            FROM {$table_social} AS sm
            LEFT JOIN {$table_content} AS c ON sm.content_id = c.id
        ";
        $social_posts = $wpdb->get_results( $query_social_posts, ARRAY_A );

        // 3. Merge the two arrays
        $all_posts = array_merge( $blog_posts, $social_posts );

        // 4. Sort the merged array by publish_date
        usort($all_posts, function($a, $b) {
            return strtotime($b['publish_date']) - strtotime($a['publish_date']); // DESC order
        });

        IACP_Logger::log('info', 'get_all_scheduled_posts combined query results', $all_posts);
        return $all_posts;
    }

    public static function delete_scheduled_post( $post_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_social_media';
        return $wpdb->delete( $table_name, array( 'id' => $post_id ), array( '%d' ) );
    }

    public static function update_scheduled_post_date( $post_id, $new_date ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_social_media';

        // The new_date should be in 'YYYY-MM-DD HH:mm:ss' format
        return $wpdb->update(
            $table_name,
            array( 'publish_date' => $new_date ),
            array( 'id' => $post_id ),
            array( '%s' ), // format for new_date
            array( '%d' )  // format for id
        );
    }

    public static function generate_social_post_suggestion( $content_id, callable $content_retriever = null, callable $text_generator = null ) {
        if ($content_retriever === null) {
            $content_retriever = ['IACP_Content_Planner', 'get_content'];
        }
        $content_data = call_user_func($content_retriever, $content_id);

        if ( ! $content_data ) {
            return new WP_Error( 'content_not_found', __( 'The selected article could not be found.', 'ia-agent-content-platform' ) );
        }

        // Create a summary of the content to feed the prompt
        $content_summary = wp_trim_words( $content_data->content, 400, '...' );

        $prompt = "Actúa como un Community Manager experto. Basado en el siguiente artículo, crea un post corto y atractivo para redes sociales. El post debe captar la atención, resumir la idea principal y animar a los usuarios a hacer clic en el enlace del artículo. Incluye 3-5 hashtags relevantes. No incluyas el enlace, solo el texto del post.\n\n";
        $prompt .= "--- INICIO DEL ARTÍCULO ---\n";
        $prompt .= "Título: " . $content_data->title . "\n\n";
        $prompt .= "Resumen: " . $content_summary . "\n";
        $prompt .= "--- FIN DEL ARTÍCULO ---\n\n";
        $prompt .= "Genera solo el texto para el post de redes sociales.";

        if ($text_generator === null) {
            $text_generator = ['IACP_Gemini_Client', 'generate_text'];
        }
        $suggestion = call_user_func($text_generator, $prompt);

        if ( is_wp_error( $suggestion ) ) {
            return $suggestion; // Pass the error up
        }

        return trim( $suggestion );
    }
}
