<?php
if (!class_exists('EncrypData_')) {
        include 'EncrypData.php';
  }
require 'db.php';

function authenticate($conn) {
    $secretKey = "ClaveFibretaDe32OptacteresSPL";
    $headers = getallheaders();
    $email = isset($headers['X-Email']) ? $headers['X-Email'] : null;
    $cardcode = isset($headers['X-CardCode']) ? $headers['X-CardCode'] : null;
    $apiKey = isset($headers['X-ApiKey']) ? $headers['X-ApiKey'] : null;

    if (is_null($email) || is_null($apiKey) || is_null($cardcode)) {
        http_response_code(401);
        echo json_encode(["error" => "Faltan credenciales:".$email."***".$cardcode."****".$apiKey]);
        exit;
    }
    $sql = "SELECT password,ApiKey FROM login_cliente WHERE cardcode = '".$cardcode."' AND email = '".$email."'";
    $result = $conn->query($sql);
    if ($result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Usuario invalido"]);
        exit;
    }else{
        if ($row = $result->fetch_assoc()) {
            if ($row['ApiKey']==='') {
                http_response_code(403);
                echo json_encode(["error" => "Usuario invalido"]);
                exit;
            }else{
                if(!leerApiKey($apiKey, $secretKey)){
                    http_response_code(403);
                        echo json_encode(["error" => "API KEY incorrectas"]);
                }else{
                    if($apiKey!=$row['ApiKey']){
                        http_response_code(403);
                        echo json_encode(["error" => "Credenciales incorrectas"]);
                        exit;
                    }
                }
            }
        }
    }
   
}

function leerApiKey($apiKey, $secretKey) {
    $decoded = base64_decode($apiKey);
    $iv = substr($decoded, 0, 16);
    $encryptedData = substr($decoded, 16);
    return openssl_decrypt($encryptedData, 'AES-256-CBC', $secretKey, 0, $iv);
}
?>
