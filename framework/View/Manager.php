<?php

namespace Framework\View;

use Exception;
use Framework\View\Engine\Engine;

/**
 * Manager (The View Orchestrator)
 * -----------------------------
 * This class is the central brain of the view system. It manages where templates are located
 * and which rendering engine should be used for a given template extension.
 */
class Manager
{
    /**
     * @var array Stores all configured directories where views reside (e.g., ['/app/views', '/admin/views']).
     */
    protected array $paths = [];

    /**
     * @var array Maps file extensions to their corresponding Engine objects.
     * Key: extension string (e.g., 'basic.php'), Value: Engine object instance.
     */
    protected array $engines = [];

    /**
     * Adds a directory path to the list of locations where templates can be found.
     *
     * @param string $path The absolute or relative path to add.
     * @return static Returns the Manager instance itself, allowing for method chaining.
     */
    public function addPath(string $path): static
    {
        $this->paths[] = $path;
        return $this; // Return 'static' (the current object)
    }

    /**
     * Registers a specific rendering engine against a file extension.
     *
     * @param string $extension The file extension identifier (e.g., 'basic.php').
     * @param Engine $engine An instance of an Engine class that implements the Engine interface.
     * @return static Returns the Manager instance itself, allowing for method chaining.
     */
    public function addEngine(string $extension, Engine $engine): static
    {
        $this->engines[$extension] = $engine;
        return $this; // Return 'static' (the current object)
    }

    /**
     * Attempts to render a template by checking all registered paths against all registered engines.
     *
     * @param string $template The name of the template file (e.g., 'home').
     * @param array $data Data to inject into the template during rendering.
     * @return string The final rendered HTML content.
     * @throws Exception If no matching template/engine combination is found.
     */
    public function render(string $template, array $data = []): string
    {
        // Loop through every registered engine (e.g., BasicEngine, PhpEngine)
        foreach ($this->engines as $extension => $engine) {
            // For each engine, check every known path
            foreach ($this->paths as $path) {
                // Construct the full file name: /path/to/views/template_name.extension
                $file = "{$path}/{$template}.{$extension}";

                // Check if a file actually exists at this location!
                if (is_file($file)) {
                    // Found it! Delegate the rendering job to this specific engine instance.
                    return $engine->render($file, $data);
                }
            }
        }

        // If the loops complete without finding a match, throw an error.
        throw new Exception("Unable to render template '{$template}'. Check paths and extensions.");
    }
}