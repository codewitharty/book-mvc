<?php

use Framework\View;

/**
 * Function view() - The Application Entry Point for Template Rendering
 * ---------------------------------------------------------------
 * This function acts as a global facade. When you call 'view('home', $data)', this is what runs.
 * It ensures the View Manager is set up once and then delegates the rendering task to it.
 *
 * @param string $template The name of the template file to load (e.g., 'about').
 * @param array $data Optional associative array containing variables for the view. Defaults to empty array.
 * @return string The final rendered content of the template.
 */
if (!function_exists('view')) {
    /**
     * @throws Exception
     */
    function view(string $template, array $data = []): string
    {
        static $manager; // The persistent system object that remembers its setup!

        // Check if the Manager has been initialized yet (i.e., is it null?)
        if (!$manager) {
            // 1. Instantiate the core rendering manager.
            $manager = new View\Manager();

            // 2. Configure Paths: Tell the manager where to look for templates.
            // __DIR__ resolves to the current directory of this file.
            $manager->addPath(__DIR__ . '/../resources/views');

            // 3. Configure Engines: Register specific rendering tools against extensions.
            // We are telling the Manager: "If you see a '.basic.php' file, use BasicEngine!"
            $manager->addEngine('basic.php', new View\Engine\BasicEngine());
            $manager->addEngine('advanced.php', new View\Engine\AdvancedEngine());
            $manager->addEngine('php', new View\Engine\PhpEngine());

            $manager->addMacro('escape', fn($value) => htmlspecialchars($value));
            $manager->addMacro('includes', fn(...$params) => print view(...$params));
        }

        // 4. Delegate the work! Ask the persistent Manager to resolve the template.
        return $manager->resolve($template, $data);
    }
}
