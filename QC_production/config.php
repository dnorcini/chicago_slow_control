<?php
// config.php
// Site settings. Committed to git, so no secrets here: the DB credentials are
// in db_config.php (git-ignored) or in env vars.

const SITE_TITLE      = 'Production';
const WEB_BG_COLOUR   = 'white';
const WEB_TEXT_COLOUR = 'black';
const TIMEZONE        = 'America/New_York';      // the server's time zone
const APP_VERSION     = '2.0';                   // shown in the page footer; raise it when a new version goes live

// Uploaded files: <stage folder>/<item folder>/<slot>.<ext>, e.g. edit_die/die_5/image1_file.png.
// In the container it is its own mount, outside the code and the web root
// (env UPLOAD_DIR=/data/uploads, see targetOS/). Without the env var: uploads/ here.
define('UPLOAD_DIR', rtrim(getenv('UPLOAD_DIR') ?: __DIR__ . '/uploads', '/'));
