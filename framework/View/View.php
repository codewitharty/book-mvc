<?php

namespace Framework\View;

use Framework\View\Engine\Engine;

/**
 * View
 * -----
 * Represents a single view instance, acting as the data container and rendering trigger.
 * This class holds all necessary information (path, data, engine) required to render a template.
 */
class View
{
    /**
     * Constructs a new View instance.
     *
     * @param Engine $engine The specific rendering engine responsible for executing the template (e.g., PhpEngine).
     * @param string $path The absolute file path to the template being viewed.
     * @param array<string, mixed> $data Associative data array containing variables to inject into the view scope. Defaults to an empty array.
     */
    public function __construct(protected Engine $engine, public string $path, public array $data = [])
    {
    }

    /**
     * Converts the View object into its string representation.
     * --------------------------------------------------
     * This magic method is automatically called whenever a View object is treated as a string (e.g., echo $view;).
     * It delegates the actual rendering task to the injected Engine instance, returning the final HTML output.
     *
     * @return string The fully rendered HTML content of the template.
     */
    public function __toString(): string
    {
        return $this->engine->render($this);
    }
}
