<?php
/**
 * Import cases CPT content. Idempotent — run via:
 *   wp eval-file /scripts/import/import-cases.php
 */

require_once __DIR__ . '/lib/common.php';

$w4m_cases = require __DIR__ . '/data/cases.php';

w4m_import_run_cpt( 'Cases', 'cases', 'case', 'group_6ba8f99a', $w4m_cases );
