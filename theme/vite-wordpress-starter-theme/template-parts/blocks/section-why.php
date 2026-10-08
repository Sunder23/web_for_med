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
if (is_array($why_items)):
?>
	<section class="s-why">
		<div class="s-why__wrap l-wrap">
			<div class="s-why__inner l-frame-x">
				<div class="why-chat">
					<?php foreach ($why_items as $key => $why_item) :
						$screen_id = 'why-chat-screen-' . $key;
						$trigger_id = 'why-reason-trigger-' . $key;
					?>
						<div
							id="<?= esc_attr($screen_id); ?>"
							class="why-chat__screen <?php echo $key === 0 ? ' why-chat__screen--active why-chat__screen--entered' : ''; ?>"
							data-chat-screen="<?= esc_attr((string) $key); ?>"
							aria-hidden="<?= $key === 0 ? 'false' : 'true'; ?>"
							aria-labelledby="<?= esc_attr($trigger_id); ?>">
							<div class="why-chat__header">
								<?php echo wp_get_attachment_image($why_item['avatar'], 'full', '', ['class' => 'why-chat__avatar']); ?>
								<span class="why-chat__name"><?php echo esc_html($why_item['chat_name']); ?></span>
							</div>
							<div class="why-chat__dialog">
								<?php foreach ($why_item['messages'] as $message_index => $message) : ?>
									<div
										class="why-msg why-msg--<?php echo esc_attr($message['side']); ?><?php echo $key === 0 ? ' why-msg--visible' : ''; ?>"
										data-why-message
										data-message-index="<?= esc_attr((string) $message_index); ?>">
										<p><?php echo esc_html($message['text']); ?></p>
										<span class="why-msg__time"><?php echo esc_html($message['time']); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="why-reasons">
					<div class="why-reasons__header">
						<h2 class="why-reasons__title section-title"><?php echo esc_html($args['title']); ?></h2>
					</div>
					<ul class="why-reasons__list">
						<?php foreach ($why_items as $key => $why_item) :
							$screen_id = 'why-chat-screen-' . $key;
							$trigger_id = 'why-reason-trigger-' . $key;
						?>
							<li class="why-reasons__item<?= ($key === 0) ? ' why-reasons__item--active why-reasons__item--hl' : ''; ?>">
								<button
									id="<?= esc_attr($trigger_id); ?>"
									class="why-reasons__trigger icon-list__item"
									type="button"
									data-reasons-target="<?= esc_attr((string) $key); ?>"
									aria-controls="<?= esc_attr($screen_id); ?>"
									aria-expanded="<?= $key === 0 ? 'true' : 'false'; ?>">
									<?php echo wp_get_attachment_image($why_item['reasons']['icon'], 'full', '', ['class' => 'why-reasons__icon icon-list__icon']); ?>
									<span><?php echo esc_html($why_item['reasons']['text']); ?></span>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
