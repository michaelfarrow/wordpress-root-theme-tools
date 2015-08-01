<?php
/**
 * Enqueue scripts and stylesheets
 *
 * Enqueue stylesheets in the following order:
 * 1. /theme/assets/css/main.min.css
 *
 * Enqueue scripts in the following order:
 * 1. jquery-1.11.0.min.js via Google CDN
 * 2. /theme/assets/js/vendor/modernizr-2.7.0.min.js
 * 3. /theme/assets/js/main.min.js (in footer)
 */
global $git_commit;

$git_commit = root_current_git_commit();

function root_scripts() {
  global $git_commit;

  wp_enqueue_style('root_main', get_template_directory_uri() . '/assets/css/main.css', false, $git_commit);

  // jQuery is loaded using the same method from HTML5 Boilerplate:
  // Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
  // It's kept in the header instead of footer to avoid conflicts with plugins.
  if (!is_admin()) {
    wp_deregister_script('jquery');
    // wp_register_script('jquery', get_template_directory_uri() . '/libs/jquery/dist/jquery.js', array(), null, false);
  }

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  wp_register_script('modernizr', get_template_directory_uri() . '/assets/js/vendor/modernizr.js', array(), $git_commit, false);
  
  wp_enqueue_script('modernizr');
  // wp_enqueue_script('jquery');
  wp_enqueue_script('root_scripts');
}
add_action('wp_enqueue_scripts', 'root_scripts', 100);

function root_foot_require() {
  global $git_commit;
  echo '<script data-main="'
  .wp_make_link_relative(get_template_directory_uri())
  .'/assets/js/main.js?v='.$git_commit.'" src="'
  .wp_make_link_relative(get_template_directory_uri())
  .'/assets/js/vendor/require.js?v='.$git_commit.'"></script>';
}

add_action('wp_footer', 'root_foot_require', 100);

function root_google_analytics() { ?>
<script>
  (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
  function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
  e=o.createElement(i);r=o.getElementsByTagName(i)[0];
  e.src='//www.google-analytics.com/analytics.js';
  r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
  ga('create','<?php print get_options_field('analytics_id', GOOGLE_ANALYTICS_ID); ?>');ga('send','pageview');
</script>

<?php }
if ((GOOGLE_ANALYTICS_ID || get_options_field('analytics_id')) && !current_user_can('manage_options')) {
  add_action('wp_footer', 'root_google_analytics', 20);
}
