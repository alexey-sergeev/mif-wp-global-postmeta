# mif-wp-global-postmeta
Плагин глобальных метаданных для Multisite WordPress

Реализует функционал метаданных для записей, но в масштабах всего мультисайта WordPress. 
Все данные хранятся в одной таблице сайта (она создается при активации плагина).
В качестве ключа мета-записи используется не только id записи, но и id сайта.

Плагин позволяет обмениваться данными между разными сайтами одной платформы Multisite WordPress.

function add_global_postmeta( $site_id, $post_id, $meta_key, $meta_value )

function update_global_postmeta( $site_id, $post_id, $meta_key, $meta_value, $prev_value = '' )

function get_global_postmeta( $site_id, $post_id, $meta_key = false, $single = false )

function delete_global_postmeta( $site_id, $post_id, $meta_key, $meta_value = '' )


Сергеев А. Н., апрель 2020
