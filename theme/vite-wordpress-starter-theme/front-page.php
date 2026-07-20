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
						<p class="hero__subtitle" data-aos="fade-up" data-aos-duration="400" data-aos-delay="800"><?php echo esc_html($hero['subtitle']); ?></p>
					</div>
					<div class="hero__actions">
						<a href="<?php echo esc_url(($hero['primary_link'])['url'] ?? '#'); ?>" class="btn btn--primary" data-aos="fade" data-aos-duration="350" data-aos-delay="1050"><?php echo esc_html(($hero['primary_link'])['title'] ?? ''); ?></a>
						<a href="<?php echo esc_url(($hero['secondary_link'])['url'] ?? '#'); ?>" class="btn btn--secondary" data-aos="fade" data-aos-duration="350" data-aos-delay="1250"><?php echo esc_html(($hero['secondary_link'])['title'] ?? ''); ?></a>
					</div>
				</div>
				<?php if ($hero['image']) : ?>
					<div class="hero__image glitch" data-aos="hero-glitch-reveal"
						style="
						--hero-image: url('<?php echo esc_url(wp_get_attachment_image_url($hero['image'], 'full')); ?>');
						--hero-image-mobile: url('<?php echo esc_url(wp_get_attachment_image_url($hero['image_mobile'], 'full')); ?>');
						">
						<div class="channel r"></div>
						<div class="channel g"></div>
						<div class="channel b"></div>
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

				<svg xmlns="http://www.w3.org/2000/svg" width="63" height="38" viewBox="0 0 63 38" fill="none">
					<g class="gear gear--3">
						<path d="M42.9491 26.3986H41.6244C41.431 24.9737 40.8691 23.6649 40.0365 22.5718L40.9751 21.6329C41.4053 21.2028 41.4053 20.5054 40.975 20.0754C40.545 19.6451 39.8476 19.6453 39.4175 20.0754L38.4788 21.0141C37.3856 20.1814 36.0768 19.6196 34.652 19.4262V18.1014C34.652 17.4931 34.159 17 33.5506 17C32.9422 17 32.4493 17.4931 32.4493 18.1014V19.4262C31.0244 19.6196 29.7156 20.1814 28.6225 21.0141L27.6838 20.0754C27.2537 19.6453 26.5563 19.6451 26.1263 20.0754C25.696 20.5055 25.696 21.2028 26.1261 21.6329L27.0648 22.5718C26.2322 23.6649 25.6703 24.9737 25.4769 26.3986H24.1522C23.5438 26.3986 23.0508 26.8917 23.0508 27.5C23.0508 28.1083 23.5438 28.6014 24.1522 28.6014H25.4769C25.6703 30.0263 26.2322 31.3351 27.0648 32.4282L26.1261 33.3671C25.696 33.7972 25.696 34.4946 26.1263 34.9246C26.3413 35.1397 26.6232 35.2472 26.905 35.2472C27.1868 35.2472 27.4688 35.1397 27.6839 34.9246L28.6226 33.9859C29.7158 34.8186 31.0246 35.3804 32.4494 35.5738V36.8986C32.4494 37.5069 32.9424 38 33.5508 38C34.1592 38 34.6522 37.5069 34.6522 36.8986V35.5738C36.0771 35.3804 37.3858 34.8186 38.479 33.9859L39.4176 34.9246C39.6328 35.1397 39.9146 35.2472 40.1965 35.2472C40.4783 35.2472 40.7603 35.1397 40.9753 34.9246C41.4056 34.4945 41.4056 33.7972 40.9754 33.3671L40.0368 32.4282C40.8694 31.3351 41.4312 30.0263 41.6247 28.6014H42.9494C43.5578 28.6014 44.0508 28.1083 44.0508 27.5C44.0508 26.8917 43.5575 26.3986 42.9491 26.3986ZM33.5506 33.4476C30.2712 33.4476 27.6032 30.7795 27.6032 27.5C27.6032 24.2205 30.2712 21.5524 33.5506 21.5524C36.8301 21.5524 39.4981 24.2205 39.4981 27.5C39.4981 30.7795 36.8301 33.4476 33.5506 33.4476Z" fill="#092A4A" />
						<path d="M33.5896 24C31.6597 24 30.0896 25.5701 30.0896 27.5C30.0896 29.4299 31.6597 31 33.5896 31C35.5195 31 37.0896 29.4299 37.0896 27.5C37.0896 25.5701 35.5195 24 33.5896 24ZM33.5896 28.766C32.8915 28.766 32.3236 28.1981 32.3236 27.5C32.3236 26.8019 32.8915 26.234 33.5896 26.234C34.2877 26.234 34.8556 26.8019 34.8556 27.5C34.8556 28.1981 34.2877 28.766 33.5896 28.766Z" fill="#092A4A" />
					</g>
					<g class="gear gear--2">
						<path d="M50.9922 9C47.6838 9 44.9922 11.6916 44.9922 15C44.9922 18.3084 47.6838 21 50.9922 21C54.3005 21 56.9922 18.3084 56.9922 15C56.9922 11.6916 54.3005 9 50.9922 9ZM50.9922 18.7215C48.94 18.7215 47.2707 17.052 47.2707 15C47.2707 12.948 48.94 11.2785 50.9922 11.2785C53.0443 11.2785 54.7137 12.948 54.7137 15C54.7137 17.052 53.0443 18.7215 50.9922 18.7215Z" fill="#092A4A" />
						<path d="M60.8638 12.8472L60.8528 12.8468C60.5933 11.6574 60.1232 10.5465 59.4841 9.55347L59.4946 9.54204C60.3169 8.70134 60.3111 7.34847 59.4776 6.51483C59.0708 6.10825 58.5303 5.8842 57.9552 5.8842C57.9551 5.8842 57.9554 5.8842 57.9552 5.8842C57.3883 5.8842 56.8546 6.10189 56.4501 6.49775L56.4387 6.50819C55.4457 5.8691 54.3349 5.39898 53.1454 5.1395L53.145 5.12849C53.1317 3.95279 52.1713 3.00014 50.9922 3C49.8132 3 48.8526 3.95265 48.8394 5.12849L48.8389 5.1395C47.6495 5.39912 46.5386 5.8691 45.5457 6.50819L45.5342 6.49761C44.6932 5.6754 43.3408 5.68119 42.5068 6.51483C41.6733 7.34847 41.6677 8.7012 42.4896 9.54204L42.5003 9.55362C41.8612 10.5466 41.3912 11.6575 41.1316 12.8469L41.1206 12.8474C39.9447 12.8605 38.9922 13.8212 38.9922 15.0001C38.9922 16.1792 39.9449 17.1398 41.1209 17.1529L41.1316 17.1533C41.3911 18.3427 41.8612 19.4535 42.5003 20.4465L42.4896 20.4581C41.6675 21.2989 41.6733 22.6517 42.5068 23.4852C42.9134 23.8919 43.4541 24.1158 44.0291 24.1158C44.0293 24.1158 44.0291 24.1158 44.0293 24.1158C44.5963 24.1158 45.1296 23.8981 45.5342 23.5024L45.5457 23.4918C46.5386 24.1309 47.6495 24.6009 48.8389 24.8605L48.8394 24.8715C48.8526 26.0473 49.8132 27 50.992 27C50.9922 27 50.9922 27 50.9923 27C51.5673 27 52.1079 26.7761 52.5145 26.3695C52.9155 25.9686 53.1388 25.4375 53.145 24.8716L53.1454 24.8606C54.3349 24.6012 55.4457 24.131 56.4387 23.4919L56.4503 23.5025C57.2911 24.3247 58.6439 24.3188 59.4776 23.4853C60.3112 22.6517 60.3169 21.2988 59.4946 20.4581L59.4841 20.4467C60.1232 19.4536 60.5931 18.3429 60.8528 17.1535L60.8635 17.1531C62.0395 17.1399 62.9922 16.1794 62.9922 15.0003C62.9922 13.821 62.0397 12.8605 60.8638 12.8472ZM50.992 22.9764C46.5867 22.9764 43.0155 19.4052 43.0155 15C43.0155 10.5948 46.5867 7.02362 50.992 7.02362C55.3974 7.02362 58.9686 10.5948 58.9686 15C58.9686 19.4052 55.3974 22.9764 50.992 22.9764Z" fill="#092A4A" />
					</g>
					<g class="gear gear--1">
						<path d="M14 7C10.1401 7 7 10.1401 7 14C7 17.8599 10.1401 21 14 21C17.8599 21 21 17.8599 21 14C21 10.1401 17.8599 7 14 7ZM14 19.4444C10.998 19.4444 8.55556 17.0021 8.55556 14C8.55556 10.9979 10.998 8.55556 14 8.55556C17.002 8.55556 19.4444 10.9979 19.4444 14C19.4444 17.0021 17.002 19.4444 14 19.4444Z" fill="#092A4A" />
						<path d="M25.5027 21.0941L24.5329 20.1243C25.0863 19.1769 25.5048 18.1699 25.7827 17.1175H27.1498C27.6194 17.1175 28 16.7368 28 16.2673V11.7328C28 11.2633 27.6194 10.8826 27.1498 10.8826H25.7827C25.5048 9.83008 25.0862 8.82321 24.5329 7.87585L25.5027 6.90605C25.8347 6.57402 25.8347 6.03567 25.5027 5.70375L22.2964 2.49745C21.9642 2.16542 21.426 2.16542 21.094 2.49745L20.1243 3.46714C19.1771 2.91383 18.1701 2.4953 17.1175 2.21734V0.850206C17.1175 0.380665 16.737 0 16.2673 0H11.7329C11.2633 0 10.8827 0.380665 10.8827 0.850206V2.21734C9.83015 2.4953 8.82317 2.91383 7.87582 3.46714L6.90614 2.49745C6.74664 2.33795 6.53046 2.2484 6.30487 2.2484C6.07929 2.2484 5.86322 2.33795 5.70361 2.49745L2.49733 5.70363C2.1653 6.03567 2.1653 6.57402 2.49733 6.90594L3.46713 7.87574C2.9137 8.82298 2.49517 9.82997 2.21733 10.8825H0.850202C0.380551 10.8825 0 11.2632 0 11.7327V16.2672C0 16.7367 0.380551 17.1174 0.850202 17.1174H2.21733C2.49517 18.1699 2.91381 19.1769 3.46713 20.1241L2.49733 21.0939C2.1653 21.426 2.1653 21.9643 2.49733 22.2963L5.7035 25.5025C5.863 25.662 6.07917 25.7516 6.30476 25.7516C6.53035 25.7516 6.74641 25.662 6.90602 25.5025L7.87571 24.5329C8.82306 25.0862 9.83004 25.5047 10.8826 25.7827V27.1498C10.8826 27.6193 11.2631 28 11.7328 28H16.2672C16.7369 28 17.1174 27.6193 17.1174 27.1498V25.7827C18.17 25.5047 19.1769 25.0862 20.1242 24.5329L21.0939 25.5025C21.426 25.8346 21.9642 25.8346 22.2963 25.5025L25.5026 22.2963C25.8346 21.9644 25.8346 21.4261 25.5027 21.0941ZM24.2751 16.0981C24.0088 17.4101 23.4943 18.6484 22.7457 19.7788C22.5224 20.116 22.5675 20.5636 22.8533 20.8494L23.6991 21.6951L21.6951 23.6991L20.8495 22.8534C20.5633 22.5675 20.1158 22.5226 19.7789 22.7457C18.6485 23.4942 17.4101 24.0089 16.0981 24.2752C15.7018 24.3557 15.417 24.704 15.417 25.1084V26.2996H12.583V25.1084C12.583 24.704 12.2981 24.3556 11.9019 24.2752C10.5899 24.0088 9.35143 23.4942 8.221 22.7457C7.88398 22.5226 7.43632 22.5675 7.15043 22.8534L6.30476 23.6991L4.30089 21.6952L5.14667 20.8495C5.43245 20.5638 5.47757 20.116 5.25425 19.779C4.50573 18.6486 3.99108 17.4102 3.72468 16.0981C3.64431 15.7019 3.29584 15.4172 2.89148 15.4172H1.7004V12.5832H2.8916C3.29584 12.5832 3.64442 12.2984 3.72479 11.9022C3.9913 10.5902 4.50596 9.3517 5.25436 8.22138C5.47768 7.88424 5.43257 7.43658 5.14679 7.1508L4.301 6.30513L6.30487 4.30125L7.15054 5.14692C7.43644 5.43282 7.88421 5.47771 8.22112 5.25461C9.35155 4.50609 10.5899 3.99143 11.902 3.72515C12.2982 3.64478 12.5831 3.29631 12.5831 2.89195V1.70041H15.4171V2.89161C15.4171 3.29597 15.7019 3.64432 16.0982 3.72481C17.4102 3.99121 18.6486 4.50575 19.779 5.25427C20.116 5.47737 20.5637 5.43248 20.8496 5.14658L21.6952 4.30091L23.6992 6.3049L22.8534 7.15057C22.5677 7.43635 22.5225 7.88413 22.7459 8.22115C23.4945 9.35159 24.009 10.5899 24.2752 11.9019C24.3557 12.2982 24.704 12.5829 25.1085 12.5829H26.2996V15.417H25.1083C24.7039 15.4171 24.3556 15.7018 24.2751 16.0981Z" fill="#092A4A" />
						<path d="M14 10C11.7944 10 10 11.7944 10 14C10 16.2056 11.7944 18 14 18C16.2056 18 18 16.2056 18 14C18 11.7944 16.2056 10 14 10ZM14 16.3099C12.7263 16.3099 11.6901 15.2737 11.6901 14C11.6901 12.7263 12.7263 11.6901 14 11.6901C15.2737 11.6901 16.3099 12.7263 16.3099 14C16.3099 15.2737 15.2737 16.3099 14 16.3099Z" fill="#092A4A" />
					</g>
				</svg>
				<!-- <svg class="s-quote__cogs" width="82" height="50" viewBox="0 0 82 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<g class="gear gear--1">
						<path d="M22.5 9C16.1588 9 11 14.3831 11 21C11 27.6169 16.1588 33 22.5 33C28.8412 33 34 27.6169 34 21C34 14.3831 28.8412 9 22.5 9ZM22.5 30.3333C17.5681 30.3333 13.5556 26.1465 13.5556 21C13.5556 15.8535 17.5681 11.6667 22.5 11.6667C27.4319 11.6667 31.4444 15.8535 31.4444 21C31.4444 26.1465 27.4319 30.3333 22.5 30.3333Z" fill="#092A4A" />
						<path d="M40.0756 32.3945L38.5517 30.9051C39.4213 29.4502 40.079 27.9038 40.5156 26.2876H42.664C43.402 26.2876 44 25.703 44 24.9819V18.0183C44 17.2972 43.402 16.7126 42.664 16.7126H40.5156C40.079 15.0962 39.4211 13.5499 38.5517 12.0951L40.0756 10.6057C40.5974 10.0958 40.5974 9.26906 40.0756 8.75933L35.0372 3.83537C34.5152 3.32546 33.6694 3.32546 33.1477 3.83537L31.6239 5.32454C30.1354 4.4748 28.553 3.83206 26.899 3.4052V1.30567C26.899 0.584593 26.301 0 25.5629 0H18.4374C17.6994 0 17.1014 0.584593 17.1014 1.30567V3.4052C15.4474 3.83206 13.865 4.4748 12.3763 5.32454L10.8525 3.83537C10.6019 3.59043 10.2622 3.4529 9.90766 3.4529C9.55317 3.4529 9.21364 3.59043 8.96282 3.83537L3.92437 8.75915C3.40261 9.26906 3.40261 10.0958 3.92437 10.6055L5.44834 12.0949C4.57867 13.5496 3.92099 15.096 3.48437 16.7124H1.33603C0.598008 16.7124 0 17.297 0 18.0181V24.9817C0 25.7028 0.598008 26.2874 1.33603 26.2874H3.48437C3.92099 27.9038 4.57885 29.4502 5.44834 30.9049L3.92437 32.3943C3.40261 32.9042 3.40261 33.7309 3.92437 34.2407L8.96264 39.1646C9.21328 39.4096 9.55299 39.5471 9.90748 39.5471C10.262 39.5471 10.6015 39.4096 10.8523 39.1646L12.3761 37.6755C13.8648 38.5252 15.4472 39.1679 17.1012 39.5948V41.6943C17.1012 42.4154 17.6992 43 18.4372 43H25.5628C26.3008 43 26.8988 42.4154 26.8988 41.6943V39.5948C28.5528 39.1679 30.1352 38.5252 31.6237 37.6755L33.1475 39.1646C33.6694 39.6745 34.5152 39.6745 35.037 39.1646L40.0754 34.2407C40.5972 33.7311 40.5972 32.9044 40.0756 32.3945ZM38.1466 24.7221C37.7281 26.7369 36.9196 28.6386 35.7433 30.3747C35.3924 30.8924 35.4633 31.5799 35.9124 32.0188L37.2415 33.3175L34.0923 36.395L32.7634 35.0963C32.3138 34.6573 31.6105 34.5883 31.0811 34.9309C29.3047 36.0805 27.3587 36.8708 25.297 37.2798C24.6742 37.4034 24.2267 37.9383 24.2267 38.5593V40.3887H19.7733V38.5593C19.7733 37.9383 19.3256 37.4032 18.703 37.2798C16.6413 36.8706 14.6951 36.0805 12.9187 34.9309C12.3891 34.5883 11.6857 34.6573 11.2364 35.0963L9.90748 36.395L6.75854 33.3176L8.08763 32.0189C8.53671 31.5801 8.60761 30.8924 8.25668 30.3748C7.08044 28.639 6.27169 26.7371 5.85307 24.7221C5.72677 24.1137 5.17917 23.6764 4.54376 23.6764H2.67206V19.3241H4.54393C5.17917 19.3241 5.72695 18.8868 5.85325 18.2784C6.27205 16.2635 7.08079 14.3615 8.25686 12.6257C8.60779 12.1079 8.53689 11.4205 8.08781 10.9816L6.75872 9.68287L9.90766 6.60549L11.2366 7.9042C11.6858 8.34325 12.3895 8.41219 12.9189 8.06958C14.6953 6.92007 16.6413 6.1297 18.7032 5.72076C19.3258 5.59733 19.7735 5.06218 19.7735 4.4412V2.61135H24.2269V4.44068C24.2269 5.06166 24.6744 5.59664 25.2971 5.72024C27.3589 6.12935 29.3049 6.91955 31.0813 8.06906C31.6109 8.41167 32.3143 8.34273 32.7636 7.90367L34.0925 6.60497L37.2416 9.68252L35.9126 10.9812C35.4635 11.4201 35.3926 12.1078 35.7435 12.6253C36.9199 14.3614 37.7285 16.2631 38.1468 18.2779C38.2732 18.8865 38.8206 19.3238 39.4562 19.3238H41.3279V23.676H39.4559C38.8205 23.6762 38.2731 24.1135 38.1466 24.7221Z" fill="#092A4A" />
						<path d="M22 14C18.6915 14 16 17.1401 16 21C16 24.8599 18.6915 28 22 28C25.3085 28 28 24.8599 28 21C28 17.1401 25.3085 14 22 14ZM22 25.0423C20.0895 25.0423 18.5352 23.229 18.5352 21C18.5352 18.771 20.0895 16.9577 22 16.9577C23.9105 16.9577 25.4648 18.771 25.4648 21C25.4648 23.229 23.9105 25.0423 22 25.0423Z" fill="#092A4A" />
					</g>
					<g class="gear gear--2">
						<path d="M62.6885 36.1888H61.1114C60.8812 34.4925 60.2123 32.9344 59.221 31.633L60.3385 30.5154C60.8506 30.0033 60.8506 29.1731 60.3383 28.6612C59.8265 28.149 58.9962 28.1491 58.4842 28.6612L57.3667 29.7787C56.0653 28.7874 54.5071 28.1185 52.811 27.8883V26.3112C52.811 25.5871 52.2241 25 51.4998 25C50.7755 25 50.1887 25.5871 50.1887 26.3112V27.8883C48.4923 28.1185 46.9343 28.7874 45.6329 29.7787L44.5155 28.6612C44.0034 28.1491 43.1732 28.149 42.6613 28.6612C42.1491 29.1733 42.1491 30.0033 42.6611 30.5154L43.7786 31.633C42.7874 32.9344 42.1185 34.4925 41.8882 36.1888H40.3112C39.5869 36.1888 39 36.7759 39 37.5C39 38.2241 39.5869 38.8112 40.3112 38.8112H41.8882C42.1185 40.5075 42.7874 42.0656 43.7786 43.367L42.6611 44.4846C42.1491 44.9967 42.1491 45.8269 42.6613 46.3388C42.9173 46.5949 43.2529 46.7229 43.5884 46.7229C43.9239 46.7229 44.2595 46.5949 44.5157 46.3388L45.6331 45.2213C46.9345 46.2126 48.4927 46.8815 50.1888 47.1117V48.6888C50.1888 49.4129 50.7757 50 51.5 50C52.2243 50 52.8112 49.4129 52.8112 48.6888V47.1117C54.5075 46.8815 56.0655 46.2126 57.3669 45.2213L58.4843 46.3388C58.7405 46.5949 59.0759 46.7229 59.4116 46.7229C59.7471 46.7229 60.0827 46.5949 60.3387 46.3388C60.8509 45.8268 60.8509 44.9967 60.3389 44.4846L59.2214 43.367C60.2126 42.0656 60.8815 40.5075 61.1117 38.8112H62.6888C63.4131 38.8112 64 38.2241 64 37.5C64 36.7759 63.4128 36.1888 62.6885 36.1888ZM51.4998 44.5804C47.5957 44.5804 44.4195 41.4042 44.4195 37.5C44.4195 33.5958 47.5957 30.4196 51.4998 30.4196C55.404 30.4196 58.5801 33.5958 58.5801 37.5C58.5801 41.4042 55.404 44.5804 51.4998 44.5804Z" fill="#092A4A" />
						<path d="M51.5 33C49.0187 33 47 35.0187 47 37.5C47 39.9813 49.0187 42 51.5 42C53.9813 42 56 39.9813 56 37.5C56 35.0187 53.9813 33 51.5 33ZM51.5 39.1277C50.6025 39.1277 49.8723 38.3975 49.8723 37.5C49.8723 36.6025 50.6025 35.8723 51.5 35.8723C52.3975 35.8723 53.1277 36.6025 53.1277 37.5C53.1277 38.3975 52.3975 39.1277 51.5 39.1277Z" fill="#092A4A" />
					</g>
					<g class="gear gear--3">
						<path d="M67 8C63.1403 8 60 10.9159 60 14.5C60 18.0841 63.1403 21 67 21C70.8597 21 74 18.0841 74 14.5C74 10.9159 70.8597 8 67 8ZM67 18.5316C64.6058 18.5316 62.6582 16.723 62.6582 14.5C62.6582 12.277 64.6058 10.4684 67 10.4684C69.3942 10.4684 71.3418 12.277 71.3418 14.5C71.3418 16.723 69.3942 18.5316 67 18.5316Z" fill="#092A4A" />
						<path d="M79.3395 11.8987L79.3257 11.8982C79.0014 10.461 78.4137 9.11869 77.6148 7.91878L77.6281 7.90497C78.6558 6.88912 78.6486 5.2544 77.6067 4.24709C77.0983 3.7558 76.4226 3.48508 75.7038 3.48508C75.7036 3.48508 75.704 3.48508 75.7038 3.48508C74.9951 3.48508 74.328 3.74812 73.8224 4.22644L73.8081 4.23907C72.5668 3.46682 71.1783 2.89877 69.6916 2.58523L69.691 2.57193C69.6744 1.15129 68.4739 0.000170586 67 0C65.5263 0 64.3256 1.15111 64.309 2.57193L64.3084 2.58523C62.8217 2.89894 61.433 3.46682 60.1919 4.23907L60.1776 4.22627C59.1263 3.23278 57.4357 3.23977 56.3933 4.24709C55.3514 5.2544 55.3443 6.88895 56.3718 7.90497L56.3852 7.91895C55.5863 9.11886 54.9988 10.4612 54.6743 11.8984L54.6605 11.8989C53.1907 11.9148 52 13.0756 52 14.5002C52 15.9249 53.1908 17.0856 54.6609 17.1014L54.6743 17.102C54.9986 18.5391 55.5863 19.8813 56.3852 21.0812L56.3718 21.0952C55.3442 22.1112 55.3514 23.7458 56.3933 24.7529C56.9015 25.2444 57.5774 25.5149 58.2962 25.5149C58.2964 25.5149 58.2962 25.5149 58.2964 25.5149C59.0051 25.5149 59.6718 25.2519 60.1776 24.7737L60.1919 24.7609C61.433 25.5332 62.8217 26.1011 64.3084 26.4148L64.309 26.4281C64.3256 27.8489 65.5263 29 66.9998 29C67 29 67 29 67.0002 29C67.719 29 68.3947 28.7295 68.9029 28.2382C69.4041 27.7537 69.6833 27.1119 69.691 26.4282L69.6916 26.4149C71.1783 26.1014 72.5668 25.5333 73.8081 24.7611L73.8226 24.7739C74.8737 25.7674 76.5646 25.7602 77.6067 24.7531C78.6488 23.7458 78.6558 22.111 77.6281 21.0952L77.6148 21.0814C78.4137 19.8815 79.0012 18.5393 79.3257 17.1021L79.3391 17.1016C80.8092 17.0857 82 15.9251 82 14.5003C82 13.0754 80.8093 11.9148 79.3395 11.8987ZM66.9998 24.1381C61.4932 24.1381 57.0291 19.823 57.0291 14.5C57.0291 9.17703 61.4932 4.86188 66.9998 4.86188C72.5065 4.86188 76.9705 9.17703 76.9705 14.5C76.9705 19.823 72.5065 24.1381 66.9998 24.1381Z" fill="#092A4A" />
					</g>
				</svg> -->
				<h2 class="s-quote__text" data-aos="fade-in" data-aos-duration="600"><?php echo wp_kses($quote['text'], ['br' => ['class' => true]]); ?></h2>
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

			<section class="s-solutions" id="about">
				<div class="s-solutions__wrap l-wrap">
					<div class="s-solutions__inner">
						<?php foreach ($solutions as $item) : ?>
							<div class="solution-card info-card">
								<?php if ($item['image']) : ?>
									<div class="solution-card-image">
										<?php echo wp_get_attachment_image($item['image'], [200, 308]); ?>
									</div>
								<?php endif; ?>
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


	<section class="s-services" id="services">
		<div class="s-services__wrap l-wrap">
			<div class="s-services__header l-frame-x">
				<svg viewBox="-30 -30 60 60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="galaxy_icon" data-name="Galaxy icon">
					<title>Galaxy icon</title>
					<g id="galaxy_3d">
						<g id="galaxy_rotor">
							<g transform="translate(.25 -.5)">
								<circle class="galaxy_core" r="8.7"></circle>
								<circle class="galaxy_core" r="12.7"></circle>
								<path id="galaxy_limb" d="M0-14.2c9 0 15.8 7.5 15.8 17 0 12.5-12 18.3-21 18.3-12 0-21.3-12-21.3-25 0-14 12-23 22.7-25.6q-11 4.5-11.7 7.5c-5 4-8.7 10-8.7 19 0 10 9 21 18 21 14 0 20-9 19.5-22z"></path>
								<use xlink:href="#galaxy_limb" transform="scale(-1)"></use>
							</g>
						</g>
					</g>
				</svg>
				<h2 class="section-title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html($services['title']); ?></h2>
			</div>
			<div class="s-services__body l-frame-x">
				<div class="services-img glitch-image">
					<?php echo wp_get_attachment_image($services['image'], 'full', '', ['class' => ' services-img__item ']); ?>
				</div>
				<ol class="services-list">
					<?php foreach ($services['items'] as $key => $item) : ?>
						<li
							class="services-list__item icon-list__item<?php echo $key === 0 ? ' services-list__item--active' : ''; ?>"
							data-service-target="<?php echo esc_attr((string) $key); ?>"
							style="--glitch-offset <?= $key * 12 ?>">
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
				<?php
				$image_url  = wp_get_attachment_image_url($cases['bg_image'], 'full');
				echo wp_get_attachment_image($cases['bg_image'], 'full', '', ['class' => 'cases-slider-bg']); ?>
				<div class="cases-slider swiper-wrapper">

					<?php foreach ($cases['items'] as $key => $item) :
						switch ($key % 3) {
							case 0:
								$position = 'left center';
								break;

							case 1:
								$position = 'center center';
								break;

							case 2:
								$position = 'right center';
								break;
						}
					?>
						<div class="cases-slide swiper-slide">
							<div class="cases-slide-card">
								<div class="cases-slide__desc">
									<p><?php echo esc_html($item['description']); ?></p>
								</div>
								<div class="cases-slide__visual" style="
								--case-bg-mobile: url('<?php echo esc_url($image_url); ?>');
								--case-bg-position: <?php echo esc_attr($position); ?>; ">
									<div class="cases-slide__card">
										<p><?php echo esc_html($item['result']); ?></p>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="cases-dots" id="casesDots"></div>
		</div>
	</section>

	<section class="s-process s-process--top" id="process">
		<div class="s-process__wrap l-wrap">
			<div class="s-process__header l-frame-x">
				<div class="pulse-icon">
					<div class="icon-wrap">

					</div>
					<div class="elements">
						<div class="circle circle-outer"></div>
						<div class="circle circle-inner"></div>
						<div class="pulse pulse-1"></div>
						<div class="pulse pulse-2"></div>
						<div class="pulse pulse-3"></div>
					</div>
				</div>

				<h2 class="section-title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html($process['title']); ?></h2>
			</div>

		</div>
	</section>
	<section class="s-process s-process--bottom">
		<div class="s-process__wrap l-wrap">
			<div class="s-process__steps ">
				<?php foreach ($process['items'] as $item) : ?>
					<div class="process-step info-card">
						<?php if (!empty($item['image'])): ?>
							<div class="process-step--image">
								<?php echo wp_get_attachment_image($item['image'], [200, 173], '', ['class' => 'process-step--image__item']); ?>
							</div>
						<?php endif; ?>
						<div class="process-step__top info-card__body">
							<span class="tag c-tag"><?php echo esc_html($item['tag']); ?></span>
							<h3 class="process-step__title card-title"><?php echo esc_html($item['title']); ?></h3>
							<p class="process-step__text card-text"><?php echo esc_html($item['text']); ?></p>
						</div>
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
