<?php

include_once './src/KronicleRest/index.php';
include_once './src/KronicleRest/KronicleMySQL.php';

$kronDb = new \kronicle\rest\database\MySQL\KronicleMySQL();
$kronApp = new \kronicle\rest\KronicleRest($kronDb);
//$kronApp->connectToDB();
/* $kronApp->addRoute('GET', "/", function() use($kronApp) {
    $output = file_get_contents('./index.html');
    $kronApp->app->response->write($output);
});

$kronApp->addRoute('POST', '/test/:id', function($id) use($kronApp) {
    //echo "Test World!";
    $params = $kronApp->app->request->post();
    $kronApp->app->response->write(json_encode($id));
});

$kronApp->addRoute('GET', '/api/v1/:type', function($type) use($kronApp){
    $params = $kronApp->app->request->get();
    if($params != null){
        $results = $kronApp->db->getAll($type, $params);
    } else {
        $results = $kronApp->db->getAll($type, null);
    }
    $kronApp->app->response->write(json_encode($results));
});

$kronApp->addRoute('GET', '/api/v1/:type/:id', function($type, $id) use ($kronApp){
    $params = $kronApp->app->request->get();
    if($params != null){
        $results = $kronApp->db->get($type, $id, $params);
    } else {
        $results = $kronApp->db->get($type, $id, null);
    }
    $kronApp->app->response->write(json_encode($results));
});

$kronApp->addRoute('POST', '/api/v1/:type', function($type) use ($kronApp){
    //TODO: accpet multiple types not just JSON
    $params = json_decode($kronApp->app->request->getBody());
    if($params != null){
        $success = $kronApp->db->create($type, $params, null);
    } else {
        $success = $kronApp->db->create($type, null, null);
    }
    $kronApp->app->response->write(json_encode($success));
});

$kronApp->addRoute('PUT', "/api/v1/:type/:id", function($type, $id){
    $params = $app->request->put();
    if($params != null){
        $success = $db->update($type, $id, $params);
    } else {
        $success = $db->update($type, $id, null);
    }
    return json_encode($success);
});

$kronApp->addRoute('DELETE', "/api/v1/:type/:id", function($type,$id){
    $success = $db->delete($type, $id);
    return json_encode($success);
});*/



$kronApp->run();

?>