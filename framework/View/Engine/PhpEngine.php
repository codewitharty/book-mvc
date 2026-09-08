<?php

namespace Framework\View\Engine;

use Exception;
use Framework\View\Manager;
use Framework\View\View;

/**
 * PhpEngine
 * --------
 * Implements the Engine interface by executing PHP code directly within the template file.
 * This allows for full procedural/object-oriented logic inside the view itself (e.g., loops, conditionals).
 */
class PhpEngine implements Engine
{
    // Enables dependency injection to access Manager methods (resolve, macros, paths) without hard-coupling.
    use HasManager;

    /**
     * @var array<string, string> Stores layout templates mapped by a unique key.
     * The key is typically the real path of the file that called extends(), and the value is the template name/path to wrap it in.
     */
    protected array $layouts = [];

    /**
     * Magic method to handle macro calls in templates (e.g., echo $this->alert('info', 'Hello!'));
     * ---------------------------------------------------------------------------------------
     * Allows templates to invoke registered macros directly without explicitly calling manager->useMacro().
     * When a template echoes '$this->myMacro(...)', PHP calls this method automatically.
     * It delegates the execution to the Manager's useMacro() method, enabling clean, fluent syntax!
     *
     * @param string $name The macro name called in the template (e.g., 'alert', 'card').
     * @param array $values Arguments passed to the macro closure.
     * @return Manager Returns whatever the macro closure returns (or void if it just outputs).
     * @throws Exception If the macro hasn't been registered.
     */
    public function __call(string $name, array $values)
    {
        return $this->manager->useMacro($name, ...$values);
    }

    /**
     * Renders a template by executing it using PHP's include mechanism and output buffering.
     * ----------------------------------------------------------------------------------
     * This method executes the view file, captures its output, and checks if a layout is defined for this specific rendering context.
     * If a layout exists, it wraps the content inside that layout before returning.
     *
     * @param View $view The View object containing the template path, data, and required layout key.
     * @return string The final rendered HTML content (either raw or wrapped in a layout).
     * @throws Exception
     */
    public function render(View $view): string
    {
        // Converts associative array keys from the View object into local variables in this scope.
        // If $view->data is ['user_name' => 'Alice'], then inside the template, you can now use $user_name directly!
        extract($view->data);

        // Start capturing output! All subsequent echoes/prints will go to memory instead of screen.
        ob_start();

        // Execute the template file. This runs all the PHP code inside it.
        include($view->path);

        // Retrieve everything that was captured by ob_start() into $contents.
        $contents = ob_get_contents();

        // Clean up! Tells PHP to clear the buffer, making it ready for the next render call.
        ob_end_clean();

        // We check if an entry exists in our $layouts map that specifically matches the path of the view being rendered ($view->path).
        if (isset($this->layouts[$view->path])) {
            // If it exists, we retrieve the associated layout template name/path.
            $layout = $this->layouts[$view->path];
            // A layout template was found for this specific view context!
            $contentsWithLayout = view($layout, array_merge(
                $view->data,
                ['contents' => $contents],
            ));

            return $contentsWithLayout;
        }

        return $contents; // Return the final string content captured from the buffer.
    }

    /**
     * Sets the layout template for this engine instance based on where it is called from.
     * ----------------------------------------------------------------------------------
     * Instead of just setting a static layout, this method uses debug_backtrace() to determine
     * the file path that initiated the call and maps the provided $template name to that specific file.
     * This allows for context-aware layout wrapping (e.g., 'admin/dashboard.php' gets wrapped in 'admin_layout.php').
     *
     * @param string $template The template name or path to use as the wrapper layout.
     * @return static Returns the current instance of PhpEngine for method chaining.
     */
    protected function extends(string $template): static
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        // Map the template name to the absolute path of the file that called 'extends()'.
        $this->layouts[realpath($backtrace[0]['file'])] = $template;
        return $this;
    }
}