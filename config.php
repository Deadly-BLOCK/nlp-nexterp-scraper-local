<?php

define('APP_ROOT',  __DIR__);
define('DATA_DIR',  __DIR__ . '/data');
define('POSTS_DIR', __DIR__ . '/posts');
define('LIB_DIR',   __DIR__ . '/lib');

define('NODE_BIN',   getenv('NODE_BIN')   ?: 'node');
define('SCRAPER_JS', __DIR__ . '/app.js');

define('USER_KEY_SECRET', getenv('NLP_KEY_SECRET') ?: 'CHANGE-ME-set-NLP_KEY_SECRET-env-to-a-long-random-string');

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_MODEL',   getenv('GEMINI_MODEL')   ?: 'gemini-2.5-flash');
define('GEMINI_TIMEOUT', 30);

define('NOTES_MAX',        200);
define('AI_HISTORY_MAX',   100);
define('PREFS_MAX_READIDS', 1000);

define('SESSION_NAME', 'nlp_sid');
