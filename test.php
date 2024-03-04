<?php

if (getenv("COVERAGE") && function_exists('xdebug_start_code_coverage')) {
  xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
}

////////////////////////////////////////////////////////////////////////////////
// Internal WordPress function stubs
////////////////////////////////////////////////////////////////////////////////

$shortcodes = [];
function add_shortcode($name, $function) {
  global $shortcodes;
  $shortcodes[$name] = $function;
}

function add_action($name, $function) {}
function add_filter($name, $function) {}
function nocache_headers() {}

function esc_attr($attr) {
  return htmlspecialchars($attr);
}

function is_user_logged_in() {
  return true;
}

function shortcode_atts($pairs, $atts) {
  $result = array();
  foreach ($pairs as $name => $default) {
    if (array_key_exists($name, $atts)) {
      $result[$name] = $atts[$name];
    }
    else {
      $result[$name] = $default;
    }
  }
  return $result;
}

$ltiEnabled = true;
function get_option($key) {
  global $ltiEnabled;
  if ($key == "bw_lti_enabled") {
    return $ltiEnabled;
  }
  else if ($key == "bw_lti_consumer_key") {
    return "MYKEY";
  }
  else if ($key == "bw_lti_consumer_secret") {
    return "MYSECRET";
  }
}

function get_the_ID() {
  return "page123";
}

function get_the_title() {
  return "My site";
}

function wp_get_current_user() {
  $user = new StdClass();
  $user->ID = "user123";
  $user->user_email = "john@doe.com";
  $user->user_firstname = "John";
  $user->user_lastname = "Doe";
  return $user;
}

function apply_filters($hook, $value) {
  return $value;
}

include 'wp-bookwidgets.php';

function dummy_time() {
  return 1234567890;
}

function dummy_uniqid() {
  return "my-unique-id";
}

function assertEqual($a, $b) {
  if ($a != $b) {
    print "Assertion failed: '{$a}' != '{$b}'";
    exit(1);
  }
}

assertEqual(
  ""
  . "<form action=\"https://www.bookwidgets.com/play/ABCDE\" method=\"POST\" encType=\"application/x-www-form-urlencoded\" target=\"widgetFrame\" name=\"widgetLaunchForm\">"
    . "<input type=\"hidden\" name=\"lti_message_type\" value=\"basic-lti-launch-request\"/>"
    . "<input type=\"hidden\" name=\"lti_version\" value=\"LTI-1p0\"/>"
    . "<input type=\"hidden\" name=\"user_id\" value=\"user123\"/>"
    . "<input type=\"hidden\" name=\"roles\" value=\"Student\"/>"
    . "<input type=\"hidden\" name=\"lis_person_contact_email_primary\" value=\"john@doe.com\"/>"
    . "<input type=\"hidden\" name=\"lis_person_name_given\" value=\"John\"/>"
    . "<input type=\"hidden\" name=\"lis_person_name_family\" value=\"Doe\"/>"
    . "<input type=\"hidden\" name=\"oauth_consumer_key\" value=\"MYKEY\"/>"
    . "<input type=\"hidden\" name=\"oauth_signature_method\" value=\"HMAC-SHA1\"/>"
    . "<input type=\"hidden\" name=\"oauth_timestamp\" value=\"1234567890\"/>"
    . "<input type=\"hidden\" name=\"oauth_nonce\" value=\"my-unique-id\"/>"
    . "<input type=\"hidden\" name=\"oauth_version\" value=\"1.0\"/>"
    . "<input type=\"hidden\" name=\"tool_consumer_info_product_family_code\" value=\"wp-bookwidgets\"/>"
    . "<input type=\"hidden\" name=\"tool_consumer_instance_name\" value=\"My site\"/>"
    . "<input type=\"hidden\" name=\"context_id\" value=\"page123\"/>"
    . "<input type=\"hidden\" name=\"oauth_signature\" value=\"NirSCiYWBee+c5QpqXR7VAAEZZk=\"/>"
    . "<input type=\"submit\" value=\"Play Widget\" style=\"display: none\"/>"
  . "</form>",
  wp_bookwidgets\get_lti_launch_form("https://www.bookwidgets.com/play/ABCDE", 'widgetLaunchForm', 'widgetFrame', 'dummy_time', 'dummy_uniqid')
);

