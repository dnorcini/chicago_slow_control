<?php
// config.php
// Site settings. Committed to git, so no secrets here: the DB credentials are
// in db_config.php (git-ignored) or in env vars.

const SITE_TITLE      = 'Production';
const WEB_BG_COLOUR   = 'white';
const WEB_TEXT_COLOUR = 'black';
const TIMEZONE        = 'America/New_York';      // the server's time zone
const UPLOAD_DIR      = __DIR__ . '/uploads';    // uploads/<stage folder>/<item folder>/<slot>.<ext>
