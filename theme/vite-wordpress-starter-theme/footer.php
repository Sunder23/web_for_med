  </div><!-- #content -->

  <?php
  $contant = get_field('front_page_contact');
  $footer = [
    'logo' => get_field('logo', 'options'),
    'cover' => get_field('cover', 'options'),
    'mail' => get_field('mail', 'options'),
    'socials' => get_field('socials', 'options'),
  ]
  ?>

  <footer class="footer" id="contacts">
    <div class="footer__form-section">
      <div class="l-wrap">
        <div class="footer__form-wrap">
          <div class="footer__form-content">
            <?php if (!empty($contant['title'])) : ?>
              <h2 class="footer__form-title"><?php echo esc_html($contant['title']); ?></h2>
            <?php endif; ?>
            <?php if (!empty($contant['text'])) : ?>
              <p class="footer__form-subtitle"><?php echo esc_html($contant['text']); ?></p>
            <?php endif; ?>
          </div>
          <?php if (!empty($contant['contact_form'])) : ?>
            <div class="contact-form">
              <?php echo do_shortcode($contant['contact_form']); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="footer__cover">
      <div class="l-wrap">
        <div class="l-frame-x">
          <?php echo wp_get_attachment_image($footer['cover'], 'full', '', ['class' => 'footer__cover-img']); ?>
        </div>
      </div>
    </div>

    <div class="footer__bar">
      <div class="footer__bar-wrap">
        <div class="footer__bar-inner">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="logo logo--sm">
            <?php echo wp_get_attachment_image($footer['logo'], 'full', '', ['class' => 'logo__img']); ?>
          </a>
          <nav class="footer__nav" aria-label="<?php esc_attr_e('Footer navigation', 'textdomaintomodify'); ?>">
            <?php
            wp_nav_menu(
              [
                'theme_location' => 'menu-main',
                'menu_id'        => 'footer-menu-main',
                'container'      => false,
                'fallback_cb'    => false,
              ]
            );
            ?>
          </nav>
          <div class="footer__contacts">
            <?php if (!empty($footer['mail'])) : ?>
              <a href="<?php echo esc_attr($footer['mail']['url']); ?>" class="footer__email" target="_blank" rel="noopener noreferrer"><?php echo esc_html($footer['mail']['title']); ?></a>
            <?php endif; ?>
            <?php if (!empty($footer['socials'])) : ?>
              <div class="footer__socials">
                <?php foreach ($footer['socials'] as $social) : ?>
                  <a href="<?php echo esc_url($social['link']); ?>" class="footer__social">
                    <?php echo wp_get_attachment_image($social['icon'], 'full', '', ['class' => 'footer__social-icon']); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </footer>
  </div>
  <?php wp_footer(); ?>
  </body>

  </html>