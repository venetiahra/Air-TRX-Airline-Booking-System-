<?php
interface CrudInterface {
    public function create(array $data): bool;
    public function read(): array;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
?>