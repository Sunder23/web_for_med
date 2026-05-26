<?php
get_header();

$hero = get_field('front_page_hero');
$clinics = get_field('front_page_clinics');
$quote = get_field('front_page_quote');
$problems = get_field('front_page_problems');
$banner_text = get_field('front_page_banner_text');
$solutions = get_field('front_page_solutions');
$services = get_field('front_page_services');
$cases = get_field('front_page_cases');
$process = get_field('front_page_process');
$why = get_field('front_page_why');
$contact_form_shortcode = get_field('front_page_contact_form_shortcode');
?>


<main>
	<section class="hero">
		<div class="hero__wrap l-wrap">
			<div class="hero__content l-frame-x">
				<div class="hero__text">
					<div class="hero__titles">
						<h1 class="hero__title"><?php echo esc_html($hero['title']); ?></h1>
						<p class="hero__subtitle"><?php echo esc_html($hero['subtitle']); ?></p>
					</div>
					<div class="hero__actions">
						<a href="<?php echo esc_url(($hero['primary_link'])['url'] ?? '#'); ?>" class="btn btn--primary"><?php echo esc_html(($hero['primary_link'])['title'] ?? ''); ?></a>
						<a href="<?php echo esc_url(($hero['secondary_link'])['url'] ?? '#'); ?>" class="btn btn--secondary"><?php echo esc_html(($hero['secondary_link'])['title'] ?? ''); ?></a>
					</div>
				</div>
				<?php if ($hero['image']) : ?>
					<div class="hero__image"
						style="--hero-image-mobile: url('<?php echo esc_url(wp_get_attachment_image_url($hero['image_mobile'], 'full')); ?>');">
						<div
							class="hero__mosaic"
							aria-hidden="true"
							style="--hero-image: url('<?php echo esc_url(wp_get_attachment_image_url($hero['image'], 'full')); ?>');">
							<?php for ($tile_index = 1; $tile_index <= 9; $tile_index++) : ?>
								<span class="hero__tile hero__tile--<?php echo esc_attr((string) $tile_index); ?>"></span>
							<?php endfor; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="s-clinics">
		<div class="s-clinics__wrap l-wrap">
			<div class="clinics-grid">
				<?php foreach ($clinics['items'] as $item) : ?>
					<div class="clinics-grid__item">
						<?php echo wp_get_attachment_image($item['icon'], 'full', '', ['class' => 'clinics-grid__icon']); ?>
						<span><?php echo esc_html($item['title']); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="clinics-aside">
				<?php echo wp_get_attachment_image($clinics['aside_icon'], 'full', '', ['class' => 'clinics-aside__icon']); ?>
				<p class="clinics-aside__text"><?php echo esc_html($clinics['aside_text']); ?></p>
			</div>
		</div>
	</section>

	<section class="s-quote">
		<div class="s-quote__wrap l-wrap">
			<div class="s-quote__inner l-frame-x">
				<svg class="s-quote__cogs" width="82" height="50" viewBox="0 0 82 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<g class="gear gear--1">
						<path d="M22.5 9C16.1588 9 11 14.3831 11 21C11 27.6169 16.1588 33 22.5 33C28.8412 33 34 27.6169 34 21C34 14.3831 28.8412 9 22.5 9ZM22.5 30.3333C17.5681 30.3333 13.5556 26.1465 13.5556 21C13.5556 15.8535 17.5681 11.6667 22.5 11.6667C27.4319 11.6667 31.4444 15.8535 31.4444 21C31.4444 26.1465 27.4319 30.3333 22.5 30.3333Z" fill="#AEBBC4" />
						<path d="M40.0756 32.3945L38.5517 30.9051C39.4213 29.4502 40.079 27.9038 40.5156 26.2876H42.664C43.402 26.2876 44 25.703 44 24.9819V18.0183C44 17.2972 43.402 16.7126 42.664 16.7126H40.5156C40.079 15.0962 39.4211 13.5499 38.5517 12.0951L40.0756 10.6057C40.5974 10.0958 40.5974 9.26906 40.0756 8.75933L35.0372 3.83537C34.5152 3.32546 33.6694 3.32546 33.1477 3.83537L31.6239 5.32454C30.1354 4.4748 28.553 3.83206 26.899 3.4052V1.30567C26.899 0.584593 26.301 0 25.5629 0H18.4374C17.6994 0 17.1014 0.584593 17.1014 1.30567V3.4052C15.4474 3.83206 13.865 4.4748 12.3763 5.32454L10.8525 3.83537C10.6019 3.59043 10.2622 3.4529 9.90766 3.4529C9.55317 3.4529 9.21364 3.59043 8.96282 3.83537L3.92437 8.75915C3.40261 9.26906 3.40261 10.0958 3.92437 10.6055L5.44834 12.0949C4.57867 13.5496 3.92099 15.096 3.48437 16.7124H1.33603C0.598008 16.7124 0 17.297 0 18.0181V24.9817C0 25.7028 0.598008 26.2874 1.33603 26.2874H3.48437C3.92099 27.9038 4.57885 29.4502 5.44834 30.9049L3.92437 32.3943C3.40261 32.9042 3.40261 33.7309 3.92437 34.2407L8.96264 39.1646C9.21328 39.4096 9.55299 39.5471 9.90748 39.5471C10.262 39.5471 10.6015 39.4096 10.8523 39.1646L12.3761 37.6755C13.8648 38.5252 15.4472 39.1679 17.1012 39.5948V41.6943C17.1012 42.4154 17.6992 43 18.4372 43H25.5628C26.3008 43 26.8988 42.4154 26.8988 41.6943V39.5948C28.5528 39.1679 30.1352 38.5252 31.6237 37.6755L33.1475 39.1646C33.6694 39.6745 34.5152 39.6745 35.037 39.1646L40.0754 34.2407C40.5972 33.7311 40.5972 32.9044 40.0756 32.3945ZM38.1466 24.7221C37.7281 26.7369 36.9196 28.6386 35.7433 30.3747C35.3924 30.8924 35.4633 31.5799 35.9124 32.0188L37.2415 33.3175L34.0923 36.395L32.7634 35.0963C32.3138 34.6573 31.6105 34.5883 31.0811 34.9309C29.3047 36.0805 27.3587 36.8708 25.297 37.2798C24.6742 37.4034 24.2267 37.9383 24.2267 38.5593V40.3887H19.7733V38.5593C19.7733 37.9383 19.3256 37.4032 18.703 37.2798C16.6413 36.8706 14.6951 36.0805 12.9187 34.9309C12.3891 34.5883 11.6857 34.6573 11.2364 35.0963L9.90748 36.395L6.75854 33.3176L8.08763 32.0189C8.53671 31.5801 8.60761 30.8924 8.25668 30.3748C7.08044 28.639 6.27169 26.7371 5.85307 24.7221C5.72677 24.1137 5.17917 23.6764 4.54376 23.6764H2.67206V19.3241H4.54393C5.17917 19.3241 5.72695 18.8868 5.85325 18.2784C6.27205 16.2635 7.08079 14.3615 8.25686 12.6257C8.60779 12.1079 8.53689 11.4205 8.08781 10.9816L6.75872 9.68287L9.90766 6.60549L11.2366 7.9042C11.6858 8.34325 12.3895 8.41219 12.9189 8.06958C14.6953 6.92007 16.6413 6.1297 18.7032 5.72076C19.3258 5.59733 19.7735 5.06218 19.7735 4.4412V2.61135H24.2269V4.44068C24.2269 5.06166 24.6744 5.59664 25.2971 5.72024C27.3589 6.12935 29.3049 6.91955 31.0813 8.06906C31.6109 8.41167 32.3143 8.34273 32.7636 7.90367L34.0925 6.60497L37.2416 9.68252L35.9126 10.9812C35.4635 11.4201 35.3926 12.1078 35.7435 12.6253C36.9199 14.3614 37.7285 16.2631 38.1468 18.2779C38.2732 18.8865 38.8206 19.3238 39.4562 19.3238H41.3279V23.676H39.4559C38.8205 23.6762 38.2731 24.1135 38.1466 24.7221Z" fill="#AEBBC4" />
						<path d="M22 14C18.6915 14 16 17.1401 16 21C16 24.8599 18.6915 28 22 28C25.3085 28 28 24.8599 28 21C28 17.1401 25.3085 14 22 14ZM22 25.0423C20.0895 25.0423 18.5352 23.229 18.5352 21C18.5352 18.771 20.0895 16.9577 22 16.9577C23.9105 16.9577 25.4648 18.771 25.4648 21C25.4648 23.229 23.9105 25.0423 22 25.0423Z" fill="#AEBBC4" />
					</g>
					<g class="gear gear--2">
						<path d="M62.6885 36.1888H61.1114C60.8812 34.4925 60.2123 32.9344 59.221 31.633L60.3385 30.5154C60.8506 30.0033 60.8506 29.1731 60.3383 28.6612C59.8265 28.149 58.9962 28.1491 58.4842 28.6612L57.3667 29.7787C56.0653 28.7874 54.5071 28.1185 52.811 27.8883V26.3112C52.811 25.5871 52.2241 25 51.4998 25C50.7755 25 50.1887 25.5871 50.1887 26.3112V27.8883C48.4923 28.1185 46.9343 28.7874 45.6329 29.7787L44.5155 28.6612C44.0034 28.1491 43.1732 28.149 42.6613 28.6612C42.1491 29.1733 42.1491 30.0033 42.6611 30.5154L43.7786 31.633C42.7874 32.9344 42.1185 34.4925 41.8882 36.1888H40.3112C39.5869 36.1888 39 36.7759 39 37.5C39 38.2241 39.5869 38.8112 40.3112 38.8112H41.8882C42.1185 40.5075 42.7874 42.0656 43.7786 43.367L42.6611 44.4846C42.1491 44.9967 42.1491 45.8269 42.6613 46.3388C42.9173 46.5949 43.2529 46.7229 43.5884 46.7229C43.9239 46.7229 44.2595 46.5949 44.5157 46.3388L45.6331 45.2213C46.9345 46.2126 48.4927 46.8815 50.1888 47.1117V48.6888C50.1888 49.4129 50.7757 50 51.5 50C52.2243 50 52.8112 49.4129 52.8112 48.6888V47.1117C54.5075 46.8815 56.0655 46.2126 57.3669 45.2213L58.4843 46.3388C58.7405 46.5949 59.0759 46.7229 59.4116 46.7229C59.7471 46.7229 60.0827 46.5949 60.3387 46.3388C60.8509 45.8268 60.8509 44.9967 60.3389 44.4846L59.2214 43.367C60.2126 42.0656 60.8815 40.5075 61.1117 38.8112H62.6888C63.4131 38.8112 64 38.2241 64 37.5C64 36.7759 63.4128 36.1888 62.6885 36.1888ZM51.4998 44.5804C47.5957 44.5804 44.4195 41.4042 44.4195 37.5C44.4195 33.5958 47.5957 30.4196 51.4998 30.4196C55.404 30.4196 58.5801 33.5958 58.5801 37.5C58.5801 41.4042 55.404 44.5804 51.4998 44.5804Z" fill="#AEBBC4" />
						<path d="M51.5 33C49.0187 33 47 35.0187 47 37.5C47 39.9813 49.0187 42 51.5 42C53.9813 42 56 39.9813 56 37.5C56 35.0187 53.9813 33 51.5 33ZM51.5 39.1277C50.6025 39.1277 49.8723 38.3975 49.8723 37.5C49.8723 36.6025 50.6025 35.8723 51.5 35.8723C52.3975 35.8723 53.1277 36.6025 53.1277 37.5C53.1277 38.3975 52.3975 39.1277 51.5 39.1277Z" fill="#AEBBC4" />
					</g>
					<g class="gear gear--3">
						<path d="M67 8C63.1403 8 60 10.9159 60 14.5C60 18.0841 63.1403 21 67 21C70.8597 21 74 18.0841 74 14.5C74 10.9159 70.8597 8 67 8ZM67 18.5316C64.6058 18.5316 62.6582 16.723 62.6582 14.5C62.6582 12.277 64.6058 10.4684 67 10.4684C69.3942 10.4684 71.3418 12.277 71.3418 14.5C71.3418 16.723 69.3942 18.5316 67 18.5316Z" fill="#AEBBC4" />
						<path d="M79.3395 11.8987L79.3257 11.8982C79.0014 10.461 78.4137 9.11869 77.6148 7.91878L77.6281 7.90497C78.6558 6.88912 78.6486 5.2544 77.6067 4.24709C77.0983 3.7558 76.4226 3.48508 75.7038 3.48508C75.7036 3.48508 75.704 3.48508 75.7038 3.48508C74.9951 3.48508 74.328 3.74812 73.8224 4.22644L73.8081 4.23907C72.5668 3.46682 71.1783 2.89877 69.6916 2.58523L69.691 2.57193C69.6744 1.15129 68.4739 0.000170586 67 0C65.5263 0 64.3256 1.15111 64.309 2.57193L64.3084 2.58523C62.8217 2.89894 61.433 3.46682 60.1919 4.23907L60.1776 4.22627C59.1263 3.23278 57.4357 3.23977 56.3933 4.24709C55.3514 5.2544 55.3443 6.88895 56.3718 7.90497L56.3852 7.91895C55.5863 9.11886 54.9988 10.4612 54.6743 11.8984L54.6605 11.8989C53.1907 11.9148 52 13.0756 52 14.5002C52 15.9249 53.1908 17.0856 54.6609 17.1014L54.6743 17.102C54.9986 18.5391 55.5863 19.8813 56.3852 21.0812L56.3718 21.0952C55.3442 22.1112 55.3514 23.7458 56.3933 24.7529C56.9015 25.2444 57.5774 25.5149 58.2962 25.5149C58.2964 25.5149 58.2962 25.5149 58.2964 25.5149C59.0051 25.5149 59.6718 25.2519 60.1776 24.7737L60.1919 24.7609C61.433 25.5332 62.8217 26.1011 64.3084 26.4148L64.309 26.4281C64.3256 27.8489 65.5263 29 66.9998 29C67 29 67 29 67.0002 29C67.719 29 68.3947 28.7295 68.9029 28.2382C69.4041 27.7537 69.6833 27.1119 69.691 26.4282L69.6916 26.4149C71.1783 26.1014 72.5668 25.5333 73.8081 24.7611L73.8226 24.7739C74.8737 25.7674 76.5646 25.7602 77.6067 24.7531C78.6488 23.7458 78.6558 22.111 77.6281 21.0952L77.6148 21.0814C78.4137 19.8815 79.0012 18.5393 79.3257 17.1021L79.3391 17.1016C80.8092 17.0857 82 15.9251 82 14.5003C82 13.0754 80.8093 11.9148 79.3395 11.8987ZM66.9998 24.1381C61.4932 24.1381 57.0291 19.823 57.0291 14.5C57.0291 9.17703 61.4932 4.86188 66.9998 4.86188C72.5065 4.86188 76.9705 9.17703 76.9705 14.5C76.9705 19.823 72.5065 24.1381 66.9998 24.1381Z" fill="#AEBBC4" />
					</g>
				</svg>
				<h2 class="s-quote__text"><?php echo wp_kses($quote['text'], ['br' => ['class' => true]]); ?></h2>
			</div>
		</div>
	</section>
	<div class="sections_wrapper">
		<section class="s-problems">
			<div class="s-problems__wrap l-wrap">
				<div class="s-problems__inner">
					<?php foreach ($problems as $item) : ?>
						<div class="problem-card info-card">
							<span class="tag c-tag"><?php echo esc_html($item['tag']); ?></span>
							<div class="problem-card__body info-card__body">
								<h3 class="problem-card__title card-title"><?php echo esc_html($item['title']); ?></h3>
								<p class="problem-card__text card-text"><?php echo esc_html($item['text']); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<div class="sections_inner">
			<section class="s-banner">
				<div class="s-banner__inner">
					<h2 class="s-banner__text"><?php echo esc_html($banner_text); ?></h2>
				</div>
			</section>

			<section class="s-solutions">
				<div class="s-solutions__wrap l-wrap">
					<div class="s-solutions__inner">
						<?php foreach ($solutions as $item) : ?>
							<div class="solution-card info-card">
								<div class="solution-card__body info-card__body">
									<h3 class="solution-card__title card-title"><?php echo esc_html($item['title']); ?></h3>
									<p class="solution-card__text card-text"><?php echo esc_html($item['text']); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</div>
	</div>


	<section class="s-services" id="about">
		<div class="s-services__wrap l-wrap">
			<div class="s-services__header l-frame-x">
				<h2 class="section-title"><?php echo esc_html($services['title']); ?></h2>
			</div>
			<div class="s-services__body l-frame-x">
				<div class="services-img">
					<?php foreach ($services['items'] as $key => $item) :
						if ($key === 0) { ?>
							<?php echo wp_get_attachment_image($item['image'], 'full', '', ['class' => ' services-img__item services-img__item--active services-img__item--' . $key, 'data-service-image' => (string) $key]); ?> }
						<?php } else { ?>
							<?php echo wp_get_attachment_image($item['image'], 'full', '', ['class' => ' services-img__item services-img__item--' . $key, 'data-service-image' => (string) $key]); ?>
						<?php } ?>
					<?php endforeach; ?>
				</div>
				<ol class="services-list">
					<?php foreach ($services['items'] as $key => $item) : ?>
						<li
							class="services-list__item icon-list__item<?php echo $key === 0 ? ' services-list__item--active' : ''; ?>"
							data-service-target="<?php echo esc_attr((string) $key); ?>">
							<?php echo wp_get_attachment_image($item['icon'], 'full', '', ['class' => 'services-list__icon icon-list__icon']); ?>
							<?php if (! empty($item['text'])) : ?>
								<div class="services-list__content">
									<p class="services-list__title"> <span class="services-list__number"><?= $key + 1 ?>.</span> <?php echo esc_html($item['title']); ?></p>
									<p class="services-list__desc"><?php echo esc_html($item['text']); ?></p>
								</div>
							<?php else : ?>
								<span><?php echo esc_html($item['title']); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</section>

	<section class="s-cases" id="cases">
		<div class="s-cases__wrap l-wrap">
			<div class="s-cases__header l-frame-x">
				<h2 class="section-title"><?php echo esc_html($cases['title']); ?></h2>
				<div class="s-cases__nav">
					<button class="s-cases__btn" id="casePrev" aria-label="<?php esc_attr_e('Стрілка вліво', 'textdomaintomodify'); ?>">
						<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M13.6757 16.5L23 6.91667L20.1622 4L8 16.5L20.1622 29L23 26.0833L13.6757 16.5Z" fill="#AEBBC4" />
						</svg>
					</button>
					<button class="s-cases__btn" id="caseNext" aria-label="<?php esc_attr_e('Стрілка вправо', 'textdomaintomodify'); ?>">
						<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M17.3243 16.5L8 6.91667L10.8378 4L23 16.5L10.8378 29L8 26.0833L17.3243 16.5Z" fill="#AEBBC4" />
						</svg>
					</button>
				</div>
			</div>
			<div class="cases-slider-wrap swiper" id="casesSlider">
				<?php echo wp_get_attachment_image($cases['bg_image'], 'full', '', ['class' => 'cases-slider-bg']); ?>
				<div class="cases-slider swiper-wrapper">
					<?php foreach ($cases['items'] as $item) : ?>
						<div class="cases-slide swiper-slide">
							<div class="cases-slide__desc">
								<p><?php echo esc_html($item['description']); ?></p>
							</div>
							<div class="cases-slide__visual">
								<div class="cases-slide__card">
									<p><?php echo esc_html($item['result']); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="cases-dots" id="casesDots"></div>
		</div>
	</section>

	<section class="s-process s-process--top">
		<div class="s-process__wrap l-wrap">
			<div class="s-process__header l-frame-x">
				<h2 class="section-title"><?php echo esc_html($process['title']); ?></h2>
			</div>

		</div>
	</section>
	<section class="s-process s-process--bottom">
		<div class="s-process__wrap l-wrap">
			<div class="s-process__steps ">
				<?php foreach ($process['items'] as $item) : ?>
					<div class="process-step info-card">
						<div class="process-step__top info-card__body">
							<span class="tag c-tag"><?php echo esc_html($item['tag']); ?></span>
							<h3 class="process-step__title card-title"><?php echo esc_html($item['title']); ?></h3>
						</div>
						<p class="process-step__text card-text"><?php echo esc_html($item['text']); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

	</section>
	<?php
	$why_items = $why['items'];
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
							<h2 class="why-reasons__title section-title"><?php echo esc_html($why['title']); ?></h2>
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
</main>
<?php get_footer();
