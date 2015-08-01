<?php
/**
 * Root relative URLs
 *
 * WordPress likes to use absolute URLs on everything - let's clean that up.
 * Inspired by http://www.456bereastreet.com/archive/201010/how_to_make_wordpress_urls_root_relative/
 *
 * You can enable/disable this feature in config.php:
 * current_theme_supports('root-relative-urls');
 *
 * @author Scott Walkinshaw <scott.walkinshaw@gmail.com>
 */
function root_root_relative_url($input) {
  preg_match('|https?://([^/]+)(/.*)|i', $input, $matches);

  $http_host = (array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : $_SERVER['REMOTE_ADDR']);

  if (!isset($matches[1]) || !isset($matches[2])) {
    return $input; 
  } elseif (($matches[1] === $_SERVER['SERVER_NAME']) || $matches[1] === $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']) {
    return wp_make_link_relative($input);
  } elseif (($matches[1] === $http_host) || $matches[1] === $http_host . ':' . $_SERVER['SERVER_PORT']) {
    return wp_make_link_relative($input);
  } else {
    return $input;
  }
}

function root_enable_root_relative_urls() {
  return !(is_admin() || in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) && current_theme_supports('root-relative-urls');
}

if (root_enable_root_relative_urls()) {
  $root_rel_filters = array(
    'bloginfo_url',
    'the_permalink',
    'wp_list_pages',
    'wp_list_categories',
    'root_wp_nav_menu_item',
    'the_content_more_link',
    'the_tags',
    'home_url',
    'author_link',
    'get_pagenum_link',
    'get_comment_link',
    'month_link',
    'day_link',
    'year_link',
    'tag_link',
    'the_author_posts_link',
    'script_loader_src',
    'style_loader_src'
  );

  add_filters($root_rel_filters, 'root_root_relative_url');
}
