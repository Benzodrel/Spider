<?php


interface QueueTable
{
    public function getOne(): array|null;
    public function add(string $path, string $mask):bool;
    public function delete($id):bool;
    public function updateStatus($id):bool;
    public function getOneUpdateStatus(): array|null;
}