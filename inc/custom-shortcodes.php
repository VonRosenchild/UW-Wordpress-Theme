<?php

/**
 * allow shortcodes in text widgets
 */
add_filter( 'widget_text', 'do_shortcode' );

/**
 * The Caption shortcode filter - original function in wp-includes/media.php
 */

add_filter('img_caption_shortcode', 'add_photo_credit_to_img_caption_shortcode',10,3);

if ( !function_exists('add_photo_credit_to_img_caption_shortcode')) :

  function add_photo_credit_to_img_caption_shortcode($val, $attr, $content = null)
  {
    extract(shortcode_atts(array(
      'id'	=> '',
      'align'	=> '',
      'width'	=> '',
      'caption' => ''
    ), $attr));
    
    if ( 1 > (int) $width || empty($caption) )
      return $content;

    if ( $id ) $id = 'id="' . esc_attr($id) . '" ';

    preg_match('/([\d]+)/', $id, $match);

    if ( $match[0] ) $credit = get_post_meta($match[0], '_media_credit', true);

    if ( $credit ) $credit = '<p class="wp-media-credit">'. $credit . '</p>';

    return '<div ' . $id . 'class="wp-caption ' . esc_attr($align) . '" style="width: ' . (10 + (int) $width) . 'px">'
    . do_shortcode( $content ) . $credit . '<p class="wp-caption-text">' . $caption . '</p></div>';
  }

endif;

/**
 * The RSS Shortcode
 */

if ( ! function_exists('uw_feed_shortcode') ):
  function uw_feed_shortcode( $atts ) 
  {

    extract( shortcode_atts( array(
        'url'    => null,
        'number' => 5,
        'title'  => null,
        'heading' => 'h3',
        'span'   => 4
      ), $atts ) );

    if ( $url == null || is_feed() )
      return '';


    $content = '';

    $feed = fetch_feed($url);


    if (!is_wp_error( $feed ) ) 
    { 
      $url = $feed->get_permalink();
      $feed_items = $feed->get_items(0, $number); 
      $feed_items = $feed->get_items(0, $number); 
      $pullleft = $span === 4 ? 'pull-left' : '';

      $title = ($title == null ) ? $feed->get_title() : $title;

      $content = "<div class=\"row $pullleft\">";
      $content .= "<div class=\"span$span\">";
      $content .= "<div class=\"feed-in-body\"><a href=\"$url\" title=\"$title\"><$heading>$title</$heading></a></div>";
      $content .= "<ul>";

      foreach ($feed_items as $index=>$item) 
      {
          $title = $item->get_title();
          $link  = $item->get_link();
          $attr  = esc_attr(strip_tags($title));
          $content .= "<li><a href=\"$link\" title=\"$attr\">$title</a></li>";
      }

      $span--;
      $content .= "<a href=\"$url\" title=\"$title\" class=\"offset$span\">More</a>";
      $content .= '</ul>';
      $content .= "</div></div>";
    }
    return $content;
  }
endif;
add_shortcode( 'rss', 'uw_feed_shortcode' );

/**
 * Archive Shortcode
 */

if ( ! function_exists('uw_archive_shortcode') ):
  function uw_archive_shortcode( $atts ) 
  {
    $params = shortcode_atts( array(
        'type'      => 'postbypost',
        'format'    => 'html',
        'limit'     => '',
        'showcount' => false,
        'before'    => '',
        'after'     => '',
        'order'     => 'desc'
      ), $atts );
    $params['echo'] = false;
    return '<div class="archive-shortcode">'. wp_get_archives($params) . '</div>';
  }
endif;
add_shortcode( 'archives', 'uw_archive_shortcode' );

/**
 * Blogroll Shortcode
 */
if ( ! function_exists('uw_blogroll_shortcode') ):
  function uw_blogroll_shortcode( $atts ) 
  {

    if ( ! is_array($atts) )
      $atts = array();

    if ( get_post_type() == 'post' )
      return '';

    $params = array_merge( array(
        'excerpt'      => 'true',
        'trim'         => 'false',
        'image'        => 'hide',
        'number'       =>  5
      ), $atts );

    if ( !array_key_exists('numberposts', $params ) )
      $params['numberposts'] = $params['number'];

    $posts = get_posts($params);

    foreach ($posts as $post) {

      $link = get_permalink($post->ID);

      if ( in_array($params['excerpt'], array('show', 'true') ) ) 
      {
        $excerpt = strlen($post->post_excerpt) > 0 ? $post->post_excerpt : apply_filters('widget_text', $post->post_content);
        if ( in_array($params['trim'], array('show', 'true') ) )
          $excerpt = wp_trim_words($excerpt);
        $excerpt = wpautop($excerpt); //using apply_filters('the_content', $excerpt) causes an infinite loop
        if ( in_array($params['image'], array('show', 'true') ) ) {
          $image = get_the_post_thumbnail($post->ID, 'thumbnail', array('style'=>'float:left;padding-right:10px;'));
          $class = 'class="pull-left"';
        }
      }
      $html .= "<li $class><a href=\"$link\">{$post->post_title}</a>$image{$excerpt}</li>";
    }

    return "<ul class=\"shortcode-blogroll\">$html</ul>";

  }
