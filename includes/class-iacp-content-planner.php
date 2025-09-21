<?php

class IACP_Content_Planner {

    /**
     * Generates blog post ideas based on keywords.
     *
     * @param string $keywords The keywords to base the ideas on.
     * @param callable|null $gemini_text_generator Optional. A callable function to generate text. Used for testing.
     * @return array|WP_Error An array of ideas or a WP_Error on failure.
     */
    public static function generate_ideas( $keywords, callable $gemini_text_generator = null ) {
        $prompt = "Basado en las siguientes palabras clave: '{$keywords}', genera 3 ideas para artículos de blog. Para cada idea, proporciona un análisis completo. Devuelve el resultado como un único array JSON. Cada objeto en el array debe tener las siguientes claves exactas:
        - title: (string) El título del artículo.
        - is_simple: (string) 'Sí' o 'No', ¿un niño de 5 años lo entiende?
        - audience_interest: (string) 'Sí' o 'No', ¿a un grupo de 50-100 personas les podría interesar?
        - is_viral_reference: (string) 'Sí' o 'No', ¿hace referencia a una persona o tema viral del momento?
        - is_trending: (string) 'Sí' o 'No', ¿el tema general está en tendencia?
        - is_controversial: (string) 'Sí' o 'No', ¿el tema es controvertido?
        - score: (integer) Una puntuación de viralidad del 1 al 10 basada en los puntos anteriores.
        - hook: (string) Escribe un gancho de 1-2 frases para el inicio del artículo.
        - story: (string) Resume en 1-2 frases la historia o el contexto que podría tener el artículo.
        - moral: (string) Resume en 1 frase la moraleja o el aprendizaje principal.
        - cta: (string) Sugiere un llamado a la acción (Call To Action) para el final del artículo.";

        if ($gemini_text_generator === null) {
            $gemini_text_generator = ['IACP_Gemini_Client', 'generate_text'];
        }
        $response = call_user_func($gemini_text_generator, $prompt);

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $evaluated_ideas = self::clean_json_response( $response );

        if ( empty( $evaluated_ideas ) ) {
            return new WP_Error( 'analysis_failed', 'No se pudo analizar ninguna de las ideas generadas. Respuesta de la API: ' . esc_html( $response ) );
        }

        return $evaluated_ideas;
    }

