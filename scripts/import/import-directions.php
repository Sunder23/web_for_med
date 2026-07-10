<?php
/**
 * Import directions CPT content. Idempotent — run via:
 *   wp eval-file /scripts/import/import-directions.php
 */

require_once __DIR__ . '/lib/common.php';

$w4m_directions = require __DIR__ . '/data/directions.php';

w4m_import_run_cpt( 'Directions', 'directions', 'direction', 'group_41eea81b', $w4m_directions );
