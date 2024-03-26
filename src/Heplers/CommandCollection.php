<?php

class CommandCollection
{
    private array $collection = [];

    public function __construct(ConsoleCommand ...$obj)
    {
        foreach ($obj as $item)
            $this->collection[get_class($item)] = $item;
    }

    public function getCollection() :array
    {
        return $this->collection;
    }
}

