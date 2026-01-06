<?php

namespace Felora\Support\Arr;

use Felora\Contracts\Support\Arr\Types\Type;
use Felora\Support\Arr\Types\WildCard;

class DotNotation
{
    const SEPORATOR='.';

    public function split(string $key): array
    {
        $segments = explode(self::SEPORATOR, $key);

        return $segments;
    }

    public function join(array $remainingSegments): string
    {
        $key = implode(self::SEPORATOR, $remainingSegments);

        return $key;
    }

    public function set(array &$array, string $key, mixed $value): void
    {
        if($key == '') {
            return;
        }

        $keys = explode($this::SEPORATOR, $key);

        $current = &$array;

        foreach($keys as $segment) {
            if (! is_array($current)) {
                $current = [];
            }

            if (! array_key_exists($segment, $current)) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current = $value;
    }

    public function get(array $array, string $key): mixed
    {
        if(empty($array) || $key == '') {
            return null;
        }

        $segments = $this->split($key);

        $result = $this->getHandler($array, $segments);
        $result = ($result instanceof Type)
                    ? $result->get()
                    : $result;

        return $result;
    }

    public function has(array $array, string $key): bool
    {
        return empty($this->get($array, $key)) ? false : true;
    }

    public function forgot(array &$array, string $key): void
    {
        $segments = $this->split($key);

        $this->forgetRecursive($array, $segments);
    }

    public function search()
    {
        //
    }

    public function expand()
    {
        //
    }

    public function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        $this->flatten_recursive($result, $array, $prefix);

        return $result;
    }

    public function keys(array $array): array
    {
        return array_keys($this->flatten($array));
    }

    public function values(array $array): array
    {
        return array_values($this->flatten($array));
    }

    protected function forgetRecursive(array &$array, array $segments): void
    {
        if (empty($segments)) {
            return;
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            $this->forgetWithWildcard($array, $segments);

            return;
        }

        if (! array_key_exists($segment, $array)) {
            return;
        }

        if (is_array($array[$segment]) && ! empty($segments)) {
            $this->forgetRecursive($array[$segment], $segments);

            return;
        }

        $this->removeKey($array, $segment);
    }

    protected function forgetWithWildcard(array &$array, array $segments): void
    {
        foreach ($array as $key => &$value) {

            if (is_array($value)) {
                $this->forgetRecursive($value, $segments);

                continue;
            }

            if (empty($segments)) {
                $this->removeKey($array, $key);
            }
        }
    }

    protected function removeKey(array &$array, string|int $key): void
    {
        unset($array[$key]);
    }

    protected function getHandler(array $array, array $segments): mixed
    {
        if (empty($segments)) {
            return $array;
        }

        $currentSegment = array_shift($segments);

        if ($currentSegment === '*') {
            return $this->getWithWildcard($array, $segments);
        }

        if (! array_key_exists($currentSegment, $array)) {
            return null;
        }

        $result = empty($segments)
            ? $array[$currentSegment]
            : $this->getHandler($array[$currentSegment], $segments);

        dump($result);

        return $result;
    }

    protected function getWithWildcard(array $array, array $segments): WildCard
    {
        $results = [];

        foreach ($array as $item) {
            dump($item);
            if(count($segments) <= 0) {
                $this->appendResultByGrouping($item, $results);

                continue;
            }

            if(is_array($item)) {
                $value = $this->getHandler($item, $segments);

                if($value instanceof WildCard) {
                    $this->appendResultByFlatting($value->get(), $results);
                } else {
                    $this->appendResultByGrouping($value, $results);
                }

                continue;
            }
        }

        return new WildCard($results);
    }

    /**
    protected function getWithWildcard(array $array, array $segments): array
    {
        $results = [];

        foreach ($array as $item) {
            if (empty($segments)) {
                $this->appendResult_y($item, $results, $segments);

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $value = $this->getHandler($item, $segments);

            if ($value === null) {
                continue;
            }

            $this->appendResult_y($value, $results, $segments);
        }

        return $results;
    }
    */

    protected function appendResult_y(mixed $value, array &$results, array $segments): void
    {
        if (is_array($value) && ! empty($segments)) {
            $this->appendResultByFlatting($value, $results);

            return;
        }

        $this->appendResultByGrouping($value, $results);
    }

    protected function appendResultByFlatting($value, &$results): void
    {
        foreach ($value as $item) {
            $results[] = $item;
        }
    }

    protected function appendResultByGrouping($value, &$results): void
    {
        $results[] = $value;
    }

    /**
     * Recursively flattens a multi-dimensional array using dot notation.
     *
     * @param array $result The resulting flattened array (passed by reference)
     * @param array $array  The array to flatten
     * @param string $prefix Current key prefix used for dot notation
     * @return void
     */
    protected function flatten_recursive(array &$result, array $array, string $prefix = ''): void
    {
        foreach ($array as $key => $value) {
            $currentKey = $prefix === ''
                            ? (string) $key 
                            : $prefix . $this::SEPORATOR . $key;

            if (is_array($value) && ! empty($value)) {
                $this->flatten_recursive($result, $value, $currentKey);

                continue;
            }

            $result[$currentKey] = $value;
        }
    }
}
