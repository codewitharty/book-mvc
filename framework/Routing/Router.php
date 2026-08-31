<?php

namespace Framework\Routing;

use Exception;
use Throwable;

/**
 * Manages routing by collecting defined routes and handling incoming requests.
 */
class Router
{
    /** @var array $routes List of registered Route instances. */
    protected array $routes = []; // This array holds all the individual Route objects that have been registered with this router.
    /** @var array $errorHandlers Global error handler callbacks mapped by HTTP status code. */
    protected array $errorHandlers = []; // This map stores functions (callbacks) that dictate how specific HTTP errors (like 404 or 500) should be handled and displayed.
    /** @var Route|null $current The last matched route instance during a request. */
    protected ?Route $current; // This property keeps track of the specific Route object that was successfully matched for the current incoming request, allowing access to its details later on.

    /**
     * Registers a new route with the router.
     *
     * @param string $method The HTTP method for this route (e.g., GET, POST).
     * @param string $path The URL pattern to match against incoming requests.
     * @param callable $handler The callback function invoked when the route matches.
     * @return Route Returns the newly created Route instance.
     */
    public function add(
        string   $method,
        string   $path,
        callable $handler
    ): Route
    {
        // Create a new Route object using the provided method, path pattern, and handler callback.
        $route = new Route($method, $path, $handler);

        // Add this newly created route instance to the internal list of registered routes.
        $this->routes[] = $route;

        return $route; // Return the fully configured Route object for convenience.
    }

    /**
     * Dispatches an incoming request to the appropriate route handler.
     */
    public function dispatch()
    {
        // First, retrieve a list of all defined path patterns from the registered routes.
        $paths = $this->paths();

        // Determine the HTTP method being used for the current request, defaulting to 'GET' if not available in $_SERVER.
        $requestedMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // Determine the URI path requested by the client, defaulting to '/' if not available in $_SERVER.
        $requestPath = $_SERVER['REQUEST_URI'] ?? '/';

        // Search through all registered routes to find the first one that matches both the requested method and the request path.
        $matching = $this->match($requestedMethod, $requestPath);

        if ($matching) {
            // If a matching route was found, store it in the current property for later access.
            $this->current = $matching;

            try {
                // Attempt to execute the handler function of the matched route. This action might throw an exception if the handler fails internally.
                return $matching->dispatch(); // Return the result from the successful route dispatch.
            } catch (Throwable $e) {
                // If any Throwable (Exception or Error) occurs during dispatch, we catch it here instead of letting it crash the application immediately.
                return $this->dispatchError(); // Delegate error handling to our global 500 handler.
            }
        }

        // Check if the requested path exists in our list of defined paths, even if the method didn't match (e.g., requesting GET /home but only POST /home is defined).
        if (in_array($requestPath, $paths)) {
            // If the path exists but no matching method was found, dispatch a "Not Allowed" error response.
            return $this->dispatchNotAllowed();
        }

        // If neither an exact match nor a path existence check passed, then we have a true 404 scenario.
        return $this->dispatchNotFound(); // Dispatch the standard "Not Found" error response.
    }

    /** @return array Returns all registered path patterns. */
    public function paths(): array
    {
        $paths = []; // Initialize an empty array to hold all the route paths.
        // Iterate over every Route object stored in the internal $routes array.
        foreach ($this->routes as $route) {
            // Add the path string from each route instance into our collection.
            $paths[] = $route->path();
        }

        return $paths; // Return the complete list of all registered URL patterns.
    }

    /**
     * Finds and returns the first matching Route for the given method and path.
     *
     * @param string $method The HTTP method to match against routes.
     * @param string $path The URL path to match against routes.
     * @return ?Route Returns a matched Route instance or null if no match found.
     */
    public function match(string $method, string $path): ?Route
    {
        // Loop through every registered route in the router's collection.
        foreach ($this->routes as $route) {
            // Use the Route object's 'matches' method to check if this specific route fits the incoming request details.
            if ($route->matches($method, $path)) {
                return $route; // As soon as a match is found, return that Route instance immediately (since we prioritize order).
            }
        }

        // If the loop completes without finding any matching route, return null to signify no match was made.
        return null;
    }

