<?php

/**
 * Static site generator.
 *
 * @package \View\StaticView
 */
class StaticView {
    /**
     * Render a script to a static output file.
     * @param string $in_path The source file to render
     * @param string $out_path The rendered output file
     */
    public static function renderToFile(string $in_path = './index.php', string $out_path = './index.html'): void {
        if (ob_get_length()) ob_clean();

        ob_start();

        if (!file_exists($in_path))
            throw new RuntimeException("[Error] Failed to generate page from non-existent file \"$in_path\".");

        require_once $in_path;

        if (!file_put_contents($out_path, ob_get_clean()))
            throw new RuntimeException("[Error] Failed to generate page at \"$out_path\"");
    }
}