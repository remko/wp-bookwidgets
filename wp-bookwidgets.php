<?php

/**
 * Plugin Name: WP BookWidgets
 * Plugin URI: https://github.com/remko/wp-bookwidgets
 * Description: Integrate BookWidgets widgets in your WordPress site
 * Version: 0.9
 * Author: Remko Tronçon
 * Author URI: https://el-tramo.be
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wp-bookwidgets
*/

namespace wp_bookwidgets;

////////////////////////////////////////////////////////////////////////////////
// Options for launching a widget using LTI
// Can be changed using the `bw_widget_launch_options` filter.
////////////////////////////////////////////////////////////////////////////////

class WidgetLaunchOptions {
  /**
   * A WP_User-like struct.
   * Should have the fields `ID`, `user_email`, `user_firstname`, 
   * and `user_lastname`.
   */
  public $student;

  /**
   * Email address that will receive student results.
   * If the widget allows this, the student can still override this.
   */
  public $teacher_email = null;

  /**
   * The class ID for the widget.
   * If the widget allows this, the student can still override this.
   */
  public $student_class_id = null;

  public $context_id;
  public $submit_url = null;
}

////////////////////////////////////////////////////////////////////////////////

$baseURI = "https://www.bookwidgets.com";

function use_lti() {
  return is_user_logged_in() && get_option("bw_lti_enabled");
}

function bw_url($path) {
  global $baseURI;
  return "{$baseURI}{$path}";
}

function get_play_path($atts) {
  if ($atts["url"] != null) {
    $url = parse_url($atts["url"]);
    $path = $url["path"];
    if ($url["query"] != null) {
      $path = "{$path}?{$url['query']}";
    }
    return $path;
  }
  if (use_lti()) {
    return "/lti/play/{$atts['code']}";
  }
  return "/play/{$atts['code']}";
}

// See https://www.rfc-editor.org/rfc/rfc5849.html#section-3.4
function get_oauth_signature($url, $form, $secret) {
  $qs = [];
  $purl = parse_url($url);
  if (array_key_exists("query", $purl) && $purl["query"] != null) {
    parse_str($purl["query"], $qs);
  }
  $params = array_merge($form, $qs);
  ksort($params);
  $baseStringURI = strtok($url, "?");
  $baseString = implode("&", array_map('rawurlencode', [
    "POST",
    $baseStringURI,
    implode("&", array_map(function ($key, $value) {
      return rawurlencode($key) . '=' . rawurlencode($value);
    }, array_keys($params), $params))
  ]));
  $key = $secret . "&";
  return base64_encode(hash_hmac('sha1', $baseString, $key, true));
}

function get_lti_launch_form($url, $name, $target = null, $time = 'time', $uniqid = 'uniqid') {
  $widgetLaunchOptions = new WidgetLaunchOptions;
  $widgetLaunchOptions->student = wp_get_current_user();
  $widgetLaunchOptions->context_id = get_the_ID();
  $widgetLaunchOptions = apply_filters(
    'bw_widget_launch_options', $widgetLaunchOptions);

  $current_user = $widgetLaunchOptions->student;
  $params = array_filter([
    "lti_message_type" => "basic-lti-launch-request",
    "lti_version" => "LTI-1p0",

    "user_id" => $current_user->ID,
    "roles" => "Student",
    "lis_person_contact_email_primary" => $current_user->user_email,
    "lis_person_name_given" => $current_user->user_firstname,
    "lis_person_name_family" => $current_user->user_lastname,

    "oauth_consumer_key" => get_option("bw_lti_consumer_key"),
    "oauth_signature_method" => "HMAC-SHA1",
    "oauth_timestamp" => call_user_func($time),
    "oauth_nonce" => call_user_func($uniqid, "", true),
    "oauth_version" => "1.0",

    "tool_consumer_info_product_family_code" => "wp-bookwidgets",
    "tool_consumer_instance_name" => get_the_title(),

    "context_id" => $widgetLaunchOptions->context_id,
    "custom_teacher_email" => $widgetLaunchOptions->teacher_email,
    "custom_student_class_id" => $widgetLaunchOptions->student_class_id,
    "custom_submit_url" => $widgetLaunchOptions->submit_url,
  ], function ($value) { return $value !== null; });

  $signature = esc_attr(get_oauth_signature($url, $params, get_option("bw_lti_consumer_secret")));

  $form = "<form action=\"{$url}\" method=\"POST\" encType=\"application/x-www-form-urlencoded\"";
  if ($target) {
    $form .= " target=\"{$target}\"";
  }
  $form .= " name=\"{$name}\">";
  foreach ($params as $key => $value) {
    $name = esc_attr($key);
    $value = esc_attr($value);
    $form .= "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\"/>";
  }
  $form .= "<input type=\"hidden\" name=\"oauth_signature\" value=\"{$signature}\"/>";
  $form .= "<input type=\"submit\" value=\"Play Widget\" style=\"display: none\"/>";
  $form .= "</form>";
  return $form;
}

