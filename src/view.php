<?php
/**
 * Model View Controller Class
 *
 * Page directory structure (unique to each page):
 *
 * Required
 * index.php        page logic - `View` instance lives here
 * content.*        html body content with given MVC scope
 *
 * Optional
 * head.*           document head resources
 * libraries.*      any front-end libraries and their required content (these come after the page content)
 * scripts.*        additional inline scripts at the end of the document
 *
 * @package View
 */
class View {
    private $shell = '/templates/shell.php';
    private $components = '/templates/components';

    private $root = '';
    private $dir = '';
    private $title = '';

    private $pageHeaderSent = false;
    private $sendingPageHeader = false;

    private $vars = [];

    /**
     * Creates a new view object.
     * @param string $title The page title
     * @param string $dir The page directory.
     * @return View
     */
    public function __construct(string $title, string $dir) {
        $this->root = $_SERVER['DOCUMENT_ROOT'] . '/test/view';

        $this->shell = "$this->root/$this->shell";
        $this->components = "$this->root/$this->components";

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
     * Renders up to the page's body header (is specified).
     * This is useful for returning immediate content to the user
     * while database queries ane/or computation is taking place.
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
     * @param string $name The component name.
     * Its containing file should have the same name.
     * @param array $scope
     */
    private function includeComponent(string $name, array $scope = null): void {
        if ($scope)
            extract($scope, EXTR_PREFIX_ALL, '');

        if ($ext = self::getFileExt("$this->components/$name"))
            include "$this->components/$name.$ext";
    }

    /**
     * Require a page resource by name, if available.
     * @param string $name The resource name.
     */
    private function requireResource(string $name): void {
        extract($this->vars, EXTR_PREFIX_ALL, '');

        if ($ext = self::getFileExt("$this->dir/$name"))
            require_once "$this->dir/$name.$ext";
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
}