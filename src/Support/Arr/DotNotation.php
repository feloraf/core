<?php

namespace Felora\Support\Arr;

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
        if(empty($array)) {
            return null;
        }

        $segments = $this->split($key);

        foreach ($segments as $index => $segment) {

            if ($segment === '*') {
                return $this->handleWildcard(
                    $array,
                    array_slice($segments, $index + 1)
                );
            }

            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public function has(array $array, string $key): bool
    {
        return empty($this->get($array, $key)) ? false : true;
    }

    public function forgot(array &$array, string $key): void
    {
        $keys = $this->split($key);

        array_walk($keys, function ($segment, $index) use (&$array, &$keys) {
            if(! array_key_exists($segment, $array)) {
                return;
            }

            unset($keys[$index]);

            if($segment === '*') {
                $this->forgot($array[$segment], $this->join($keys));

                return;
            }

            if(is_array($array[$segment])) {
                $this->forgot($array[$segment], $this->join($keys));

                return;
            }

            unset($array[$segment]);
        });
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

    protected function handleWildcard(array $array, array $segments): array
    {
        $result = [];

        foreach ($array as $item) {
            if(empty($segments)) {
                $this->pushResult($result, $item, $segments);

                continue;
            }

            if(! is_array($item)) {
                continue;
            }

            $value = $this->get($item, $this->join($segments));

            if(is_null($value)) {
                continue;
            };

            $this->pushResult($result, $value, $segments);
        }

        return $result;
    }

    protected function pushResult(array &$result, mixed $value, array $segments): void
    {
        if (empty($segments) || ! is_array($value)) {
            $result[] = $value;

            return;
        }

        foreach ($value as $v) {
            $result[] = $v;
        }
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
