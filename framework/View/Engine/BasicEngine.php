<?php

namespace Framework\View\Engine;

/**
 * BasicEngine
 * ------------
 * Implements the Engine interface using simple file reading and string replacement.
 * This engine is ideal for templates that only use placeholders like { key }.
 */
class BasicEngine implements Engine
{
    /**
     * Renders a template by reading its contents and replacing placeholders.
     *
     * @param string $path The full path to the template file.
     * @param array $data Associative data array containing variables for replacement.
     * @return string The rendered content with all { key } placeholders replaced.
     */
    public function render(string $path, array $data = []): string
    {
        // 1. Read the entire file contents into a single string variable.
        $contents = file_get_contents($path);

        // 2. Iterate over all provided data keys/values.
        foreach ($data as $key => $value) {
            // 3. Replace placeholders in the format "{ key }" with the actual value.
            $contents = str_replace(
                "{ $key }", $value, $contents
            );
        }

        return $contents; // Return the final string content.
    }
}