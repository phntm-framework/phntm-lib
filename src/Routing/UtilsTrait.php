<?php

namespace Phntm\Lib\Routing;

trait UtilsTrait
{
    private const STATIC_SEGMENT_WEIGHT = 256;  // 2^8
    private const DYNAMIC_SEGMENT_WEIGHT = 16;  // 2^4
    private const POSITION_MULTIPLIER = 8;  // 2^3

    /**
     * Converts a namespace to a route
     *
     * @returns string
     */
    public static function n2r(string $namespace): string
    {
        // remove the namespace and the class name suffix
        $namespace = preg_replace('/\\\Page$/', '', $namespace);
        $namespace = preg_replace('/\\\Manage$/', '', $namespace);
        $namespace = ltrim($namespace, 'Pages');

        $namespace = explode('\\', $namespace);
        foreach ($namespace as $key => $part) {
            $namespace[$key] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $part));
        }
        $namespace = implode('/', array_map('lcfirst', $namespace));
        return $namespace;
    }

    /**
     * Converts a route to a namespace
     *
     * @returns string
     */
    public static function r2n(string $route): string
    {
        $route = explode('/', $route);
        $route = implode('\\', array_map('ucfirst', $route));
        return 'Pages' . $route . '\\Page';
    }

    /**
     * Converts a route to a path path in the pages folder
     *
     * @returns string
     */
    public static function r2p(string $route): string
    {
        $route = explode('/', $route);
        $route = implode('/', array_map('ucfirst', $route));
        return $route;
    }

    public static function calcRoutePriority(string $path): int
    {
        // Remove leading/trailing slashes and split into segments
        $segments = array_filter(explode('/', trim($path, '/')));
        
        if (empty($segments)) {
            return 9999;
        }

        $priority = 0;
        $position = count($segments);

        foreach ($segments as $segment) {
            // Check if segment is dynamic (contains {} characters)
            $isDynamic = preg_match('/\{.*\}/', $segment);
            
            // Base segment score based on type
            $segmentScore = $isDynamic 
                ? self::DYNAMIC_SEGMENT_WEIGHT 
                : self::STATIC_SEGMENT_WEIGHT;
            
            // Add position-weighted score
            $priority += $segmentScore + ($position * self::POSITION_MULTIPLIER);
            
            $position--;
        }

        return $priority;
    }
}