function get_autolaunch_script($name) {
  return "<script language=\"javascript\">document.{$name}.submit();</script>";
}

function get_new_tab_icon() {
  return '<svg class="bw-icon" width="1792" height="1792" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1408 928v320q0 119-84.5 203.5t-203.5 84.5h-832q-119 0-203.5-84.5t-84.5-203.5v-832q0-119 84.5-203.5t203.5-84.5h704q14 0 23 9t9 23v64q0 14-9 23t-23 9h-704q-66 0-113 47t-47 113v832q0 66 47 113t113 47h832q66 0 113-47t47-113v-320q0-14 9-23t23-9h64q14 0 23 9t9 23zm384-864v512q0 26-19 45t-45 19-45-19l-176-176-652 652q-10 10-23 10t-23-10l-114-114q-10-10-10-23t10-23l652-652-176-176q-19-19-19-45t19-45 45-19h512q26 0 45 19t19 45z"/></svg>';
}

add_shortcode('bw_link', function($atts, $content = null) {
  $a = shortcode_atts([
    'code' => '',
    'url' => null,
  ], $atts);
  $text = !empty($content) ? $content : (!empty($a["url"]) ? $a["url"] : $a["code"]);
  $url = use_lti()
    ? home_url("?bw_link=" . urlencode(get_play_path($a)))
    : bw_url(get_play_path($a));
  return "<a href=\"" . esc_attr($url) . "\">{$text}</a>";
});

$bwNextEmbedID = 0;
$bwForms = [];

add_action('wp_footer', function () {
  global $bwForms;
  foreach ($bwForms as $form) {
    echo $form;
  }
});

add_shortcode("bw_embed", function ($atts) {
  $a = shortcode_atts([
    'code' => '',
    'url' => null,
    'width' => null,
    'height' => null,
    'allowfullscreen' => null
  ], $atts);
  $result = "<div class=\"bw-widget-wrapper\">";
  $url = bw_url(get_play_path($a));
  if (use_lti()) {
    global $bwNextEmbedID;
    global $bwForms;
    $embedID = $bwNextEmbedID++;
    $formName = "bwWidgetLaunchForm{$embedID}";
    $frameName = "bwWidgetFrame{$embedID}";
    $bwForms[] = get_lti_launch_form($url, $formName, $frameName);
    $bwForms[] = get_autolaunch_script($formName);
    $result .= "<iframe allow=\"microphone *; microphone;\" name={$frameName}";
  }
  else {
    $result .= "<iframe allow=\"microphone *; microphone;\" src=\"" . esc_attr($url) . "\"";
  }
  $result .= " class=\"bw-widget-frame\"";
  if ($a['width']) {
    $result .= " width=\"" . esc_attr($a['width']) . "\"";
  }
  if ($a['height']) {
    $result .= " height=\"" . esc_attr($a['height']) . "\"";
  }
  $result .= "></iframe>";
  if (($a['allowfullscreen'] == "1") || ($a['allowfullscreen'] == "true")) {
    $result .= "<a class=\"bw-widget-new-tab\" href=\"" . esc_attr($url) . "\" target=\"_blank\">" . get_new_tab_icon() . "</a>";
  }
  $result .= "</div>";

  return $result;
});


