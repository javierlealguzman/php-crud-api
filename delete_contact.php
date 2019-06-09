<?php 
require_once("models/contact.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Content-Type: application/json");

if($_SERVER["REQUEST_METHOD"] == "DELETE")
{   
    parse_str(file_get_contents("php://input"), $data);

    if(isset($data["id"])) {

        $contact = new Contact($data["id"]);  
            
        if($contact->getId() > 0)
        {
            if($contact->delete())
            {
                $contact->deletePhoto();
                
                echo json_encode(array(
                    "error" => false,
                    "message" => "Contact deleted successfully"
                ));
            }
            else
            {
                echo json_encode(array(
                    "error" => true,
                    "message" => "Contact could not be deleted"
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
            'message' => 'Param id is required'
        ));
    }
}
?>