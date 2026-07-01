<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<?php $header = [
  'logo'          => get_field('logo', 'options'),
  'header_button' => get_field('header_button', 'options'),
  'mail'          => get_field('mail', 'options'),
  'socials'       => get_field('socials', 'options'),
];
?>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <div id="page" class="site">

    <header class="header" id="header">
      <div class="header__wrap">
        <div class="header__inner">
          <?php if (!empty($header['logo'])) : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
              <?php echo wp_get_attachment_image($header['logo'], 'full'); ?>
            </a>
          <?php endif; ?>

          <!-- Desktop nav — inside header for flex layout -->
          <nav class="nav nav--desktop" aria-label="<?php esc_attr_e('Main navigation', 'textdomaintomodify'); ?>">
            <?php
            wp_nav_menu([
              'theme_location' => 'menu-main',
              'menu_id'        => 'menu-main-desktop',
              'container'      => false,
              'fallback_cb'    => false,
            ]);
            ?>
          </nav>

          <?php if (!empty($header['header_button'])) : ?>
            <a href="<?php echo esc_url($header['header_button']['url']); ?>" class="btn btn--primary btn--sm header__cta">
              <?php echo esc_html($header['header_button']['title']); ?>
            </a>
          <?php endif; ?>

          <button class="burger" id="burger" aria-label="<?php esc_attr_e('Відкрити меню', 'textdomaintomodify'); ?>" aria-expanded="false" aria-controls="mainNav">
            <span></span><span></span><span></span>
          </button>
        </div>
      </div>
    </header>

    <!-- Mobile overlay nav — outside header so its z-index is compared against header in root context -->
    <nav class="nav nav--mobile-overlay" id="mainNav" aria-label="<?php esc_attr_e('Mobile navigation', 'textdomaintomodify'); ?>" aria-hidden="true">

      <div class="nav__links">
        <?php
        wp_nav_menu([
          'theme_location' => 'menu-main',
          'menu_id'        => 'menu-main-mobile',
          'container'      => false,
          'fallback_cb'    => false,
        ]);
        ?>
      </div>

      <div class="nav__mobile-footer">
        <?php if (!empty($header['socials']) || !empty($header['mail'])) : ?>
          <div class="nav__socials-row">
            <?php if (!empty($header['socials'])) : ?>
              <div class="nav__social-icons">
                <?php foreach ($header['socials'] as $social) : ?>
                  <a href="<?php echo esc_url($social['link']); ?>" class="nav__social-item" target="_blank" rel="noopener noreferrer">
                    <?php echo wp_get_attachment_image($social['icon'], 'full', '', ['class' => 'nav__social-icon']); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($header['mail'])) : ?>
              <a href="<?php echo esc_attr($header['mail']['url']); ?>" class="nav__email" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html($header['mail']['title']); ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($header['header_button'])) : ?>
          <div class="nav__cta-row">
            <a href="<?php echo esc_url($header['header_button']['url']); ?>" class="btn btn--primary nav__cta-btn">
              <?php echo esc_html($header['header_button']['title']); ?>
            </a>
          </div>
        <?php endif; ?>
      </div>

    </nav>

    <div id="content" class="site-content">
