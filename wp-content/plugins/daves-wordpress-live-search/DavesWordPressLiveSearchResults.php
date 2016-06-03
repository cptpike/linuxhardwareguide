<?php

include "DWLSTransients.php";

/**
 * Value object class
 */
class DavesWordPressLiveSearchResults {
  // Search sources
  const SEARCH_CONTENT = 0;
  const SEARCH_WPCOMMERCE = 1;

  public $searchTerms;
  public $results;
  public $displayPostMeta;

  /**
   * @param int $source search source constant
   * @param string $searchTerms
   * @param boolean $displayPostMeta Show author & date for each post. Defaults to TRUE to keep original bahavior from before I added this flag
   */
  function DavesWordPressLiveSearchResults($source, $searchTerms, $displayPostMeta = true, $maxResults = -1) {

    $this->results = array();
    $this->populate($source, $searchTerms, $displayPostMeta, $maxResults);
    $this->displayPostMeta = $displayPostMeta;
    
  }

  /**
   *
   * @global type $wp_locale
   * @global type $wp_query
   * @param int $source SEARCH_ constant
   * @param type $wpQueryResults
   * @param type $displayPostMeta
   * @param string $maxResults 
   */
  private function populate($source, $wpQueryResults, $displayPostMeta, $maxResults) {
  
    global $wp_locale;
    global $wp_query;

    $dateFormat = get_option('date_format');

    // Some other plugin threw a fit once if I didn't instantiate
    // WP_Query once to initialize everything and then call it
    // for real. Might have been "Search Everything". I think there's
    // a comment about it in an old version of DWLS.
    $wp_query = $wpQueryResults = new WP_Query();
    $wp_query = $wpQueryResults = new WP_Query();

    if (function_exists('relevanssi_do_query')) {
      // Relevanssi isn't treating 0 as "unlimited" results
      // like WordPress's native search does. So we'll replace
      // $maxResults with a really big number, the biggest one
      // PHP knows how to represent, if $maxResults == -1
      // (unlimited)
      if (-1 == $maxResults) {
        $maxResults = PHP_INT_MAX;
      }
    }

    $wpQueryParams = $_GET;
    $wpQueryParams['showposts'] = $maxResults;
    if(self::SEARCH_WPCOMMERCE === $source) {
        $wpQueryParams['post_type'] = 'wpsc-product';
    }
    else {
        # LHG: was "any", but now limited to HW posts
        $wpQueryParams['post_type'] = 'post';
    }
    $wpQueryParams['post_status'] = 'publish';
    $queryString = http_build_query($wpQueryParams);

    $wpQueryResults->query($queryString);

    $this->searchTerms = $wpQueryResults->query_vars['s'];

    $wpQueryResults = apply_filters('dwls_alter_results', $wpQueryResults, $maxResults);

    foreach ($wpQueryResults->posts as $result) {
      // Add author names & permalinks
      if ($displayPostMeta) {
        $result->post_author_nicename = $this->authorName($result->post_author);
      }

      $result->permalink = lhg_translate_search_url( get_permalink($result->ID) );

      if (function_exists('get_post_thumbnail_id')) {
        // Support for WP 2.9 post thumbnails
        $postImageID = get_post_thumbnail_id($result->ID);
        $postImageData = wp_get_attachment_image_src($postImageID, apply_filters('post_image_size', 'thumbnail'));
	    $hasThumbnailSet = ($postImageData !== false);
      }
      else {
      	// No support for post thumbnails
        $hasThumbnailSet = false;
      }

      if($hasThumbnailSet) {
        $result->attachment_thumbnail = $postImageData[0];
      } else {
        // If no post thumbnail, grab the first image from the post
        $applyContentFilter = get_option('daves-wordpress-live-search_apply_content_filter', false);
        $content = $result->post_content;
        if($applyContentFilter) {
          $content = apply_filters('the_content', $content);
        }
        $content = str_replace(']]>', ']]&gt;', $content);
        $result->attachment_thumbnail = $this->firstImg($content);
      }

      $result->attachment_thumbnail = apply_filters('dwls_attachment_thumbnail', $result->attachment_thumbnail);

      $result->post_excerpt = $this->excerpt($result);

      $result->post_date = date_i18n($dateFormat, strtotime($result->post_date));
      $result->post_date = apply_filters('dwls_post_date', $result->post_date);

      // We don't want to send all this content to the browser
      unset($result->post_content);

      // xLocalization
      $result->post_title = apply_filters("localization", $result->post_title);

      $result->post_title = apply_filters('dwls_post_title', $result->post_title);

      global $lang;
      if ($lang != "de") $pt = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($result->post_title);
      if ($lang == "de") $pt = ($result->post_title);

      $result->post_title = $pt."<br>".the_ratings_results($result->ID,0,0,0,10); #."<br>".$pt;

      $result->show_more = true;

      $this->results[] = $result;
    }
  }

