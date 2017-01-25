<?php

function wpus_human_readable_error($err) {
	switch ($err) {
		case WPUS_CODE_BAD_FORMAT:
			$errstr = "The code entered is not in a valid format. Please enter a code in one of the following formats:<br/>\n";
			$errstr .= WPUS_CODE_FORMAT_HUMAN . "<br/>\n";
			$errstr .= WPUS_CODE_FORMAT_HUMAN2 . "<br/>\n";
			break;
		case WPUS_CODE_INVALID:
			$errstr = "The code entered could not be found. Please try entering the code again.";
			break;
		case WPUS_CODE_NO_USES_REMAINING:
			$errstr = "The code entered is valid but has no more uses remaining. If this is an error, please contact the administrators.";
			break;
		case WPUS_CODE_DISABLED:
			$errstr = "The code entered has been administratively disabled.";
			break;
		default:
			$errstr = "An unknown error number $err has occurred.";
	}

	return $errstr;
}

function wpus_get_request_var ($key, $default = false) {
	if (!isset($_REQUEST[$key])) return $default;

	// if the value is an array, don't force it through trim();
	if (is_array($_REQUEST[$key])) {
		return $_REQUEST[$key];
	}

	// trim the whitespace and return
	$value = trim($_REQUEST[$key]);
	if ($value == '') {
		return $default;
	}

	return $value;
}

/**
 * wpus_redirect_url
 * Enter description here ...
 * @param unknown_type $args
 * @param unknown_type $strip
 */
function wpus_redirect_url($args = array(), $strip = array()) {
	$url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '')  . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	/*
	 * Add the things that were requested
	 */

    /*
     * Look for existing args
     */
    $existing_args = array();
    if (strpos($url, '?')) {
        $url_parts = explode('?', $url, 2);
        $url_base = $url_parts[0];
        $existing_args = explode('&', $url_parts[1]);
        foreach ($existing_args as $pair) {
            $parts = explode('=', $pair, 2);
            $key = $parts[0];
            $value = $parts[1];
            if (!array_key_exists($key, $args)) {
                $args[$key] = $value;
            }
        }
    }
    else {
        $url_base = $url;
    }

    $pairs = array();
    foreach ($args as $key => $value) {
        $pairs[] = $key . "=" . $value;
    }
    $url = $url_base . '?' . implode('&', $pairs);

	/*
	 * Remove the things that were requested
	 */
	foreach ($strip as $stripArg) {
		$url = preg_replace("/$stripArg(=.+)?&?/", '', $url);
	}

	// remove a trailing query parts if they exist
	$url = preg_replace('/\&$/', '', $url);
	$url = preg_replace('/\?$/', '', $url);

	return $url;
}

function wpus_get_messages ($wrapper_class = array(), $message_classes = array()) {

	global $wpus;
	$str = '';
	if ($wpus->HasMessages()) {
		$str .= $wpus->GetMessages($message_classes);
	}

	return $str;
}
