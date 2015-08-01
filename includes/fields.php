<?php

if(!WP_DEBUG) {
    define( 'ACF_LITE' , true );
}

function remove_options_menu(){
    remove_menu_page( 'acf-options' );  
}

function get_options_field($key, $default = null){
    return get_field($key, 'option') ?: $default;
}

//Remove annoying filter which appends attachment to some fields
remove_filter( 'acf_the_content', 'prepend_attachment' );

class Custom_Fields{
    static $search = array();

    static public function prepare_where($term, $fields)
    {
        global $wpdb;

        $term = '%'.$term.'%';

        $fields_sql_parts = array();

        foreach ($fields as $field) {
            $fields_sql_parts[] = "{$wpdb->postmeta}.meta_key = '".$field."'";
        }

        if(count($fields_sql_parts) > 0){
            $fields_sql = join(' OR ', $fields_sql_parts);

            return $wpdb->prepare(" AND (({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) OR {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE ( {$fields_sql} ) AND {$wpdb->postmeta}.meta_value LIKE %s ))", array($term, $term, $term) );
        }

        return '';
    }

    static public function posts_search($search, $wp_query)
    {
        $type = $wp_query->get('post_type');

        foreach (static::$search as $key => $info) {
            $fields = $info['fields'];
            $post_type = $info['post_type'];

            if( (is_array($type) && in_array($post_type, $type)) || (!is_array($type) && $type == $post_type) ){

                $where = static::prepare_where($wp_query->get('s'), $fields);
                if(strlen($where) > 0) return $where;
            }
        }

        return $search;
    }

    static public function posts_where($where, $wp_query)
    {

        foreach (static::$search as $key => $info) {
            $fields = $info['fields'];

            if($term = $wp_query->get('root_cf_search_'.$key)){

                $where .= static::prepare_where($term, $fields);

                break;
            }
        }

        return $where;
    }

    static public function add_vars($args, $field, $post_id)
    {
        if(array_key_exists('s', $args)){
            $args['root_cf_search_'.$field['key']] = $args['s'];
            unset($args['s']);
        }

        return $args;
    }

    static public function add_filter($keys, $fields, $post_type = null)
    {
        if(!is_array($keys)) $keys = array($keys);

        foreach ($keys as $key) {
            static::$search[$key] = array(
                'fields' => $fields,
                'post_type' => $post_type,
            );

            add_filter('acf/fields/relationship/query/key='.$key, array('Custom_Fields', 'add_vars'), 1, 3);
        }
        
        add_filter('posts_search', array('Custom_Fields', 'posts_search'), 10, 2 );
        add_filter('posts_where', array('Custom_Fields', 'posts_where'), 10, 2 );
    }
}

// Standard page definition, shows all fields

register_field_group(array (
    'key' => 'acf_default',
    'title' => 'Default',
    'fields' => array (
    ),
    'location' => array (
        array (
            array (
                'param' => 'page_template',
                'operator' => '==',
                'value' => 'default',
                'order_no' => 0,
                'group_no' => 0,
            ),
        ),
    ),
    'position' => 'normal',
    'layout' => 'no_box',
    'hide_on_screen' => array (
    ),
    'menu_order' => 0,
));


// Options

register_field_group(array (
    'key' => 'acf_options_analytics',
    'title' => 'Analytics',
    'fields' => array (
        array (
            'key' => 'field_analytics_id',
            'label' => 'Google Analytics ID',
            'name' => 'analytics_id',
            'type' => 'text',
            'default_value' => '',
            'placeholder' => GOOGLE_ANALYTICS_ID,
            'prepend' => '',
            'append' => '',
            'formatting' => 'none',
            'maxlength' => '',
        ),
    ),
    'location' => array (
        array (
            array (
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'acf-options-options',
                'order_no' => 0,
                'group_no' => 0,
            ),
        ),
    ),
    'position' => 'normal',
    'layout' => 'default',
    'hide_on_screen' => array (
    ),
    'menu_order' => 0,
));

acf_add_options_page( 'Options' );
