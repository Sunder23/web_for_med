<?php
/**
 * Theme file.
 *
 * Section: team (Figma desktop 3241:4199, mobile 3255:896).
 * Hover labels (3241:4277) follow the cursor on desktop — see
 * assets/src/js/components/teamHoverLabel.js. They are decorative.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$members = isset( $args['members'] ) && is_array( $args['members'] ) ? $args['members'] : array();

if ( ! $members ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-team] members is empty' );
	return;
}
?>
<section class="team">
	<div class="team__wrap container">
		<ul class="team__grid frame">
			<?php foreach ( $members as $index => $member ) : ?>
				<?php
				$name = isset( $member['name'] ) ? (string) $member['name'] : '';

				if ( '' === $name ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
					error_log( 'WARN [section-team] member #' . (int) $index . ' has no name, skipped' );
					continue;
				}

				$photo       = ! empty( $member['photo'] ) ? (int) $member['photo'] : 0;
				$member_role = isset( $member['role'] ) ? (string) $member['role'] : '';
				$label       = isset( $member['label'] ) ? (string) $member['label'] : '';
				?>
				<li class="team__member" data-team-member>
					<?php if ( $photo ) : ?>
						<?php
						echo wp_get_attachment_image(
							$photo,
							'medium_large',
							false,
							array(
								'class'   => 'team__photo',
								'alt'     => $name,
								'sizes'   => '(min-width: 1024px) 305px, 50vw',
								'loading' => 'lazy',
							)
						);
						?>
					<?php endif; ?>
					<div class="team__body">
						<h2 class="team__name"><?php echo esc_html( $name ); ?></h2>
						<?php if ( '' !== $member_role ) : ?>
							<p class="team__role"><?php echo esc_html( $member_role ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $label ) : ?>
						<span class="team__label" aria-hidden="true" data-team-label><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
