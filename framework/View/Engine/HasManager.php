<?php

namespace Framework\View\Engine;

use Framework\View\Manager;

/**
 * HasManager
 * ----------
 * A trait providing dependency injection for the View Manager.
 *
 * Allows engine classes to access view services without hard-coupling,
 * enabling easier testing and loose architectural design.
 */
trait HasManager
{
    /**
     * @var Manager The injected View Manager instance.
     */
    protected Manager $manager;

    /**
     * Injects the Manager instance into this engine during initialization.
     * Enables access to view services (paths, macros, resolution) without hard-coupling.
     *
     * @param Manager $manager The View Manager instance to inject.
     * @return static For method chaining.
     */
    public function setManager(Manager $manager): static
    {
        $this->manager = $manager;
        return $this;
    }
}