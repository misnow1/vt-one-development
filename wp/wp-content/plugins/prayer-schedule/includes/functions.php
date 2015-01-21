<?php

function wpps_messages() {
	$messages = array(
		'mail_sent_ok' => array(
			'description' => __( "Sender's message was sent successfully", 'wpps' ),
			'default' => __( 'Your message was sent successfully. Thanks.', 'wpps' )
		),

		'mail_sent_ng' => array(
			'description' => __( "Sender's message was failed to send", 'wpps' ),
			'default' => __( 'Failed to send your message. Please try later or contact administrator by other way.', 'wpps' )
		),

		'akismet_says_spam' => array(
			'description' => __( "Akismet judged the sending activity as spamming", 'wpps' ),
			'default' => __( 'Failed to send your message. Please try later or contact administrator by other way.', 'wpps' )
		),

		'validation_error' => array(
			'description' => __( "Validation errors occurred", 'wpps' ),
			'default' => __( 'Validation errors occurred. Please confirm the fields and submit it again.', 'wpps' )
		),

		'accept_terms' => array(
			'description' => __( "There is a field of term that sender is needed to accept", 'wpps' ),
			'default' => __( 'Please accept the terms to proceed.', 'wpps' )
		),

		'invalid_email' => array(
			'description' => __( "Email address that sender entered is invalid", 'wpps' ),
			'default' => __( 'Email address seems invalid.', 'wpps' )
		),

		'invalid_required' => array(
			'description' => __( "There is a field that sender is needed to fill in", 'wpps' ),
			'default' => __( 'Please fill the required field.', 'wpps' )
		)
	);

	return apply_filters( 'wpps_messages', $messages );
}

function wpps_default_form_template() {
	$template =
		'<p>' . __( 'Your Name', 'wpps' ) . ' ' . __( '(required)', 'wpps' ) . '<br />' . "\n"
		. '    [text* your-name] </p>' . "\n\n"
		. '<p>' . __( 'Your Email', 'wpps' ) . ' ' . __( '(required)', 'wpps' ) . '<br />' . "\n"
		. '    [email* your-email] </p>' . "\n\n"
		. '<p>' . __( 'Subject', 'wpps' ) . '<br />' . "\n"
		. '    [text your-subject] </p>' . "\n\n"
		. '<p>' . __( 'Your Message', 'wpps' ) . '<br />' . "\n"
		. '    [textarea your-message] </p>' . "\n\n"
		. '<p>[submit "' . __( 'Send', 'wpps' ) . '"]</p>';

	return $template;
}