endif;
add_shortcode( 'blogroll', 'uw_blogroll_shortcode' );

/**
 * Youtube playlist shortcode
 */
if ( ! function_exists( 'uw_youtube_playlist_shortcode' ) ) :
  function uw_youtube_playlist_shortcode( $atts ) 
  {
    wp_enqueue_script('widget-youtube-playlist');

    $content = '
      <div id="youtube-playlist-player" class="row">

        <div class="youtube-large">
            <div id="youtubeapi" data-pid="'.$atts['id'].'"></div>
        </div>

        <div class="youtube-small">
          <div id="youtube-player-controls">
              <div class="scrollbar">
              <div class="track">
              <div class="thumb">
              <div class="end">
              </div></div></div></div>
              <div class="viewport">
              <div id="vidContent" class="overview">
              </div></div>
          </div>
        </div>

      </div>
    ';
    
    return $content; 
  }
endif;
add_shortcode('youtube', 'uw_youtube_playlist_shortcode');

/**
 * Google calendar shortcode
 */
// turns an iframe into a shortcode
add_filter( 'pre_kses', 'uw_google_calendar_embed_to_shortcode' );
if ( ! function_exists('uw_google_calendar_embed_to_shortcode') ) :
  function uw_google_calendar_embed_to_shortcode( $content ) 
  {
    if ( false === strpos( $content, '<iframe ' ) && false === strpos( $content, 'google.com/calendar' ) )
      return $content;
    
	  $content = preg_replace_callback( '#&lt;iframe\s[^&]*?(?:&(?!gt;)[^&]*?)*?src="https?://.*?\.google\.(.*?)/(.*?)\?(.+?)"[^&]*?(?:&(?!gt;)[^&]*?)*?&gt;\s*&lt;/iframe&gt;\s*(?:&lt;br\s*/?&gt;)?\s*#i', 'uw_google_calendar_embed_to_shortcode_callback', $content );

	  $content = preg_replace_callback( '!\<iframe\s[^>]*?src="https?://.*?\.google\.(.*?)/(.*?)\?(.+?)"[^>]*?\>\s*\</iframe\>\s*!i', 'uw_google_calendar_embed_to_shortcode_callback', $content );

    return $content;
  }
endif;

if ( ! function_exists('uw_google_calendar_embed_to_shortcode_callback') ) :
function uw_google_calendar_embed_to_shortcode_callback ( $match ) {
	if ( preg_match( '/\bwidth=[\'"](\d+)/', $match[0], $width ) ) {
		$width = min( array( (int) $width[1] , 630 ) );
	} else {
		$width = 630;
	}

	if ( preg_match( '/\bheight=[\'"](\d+)/', $match[0], $height ) ) {
		$height = (int) $height[1];
	} else {
		$height = 500;
	}
  $url = $match[3];

  return "[googleapps domain=\"www\" dir=\"calendar/embed\" query=\"$url\" width=\"$width\" height=\"$height\"]";
}
endif;

if ( ! function_exists('uw_google_calendar_shortcode') ) :
function uw_google_calendar_shortcode( $atts ) {

    $params = shortcode_atts( array(
      'query'   => '',
      'dir'     => '',
      'domain'  => 'www',
      'width'   => 620,
      'height'  => 500,
    ), $atts );
    extract($params);

    if ( $dir == 'calendar/embed' ) 
	    return '<div class="googleapps-'. $app .'"><iframe width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/calendar/embed?' . $query . '"></iframe></div>';

    return '';
}
endif;

add_shortcode( 'googleapps', 'uw_google_calendar_shortcode' );


/**
 * UWTV Shortcode and iFrame to shortcode conversion
 */
add_filter( 'pre_kses', 'uwtv_embed_to_shortcode' );
if ( ! function_exists('uwtv_embed_to_shortcode') ) :
  function uwtv_embed_to_shortcode( $content ) 
  {
    if ( false === strpos( $content, '<iframe ' ) && false === strpos( $content, 'uwtv.org/video/iframe' ) )
      return $content;
    
	  $content = preg_replace_callback( '#&lt;iframe\s[^&]*?(?:&(?!gt;)[^&]*?)*?src="http?://.*?\.uwtv\.(.*?)/(.*?)\?(.+?)"[^&]*?(?:&(?!gt;)[^&]*?)*?&gt;\s*&lt;/iframe&gt;\s*(?:&lt;br\s*/?&gt;)?\s*#i', 'uwtv_embed_to_shortcode_callback', $content );

	  $content = preg_replace_callback( '!\<iframe\s[^>]*?src="http?://.*?\.uwtv\.(.*?)/(.*?)\?(.+?)"[^>]*?\>\s*\</iframe\>\s*!i', 'uwtv_embed_to_shortcode_callback', $content );

    return $content;
  }
