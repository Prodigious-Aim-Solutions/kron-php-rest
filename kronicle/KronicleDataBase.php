<?php

namespace kronicle\rest\database;

interface KronicleDataBase {
    public function connect();
    public function getAll($type, $params);
    public function get($type, $id, $params);
    public function create($type, $obj, $params);
    public function update($type, $obj, $params);
    public function delete($type, $obj, $params);
    public function addType($schema);
    
    public function getTypes();
    //public function createType($schema);
    //public function updateType($schema);
    //public function deleteType($schema);
}


?>