<?php
// DIRS
define('PUB_DIR', DIR . '/public_html');
define('BIN_DIR', DIR . '/bin');
define('COMP_DIR', DIR . '/components');
define('STYLE_DIR', DIR . '/styles');
define('DATA_DIR', DIR . '/data');
define('MAIN_TEMPLATE_FILE', COMP_DIR . '/layout/index.html');
define('BLOG_CSS_FILES', [
    'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/base16/gigavolt.min.css'
]);

define('BLOG_BASE_URL', 'https://api.dev.designly.biz/blog');
define('HTML_LAYOUT', COMP_DIR . '/layout/index.html');
define('HTML_SIDEBAR', COMP_DIR . '/layout/sidebar.html');
define('HTML_HEADER', COMP_DIR . '/layout/header.html');

// Load env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
$dotenv->required([]);

define('DEFAULT_ROUTE_FILE', DIR . '/pages/home.php');
define('CDN_URL', 'https://cdn.designly.biz');
