<?php

namespace kronicle\rest\database\MySQL;

include_once './src/KronicleRest/KronicleDataBase.php';

class KronicleMySQL implements \kronicle\rest\database\KronicleDataBase
{
    private $db = null;
    
    public function __construct(){
        
    }
    
    public function connect() {
        $this->db = new \PDO("mysql:host=localhost;dbname=kronicle", "root", "");
    }
    
    public function getAll($type, $params) {
        //TODO: should have a query buidler to accept $params and produce proper sql
        $query = "SELECT * FROM tbl_$type";
        if($params == null){
            $stmt = $this->db->query($query);
        } else {
            $query = $query . " WHERE " . $this->formatParams($params);
            $stmt = $this->db->query($query);
        }
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $results;
    }
    
    public function get($type, $id, $params){
        $query = "SELECT * FROM tbl_$type WHERE ID = $id";
        if($params == null){
            $stmt = $this->db->query($query);
        } else {
            $query = $query . " AND " . $this->formatParams($params);
            $stmt = $this->db->query($query);
        }
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $results;
    }
    
    public function create($type, $obj, $params){
        $table = "tbl_$type";        
        if($this->tableExists($table)){ 
            $query = "INSERT INTO $table " . $this->formatInsert($obj);
            $stmt = $this->db->prepare($query);
            $results = $stmt->execute($obj_vars);
        } else {
            return false;
        }
        return $results;
    }
    
    public function update($type, $obj, $params){
        $table = "tbl_$type";
        $id = $obj->id;
        unset($obj->id);
        echo json_encode($obj);
        //$query = "UPDATE $table SET title=:title, body=:body, tags=:tags, date=:date, author=:author WHERE id=$obj.id"
        if(!empty($id) && $this->tableExists($table)){
            $query = "UPDATE $table SET " . $this->formatUpdate($obj). " WHERE id = $id";
            //echo "$query";
            $stmt = $this->db->prepare($query);
            $obj_vars = get_object_vars($obj);
            $results = $stmt->execute($obj_vars);
        } else {
            return false;
        }
        return $results;
    }
    
    public function delete($type, $obj, $params){
        $table = "tbl_$type";
        if(!empty($obj) && $this->tableExists($table)){
            $query = "DELETE FROM $table WHERE id=$obj";
        	$stmt = $this->db->prepare($query);
        	$results = $stmt->execute();
        } else {
            return false;
        }
        return $results;        
    }
    
    public function addType($schema){
        // TODO: needs a pile of error checking and probably refactoring
        $name = $schema->name;
        $table_out = "create table tbl_$name(ID int not null auto_increment, ";
        $meta_out = "create table meta_$name(ID varchar(100), ";
        $meta_prims = "";
        $meta_vals = "";
        foreach($schema->fields as $field => $value ){
            $currentPrimitive = $this->getPrimitive($value);
            $table_out += "$field $currentPrimitive, ";
            $meta_out += "$field varchar(100), ";
            $meta_prims += "$field,";
            $meta_vals += "\"$value\",";
        }
        $table_out = substr_replace($table_out, '', strrpos($table_out, ','), 1);
        $meta_out = substr_replace($meta_out, '', strrpos($meta_out, ','), 1);
        $meta_prims = substr_replace($meta_prims, '', strrpos($meta_prims, ','), 1);
        $meta_vals = substr_replace($meta_vals, '', strrpos($meta_vals, ','), 1);
        $table_out += ")";
        $meta_out += ")";
        $meta_insert = "insert into meta_$name (ID, $meta_prims) values ("int", $meta_vals)";
        $table_stmt = $this->db->prepare($table_out);
        $meta_stmt = $this->db->prepare($meta_out);
        $meta_insert_stmt = $this->db->prepare($meta_insert);
        $table_stmt->execute();
        $meta_stmt->execute();
        $meta_insert_stmt->execute();
        return true;
    }
    
    public function getTypes(){
        $query = "SHOW TABLES;";
        //$output = array();
        $result = $this->db->query($query);
        $results = $result->fetchAll(\PDO::FETCH_ASSOC);
        //$results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach($results as $row) {
            if(strrpos($row['Tables_in_kronicle'], 'tbl_') !== false){
            	$current = $row['Tables_in_kronicle'];
                $type = explode('tbl_', $current)[1];
                $meta_current = "meta_$type";
                $meta_query = "select * from $meta_current";
                $meta_result = $this->db->query($meta_query);
                $meta_data = $meta_result->fetch(\PDO::FETCH_ASSOC);
                $output_data = new \stdClass();
                $output_data->name = $type;
                $output_data->meta = $meta_data;                
                $output[] = $output_data;
            }
        }
        return $output;
    }
    
    private function getPrimitive($primitive){
        switch($primitive){
            case "short text": return "varchar(250)";
            case "long text": return "varchar(5000)";
            case "int": return "int";
            case "date": return "datetime";
        }
    }
    
    private function formatParams($params){
        $output = "";
        foreach($params as $key => $value){
            $output = $output . "$key = $value AND";
        }
        $output = substr_replace($output, ' ', strrpos($output, 'AND'), 3);
        return $output;
    }
    
    private function formatInsert($obj){
        $fields = "(date, ";
        $values = "(CURDATE(), ";
        foreach($obj as $key => $value){
            $fields = $fields . " $key,";
            $values = $values . " :$key,";
        }
        $fields = substr_replace($fields, ' ', strrpos($fields, ','), 1);
        $values = substr_replace($values, ' ', strrpos($values, ','), 1);
        $fields = $fields . ")";
        $values = $values . ")";
        return "$fields VALUES $values";
    }
    
    private function formatUpdate($obj){
        $output = "date=CURDATE(), ";
        foreach($obj as $key => $value){
            if($key != "id"){
                $output = $output . " $key=:$key,";
            }
        }
        $output = substr_replace($output, ' ', strrpos($output, ','), 1);
        return $output;
    }
    
    private function tableExists($table){
        $results = $this->db->query("SHOW TABLES LIKE $table");
        return count($results) > 0; 
    }
    
    private function createType($schema){
        $typename = $schema->name;
        $output = "CREATE TABLE tbl_$typename IF NOT EXISTS (ID INT NOT NULL AUTO_INCREMENT,";
        foreach($schema->fields as $name => $type){
            switch($type){
                case 'sstring':
                    $output = $output . " $name  VARCHAR(250),";
                    break;
                case 'lstring':
                    $output = $output . " $name VARCHAR(5000),";
                    break;
                case 'int':
                    $output = $output . " $name INT,";
                    break;
                case 'date':
                    $output = $output . " $name DATE,";
            }
        }
        $output = substr_replace($output, ' ', strrpos($output, ','), 1); // replace trailing comma
        $output = $output . ");";
        addTypeTable("tlb_$typename");
        return $output;
    }
    
    private function isValidSchema($schema){
        foreach($schema->fields as $name => $type){
            foreach($this->types as $sys_type){
                if($type != $sys_type){
                    return false;
                }
            }
        }
        return true;
    }
    
    private function addTypeTable($table){
        $mysqli->query("INSERT INTO tbl_schema_types VALUES ($table);");
    }
}
?>