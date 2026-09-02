<?php

namespace Framework\View\Engine;

/**
 * PhpEngine
 * --------
 * Implements the Engine interface by executing PHP code directly within the template file.
 * This allows for full procedural/object-oriented logic inside the view itself (e.g., loops, conditionals).
 */
class PhpEngine implements Engine
{
    /**
     * @var string Stores the path of the currently rendering template file.
     */
    protected string $path;

    /**
     * Renders a template by executing it using PHP's include mechanism and output buffering.
     *
     * @param string $path The full path to the template file.
     * @param array $data Associative data array containing variables for injection.
     * @return string The content captured from the buffer after the template runs.
     */
    public function render(string $path, array $data = []): string
    {
        // Store the path so we know what file was rendered.
        $this->path = $path;

        // Crucial step: Converts associative array keys into local variables in this scope.
        // If $data is ['user_name' => 'Alice'], then inside the template, you can just use $user_name.
        extract($data);

        // Start capturing output! All subsequent echoes/prints will go to memory instead of screen.
        ob_start();

        // Execute the template file. This runs all the PHP code inside it.
        include($this->path);

        // Retrieve everything that was captured by ob_start() into $contents.
        $contents = ob_get_contents();

        // Clean up! Tells PHP to clear the buffer, making it ready for the next render call.
        ob_end_clean();

        return $contents; // Return the final string content captured from the buffer.
    }
}