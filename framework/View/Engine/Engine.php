<?php

namespace Framework\View\Engine;

/**
 * Interface Engine
 * ------------------
 * Defines the contract that all view rendering engines must adhere to.
 * Any class implementing this interface promises to provide a 'render' method,
 * ensuring consistency across different template processing methods (e.g., Basic, PHP, Twig).
 */
interface Engine
{
    /**
     * Renders a specific template file using the engine's logic.
     *
     * @param string $path The full path to the template file (e.g., /views/home.php).
     * @param array $data An associative array of data to inject into the template.
     * @return string The fully rendered content of the template as a string (usually HTML).
     */
    public function render(string $path, array $data = []): string;
}