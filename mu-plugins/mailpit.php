<?php

/**
 * Local mail catcher via Mailpit (dev only).
 * Active only when WP_ENVIRONMENT_TYPE=local — safe to deploy as-is to production.
 */

defined( 'ABSPATH' ) || exit;

if (wp_get_environment_type() !== 'local') {
	return;
}

add_action('phpmailer_init', function (PHPMailer\PHPMailer\PHPMailer $phpmailer) {
	$phpmailer->isSMTP();
	$phpmailer->Host       = 'mailpit';
	$phpmailer->Port       = 1025;
	$phpmailer->SMTPAuth   = false;
});
