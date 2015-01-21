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
	if (empty($value)) {
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
	if (count($args) > 0) {
		$kvpairs = array();
		foreach ($args as $key => $value) {
			$kvpairs[] = $key . '=' . $value;
		}
		
		$url .= (strpos($url, '?') === false) ? '?' : '&';
		$url .= join('&', $kvpairs);
	}
	
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
		array_push($wrapper_class, 'wpus-messages');
		$str .= '<div class="' . implode(' ', $wrapper_class) . '">' . "\n";
		$str .= $wpus->GetMessages($message_classes);
		$str .= "\n</div>\n";
	}
	
	return $str;
}
