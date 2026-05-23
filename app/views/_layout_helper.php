<?php
// This file provides a layout rendering helper used by controllers
// It is NOT a standalone view — it is included by the layout/main.php
// The $content variable is set by each view file via output buffering

function render_with_layout(string $viewPath, array $data = []): void
{
    extract($data);
    ob_start();
    include $viewPath;
    $content = ob_get_clean();
    include __DIR__ . '/layout/main.php';
}
