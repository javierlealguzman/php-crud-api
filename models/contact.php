<?php  

require_once($_SERVER['DOCUMENT_ROOT'].'/php-crud-api/bd/connection.php');

class Contact {
    
    //Attributes
    private $id;
    private $firstname;
    private $surname;
    private $phone;
    private $email;
    private $photo;

    //Getters and Setters
    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; }

    public function getFirstName() { return $this->firstname; }
    public function setFirstName($value) { $this->firstname = $value; }

    public function getSurname() { return $this->surname; }
    public function setSurname($value) { $this->surname = $value; }

    public function getPhone() { return $this->phone; }
    public function getFormatedPhone() {
        $phones = array();

        if(!empty($this->phone))
            $phones = explode(',', str_replace(' ', '', $this->phone));

        return json_encode($phones);

    }
    public function setPhone($value) { $this->phone = $value; }

    public function getEmail() { return $this->email; }
    public function getFormatedEmail() {
        $emails = array();

        if(!empty($this->email))
            $emails = explode(',', str_replace(' ', '', $this->email));

        return json_encode($emails);
    }
    public function setEmail($value) { $this->email = $value; }

    public function getPhoto() { return $this->photo; }
    public function setPhoto($value) { $this->photo = $value; }
    public function getBase64Photo() {
        
        $base64 = "";

        if(!empty($this->photo))
        {
            $url = $_SERVER['DOCUMENT_ROOT'].'/php-crud-api/photos/' . $this->photo;
            $base64 = base64_encode(file_get_contents($url));    
        }
        
        return $base64;
    }

    //Constructor
    public function __construct()
    {
        if(func_num_args() == 0)
        {
            $this->id = 0;
            $this->firstname = "";
            $this->surname = "";
            $this->phone = "";
            $this->email = "";
            $this->photo = "";
        }
        elseif(func_num_args() == 1) {
            $id = func_get_arg(0);

            $connection = MySqlConnection::getDBConnection();
            
            $query = "SELECT id, first_name, surname, phone, email, photo FROM contacts WHERE id = ?";
            
            $command = $connection->prepare($query);
            $command->bind_param("s", $id);
            $command->execute();
            $command->bind_result($id, $firstname, $surname, $phone, $email, $photo);
            $found= $command->fetch();
            
            mysqli_stmt_close($command);
            
            $connection->close();
            
            $this->id = $found ? $id : 0;
            $this->firstname = $found ? $firstname: "";
            $this->surname = $found ? $surname : "";
            $this->phone = $found ? $phone : "";
            $this->email = $found ? $email : "";
            $this->photo = $found ? $photo : "";
            
        }
        elseif(func_num_args() == 6)
        {
            $arguments = func_get_args();
				
            $this->id = $arguments[0];
            $this->firstname = $arguments[1];
            $this->surname = $arguments[2];
            $this->phone = $arguments[3];
            $this->email = $arguments[4];
            $this->photo = $arguments[5];
        }
    }

    public function add() {
        $connection = MySqlConnection::getDBConnection();

        $query = "INSERT INTO contacts (first_name, surname, phone, email, photo) VALUES (?, ?, ?, ?, ?)";

        $command = $connection->prepare($query);

        $command->bind_param("sssss", $this->firstname, $this->surname, $this->phone, $this->email, $this->photo);

        $result = $command->execute();

        mysqli_stmt_close($command);

        $connection->close();

        return $result;
    }

    public function delete()
    {
        $connection = MySqlConnection::getDBConnection();
        
        $query = "DELETE FROM contacts WHERE id = ?";
        
        $command = $connection->prepare($query);
        
        $command->bind_param("s", $this->id);
        
        $result= $command->execute();
        
        mysqli_stmt_close($command);
        
        $connection->close();
        
        return $result;
    }

    public function update() 
    {
        $connection = MySqlConnection::getDBConnection();
        
        $query = "UPDATE contacts SET first_name = ?, surname = ?, phone = ?, email = ?, photo = ? WHERE id = ?";
        
        $command = $connection->prepare($query);
        
        $command->bind_param("sssssi", $this->firstname, $this->surname, $this->phone, $this->email, $this->photo, $this->id);
        
        $result= $command->execute();
        
        mysqli_stmt_close($command);
        
        $connection->close();
        
        return $result;
    }

    public function savePhoto($base64) {
        $result = false;
        $url = $_SERVER['DOCUMENT_ROOT'].'/php-crud-api/photos/';
        
        if(!empty($base64) && !empty($this->photo))
            $result = file_put_contents($url.$this->photo, base64_decode($base64));
        
        return $result !== false;
    }

    public static function getContacts($filter_field, $filter_value)
    {
        $contacts = array();
        
        $columns_bd = array("firstname", "surname", "phone", "email", "photo");
        
        $filter_field = strtolower($filter_field);
        
        $filter = in_array($filter_field, $columns_bd);
        
        $connection = MySqlConnection::getDBConnection();

        $query = "SELECT id, first_name, surname, phone, email, photo FROM contacts";

        if(in_array($filter_field, array("phone", "email")))
        {
            $filter_value = "%".$filter_value."%";
            if($filter) $query .= " WHERE " . $filter_field . " like ?";
        }
        elseif($filter)
            $query .= " WHERE " . $filter_field . " = ?";

        $command = $connection->prepare($query);

        if($filter)
            $command->bind_param("s", $filter_value);

        $command->execute();

        $command->bind_result($id, $firstname, $surname, $phone, $email, $photo);

        while($command->fetch())
            array_push($contacts, new Contact($id, $firstname, $surname, $phone, $email, $photo));

        mysqli_stmt_close($command);

        $connection->close();

        return $contacts;
    }

    public function toJSON() {
        
        return json_encode(array(
            'id' => $this->id,
            'firstname' => $this->firstname,
            'surname' => $this->surname,
            'phones' => json_decode($this->getFormatedPhone()),
            'emails' => json_decode($this->getFormatedEmail()),
            'photo' => $this->getBase64Photo()
        ));
    }

    public static function getAllJSON($filter_field, $filter_value) {
        $contacts = array();
        
        foreach(self::getContacts($filter_field, $filter_value) as $contact) {
            array_push($contacts, json_decode($contact->toJSON()));
        }

        return json_encode(array(
            'contacts' => $contacts
        ));
    }

    public function deletePhoto() {
        $deleted = false;
        if(!empty($this->photo))
        {
            $url = $_SERVER['DOCUMENT_ROOT'].'/php-crud-api/photos/' . $this->photo;
            if(file_exists($url)) $deleted = unlink($url);
        }

        return $deleted;
    }
}
?>