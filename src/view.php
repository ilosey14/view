<?php
/**
 * Model View Controller Class
 *
 * Page directory structure (unique to each page):
 *
 * | Script | Description |
 * |:-|:-|
 * | *Required* |
 * | `index.php`    | page logic - `View` instance lives here
 * | `content.*`    | html body content with given MVC scope
 * | *Optional* |
 * | `head.*`       | document head resources
 * | `libraries.*`  | any front-end libraries and their required content (these come after the page content)
 * | `scripts.*`    | additional inline scripts at the end of the document
 *
 * @package View
 */
class View {
    private $components = 'templates/components';
    private $scripts = 'public/src';
    private $shell = 'templates/shell.php';

    private $root = '/var/www';
    private $dir = '';
    private $title = '';

    private $pageHeaderSent = false;
    private $sendingPageHeader = false;

    private $vars = [];

    /**
     * Creates a new view object.
     * @param string $title The document title
     * @param string $dir The page source directory
     * @return View
     */
    public function __construct(string $title, string $dir) {
        // $this->root = $_SERVER['DOCUMENT_ROOT'] . '/..';

        $this->components = "$this->root/$this->components";
        $this->scripts = "$this->root/$this->scripts";
        $this->shell = "$this->root/$this->shell";

        $this->title = $title;
        $this->dir = $dir;
    }

    /**
     * Renders the page with the current value set.
     */
    public function render(): void {
        require $this->shell;

        if ($this->pageHeaderSent)
            ob_end_flush();
    }

    /**
     * Renders up to the page's body header (if specified).
     * This is useful for returning immediate content to the user
     * while database queries ane/or computation are taking place.
     * @see https://stackoverflow.com/a/4192086/12588503
     */
    public function renderPageHeader(): void {
        ob_start();

        // set flags to render beginning of page
        $this->sendingPageHeader = true;
        $this->render();
        $this->pageHeaderSent = true;

        // fill buffer to force send
        $ob_length = ob_get_length();

        if ($ob_length < 4096)
            echo str_repeat(' ', 4096 - $ob_length), '<!-- flush -->';

        ob_flush();
        flush();
    }

    /**
     * Sets a response header.
     * @param string $name Header name
     * @param string|array $value One or multiple header values
     */
    public function setHeader(string $name, $value): void {
        if (headers_sent()) return;

        if (count($value))
            $value = implode('; ', $value);

        header("$name: $value");
    }

    /**
     * Include a component in a template by name.
     * Components are globally available to any view and are restricted to the provided scope.
     * @param string $name The component name.
     * Its containing file should have the same name.
     * @param array $scope The component's inherited variable scope
     */
    private function includeComponent(string $name, array $scope = null): void {
        if ($ext = self::getFileExt("$this->components/$name")) {
            $__name = $name;
            $__ext = $ext;

            if ($scope) extract($scope);

            include "$this->components/$__name.$__ext";
        }
    }

    /**
     * Require a page resource by name, if available.
     * Resources are unique to a view directory and have access to the view template scope.
     * @param string $name The resource name
     */
    private function requireResource(string $name): void {
        if ($ext = self::getFileExt("$this->dir/$name")) {
            $__name = $name;
            $__ext = $ext;

            extract($this->vars);

            require_once "$this->dir/$__name.$__ext";
        }
    }

    /**
     * Embeds a JavaScript library into the markup.
     *
     * | Flag | Description |
     * |-|:-|
     * | `anonymous` | Wrap script in anonymous function |
     * | `compressed` | Compress the script |
     * | `path` | Specify a script path relative to the server root for the `$name` |
     *
     * @param string $name The script name
     * @param array $vars Name-value pairs to pass to the script as embedded variables
     * @param array $flags
     */
    private function embedScript(string $name, array $vars = null, array $flags = []): void {
        $path = in_array('path', $flags) ? "$this->root/$name" : "$this->scripts/$name.js";
        $anonymous = in_array('anonymous', $flags);
        $compress = in_array('compressed', $flags);
        $new_line = $compress ? '' : PHP_EOL;

        if (!file_exists($path)) {
            echo "<!-- script \"$name\" does not exist -->";
            return;
        }

        echo '<script>', $new_line;

        // anonymous
        if ($anonymous)
            echo '(function () {', $new_line;

        // insert vars
        if ($vars) {
            foreach ($vars as $name => $value)
                echo "var $name = ", json_encode($value), ';', $new_line;

            echo $new_line;
        }

        // script contents
        if ($compress)
            echo self::compressScript($path);
        else
            echo file_get_contents($path), $new_line;

        if ($anonymous)
            echo '})();', $new_line;

        echo '</script>';
    }

    /**
     * Set a template variable.
     * These are accessible in the page or template components.
     * @param string $name Variable name
     * @param mixed $value Variable value
     */
    public function __set(string $name, $value): void {
        $this->vars[$name] = $value;
    }

    /**
     * Gets a template variable.
     * @param string $name Name of an existing variable
     * @return mixed Existing variable value, otherwise null
     */
    public function __get(string $name) {
        return $this->vars[$name] ?? null;
    }

    /**
     * Returns the extension of the first file to match a filename.
     * @param string $filename The full path of a file excluding the extension
     * @return string|null The file extension or `null` if no files exists
     */
    private static function getFileExt(string $filename) {
        $files = glob("$filename.*");
        return count($files) ? pathinfo($files[0], PATHINFO_EXTENSION) : null;
    }

    /**
     * Removes comments and whitespace to compress script.
     * Limitations: This is a quick, loose replace and does not preserve
     * patterns inside string.
     * @param string $path The script path
     * @return string
     */
    private static function compressScript(string $path): string {
        return preg_replace(
            [
                '/\/\/.*?\n/',
                '/\/\*[\S\s\r\n]*?\*\//',
                '/[\s\r\n]+/'
            ],
            [
                '',
                '',
                ' '
            ],
            file_get_contents($path)
        );
    }
}