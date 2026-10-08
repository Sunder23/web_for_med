<?php
/**
 * Theme file.
 *
 * Section: why.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
?>
<?php
$why_items = $args['items'];
if ( is_array( $why_items ) ) :
	?>
	<section class="why">
		<div class="why__wrap container">
			<div class="why__inner frame">
				<div class="why-chat">
					<?php
					foreach ( $why_items as $key => $why_item ) :
						$screen_id  = 'why-chat-screen-' . $key;
						$trigger_id = 'why-reason-trigger-' . $key;
						?>
						<div
							id="<?php echo esc_attr( $screen_id ); ?>"
							class="why-chat__screen <?php echo 0 === $key ? ' why-chat__screen--active why-chat__screen--entered' : ''; ?>"
							data-chat-screen="<?php echo esc_attr( (string) $key ); ?>"
							aria-hidden="<?php echo 0 === $key ? 'false' : 'true'; ?>"
							aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>">
							<div class="why-chat__header">
								<?php echo wp_get_attachment_image( $why_item['avatar'], 'full', '', array( 'class' => 'why-chat__avatar' ) ); ?>
								<span class="why-chat__name"><?php echo esc_html( $why_item['chat_name'] ); ?></span>
							</div>
							<div class="why-chat__dialog">
								<?php foreach ( $why_item['messages'] as $message_index => $message ) : ?>
									<div
										class="why-message why-message--<?php echo esc_attr( $message['side'] ); ?><?php echo 0 === $key ? ' why-message--visible' : ''; ?>"
										data-why-message
										data-message-index="<?php echo esc_attr( (string) $message_index ); ?>">
										<p class="why-message__text"><?php echo esc_html( $message['text'] ); ?></p>
										<span class="why-message__time"><?php echo esc_html( $message['time'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="why-reasons">
					<div class="why-reasons__header">
						<h2 class="why-reasons__title section-title"><?php echo esc_html( $args['title'] ); ?></h2>
					</div>
					<ul class="why-reasons__list">
						<?php
						foreach ( $why_items as $key => $why_item ) :
							$screen_id  = 'why-chat-screen-' . $key;
							$trigger_id = 'why-reason-trigger-' . $key;
							?>
							<li class="why-reasons__item<?php echo ( 0 === $key ) ? ' why-reasons__item--active why-reasons__item--hl' : ''; ?>">
								<button
									id="<?php echo esc_attr( $trigger_id ); ?>"
									class="why-reasons__trigger icon-list__item"
									type="button"
									data-reasons-target="<?php echo esc_attr( (string) $key ); ?>"
									aria-controls="<?php echo esc_attr( $screen_id ); ?>"
									aria-expanded="<?php echo 0 === $key ? 'true' : 'false'; ?>">
									<?php echo wp_get_attachment_image( $why_item['reasons']['icon'], 'full', '', array( 'class' => 'why-reasons__icon icon-list__icon' ) ); ?>
									<span class="why-reasons__label"><?php echo esc_html( $why_item['reasons']['text'] ); ?></span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