assertEqual(
  ""
  . "<form action=\"https://www.bookwidgets.com/play/ABCDE?teacher_id=123456\" method=\"POST\" encType=\"application/x-www-form-urlencoded\" target=\"widgetFrame\" name=\"widgetLaunchForm\">"
    . "<input type=\"hidden\" name=\"lti_message_type\" value=\"basic-lti-launch-request\"/>"
    . "<input type=\"hidden\" name=\"lti_version\" value=\"LTI-1p0\"/>"
    . "<input type=\"hidden\" name=\"user_id\" value=\"user123\"/>"
    . "<input type=\"hidden\" name=\"roles\" value=\"Student\"/>"
    . "<input type=\"hidden\" name=\"lis_person_contact_email_primary\" value=\"john@doe.com\"/>"
    . "<input type=\"hidden\" name=\"lis_person_name_given\" value=\"John\"/>"
    . "<input type=\"hidden\" name=\"lis_person_name_family\" value=\"Doe\"/>"
    . "<input type=\"hidden\" name=\"oauth_consumer_key\" value=\"MYKEY\"/>"
    . "<input type=\"hidden\" name=\"oauth_signature_method\" value=\"HMAC-SHA1\"/>"
    . "<input type=\"hidden\" name=\"oauth_timestamp\" value=\"1234567890\"/>"
    . "<input type=\"hidden\" name=\"oauth_nonce\" value=\"my-unique-id\"/>"
    . "<input type=\"hidden\" name=\"oauth_version\" value=\"1.0\"/>"
    . "<input type=\"hidden\" name=\"tool_consumer_info_product_family_code\" value=\"wp-bookwidgets\"/>"
    . "<input type=\"hidden\" name=\"tool_consumer_instance_name\" value=\"My site\"/>"
    . "<input type=\"hidden\" name=\"context_id\" value=\"page123\"/>"
    . "<input type=\"hidden\" name=\"oauth_signature\" value=\"yygMp142esoarOKBUVzfB3+id2M=\"/>"
    . "<input type=\"submit\" value=\"Play Widget\" style=\"display: none\"/>"
  . "</form>",
  wp_bookwidgets\get_lti_launch_form("https://www.bookwidgets.com/play/ABCDE?teacher_id=123456", 'widgetLaunchForm', 'widgetFrame', 'dummy_time', 'dummy_uniqid')
);

// TODO
call_user_func($shortcodes["bw_embed"], [
  "code" => "ABCDE",
  "width" => "640",
  "height" => "480"
]);
$ltiEnabled = false;
call_user_func($shortcodes["bw_embed"], [
  "code" => "ABCDE",
  "width" => "640",
  "height" => "480"
]);

// TODO
call_user_func($shortcodes["bw_link"], ["code" => "ABCDE"]);

// Poor man's coverage
if (getenv("COVERAGE") && function_exists('xdebug_start_code_coverage')) {
  $report = "";
  foreach (xdebug_get_code_coverage() as $file => $values) {
    if (strpos($file, "test.php")) { continue; }
    $handle = fopen(basename($file), "r");
    $i = 1;
    while (($line = fgets($handle)) !== false) {
      if (!isset($values[$i]) || $values[$i] === -2) {
        $prefix = " ";
      }
      else if ($values[$i] === 1) {
        $prefix = "+";
      }
      else if ($values[$i] === -1) {
        $prefix = "-";
      }
      $i++;
      $report .= "{$prefix} |{$line}";
    }
    fclose($handle);
  }
  file_put_contents("coverage.txt", $report);
}

?>
