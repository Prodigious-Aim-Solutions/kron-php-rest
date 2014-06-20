<?php
namespace kronicle\rest;

require './vendor/slim/slim/Slim/Slim.php';
require './vendor/autoload.php';

\Slim\Slim::registerAutoloader();

##require './src/KronicleRest/KronicleMySQL.php';

class KronicleRest 
{
    public $app;
    public $db;
    
    /****
     * Main constructor function
     * Param: $db - the database for Kronicle to use TODO: should take an array of databases
     * Returns: nothing, creates new instance of a KronicleRest object
    ****/
    public function __construct($db) 
    {
        if(!empty($db))
        {
            $this->app = new \Slim\Slim();        
            $this->db = $db;
            $this->connectToDB();
            $this->addDefaultRESTRoutes();
        } else {
            throw new Exception('Constructor must be passed a database');
        }
    }
    
    /****
     * Function to run the app
     * Param: none
     * Returns: nothing
    ****/
    public function run() 
    {
        $this->app->run();
    }
    
    /****
     * Function to add a route to KronicleRest
     * Param: $method - the HTTP method to use ie GET, POST, PUT, DELETE, will accept custom as well
     * Param: $route - the route being mapped
     * Param: $handle - the function called to handle the route
     * Returns: nothing
    ****/
    public function addRoute($method, $route, $handle) 
    {
        //echo "$method: $route<br />";
        $this->app->map($route, $handle)->via($method);
    }
    
    /****
     * Function to connect to DB TDOD: multi connect in the future
     * Param: none
     * Returns: nothing
    ****/
    public function connectToDB()
    {
        $this->db->connect();
    }
    
    /****
     * Function to add the default REST behavoir routes
     * Adds a GET, POST, PUT and DELETE for a REST api
     * Param: none
     * Returns: nothing
    ****/
    private function addDefaultRESTRoutes()
    {
        $this->addRoute('GET', "/", function() {
            $output = file_get_contents('./index.html');
            $this->app->response->write($output);
        });

        $this->addRoute('GET', "/api/v1/:type", function($type)  {
            //echo "get $type";
            $params = $this->app->request->get();
            if($params != null){
                $results = $this->db->getAll($type, $params);
            } else {
                $results = $this->db->getAll($type, null);
            }
            $this->app->response->write(json_encode($results));
        });

        $this->addRoute('GET', "/api/v2/:type", function($type) {
            $params = $this->app->request->get();
            if($params != null){
                $results = $this->db->getAll($type, $params);
            } else {
                $results = $this->db->getAll($type, null);
            }
            $this->app->response->write(json_encode($results));
        });

        $this->addRoute('GET', '/api/v1/:type/:id', function($type, $id) {
            $params = $this->app->request->get();
            if($params != null){
                $results = $this->db->get($type, $id, $params);
            } else {
                $results = $this->db->get($type, $id, null);
            }
            $this->app->response->write(json_encode($results));
        });

        $this->addRoute('POST', '/api/v1/:type', function($type) {
            $params = json_decode($this->app->request->getBody());
            if($params != null){
                $success = $this->db->create($type, $params, null);
            } else {
                $success = $this->db->create($type, null, null);
            }
            $this->app->response->write(json_encode($success));
        });

        $this->addRoute('PUT', "/api/v1/:type/:id", function($type, $id) {
            $params = json_decode($this->app->request->getBody());
            //echo "$params";
            if($params != null){
                $success = $this->db->update($type, $params, null);
            } else {
                $success = $this->db->update($type, null, null);
            }
            $this->app->response->write(json_encode($success));
        });

        $this->addRoute('DELETE', "/api/v1/:type/:id", function($type,$id) {
            $success = $this->db->delete($type, $id);
            $this->app->response->write(json_encode($success));
        });
        
        $this->addRoute('GET', "/api/admin/v1/", function() {
            //echo "get $type";
            //$params = $this->app->request->get();            
            $results = $this->db->getTypes();
            $this->app->response->write(json_encode($results));
        });
        
        $this->addRoute('POST', "/api/admin/v1/", function() {
            $params = json_decode($this->app->request->getBody());
            $results = $this->db->addType($params);
            $this->app->response->write(json_encode($results));
        });
    }
    
}

?>
