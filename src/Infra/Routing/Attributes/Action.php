<?php

namespace Phntm\Lib\Infra\Routing\Attributes;

use Attribute;

/**
 * Marks a method as an action
 */
#[Attribute]
class Action
{
    /**
     * @param string $slug The slug of the action
     * @param int $priority The relative priority of the action
     */
    public function __construct(public string $slug='/', public int $priority = 0) {}
}
