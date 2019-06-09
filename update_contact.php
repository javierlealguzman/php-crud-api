<?php 
require_once("models/contact.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] == "PUT")
{   
    parse_str(file_get_contents("php://input"), $data);

    if(isset($data["id"]) && isset($data["firstname"]) && isset($data["surname"]) && isset($data["phone"]) && isset($data["email"])) {

        $contact = new Contact($data["id"]);  
            
        if($contact->getId() > 0)
        {
            $change_photo = false;

            $contact->setFirstName($data['firstname']);
            $contact->setSurname($data['surname']);
            $contact->setPhone($data['phone']);
            $contact->setEmail($data['email']);

            if(isset($data["photo"]))
            {
                if($data["photo"] != $contact->getBase64Photo())
                {
                    echo $data["photo"];
                    echo '<br/>';
                    echo $contact->getBase64Photo();
                    
                    $change_photo = true;
                    $random_name = $contact->getFirstName()."_".uniqid().".jpg";
                    $contact->setPhoto($random_name);
                }
            }

            if($contact->update())
            {
                if($change_photo)
                    $contact->savePhoto($data['photo']);

                echo json_encode(array(
                    "error" => false,
                    "message" => "Contact updated successfully"
                ));
            }
            else
            {
                echo json_encode(array(
                    "error" => true,
                    "message" => "Contact could not be updated"
                ));
            }   
        }
        else
        {
            echo json_encode(array(
                "error" => true,
                "message" => "Record not found"
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