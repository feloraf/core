<?php

namespace Felora\Support\Arr;

class DotNotation
{
    const SEPORATOR='.';

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
        $keys = explode($this::SEPORATOR, $key);

        $current = $array;

        foreach($keys as $segment_key => $segment) {

            unset($keys[$segment_key]);

            if(isset($current[$segment])) {
                $current = &$current[$segment];

                continue;
            }

            if($segment === '*') {
                $result = [];
                $key = implode($this::SEPORATOR, $keys);
                foreach($current as $_segment) {
                    if(! is_array($_segment)) {
                        continue;
                    }

                    $result[] = $this->get($_segment, $key);
                }

                $current = $result;

                break;
            }

            $current = null;

            break;
        }

        return $current;
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
}
