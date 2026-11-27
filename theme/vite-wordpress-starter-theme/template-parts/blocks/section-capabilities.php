<?php
/**
 * Theme file.
 *
 * Section: capabilities (Figma desktop 3534:2404, panel 3534:2424; mobile 3589:7440, panel 3589:7469).
 * Heading, the list of competences and the "what is your task" panel: the task
 * chips are toggles, and «Обговоримо?» opens the contact drawer with the selected
 * tasks written into the first line of the form message
 * (assets/src/js/components/taskPicker.js).
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$section_title = isset( $args['title'] ) ? trim( (string) $args['title'] ) : '';
$items         = array_values(
	array_filter(
		is_array( starter_get_array_value( $args, 'items', array() ) ) ? $args['items'] : array(),
		static function ( $item ) {
			return is_array( $item ) && '' !== trim( (string) starter_get_array_value( $item, 'text', '' ) );
		}
	)
);
$tasks         = array_values(
	array_filter(
		is_array( starter_get_array_value( $args, 'tasks', array() ) ) ? $args['tasks'] : array(),
		static function ( $task ) {
			return is_array( $task ) && '' !== trim( (string) starter_get_array_value( $task, 'label', '' ) );
		}
	)
);
$tasks_title   = isset( $args['tasks_title'] ) ? trim( (string) $args['tasks_title'] ) : '';
$button_label  = isset( $args['button_label'] ) && '' !== trim( (string) $args['button_label'] ) ? (string) $args['button_label'] : __( 'Обговоримо?', 'vite-starter' );

if ( empty( $items ) && empty( $tasks ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-capabilities] items and tasks are empty' );
	return;
}
?>
<section class="capabilities">
	<?php if ( '' !== $section_title ) : ?>
		<div class="capabilities__head-wrap container">
			<div class="capabilities__head frame">
				<h2 class="section-title capabilities__title"><?php echo esc_html( $section_title ); ?></h2>
			</div>
		</div>
	<?php endif; ?>
	<div class="capabilities__body-wrap">
		<div class="capabilities__body container">
			<div class="capabilities__grid frame">
				<?php if ( $items ) : ?>
					<ul class="capabilities__list">
						<?php foreach ( $items as $item ) : ?>
							<li class="capabilities__item">
								<span class="capabilities__icon">
									<?php
									if ( ! empty( $item['icon'] ) ) {
										echo wp_get_attachment_image( (int) $item['icon'], 'full', false, array( 'alt' => '' ) );
									}
									?>
								</span>
								<span class="capabilities__text"><?php echo esc_html( (string) $item['text'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="capabilities__tasks" data-task-picker data-task-prefix="<?php esc_attr_e( 'Завдання:', 'vite-starter' ); ?>">
					<?php if ( '' !== $tasks_title ) : ?>
						<p class="capabilities__tasks-title"><?php echo esc_html( $tasks_title ); ?></p>
					<?php endif; ?>
					<div class="capabilities__tasks-body">
						<?php if ( $tasks ) : ?>
							<div class="capabilities__chips" role="group" aria-label="<?php echo esc_attr( '' !== $tasks_title ? $tasks_title : __( 'Завдання', 'vite-starter' ) ); ?>">
								<?php foreach ( $tasks as $task ) : ?>
									<button type="button" class="task-chip" aria-pressed="false" data-task-chip><?php echo esc_html( trim( (string) $task['label'] ) ); ?></button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<a class="button button--primary capabilities__button" href="#contact-form" data-contact-open data-task-picker-submit><?php echo esc_html( $button_label ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