function wpps_default_mail_template() {
	$subject = '[your-subject]';
	$sender = '[your-name] <[your-email]>';
	$body = sprintf( __( 'From: %s', 'wpps' ), '[your-name] <[your-email]>' ) . "\n"
		. sprintf( __( 'Subject: %s', 'wpps' ), '[your-subject]' ) . "\n\n"
		. __( 'Message Body:', 'wpps' ) . "\n" . '[your-message]' . "\n\n" . '--' . "\n"
		. sprintf( __( 'This mail is sent via contact form on %1$s %2$s', 'wpps' ),
			get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	$recipient = get_option( 'admin_email' );
	$additional_headers = '';
	$attachments = '';
	$use_html = 0;
	return compact( 'subject', 'sender', 'body', 'recipient', 'additional_headers', 'attachments', 'use_html' );
}

function wpps_default_mail_2_template() {
	$active = false;
	$subject = '[your-subject]';
	$sender = '[your-name] <[your-email]>';
	$body = __( 'Message body:', 'wpps' ) . "\n" . '[your-message]' . "\n\n" . '--' . "\n"
		. sprintf( __( 'This mail is sent via contact form on %1$s %2$s', 'wpps' ),
			get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	$recipient = '[your-email]';
	$additional_headers = '';
	$attachments = '';
	$use_html = 0;
	return compact( 'active', 'subject', 'sender', 'body', 'recipient', 'additional_headers', 'attachments', 'use_html' );
}

function wpps_default_messages_template() {
	$messages = array();

	foreach ( wpps_messages() as $key => $arr ) {
		$messages[$key] = $arr['default'];
	}

	return $messages;
}

function wpps_is_multisite() { // will be removed when WordPress 2.9 is not supported
	if ( function_exists( 'is_multisite' ) )
		return is_multisite();

	return false;
}

function wpps_is_main_site() { // will be removed when WordPress 2.9 is not supported
	if ( function_exists( 'is_main_site' ) )
		return is_main_site();

	return false;
}

function wpps_upload_dir( $type = false ) {
	global $switched;

	$siteurl = get_option( 'siteurl' );
	$upload_path = trim( get_option( 'upload_path' ) );

	$main_override = wpps_is_multisite() && defined( 'MULTISITE' ) && wpps_is_main_site();

	if ( empty( $upload_path ) ) {
		$dir = WP_CONTENT_DIR . '/uploads';
	} else {
		$dir = $upload_path;

		if ( 'wp-content/uploads' == $upload_path ) {
			$dir = WP_CONTENT_DIR . '/uploads';
		} elseif ( 0 !== strpos( $dir, ABSPATH ) ) {
			// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
			$dir = path_join( ABSPATH, $dir );
		}
	}

	if ( ! $url = get_option( 'upload_url_path' ) ) {
		if ( empty( $upload_path )
		|| ( 'wp-content/uploads' == $upload_path )
		|| ( $upload_path == $dir ) )
			$url = WP_CONTENT_URL . '/uploads';
		else
			$url = trailingslashit( $siteurl ) . $upload_path;
	}

	if ( defined( 'UPLOADS' ) && ! $main_override
	&& ( ! isset( $switched ) || $switched === false ) ) {
		$dir = ABSPATH . UPLOADS;
		$url = trailingslashit( $siteurl ) . UPLOADS;
	}

	if ( wpps_is_multisite() && ! $main_override
	&& ( ! isset( $switched ) || $switched === false ) ) {

		if ( defined( 'BLOGUPLOADDIR' ) )
			$dir = untrailingslashit( BLOGUPLOADDIR );

		$url = str_replace( UPLOADS, 'files', $url );
	}

	$uploads = apply_filters( 'wpps_upload_dir', array( 'dir' => $dir, 'url' => $url ) );

	if ( 'dir' == $type )
		return $uploads['dir'];
	if ( 'url' == $type )
		return $uploads['url'];

	return $uploads;
}

function wpps_l10n() {
	$l10n = array(
		'af' => __( 'Afrikaans', 'wpps' ),
		'sq' => __( 'Albanian', 'wpps' ),
		'ar' => __( 'Arabic', 'wpps' ),
		'bn_BD' => __( 'Bangla', 'wpps' ),
		'bs' => __( 'Bosnian', 'wpps' ),
		'pt_BR' => __( 'Brazilian Portuguese', 'wpps' ),
		'bg_BG' => __( 'Bulgarian', 'wpps' ),
		'ca' => __( 'Catalan', 'wpps' ),
		'zh_CN' => __( 'Chinese (Simplified)', 'wpps' ),
		'zh_TW' => __( 'Chinese (Traditional)', 'wpps' ),
		'hr' => __( 'Croatian', 'wpps' ),
		'cs_CZ' => __( 'Czech', 'wpps' ),
		'da_DK' => __( 'Danish', 'wpps' ),
		'nl_NL' => __( 'Dutch', 'wpps' ),
		'en_US' => __( 'English', 'wpps' ),
		'et' => __( 'Estonian', 'wpps' ),
		'fi' => __( 'Finnish', 'wpps' ),
		'fr_FR' => __( 'French', 'wpps' ),
		'gl_ES' => __( 'Galician', 'wpps' ),
		'ka_GE' => __( 'Georgian', 'wpps' ),
		'de_DE' => __( 'German', 'wpps' ),
		'el' => __( 'Greek', 'wpps' ),
		'he_IL' => __( 'Hebrew', 'wpps' ),
		'hi_IN' => __( 'Hindi', 'wpps' ),
		'hu_HU' => __( 'Hungarian', 'wpps' ),
		'id_ID' => __( 'Indonesian', 'wpps' ),
		'it_IT' => __( 'Italian', 'wpps' ),
		'ja' => __( 'Japanese', 'wpps' ),
		'ko_KR' => __( 'Korean', 'wpps' ),
		'lv' => __( 'Latvian', 'wpps' ),
		'lt_LT' => __( 'Lithuanian', 'wpps' ),
		'ml_IN' => __( 'Malayalam', 'wpps' ),
		'nb_NO' => __( 'Norwegian', 'wpps' ),
		'fa_IR' => __( 'Persian', 'wpps' ),
		'pl_PL' => __( 'Polish', 'wpps' ),
		'pt_PT' => __( 'Portuguese', 'wpps' ),
		'ru_RU' => __( 'Russian', 'wpps' ),
		'ro_RO' => __( 'Romanian', 'wpps' ),
		'sr_RS' => __( 'Serbian', 'wpps' ),
		'sk' => __( 'Slovak', 'wpps' ),
		'sl_SI' => __( 'Slovene', 'wpps' ),
		'es_ES' => __( 'Spanish', 'wpps' ),
		'sv_SE' => __( 'Swedish', 'wpps' ),
		'th' => __( 'Thai', 'wpps' ),
		'tr_TR' => __( 'Turkish', 'wpps' ),
		'uk' => __( 'Ukrainian', 'wpps' ),
		'vi' => __( 'Vietnamese', 'wpps' )
	);

	return $l10n;
}

?>
