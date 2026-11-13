<?php
/**
 * Seed script for the Simple Cloudflare Turnstile form settings (ported from
 * promedi_block).
 *
 * Turns Turnstile on for WordPress login, registration, password reset and
 * comments, and for every Contact Form 7 form, in "Interaction Only" mode: the
 * widget stays invisible and Cloudflare shows a checkbox only to visitors it
 * finds suspicious. Every option is idempotent — safe to re-run, locally and on
 * hosting.
 *
 * Keys are NOT set here: locally they come from the CF_TURNSTILE_* constants in
 * docker/compose.yml (Cloudflare test keys, never stored in the DB); on hosting
 * they are entered in Settings → Cloudflare Turnstile (docs/getting-started.md).
 *
 * cfturnstile_disable_button is deliberately left off: setSubmitState() in
 * contactForm.js resets the submit button's `disabled` after every request,
 * which would override the plugin's "disabled until verified" state. The token
 * is still verified on the server. cfturnstile_failsafe stays off as well.
 *
 * Order: wp plugin install simple-cloudflare-turnstile --activate → this script
 * → Settings → Cloudflare Turnstile → TEST RESPONSE.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/seed-turnstile.php
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

// Plugin check by activation, not function_exists(): without keys (WP-CLI never
// sees the CF_TURNSTILE_* constants) the plugin doesn't load its integrations.
if ( ! is_plugin_active( 'simple-cloudflare-turnstile/simple-cloudflare-turnstile.php' ) ) {
	WP_CLI::error( '[seed-turnstile] Simple Cloudflare Turnstile is not active. Install it first: wp plugin install simple-cloudflare-turnstile --activate' );
}

// Values are stored as plain strings: the plugin's sanitize callbacks are
// registered on admin_init, which WP-CLI doesn't fire.
$turnstile_options = array(
	// WordPress: login, registration, password reset, comments.
	'cfturnstile_login'      => '1',
	'cfturnstile_register'   => '1',
	'cfturnstile_reset'      => '1',
	'cfturnstile_comment'    => '1',
	// Contact Form 7: the widget before the submit button of every form.
	'cfturnstile_cf7_all'    => '1',
	// Invisible unless Cloudflare needs a click (pair with Widget Mode: Managed).
	'cfturnstile_appearance' => 'interaction-only',
);

foreach ( $turnstile_options as $turnstile_option => $turnstile_value ) {
	if ( (string) get_option( $turnstile_option ) === $turnstile_value ) {
		WP_CLI::log( "[seed-turnstile] option {$turnstile_option}: already set" );
		continue;
	}
	update_option( $turnstile_option, $turnstile_value );
	WP_CLI::log( "[seed-turnstile] option {$turnstile_option}: set to '{$turnstile_value}'" );
}

// Without keys the plugin sleeps and shows no admin notice — forms are silently
// unprotected. Locally WP-CLI never sees the constants, so checked only outside 'local'.
if ( 'local' !== wp_get_environment_type() && ( ! get_option( 'cfturnstile_key' ) || ! get_option( 'cfturnstile_secret' ) ) ) {
	WP_CLI::warning( '[seed-turnstile] Turnstile keys are missing — forms are NOT protected. Enter Site Key and Secret Key in Settings → Cloudflare Turnstile (docs/getting-started.md).' );
}

// The plugin loads no form integration until cfturnstile_tested is 'yes', which
// only the settings page test sets — not bypassed here, it proves the keys work.
if ( 'yes' !== get_option( 'cfturnstile_tested' ) ) {
	WP_CLI::warning( '[seed-turnstile] Turnstile is OFF on every form until the key test passes: Settings → Cloudflare Turnstile → TEST RESPONSE.' );
}

WP_CLI::success( '[seed-turnstile] Cloudflare Turnstile form settings seeded.' );
