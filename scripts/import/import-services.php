<?php
/**
 * Import services CPT content. Idempotent — run via:
 *   wp eval-file /scripts/import/import-services.php
 */

require_once __DIR__ . '/lib/common.php';

$w4m_services = require __DIR__ . '/data/services.php';

w4m_import_run_cpt( 'Services', 'services', 'service', 'group_9033ee91', $w4m_services );
