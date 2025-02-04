<?php

namespace Phntm\Lib\Infra\Routing\Attributes;

use Attribute;
use Phntm\Lib\Infra\Routing\Router;
use Phntm\Lib\Pages\ResolvesDynamicParams;

#[Attribute]
class Dynamic
{
    public function __construct(public string $denoted_namespace, public array $defaults = [])
    {
    }


    public static function getTypeSafePath(string $pathOrNamespace): string
    {
        $isNamespace = strpos($pathOrNamespace, '\\') !== false;
        if ($isNamespace) {
            $parts = explode('\\', $pathOrNamespace);
        } else {
            $parts = explode('/', $pathOrNamespace);
        }

        $typesafe_parts = array_map(function(string $part) {
            $type_separator = strpos($part, ':');
            if ($type_separator !== false) {

                $type = explode(':', $part)[0];

                $part = preg_replace('/{(\w+):([^}]+)}/', '{$2}', $part);
                $part = rtrim($part, '}');

                // determine the regex for the type
                $regex = match(ltrim(trim($type), '{')) {
                    'int' => '[1-9][0-9]*',
                    'string' => '[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*',
                    'float' => '\d+\.\d+',
                    'bool' => 'true|false|1|0|yes|no',
                    'array' => '\w+',
                };

                if (!$regex) {
                    return;
                }
                $part .= "<$regex>}";
            }
            return $part;
        }, $parts);

        if ($isNamespace) {
            $typesafe_namespace = implode('\\', $typesafe_parts);
            return Router::n2r($typesafe_namespace);
        } else {
            return implode('/', $typesafe_parts);
        }
    }
}