  private function excerpt($result) {

    static $excerptLength = null;
    // Only grab this value once
    if (null == $excerptLength) {
      $excerptLength = intval(get_option('daves-wordpress-live-search_excerpt_length'));
    }
    // Default value
    if (0 == $excerptLength) {
      $excerptLength = 100;
    }

    if (empty($result->post_excerpt)) {
      $content = apply_filters("localization", $result->post_content);
      $excerpt = explode(" ", strrev(substr(strip_tags($content), 0, $excerptLength)), 2);
      $excerpt = strrev($excerpt[1]);
      $excerpt .= " [...]";
    } else {
      $excerpt = apply_filters("localization", $result->post_excerpt);
    }

    $excerpt = apply_filters('the_excerpt', $excerpt);
    $excerpt = apply_filters('dwls_the_excerpt', $excerpt);

    return $excerpt;
  }

  /**
   * @return string
   */
  private function authorName($authorID) {
    static $authorCache = array();

    if (array_key_exists($authorID, $authorCache)) {
      $authorName = $authorCache[$authorID];
    } else {
      $authorData = get_userdata($authorID);
      $authorName = $authorData->display_name;
      $authorCache[$authorID] = $authorName;
    }

    $authorName = apply_filters('dwls_author_name', $authorName);

    return $authorName;
  }

  public function firstImg($post_content) {
    $matches = array();
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches);
    if(isset($matches[1][0])) {
      $first_img = $matches[1][0];
    }

    if (empty($first_img)) {
      return '/wp-uploads/2013/03/noimage130.jpg';
    }
    return $first_img;
  }

  public function ajaxSearch() {
    $maxResults = intval(get_option('daves-wordpress-live-search_max_results'));
    if ($maxResults === 0)
      $maxResults = -1;

    $cacheLifetime = intval(get_option('daves-wordpress-live-search_cache_lifetime'));
    if (!is_user_logged_in() && 0 < $cacheLifetime) {
      $doCache = TRUE;
    } else {
      $doCache = FALSE;
    }

    if ($doCache) {
      $cachedResults = DWLSTransients::get($_REQUEST['s']);
    }

    if ((!$doCache) || (FALSE === $cachedResults)) {

      // Initialize the $wp global object
      // See class WP in classes.php
      // The Relevanssi plugin is using this instead of
      // the global $wp_query object
      $wp = & new WP();
      $wp->init();  // Sets up current user.
      $wp->parse_request();

      $displayPostMeta = (bool) get_option('daves-wordpress-live-search_display_post_meta');
      if (array_key_exists('search_source', $_REQUEST)) {
        $searchSource = $_GET['search_source'];
      } else {
        $searchSource = intval(get_option('daves-wordpress-live-search_source'));
      }

      $results = new DavesWordPressLiveSearchResults($searchSource, $_GET['s'], $displayPostMeta, $maxResults);

      if ($doCache) {
        DWLSTransients::set($_REQUEST['s'], $results, $cacheLifetime);
      }
    } else {
      // Found it in the cache. Return the results.
      $results = $cachedResults;
    }

    $json = json_encode($results);

    // If we don't output the text we want outputted here and
    // then die(), the wp_ajax code will die(0) or die(-1) after
    // this function returns and that value will get echoed out
    // to the browser, resulting in a JSON parsing error.
    die($json);
  }

}

// Set up the AJAX hooks
add_action("wp_ajax_dwls_search", array("DavesWordPressLiveSearchResults", "ajaxSearch"));
add_action("wp_ajax_nopriv_dwls_search", array("DavesWordPressLiveSearchResults", "ajaxSearch"));
