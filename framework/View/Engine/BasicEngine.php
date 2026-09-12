<?php

namespace Framework\View\Engine;

use Framework\View\View;

/**
 * BasicEngine
 * ------------
 * Implements the Engine interface using simple file reading and string replacement.
 * This engine is ideal for templates that only use placeholders like { key }.
 */
class BasicEngine implements Engine
{
    // Enables dependency injection to access Manager methods (resolve, macros, paths) without hard-coupling.
    use HasManager;

    /**
     * Renders a template by reading its contents and replacing placeholders.
     *
     * @param View $view
     * @return string The rendered content with all { key } placeholders replaced.
     */
    public function render(View $view): string
    {
        // 1. Read the entire file contents into a single string variable.
        $contents = file_get_contents($view->path);

        // 2. Iterate over all provided data keys/values.
        foreach ($view->data as $key => $value) {
            // 3. Replace placeholders in the format "{ key }" with the actual value.
            $contents = str_replace(
                "{$key}", $value, $contents
            );
        }

        return $contents; // Return the final string content.
    }
}