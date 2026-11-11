<?php
/**
 * Switch the contact form (the one referenced by the `footer_contact` option)
 * to the contact-drawer markup: name, contact channel (Телефон / Email /
 * Telegram / WhatsApp), contact value, optional message.
 *
 * Template: scripts/templates/cf7-contact-form.html. The mail body is
 * rewritten for the new field names. CF7 keeps the template in post_content
 * and the `_form` meta — both are updated. Re-running is safe: a form that
 * already matches is skipped.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   write the new template and mail body.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-cf7-contact-form.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$cf7c_args  = isset( $args ) && is_array( $args ) ? $args : array();
$cf7c_apply = in_array( 'apply', $cf7c_args, true );

$cf7c_template_path = __DIR__ . '/templates/cf7-contact-form.html';
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local template file.
$cf7c_template = file_exists( $cf7c_template_path ) ? trim( (string) file_get_contents( $cf7c_template_path ) ) : '';

if ( '' === $cf7c_template ) {
	WP_CLI::error( '[cf7-contact] template not found: ' . $cf7c_template_path );
}

$cf7c_mail_body = "Ім'я: [your-name]\n\nСпосіб зв'язку: [contact-method]\n\nКонтакт: [your-contact]\n\nПовідомлення:\n[your-message]";

// Resolve the form from the footer_contact option shortcode (id is a hash or a post ID).
$cf7c_option    = function_exists( 'get_field' ) ? get_field( 'footer_contact', 'options' ) : null;
$cf7c_shortcode = is_array( $cf7c_option ) && ! empty( $cf7c_option['contact_form'] ) ? (string) $cf7c_option['contact_form'] : '';

if ( ! preg_match( '/id="([^"]+)"/', $cf7c_shortcode, $cf7c_match ) ) {
	WP_CLI::error( '[cf7-contact] footer_contact.contact_form has no CF7 shortcode id.' );
}

$cf7c_form = function_exists( 'wpcf7_get_contact_form_by_hash' ) ? wpcf7_get_contact_form_by_hash( $cf7c_match[1] ) : null;
if ( ! $cf7c_form && ctype_digit( $cf7c_match[1] ) && function_exists( 'wpcf7_contact_form' ) ) {
	$cf7c_form = wpcf7_contact_form( (int) $cf7c_match[1] );
}

if ( ! $cf7c_form ) {
	WP_CLI::error( sprintf( '[cf7-contact] CF7 form "%s" not found.', $cf7c_match[1] ) );
}

$cf7c_id   = $cf7c_form->id();
$cf7c_meta = (string) get_post_meta( $cf7c_id, '_form', true );
$cf7c_mail = get_post_meta( $cf7c_id, '_mail', true );
$cf7c_mail = is_array( $cf7c_mail ) ? $cf7c_mail : array();

$cf7c_form_same = trim( $cf7c_meta ) === $cf7c_template;
$cf7c_mail_same = isset( $cf7c_mail['body'] ) && $cf7c_mail['body'] === $cf7c_mail_body;

if ( $cf7c_form_same && $cf7c_mail_same ) {
	WP_CLI::success( sprintf( '[cf7-contact] #%d already up to date.', $cf7c_id ) );
	return;
}

WP_CLI::log( sprintf( '[cf7-contact] #%d "%s": form %s, mail %s%s.', $cf7c_id, $cf7c_form->title(), $cf7c_form_same ? 'ok' : 'outdated', $cf7c_mail_same ? 'ok' : 'outdated', $cf7c_apply ? ', updating' : ' (dry run)' ) );

if ( ! $cf7c_apply ) {
	return;
}

update_post_meta( $cf7c_id, '_form', wp_slash( $cf7c_template ) );

$cf7c_mail['body']    = $cf7c_mail_body;
$cf7c_mail['subject'] = '[_site_title]: нова заявка';
update_post_meta( $cf7c_id, '_mail', wp_slash( $cf7c_mail ) );

$cf7c_result = wp_update_post(
	array(
		'ID'           => $cf7c_id,
		'post_content' => wp_slash( $cf7c_template ),
	),
	true
);

if ( is_wp_error( $cf7c_result ) ) {
	WP_CLI::warning( sprintf( '[cf7-contact] #%d: post_content update failed: %s', $cf7c_id, $cf7c_result->get_error_message() ) );
}

WP_CLI::success( sprintf( '[cf7-contact] #%d updated.', $cf7c_id ) );
