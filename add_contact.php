<?php 
require_once("models/contact.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] == "POST")
{   

    if(isset($_POST["firstname"]) && isset($_POST["surname"]) && isset($_POST["phone"]) && isset($_POST["email"])) {

        $contact = new Contact();

        $contact->setFirstName($_POST['firstname']);
        $contact->setSurname($_POST['surname']);
        $contact->setPhone($_POST['phone']);
        $contact->setEmail($_POST['email']);

        if(isset($_POST["photo"]))
        {
            $random_name = $contact->getFirstName()."_".uniqid().".jpg";
            $contact->setPhoto($random_name);

        }

        if($contact->add())
        {
            if(isset($_POST["photo"]))
                $contact->savePhoto($_POST['photo']);
            
            
            echo json_encode(array(
                "error" => false,
                "message" => "Contact added successfully"
            ));
        }
        else
        {
            echo json_encode(array(
                "error" => true,
                "message" => "Contact could not be added"
            ));
        }   
    }
    else
    {
        echo json_encode(array(
            'error' => true,
            'message' => 'Some params are required'
        ));
    }
}
?>