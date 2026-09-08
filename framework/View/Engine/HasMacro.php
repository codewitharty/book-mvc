<?php

namespace Framework\View\Engine;

use Closure;
use Exception;
use Framework\View\Manager;

/**
 * HasMacros
 * ---------
 * A trait that enables engines to register and execute macros without hard-coupling to Manager.
 *
 * This trait is designed for engines that need to call user-defined functions within templates.
 * It provides a clean interface for adding and using macros while keeping the engine loosely coupled.
 */
trait HasMacro
{
    /**
     * @var array<string, Closure> Stores macro logic as closures.
     * Key: Macro name (e.g., 'alert', 'card'). Value: Closure that executes the macro.
     */
    protected array $macros = [];

    /**
     * Registers a new macro with this engine instance.
     *
     * @param string $name The unique name for the macro.
     * @param Closure $closure The logic to execute when the macro is called.
     * @return static For method chaining.
     */
    public function addMacro(string $name, Closure $closure): static
    {
        $this->macros[$name] = $closure;
        return $this;
    }

    /**
     * Executes a registered macro with given arguments.
     *
     * @param string $name The registered name of the macro.
     * @param mixed ...$values Arguments to pass into the macro's closure.
     * @return mixed Returns whatever the macro closure returns (or void if it just outputs).
     * @throws Exception
     */
    public function useMacro(string $name, ...$values): mixed
    {
        if (isset($this->macros[$name])) {
            return $this->macros[$name](...$values);
        }

        throw new Exception("Macro '{$name}' not found.");
    }
}