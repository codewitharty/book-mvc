<?php

namespace Framework\Routing;

/**
 * Represents an HTTP route that matches incoming requests against a defined path.
 */
class Route
{
    /** @var string $method The HTTP method (GET, POST, PUT, DELETE, etc.) for this route. */
    protected string $method;
    /** @var string $path The URL pattern to match against incoming requests. */
    protected string $path;
    /** @var callable $handler The callback function invoked when the route matches. */
    protected $handler;

    /** @var array $parameters Captured parameter values from matched path segments. */
    protected array $parameters = [];

    protected ?string $name = null; // A nullable property used to assign a unique, human-readable name to this route, allowing us to reference it later without knowing its exact path or method.

    /**
     * Constructs a new Route instance with method, path, and handler.
     *
     * @param string $method The HTTP method for this route.
     * @param string $path The URL pattern to match.
     * @param callable $handler The callback function to invoke on match.
     */
    public function __construct(
        string   $method,
        string   $path,
        callable $handler
    )
    {
        // Set the HTTP method for this route instance.
        $this->method = $method;
        // Store the URL pattern that this route is designed to match against incoming requests.
        $this->path = $path;
        // Assign the callable function (the handler) that will be executed when a match occurs.
        $this->handler = $handler;
    }

    /** @return array Returns the current captured parameters. */
    public function parameters(): array
    {
        // Return an array containing all the dynamic parameter values captured during a successful route match.
        return $this->parameters;
    }

    /** @return string Returns the HTTP method for this route. */
    public function method(): string
    {
        // Return the specific HTTP method (e.g., 'GET', 'POST') associated with this route.
        return $this->method;
    }

    /** @return string Returns the URL pattern for this route. */
    public function path(): string
    {
        // Return the raw, defined URL pattern string for this route.
        return $this->path;
    }

    /**
     * Checks if the provided HTTP method and path match this route.
     * Supports both literal matches and parameterized patterns.
     *
     * @param string $method The incoming request's HTTP method.
     * @param string $path The incoming request's URL path.
     * @return bool True if there is a match, false otherwise.
     */
    public function matches(string $method, string $path): bool
    {
        // Check for an exact literal match first to avoid unnecessary regex processing overhead.
        if ($this->method === $method && $this->path === $path)
        {
            return true; // A perfect match was found immediately.
        }

        $parameterNames = []; // Initialize an array to store the names of captured route parameters (e.g., 'id', 'slug').

        // The normalisePath method ensures there's a '/' before and after the path, while also removing duplicate '/' characters.
        // Examples: -> '' becomes '/', -> 'home' becomes '/home/', -> 'product/{id}' becomes '/product/{id}/'.
        $pattern = $this->normalisePath($this->path); // Normalize the route's defined path into a standardized pattern string.

        // Use preg_replace_callback to convert custom path placeholders (like {name}) into standard regular expression syntax.
        $pattern = preg_replace_callback(
            '#{([^}]+)}/#', // This regex finds anything inside curly braces, capturing the content in group 1.
            function (array $found) use (&$parameterNames) {
                // Extract the parameter name from the match and remove any trailing '?' if it denotes optionality.
                $parameterNames[] = rtrim($found[1], '?');

                // If the original placeholder ended with a '?', this is an optional parameter, so we make the following slash optional as well in the regex.
                if (str_ends_with($found[1], '?')) {
                    return '([^/]*)(?:/?)'; // Matches zero or more non-slash characters, optionally followed by a trailing slash.
                }

                // For required parameters, match one or more non-slash characters followed by a mandatory trailing slash.
                return '([^/]+)/'; // Matches one or more non-slash characters, followed by a mandatory trailing slash.
            },

            $pattern, // Apply the replacement logic to the normalized path pattern.
        );

        // If there are no route parameters (no '+' or '*' in the final regex) and it wasn't an exact literal match, this route cannot possibly match the requested path.
        if (!str_contains($pattern, '+') && !str_contains($pattern, '*')) {
            return false; // No dynamic parts to match against, so fail fast.
        }

        // Attempt to match the incoming request path against the generated regex pattern.
        preg_match_all("#{$pattern}#", $this->normalisePath($path), $matches);

        $parameterValues = []; // Initialize an array to hold the actual values captured from the matched path segments.

        if (count($matches[1]) > 0) {
            // If the regex successfully found matches and captured groups (group 1 contains all parameter values).
            // We need to assemble these captured values before we can confirm a match.
            foreach ($matches[1] as $value) {
                $parameterValues[] = $value; // Populate the array with each matched value.
            }

            // Create an empty array filled with 'null' placeholders, matching the count of parameter names. This is crucial for handling optional parameters that might not have been present in the request path.
            $emptyValue = array_fill(0, count($parameterNames), null);

            // Use the += operator (array union assignment) to merge $emptyValue into $parameterValues. This ensures that if a parameter value was captured (e.g., 'id' is present), it keeps its value; otherwise, it defaults to null from $emptyValue.
            $parameterValues += $emptyValue;

            // Combine the collected parameter names with their corresponding values into an associative array and store it in the instance property.
            $this->parameters = array_combine($parameterNames, $parameterValues);

            return true; // A successful match was found and parameters have been stored.
        }

        // If preg_match_all ran but captured no groups (count($matches[1]) is 0), then the path did not match the pattern.
        return false;
    }

    /**
     * Normalizes the path by trimming and wrapping with slashes.
     *
     * @param string $path The raw URL pattern to normalize.
     * @return string Returns the normalized path.
     */
    private function normalisePath(string $path): string
    {
        // Remove any leading or trailing slashes from the input path string.
        $path = trim($path, '/');
        // Wrap the trimmed path with a mandatory leading and trailing slash to ensure consistency (e.g., 'home' becomes '/home/').
        $path = "/{$path}/";

        // Replace any sequence of two or more slashes ('//', '///', etc.) with a single slash, cleaning up redundant separators.
        return preg_replace('/[\/]{2,}/', '/', $path);
    }

    /**
     * Executes the route's handler function.
     */
    public function dispatch()
    {
        // Execute the stored callable handler using call_user_func(). This runs the actual logic associated with this route when it matches a request.
        return call_user_func($this->handler);
    }

    /**
     * Retrieves or sets a unique name for this route.
     *
     * @param string|null $name The desired name for the route. If null, returns the current name.
     * @return string|static|null Returns the stored route name (string) or the instance itself if a name was provided.
     */
    public function name(?string $name = null): static|string|null
    {
        // If a name is provided, set it on the instance and return the instance for chaining.
        if ($name) {
            $this->name = $name;
            return $this; // Return $this to allow method chaining (e.g., $router->add('GET', '/', fn() => 'Hello World!')->name('home')).
        }

        // If no name is provided, simply return the currently stored route name.
        return $this->name;
    }
}