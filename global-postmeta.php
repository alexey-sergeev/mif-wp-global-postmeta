<?php
/*
Plugin Name: MIF WP Global Postmeta
Plugin URI: https://github.com/alexey-sergeev/mif-wp-global-postmeta
Description: Плагин глобальных метаданных для Multisite WordPress
Author: Alexey N. Sergeev
Version: 1.0.0
Author URI: https://github.com/alexey-sergeev
Multisite: true;
*/

defined( 'ABSPATH' ) || exit;


function add_global_postmeta( $site_id, $post_id, $meta_key, $meta_value )
{
    global $wpdb;

    $site_id = absint( $site_id );
    $post_id = absint( $post_id );

	$meta_key = wp_unslash( $meta_key );
	$meta_value = maybe_serialize( wp_unslash( $meta_value ) );

    $table = $wpdb->base_prefix . 'global_postmeta';

	$result = $wpdb->insert( $table, array(
		'site_id' => $site_id,
		'post_id' => $post_id,
		'meta_key' => $meta_key,
		'meta_value' => $meta_value
	) );

    wp_cache_delete( $site_id . '_' . $post_id, 'global_postmeta' );

    return $result;
}



function update_global_postmeta( $site_id, $post_id, $meta_key, $meta_value, $prev_value = '' )
{
    global $wpdb;

    $site_id = absint( $site_id );
    $post_id = absint( $post_id );

	$meta_key = wp_unslash( $meta_key );
	$meta_value = maybe_serialize( wp_unslash( $meta_value ) );
	$prev_value = maybe_serialize( wp_unslash( $prev_value ) );

    $old_data = get_global_postmeta( $site_id, $post_id, $meta_key );
    if ( ( count( $old_data ) == 1 ) && ( $old_data[0] === $meta_value ) ) return false;
    if ( count( $old_data ) == 0 ) return add_global_postmeta( $site_id, $post_id, $meta_key, $meta_value );
    
    $table = $wpdb->base_prefix . 'global_postmeta';
    $data = array( 'meta_value' => $meta_value );
    $where = array( 'site_id' => $site_id, 'post_id' => $post_id, 'meta_key' => $meta_key );

    if ( $prev_value ) $where['meta_value'] = $prev_value;

    $result = $wpdb->update( $table, $data, $where );

    if ( $result ) wp_cache_delete( $site_id . '_' . $post_id, 'global_postmeta' );

	return (boolean) $result;
}



function get_global_postmeta( $site_id, $post_id, $meta_key = false, $single = false )
{
    global $wpdb;

    $site_id = absint( $site_id );
    $post_id = absint( $post_id );

    $data = wp_cache_get( $site_id . '_' . $post_id, 'global_postmeta' );

    if ( ! $data ) {
        
        $table = $wpdb->base_prefix . 'global_postmeta';
        $result = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $table WHERE site_id = %d AND post_id = %d ORDER BY meta_id DESC", $site_id, $post_id ), ARRAY_A );

        $data = array();
        foreach ( $result as $r ) $data[$r['meta_key']][] = $r['meta_value'];

        wp_cache_set( $site_id . '_' . $post_id, $data, 'global_postmeta' );

    }

    if ( ! $meta_key ) return ( $data );

    if ( isset( $data[$meta_key] ) ) {

		if ( $single ) {

            return maybe_unserialize( $data[$meta_key][0] );

        } else {

            return array_map( 'maybe_unserialize', $data[$meta_key] );

        }

    }
       
	if ( $single ) {

		return '';

    } else {

		return array();

    }

}



function delete_global_postmeta( $site_id, $post_id, $meta_key, $meta_value = '' )
{
    global $wpdb;

    $site_id = absint( $site_id );
    $post_id = absint( $post_id );

	$meta_key = wp_unslash( $meta_key );
	$meta_value = maybe_serialize( wp_unslash( $meta_value ) );

    $table = $wpdb->base_prefix . 'global_postmeta';
    $where = array( 'site_id' => $site_id, 'post_id' => $post_id, 'meta_key' => $meta_key );

    if ( $meta_value ) $where['meta_value'] = $meta_value;

    $result = $wpdb->delete( $table, $where );

    if ( $result ) wp_cache_delete( $site_id . '_' . $post_id, 'global_postmeta' );

	return (boolean) $result;
}


// 
// Активация плагина. Надо создать таблицу глобальных мета-записей
// 

register_activation_hook( __FILE__, 'gp_plugin_activate' );

function gp_plugin_activate() 
{
    // add_option( 'Activated_Plugin', 'MIF_WP_Global_Postmeta' );

    global $wpdb;

    $max_index_length = 191;
    $table = $wpdb->base_prefix . 'global_postmeta';

    $create_table = "CREATE TABLE IF NOT EXISTS $table (
        meta_id bigint(20) unsigned NOT NULL auto_increment,
        site_id bigint(20) unsigned NOT NULL default '0',
        post_id bigint(20) unsigned NOT NULL default '0',
        meta_key varchar(255) default NULL,
        meta_value longtext,
        PRIMARY KEY  (meta_id),
        KEY site_id (site_id),
        KEY post_id (post_id),
        KEY meta_key (meta_key($max_index_length))
      ) COLLATE=utf8mb4_unicode_ci;\n";
      
    $wpdb->get_results( $create_table );

}
 

?>