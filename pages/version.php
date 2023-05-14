<?php

$version = Page::hashDir(COMP_DIR) . Page::hashDir(STYLE_DIR);

if (isset($_SESSION['version']) && $_SESSION['version'] == $version) {
    echo '';
} else {
    $_SESSION['version'] = $version;
    echo 'reload';
}