    /**
     * Registers a custom error handler for the specified HTTP status code.
     *
     * @param int $code The HTTP status code (e.g., 400, 404, 500).
     * @param callable $handler The callback function to invoke on that error type.
     */
    public function errorHandler(int $code, callable $handler): void
    {
        // Store the provided handler function in the $errorHandlers array, keyed by its corresponding HTTP status code.
        $this->errorHandlers[$code] = $handler;
    }

    /**
     * Dispatches a 400 (Bad Request) response.
     */
    public function dispatchNotAllowed()
    {
        // Use the null coalescing assignment operator (??=) to set a default handler for code 400 if one hasn't been explicitly registered yet. The default is an anonymous function returning 'not allowed'.
        $this->errorHandlers[400] ??= fn() => 'Not Allowed';
        // Execute the stored handler for status code 400 and return its result (the error message/content).
        return $this->errorHandlers[400]();
    }

    /**
     * Dispatches a 404 (Not Found) response.
     */
    public function dispatchNotFound()
    {
        // Set a default handler for code 404 if it doesn't exist, defaulting to 'not found'.
        $this->errorHandlers[404] ??= fn() => 'Not Found';
        // Execute the stored handler for status code 404 and return its result.
        return $this->errorHandlers[404]();
    }

    /**
     * Dispatches a 500 (Server Error) response.
     */
    public function dispatchError()
    {
        // Set a default handler for code 500 if it doesn't exist, defaulting to 'server error'.
        $this->errorHandlers[500] ??= fn() => 'Server Error';
        // Execute the stored handler for status code 500 and return its result.
        return $this->errorHandlers[500]();
    }

    /**
     * Performs an HTTP redirect to the given path.
     * Does not return - terminates execution after setting headers.
     *
     * @param string $path The destination URL path for the redirect.
     */
    public function redirect(string $path): void
    {
        // Send a Location header instructing the browser to redirect to the specified path. We use $replace = true to overwrite any existing location headers, and $code = 301 for permanent redirection.
        header("Location: {$path}", $replace = true, $code = 301);
        exit; // Immediately terminate script execution after sending the redirect header.
    }

    /** @return ?Route Returns the currently matched Route instance. */
    public function current(): ?Route
    {
        // Return the stored route object that was successfully matched during the last dispatch cycle, or null if nothing was matched yet.
        return $this->current;
    }

    /**
     * Retrieves or sets a unique name for this route.
     *
     * @param string $name The desired name for the route. If null, returns the current name.
     * @param array $parameters
     * @return mixed Returns the stored route name (string) or the instance itself if a name was provided.
     * @throws Exception
     */
    public function route(string $name, array $parameters = []): string
    {
        // Iterate through all registered routes to find one matching the given name.
        foreach ($this->routes as $route) {
            if ($route->name() === $name) { // Check if this specific route's assigned name matches the requested name.
                $finds = []; // Array to hold the placeholders we need to replace in the path string (e.g., "{{id}}").
                $replaces = []; // Array to hold the actual values that will substitute the placeholders (e.g., ['123']).

                foreach ($parameters as $key => $value) {
                    // One set for required parameters: uses {{key}}.
                    $finds[] = "{{$key}}";
                    $replaces[] = $value;

                    // Another set for optional parameters: uses {{key}?}}.
                    $finds[] = "{{$key}?}}";
                    $replaces[] = $value;
                }

                $path = $route->path(); // Start with the original, normalized path string.
                $path = str_replace($finds, $replaces, $path); // Replace all placeholders in the path with their corresponding values.

                // Use regex to strip out any optional parameters that were *not* provided in the request (e.g., if 'slug?' was defined but no slug was passed).
                $path = preg_replace('#{[^}]+}#', '', $path); // This removes placeholders like {id?} or {slug?}.

                // A crucial check: we should ideally verify that all *required* parameters were provided, but for simplicity here, we just return the path.
                return $path; // Return the fully resolved and cleaned URL path string.
            }
        }

        throw new Exception('Route not found'); // If the loop finishes without finding a match by name, throw an exception indicating failure.
    }
}