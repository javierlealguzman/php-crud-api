<?php 
require_once("models/contact.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] == "GET")
{
    if(isset($_GET["id"])) {

        $contact = new Contact($_GET["id"]);

        if($contact->getId() > 0)
        {
            echo json_encode(array(
                "contact" => json_decode($contact->toJSON())
            ));
        }
    }
    else
    {
        echo Contact::getAllJSON();
    }
}
?>