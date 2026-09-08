<?php

namespace Framework\View;

use Closure;
use Exception;
use Framework\View\Engine\Engine;
use Framework\View\Engine\HasMacro;

/**
 * Manager (The View Orchestrator)
 * -----------------------------
 * This class is the central brain of the view system. It manages where templates are located
 * and which rendering engine should be used for a given template extension.
 */
class Manager
{

    // Enables macro registration/execution (useful if Manager needs to handle macros directly)
    use HasMacro;

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
        $this->engines[$extension]->setManager($this);
        return $this; // Return 'static' (the current object)
    }

    /**
     * Locates and prepares a template for rendering.
     * -----------------------------------------
     * This method searches all configured paths across all registered engines to find the matching view file.
     * Once found, it instantiates and returns a View object ready to be rendered by its associated engine.
     *
     * @param string $template The base name of the template (e.g., 'homepage', 'user_profile').
     * @param array $data Associative data array containing variables to inject into the view.
     * @return View Returns a fully configured View object, ready to be rendered by its engine.
     * @throws Exception If no matching file can be found across all paths and extensions.
     */
    public function resolve(string $template, array $data = []): string
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
                    return new View($engine, realpath($file), $data);
                }
            }
        }

        // If the loops complete without finding a match, throw an error.
        throw new Exception("Could not resolve template '{$template}'. Check paths and extensions.");
    }
}