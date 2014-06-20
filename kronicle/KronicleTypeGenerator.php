<?php

class KronicleTypeGenerator
{
    public static function create($db, $schema){
        $db->addType($schema);
    }
}
?>