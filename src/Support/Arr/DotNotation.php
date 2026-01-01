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

    public function get(array $array, string $key)
    {
        $segments = $this->split($key);

        foreach ($segments as $index => $segment) {

            if ($segment === '*') {
                return $this->handleWildcard(
                    $array,
                    $this->join(array_slice($segments, $index + 1))
                );
            }

            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public function has()
    {
        //
    }

    public function keys()
    {
        //
    }

    public function values()
    {
        //
    }

    public function forget()
    {
        //
    }

    public function delete()
    {
        //
    }

    public function search()
    {
        //
    }

    public function flatten()
    {
        //
    }

    public function expand()
    {
        //
    }

    protected function handleWildcard(array $array, string $key): array
    {
        $result = [];

        foreach ($array as $item) {
            $nestedResult = $key === ''
                ? $item
                : $this->get($item, $key);

            if( is_array($nestedResult)
                && count($nestedResult)           === 1 
                && array_key_first($nestedResult) === 0
                && is_array(array_shift($array))) {
                //
                $nestedResult = array_shift(array_values($nestedResult));
            }

            if(is_array($nestedResult)) {
                $result = $nestedResult;

                continue;
            }

            $result[] = $nestedResult;
        }

        return $result;
    }
}
