<?php

namespace Framework\View\Engine;

use Framework\View\Manager;
use Framework\View\View;

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
     * @param View $view
     * @return string The fully rendered content of the template as a string (usually HTML).
     */
    public function render(View $view): string;
    public function setManager(Manager $manager): static;
}