add_action('wp_enqueue_scripts', function() {
  wp_register_style('wp-bookwidgets', plugins_url('wp-bookwidgets.css', __FILE__) );
  wp_enqueue_style('wp-bookwidgets');
});



////////////////////////////////////////////////////////////////////////////////
// Cache handling
////////////////////////////////////////////////////////////////////////////////

// Always forcing no-cache, since there's no late point where we can decide
// whether we want to do this or not :(
if (get_option('bw_lti_enabled')) {
  // Add as many no-cache headers as we can, to make sure hitting the 'back' button 
  // in the browser doesn't use a cached page (since a cached page will illegally 
  // reuse any generated nonce)
  add_filter('nocache_headers', function ($headers) {
    $headers["Cache-Control"] = "private, must-revalidate, max-age=0, no-store, no-cache, must-revalidate, post-check=0, pre-check=0";
    return $headers;
  });
  nocache_headers();
}


////////////////////////////////////////////////////////////////////////////////
// Settings
////////////////////////////////////////////////////////////////////////////////

add_action('admin_init', function () {
  register_setting('bw-settings-general', 'bw_lti_enabled');
  register_setting('bw-settings-general', 'bw_lti_consumer_key');
  register_setting('bw-settings-general', 'bw_lti_consumer_secret');
});

add_action('admin_menu', function () {
  add_options_page(
    'BookWidgets Settings', 
    'BookWidgets', 
    'administrator', 
    'bw-settings', 
    function () {
      ?>
        <div class="wrap">
          <h2>BookWidgets Settings</h2>

          <form method="post" action="options.php">
            <?php settings_fields('bw-settings-general'); ?>
            <?php do_settings_sections('bw-settings'); ?>
            <table class="form-table">
              <tr valign="top">
                <th scope="row">LTI</th>
                <td>
                  <label for="bw_lti_enabled">
                    <input id="bw_lti_enabled" type="checkbox" name="bw_lti_enabled" value="1" <?php checked('1', get_option('bw_lti_enabled')); ?> /> 
                    Automatically sign in students in widgets using their user account
                    from this site.
                  </label>
                  <p class="description">
                    To enable this, you need to enter your BookWidgets LTI credentials below.
                    You can get your LTI credentials from 
                    <a href="mailto:support@bookwidgets.com">BookWidgets support</a>.
                  </p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row">LTI Consumer Key</th>
                <td><input type="text" name="bw_lti_consumer_key" value="<?php echo esc_attr( get_option('bw_lti_consumer_key') ); ?>" /></td>
              </tr>
              <tr valign="top">
                <th scope="row">LTI Consumer Secret</th>
                <td><input type="text" name="bw_lti_consumer_secret" value="<?php echo esc_attr( get_option('bw_lti_consumer_secret') ); ?>" /></td>
              </tr>
            </table>
            <?php submit_button(); ?>
          </form>
        </div>
      <?php
    }
  );
});


////////////////////////////////////////////////////////////////////////////////
// Auto-submit form for links
////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['bw_link'])) {
  // add_filter('the_title','bw_link_page_title');
  add_filter('the_content', function () {
    return get_lti_launch_form(bw_url($_GET["bw_link"]), 'widgetLaunchForm')
      . get_autolaunch_script("widgetLaunchForm");
  });
  add_action('template_redirect', function () {
    ?>
      <html>
        <body>
          <?php the_content(); ?>
        </body>
      </html>
    <?php
    exit;
  });
}

?>
