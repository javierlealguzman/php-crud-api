<?php
class MySqlConnection{
    
    public static function getDBConnection(){
        
        $configFile = $_SERVER['DOCUMENT_ROOT'].'/php-crud-api/config/connection_params.json';
        $configData = file_get_contents($configFile);
        
        $configJSON = json_decode($configData, true);
        
        if(isset($configJSON['server']))
            $server = $configJSON['server'];
        else {
            echo 'Configuration error: Server name not found';
            die;
        }
        
        if(isset($configJSON['user']))
            $user = $configJSON['user'];
        else {
            echo 'Configuration error: User name not found';
            die;
        }
        
        if(isset($configJSON['password']))
            $password = $configJSON['password'];
        else {
            echo 'Configuration error: Password not found';
            die;
        }
        
        if(isset($configJSON['database']))
            $database = $configJSON['database'];
        else {
            echo 'Configuration error: Database name not found';
            die;
        }
            
        $connection = mysqli_connect($server, $user, $password, $database);
        $connection->set_charset('utf8');
        
        if($connection)
            return $connection;
        else{
            echo 'Could not connect to Database';
            die;
        }
    }
}
?>