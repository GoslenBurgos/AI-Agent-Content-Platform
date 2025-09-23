<?php

class IACP_Content_Planner {

    private $db;
    private $content_repository;

    public function __construct(wpdb $db, IACP_Content_Repository $content_repository) {
        $this->db = $db;
        $this->content_repository = $content_repository;
    }

    /**
     * Generates blog post ideas based on keywords.
     *
     * @param string $keywords The keywords to base the ideas on.
     * @param callable|null $gemini_text_generator Optional. A callable function to generate text. Used for testing.
     * @return array|WP_Error An array of ideas or a WP_Error on failure.
     */
    public function generate_ideas( $keywords, callable $gemini_text_generator = null ) {
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

        $evaluated_ideas = $this->clean_json_response( $response );

        if ( empty( $evaluated_ideas ) ) {
            return new WP_Error( 'analysis_failed', 'No se pudo analizar ninguna de las ideas generadas. Respuesta de la API: ' . esc_html( $response ) );
        }

        return $evaluated_ideas;
    }

    public function execute_content_workflow( $title, $theme, $draft_agent_id, $seo_agent_id = 0, $copy_agent_id = 0, $image_agent_id = 0, $title_agent_id = 0, callable $agent_retriever = null, callable $text_generator = null ) {
        if ( empty( $draft_agent_id ) ) {
            return new WP_Error( 'agent_not_selected', 'Please select an agent to generate the content.' );
        }

        // Sanitize inputs
        $title = sanitize_text_field( $title );
        $theme = wp_kses_post( $theme );

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

        $editorial_profile_prompt = $this->get_editorial_profile_prompt();
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
            $final_content = $this->process_workflow_step(
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

    private function process_workflow_step($current_content, $agent_id, $mode = 'append', $header = '', callable $agent_retriever = null, callable $text_generator = null) {
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

    public function save_content( $title, $theme, $content, $virality_score, $status, $agent_id = 0 ) {
        $insert_id = $this->content_repository->save_content( $title, $theme, $content, $virality_score, $status );
        if ( $insert_id ) {
            $this->content_repository->save_content_version( $insert_id, $content, $agent_id );
        }
        return $insert_id;
    }

    public function get_all_content() {
        return $this->content_repository->get_all_content();
    }

    public function get_content( $content_id ) {
        return $this->content_repository->get_content( $content_id );
    }

    public function update_content_status( $content_id, $status ) {
        $this->content_repository->update_content_status( $content_id, $status );
    }

    public function delete_content( $content_id ) {
        return $this->content_repository->delete_content( $content_id );
    }

    public function save_content_version( $content_id, $content, $agent_id = 0, $note = '', callable $agent_retriever = null ) {
        $this->content_repository->save_content_version( $content_id, $content, $agent_id, $note, $agent_retriever );
    }

    public function get_content_versions( $content_id ) {
        return $this->content_repository->get_content_versions( $content_id );
    }

    public function restore_content_version( $version_id, callable $agent_retriever = null ) {
        return $this->content_repository->restore_content_version( $version_id, $agent_retriever );
    }

    public function track_post_view( $content ) {
        return $this->content_repository->track_post_view( $content );
    }

    public function publish_content_as_post( $content_id, $publish_date_str = null ) {
        return $this->content_repository->publish_content_as_post( $content_id, $publish_date_str );
    }

    private function clean_json_response( $response_string ) {
        // Find the first occurrence of a JSON object or array
        $pattern = '/(\[.*\]|\{.*\})/s';
        if (preg_match($pattern, $response_string, $matches)) {
            // Return the first matched JSON block
            return json_decode($matches[0], true);
        }
        // If no JSON is found, return null
        return null;
    }

    private function get_editorial_profile_prompt() {
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