<?php
if (!class_exists('EncrypData_')) {
        include 'EncrypData.php';
  }
require 'db.php';

function authenticate($conn) {

    $headers = getallheaders();
    $email = isset($headers['X-Email']) ? $headers['X-Email'] : null;
    $password = isset($headers['X-Password']) ? $headers['X-Password'] : null;
    $cardcode = isset($headers['X-Cardcode']) ? $headers['X-Cardcode'] : null;

    if (!$email || !$password) {
        http_response_code(401);
        echo json_encode(["error" => "Faltan credenciales"]);
        exit;
    }
    $sql = "SELECT password FROM login_cliente WHERE cardcode = '".$cardcode."' AND email = '".$email."'";
    $result = $conn->query($sql);
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Usuario invalido"]);
        exit;
    }else{
        if ($row = $result->fetch_assoc()) {
            $EncrypData = new EncrypData_('password');
            $passwordDencryp = $EncrypData->cadenaDecrypt($row['password']); 
           if($passwordDencryp!=$password){
            http_response_code(403);
            echo json_encode(["error" => "Credenciales incorrectas"]);
            exit;
           }
        }
    }
   
}
?>