endif;

if ( ! function_exists('uwtv_embed_to_shortcode_callback') ) :
function uwtv_embed_to_shortcode_callback( $match ) {
	if ( preg_match( '/\bwidth=[\'"](\d+)/', $match[0], $width ) ) {
		$width = min( array( (int) $width[1] , 630 ) );
	} else {
		$width = 630;
	}

	if ( preg_match( '/\bheight=[\'"](\d+)/', $match[0], $height ) ) {
		$height = (int) $height[1];
	} else {
		$height = 500;
	}

  preg_match('/(\d+)/', $match[3], $mediaid);

  return "[uwtv video=\"{$mediaid[0]}\" width=\"$width\" height=\"$height\"]";
}
endif;

if ( ! function_exists('uwtv_shortcode') ) :
function uwtv_shortcode( $atts ) {

    $params = shortcode_atts( array(
      'video'  => '',
      'width'   => 620,
      'height'  => 500,
    ), $atts );
    extract($params);

    return '<div class="uwtv-embed"><iframe width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.uwtv.org/video/iframe-embed.aspx?mediaid=' . $video . '"></iframe></div>';
}
endif;

add_shortcode( 'uwtv', 'uwtv_shortcode' );


/**
 * TVW Shortcode and iFrame to shortcode conversion
 */
add_filter( 'pre_kses', 'tvw_embed_to_shortcode' );
if ( ! function_exists('tvw_embed_to_shortcode') ) :
  function tvw_embed_to_shortcode( $content ) 
  {
    if ( false === strpos( $content, '<iframe ' ) && false === strpos( $content, 'tvw.org/scripts/iframe' ) )
      return $content;

	  $content = preg_replace_callback( '#&lt;iframe\s[^&]*?(?:&(?!gt;)[^&]*?)*?src="http?://.*?\.tvw\.(.*?)/(.*?)\?(.+?)"[^&]*?(?:&(?!gt;)[^&]*?)*?&gt;\s*&lt;/iframe&gt;\s*(?:&lt;br\s*/?&gt;)?\s*#i', 'tvw_embed_to_shortcode_callback', $content );

	  $content = preg_replace_callback( '!\<iframe\s[^>]*?src="http?://.*?\.tvw\.(.*?)/(.*?)\?(.+?)"[^>]*?\>\s*\</iframe\>\s*!i', 'tvw_embed_to_shortcode_callback', $content );

    return $content;
  }
endif;

if ( ! function_exists('tvw_embed_to_shortcode_callback') ) :
function tvw_embed_to_shortcode_callback( $match ) {

  //parse_str(htmlspecialchars_decode($match[3]), $query);

	$width = ( preg_match( '/\bwidth=[\'"](\d+)/', $match[0], $width ) ) ?
    min( array( (int) $width[1] , 630 ) ) : 
    $width = 630;

	$height = ( preg_match( '/\bheight=[\'"](\d+)/', $match[0], $height ) ) ? 
		$height = (int) $height[1] : $height = 500;

  //  $video = $query['eventID'];
  //  $start = $query['start'];
  //  $stop  = $query['stop'];

  return "[tvw query=\"{$match[3]}\" width=\"$width\" height=\"$height\"]";
}
endif;

if ( ! function_exists('tvw_shortcode') ) :
function tvw_shortcode( $atts ) {

    $params = shortcode_atts( array(
      'query'  => '',
      'width'   => 620,
      'height'  => 500,
    ), $atts );
    extract($params);

    return '<div class="tvw-embed"><iframe width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.tvw.org/scripts/iframe_video.php?'.$query.'"></iframe></div>';
}
endif;
add_shortcode( 'tvw', 'tvw_shortcode' );

/**
 * This shortcode allows iFrames for editors.
 * Only certain domains are allowed, listed in /inc/helper-functions.php
 */
if ( ! function_exists('uw_iframe_shortcode') ) :
function uw_iframe_shortcode($atts) {

    $params = shortcode_atts(array(
      'src' => '',
      'height' => get_option('embed_size_h'),
      'width' => get_option('embed_size_w')
    ), $atts);

    $params['src'] = esc_url($params['src'], array('http','https'));
    if ( $params['src'] == '' )
      return '';

    $parsed = parse_url($params['src']);
    if ( array_key_exists('host', $parsed) && !in_array($parsed['host'], get_iframe_domains() ) )
      return '';

    return "<iframe src=\"{$params['src']}\" width=\"{$params['width']}\" height=\"{$params['height']}\" frameborder=\"0\"></iframe>";

}
endif;
add_shortcode( 'iframe', 'uw_iframe_shortcode' );