    public static function execute_content_workflow( $title, $theme, $draft_agent_id, $seo_agent_id = 0, $copy_agent_id = 0, $image_agent_id = 0, $title_agent_id = 0, callable $agent_retriever = null, callable $text_generator = null ) {
        if ( empty( $draft_agent_id ) ) {
            return new WP_Error( 'agent_not_selected', 'Please select an agent to generate the content.' );
        }

        if ($agent_retriever === null) {
            $agent_retriever = ['IACP_Agents', 'get_agent'];
        }
        if ($text_generator === null) {
            $text_generator = ['IACP_Gemini_Client', 'generate_text'];
        }

        $draft_agent = call_user_func($agent_retriever, $draft_agent_id);
        if ( ! $draft_agent ) {
            return new WP_Error( 'agent_not_found', 'The selected agent could not be found.' );
        }

        $editorial_profile_prompt = self::get_editorial_profile_prompt();
        $draft_prompt = $draft_agent->prompt;
        $draft_prompt = str_replace( '[TITULO]', $title, $draft_prompt );
        $draft_prompt = str_replace( '[TEMA]', $theme, $draft_prompt );
        $draft_prompt = str_replace( '[PERFIL_EDITORIAL]', $editorial_profile_prompt, $draft_prompt );

        $final_content = call_user_func($text_generator, $draft_prompt);

        if ( is_wp_error( $final_content ) ) {
            return $final_content;
        }

        $workflow_steps = [
            ['agent_id' => $seo_agent_id, 'mode' => 'append', 'header' => "\n\n--- SEO ANALYSIS ---\n"],
            ['agent_id' => $copy_agent_id, 'mode' => 'replace'],
            ['agent_id' => $image_agent_id, 'mode' => 'append', 'header' => "\n\n--- IMAGE PROMPT SUGGESTIONS ---
"],
            ['agent_id' => $title_agent_id, 'mode' => 'append', 'header' => "\n\n--- ALTERNATIVE TITLE SUGGESTIONS ---
"]
        ];

        foreach ($workflow_steps as $step) {
            $final_content = self::process_workflow_step(
                $final_content,
                $step['agent_id'],
                $step['mode'],
                isset($step['header']) ? $step['header'] : '',
                $agent_retriever,
                $text_generator
            );
        }

        return $final_content;
    }

    private static function process_workflow_step($current_content, $agent_id, $mode = 'append', $header = '', callable $agent_retriever = null, callable $text_generator = null) {
        if (empty($agent_id)) {
            return $current_content;
        }

        if ($agent_retriever === null) {
            $agent_retriever = ['IACP_Agents', 'get_agent'];
        }
        if ($text_generator === null) {
            $text_generator = ['IACP_Gemini_Client', 'generate_text'];
        }

        $agent = call_user_func($agent_retriever, $agent_id);
        if (!$agent) {
            return $current_content;
        }

        $prompt = str_replace('[BORRADOR_ARTICULO]', $current_content, $agent->prompt);
        $result = call_user_func($text_generator, $prompt);

        if (is_wp_error($result)) {
            IACP_Logger::log('warn', 'Workflow step failed for agent ' . $agent_id, ['error' => $result->get_error_message()]);
            return $current_content;
        }

        return ($mode === 'replace') ? $result : $current_content . $header . $result;
    }

    public static function save_content( $title, $theme, $content, $virality_score, $status, $agent_id = 0 ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content';

        $wpdb->insert(
            $table_name,
            [
                'title' => $title,
                'theme' => $theme,
                'content' => $content,
                'virality_score' => $virality_score,
                'status' => $status,
            ]
        );
        $insert_id = $wpdb->insert_id;
        if ( $insert_id ) {
            self::save_content_version( $insert_id, $content, $agent_id );
        }
        return $insert_id;
    }

    public static function get_all_content() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content';
        return $wpdb->get_results( "SELECT * FROM $table_name" );
    }

    public static function get_content( $content_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $content_id ) );
    }

    public static function update_content_status( $content_id, $status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content';
        $wpdb->update(
            $table_name,
            [ 'status' => $status ],
            [ 'id' => $content_id ],
            [ '%s' ], [ '%d' ]
        );
    }

    public static function delete_content( $content_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content';
        $versions_table_name = $wpdb->prefix . 'iacp_content_versions';
        $wpdb->delete( $versions_table_name, [ 'content_id' => $content_id ], [ '%d' ] );
        return $wpdb->delete( $table_name, [ 'id' => $content_id ], [ '%d' ] );
    }

    public static function save_content_version( $content_id, $content, $agent_id = 0, $note = '', callable $agent_retriever = null ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content_versions';

        $version_note = $note;
        if ( empty($version_note) && !empty($agent_id) ) {
            if ($agent_retriever === null) {
                $agent_retriever = ['IACP_Agents', 'get_agent'];
            }
            $agent = call_user_func($agent_retriever, $agent_id);
            $version_note = $agent ? sprintf('Generated by agent: %s', $agent->name) : 'Initial generation';
        } elseif (empty($version_note)) {
            $version_note = 'Initial version';
        }

        $wpdb->insert(
            $table_name,
            [
                'content_id'   => $content_id,
                'content'      => $content,
                'version_note' => $version_note,
                'created_at'   => current_time( 'mysql' ),
                'created_by'   => get_current_user_id(),
            ]
        );
    }

    public static function get_content_versions( $content_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content_versions';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE content_id = %d ORDER BY created_at DESC", $content_id ) );
    }

    public static function restore_content_version( $version_id, callable $agent_retriever = null ) {
        global $wpdb;
        $versions_table = $wpdb->prefix . 'iacp_content_versions';
        $content_table = $wpdb->prefix . 'iacp_content';

        $version_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $versions_table WHERE id = %d", $version_id ) );
        if ( ! $version_data ) {
            return new WP_Error( 'version_not_found', 'Content version not found.' );
        }

        $wpdb->update(
            $content_table,
            [ 'content' => $version_data->content ],
            [ 'id' => $version_data->content_id ],
            [ '%s' ], [ '%d' ]
        );
        self::save_content_version( $version_data->content_id, $version_data->content, 0, sprintf( 'Restored from version created at %s', $version_data->created_at ), $agent_retriever );
        return true;
    }

    public static function track_post_view( $content ) {
        global $post;

        if ( is_singular( 'post' ) && in_the_loop() && is_main_query() && is_object( $post ) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'iacp_content';

            $content_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table_name WHERE post_id = %d",
                $post->ID
            ) );

            if ( $content_id ) {
                $cookie_name = 'iacp_viewed_post_' . $content_id;
                if ( ! isset( $_COOKIE[ $cookie_name ] ) ) {
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE $table_name SET views = views + 1 WHERE id = %d",
                        $content_id
                    ) );
                    setcookie( $cookie_name, '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
                }
            }
        }

        return $content;
    }

    public static function publish_content_as_post( $content_id, $publish_date_str = null ) {
        IACP_Logger::log('debug', '--- INICIANDO DEPURACIÓN DE PUBLICACIÓN PROGRAMADA ---');
        IACP_Logger::log('debug', '[PASO 1] Valor crudo de `publish_date_str` recibido: ' . var_export($publish_date_str, true));

        $content_data = self::get_content( $content_id );

        if ( ! $content_data ) {
            IACP_Logger::log('error', 'No se encontró contenido para el ID: ' . $content_id);
            return new WP_Error( 'content_not_found', 'Contenido no encontrado para publicar como post.' );
        }

        if ( empty( $publish_date_str ) ) {
            $post_status = 'publish';
            $post_date_gmt = current_time('mysql', 1);
            $post_date = current_time('mysql');
            IACP_Logger::log('debug', '[CASO] No se proporcionó fecha. Publicando inmediatamente.', ['status' => $post_status, 'date_gmt' => $post_date_gmt]);
        } else {
            $local_date_str = str_replace('T', ' ', $publish_date_str) . ':00';
            IACP_Logger::log('debug', '[PASO 2] String de fecha local formateado: ' . $local_date_str);

            $gmt_date_str = get_gmt_from_date($local_date_str);
            IACP_Logger::log('debug', '[PASO 3] String de fecha convertido a GMT: ' . $gmt_date_str);

            $local_date_for_post = get_date_from_gmt($gmt_date_str);
            IACP_Logger::log('debug', '[PASO 4] Fecha GMT convertida de nuevo a local para `post_date`: ' . $local_date_for_post);

            $gmt_timestamp_to_publish = strtotime($gmt_date_str);
            $current_gmt_timestamp = current_time('timestamp', 1);
            IACP_Logger::log('debug', '[PASO 5] Comparando Timestamps GMT', [
                'timestamp_para_publicar' => $gmt_timestamp_to_publish,
                'timestamp_actual_gmt'    => $current_gmt_timestamp,
                'diferencia_segundos'     => $gmt_timestamp_to_publish - $current_gmt_timestamp
            ]);

            if ( $gmt_timestamp_to_publish > $current_gmt_timestamp ) {
                $post_status = 'future';
            } else {
                $post_status = 'publish';
            }
            
            IACP_Logger::log('debug', '[PASO 6] Estado de post determinado: ' . $post_status);
            
            $post_date = $local_date_for_post;
            $post_date_gmt = $gmt_date_str;
        }

        $new_post = [
            'post_title'    => $content_data->title,
            'post_content'  => $content_data->content,
            'post_status'   => $post_status,
            'post_type'     => 'post',
            'post_author'   => get_current_user_id(),
            'post_date'     => $post_date,
            'post_date_gmt' => $post_date_gmt,
        ];
        IACP_Logger::log('debug', '[PASO 7] Datos finales para `wp_insert_post`:', $new_post);

        $post_id = wp_insert_post( $new_post, true );

        if ( is_wp_error( $post_id ) ) {
            IACP_Logger::log('error', '`wp_insert_post` falló.', $post_id->get_error_messages());
            return $post_id;
        }

        IACP_Logger::log('info', 'Post insertado/programado con éxito. ID: ' . $post_id);

        global $wpdb;
        $table_name = $wpdb->prefix . 'iacp_content';
        $wpdb->update(
            $table_name,
            [ 'post_id' => $post_id ],
            [ 'id' => $content_id ],
            [ '%d' ], [ '%d' ]
        );

        IACP_Logger::log('debug', '--- FINALIZANDO DEPURACIÓN DE PUBLICACIÓN PROGRAMADA ---');
        return $post_id;
    }

    private static function clean_json_response( $response_string ) {
        $cleaned_string = trim( str_replace( [ '```json', '```' ], '', $response_string ) );
        return json_decode( $cleaned_string, true );
    }

    private static function get_editorial_profile_prompt() {
        $target_audience = get_option('iacp_editorial_profile_target_audience', '');
        $voice_tone = get_option('iacp_editorial_profile_voice_tone', 'professional but accessible');
        $style_guide = get_option('iacp_editorial_profile_style_guide', '');
        $banned_words = get_option('iacp_editorial_profile_banned_words', '');

        $prompt = "\n\n--- Perfil Editorial ---\n";
        $prompt .= "Audiencia Objetivo: " . ($target_audience ?: 'General') . "\n";
        $prompt .= "Tono y Voz: " . ($voice_tone ?: 'Neutral') . "\n";
        if ($style_guide) $prompt .= "Guía de Estilo: " . $style_guide . "\n";
        if ($banned_words) $prompt .= "Palabras Prohibidas: " . $banned_words . "\n";
        $prompt .= "----------------------\n";

        return $prompt;
    }
}