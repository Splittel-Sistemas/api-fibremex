<?php
// Evita que cualquier salida previa dañe el JSON
ob_start();
ob_clean();
header("Content-Type: application/json");
require 'auth.php';
authenticate($conn);
$headers = getallheaders();
 $cardcode = isset($headers['X-Cardcode']) ? $headers['X-Cardcode'] : null;
 $email = isset($headers['X-Email']) ? $headers['X-Email'] : null;
 $sqlCliente = "SELECT descuento FROM login_cliente WHERE cardcode = '".$cardcode."' AND email = '".$email."'";
 $resultCliente = $conn->query($sqlCliente);
 $rowCliente = $resultCliente->fetch_assoc();
 $descuento_cliente=$rowCliente['descuento'];

$sql = "SELECT codigo,desc_producto,existencia,precio,descuento_producto,img_principal,id_marca,subcategoria,info_tecnica FROM catalogo_productos WHERE activo='si' AND precio > 0";
$result = $conn->query($sql);
$productos = [];
if ($result && $result->num_rows !== 0) {

while ($row = $result->fetch_assoc()) {
    /********************* LOGICA DE DESCUENTO POR CLIENTE ***********************/
    if($row["descuento_producto"] == -1){
        if( $descuento_cliente > 0){
            $PL=bcdiv($row["precio"] - ($row["precio"] * ($descuento_cliente / 100)), 1, 3);
        }else{
            $PL=$row["precio"];
        }
    }elseif($row["descuento_producto"] > 0){
        if($row["descuento_producto"] >= $descuento_cliente){
            $PL=bcdiv($row["precio"] - ($row["precio"] * ($descuento_cliente / 100)), 1, 3);
        }else{
            $PL=bcdiv($row["precio"] - ($row["precio"] * ($row["descuento_producto"] / 100)), 1, 3);
        }
    }else{
        $PL=$row["precio"];
    }
    /****************************************************************************/
    /********************* OBTENER LA URL PRINCIPAL DEL PRODUCTO ***********************/
    $url='';
    if($row["img_principal"]!='')
        $url=urldecode(utf8_encode("https://fibremex.com/fibra-optica/public/images/img_spl/productos/".$row["codigo"]."/thumbnail/".$row["img_principal"]));

    /***********************************************************************************/
    /********************* OBTENER LA MARCA DEL PRODUCTO ***********************/
    $marca='S/M';
    $sqlMarca = "SELECT desc_marca FROM catalogo_marcas WHERE id_marca = '".$row["id_marca"]."'";
    $resultMarca = $conn->query($sqlMarca);
    if($resultMarca->num_rows > 0){
    $rowMarca = $resultMarca->fetch_assoc();
    $marca=strtoupper($rowMarca['desc_marca']);
    }
    /***********************************************************************************/
    /********************* OBTENER FICHA TECNICA DEL PRODUCTO ***********************/
    $FT='S/F';
    $sqlFT = "SELECT ruta 
                FROM catalogo_fichas_tecnicas T0
                INNER JOIN u_producto_ficha T1 ON T0.id_ficha = T1.id_producto
                WHERE T1.id_ficha = '".$row["info_tecnica"]."'";
    $resultFT = $conn->query($sqlFT);
    if($resultFT->num_rows > 0){
    $rowFT = $resultFT->fetch_assoc();
    $FT=urldecode("https://fibremex.com/fibra-optica/public/images/img_spl/".utf8_encode($rowFT['ruta'].".pdf"));
    }
    /***********************************************************************************/
    /********************* OBTENER LA FAMILIA Y SUBFAMILIA DEL PRODUCTO ***********************/
    $categoria='';
    $subcategoria='';
    $sqlCat = "SELECT T2.desc_familia, T0.descripcion 
                FROM menu_principal T0
                INNER JOIN u_menu_subcategorias T1 ON T0.id = T1.id_menu
                INNER JOIN menu_categorias T2 ON T0.id_categoria = T2.id_codigo
                WHERE T1.id_subcategorias='".$row["subcategoria"]."'";
    $resultCat = $conn->query($sqlCat);
    if($resultCat->num_rows > 0){
    $rowCat = $resultCat->fetch_assoc();
    $subcategoria=utf8_encode($rowCat['descripcion']);
    $categoria=utf8_encode($rowCat['desc_familia']);
    }
    /***********************************************************************************/


    $productos[] = [
            "Codigo" => $row["codigo"],
            "Descripcion" => utf8_encode($row["desc_producto"]),
            "Stock" => floor($row["existencia"]*0.625),
            "Precio" => $PL,
            "Moneda" => "USD",
            "Imagen" => $url,
            "FichaTecnica" => $FT,
            "Marca" => $marca,
            "Categoria" => $categoria,
            "Subcategoria" => $subcategoria,
        ];
        //echo json_encode($row);
}
//var_dump($productos);

echo json_encode(
    ["status" => "success", "data" => $productos],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}else{

    echo json_encode(["status" => "success", "data" => $productos]);
}
ob_end_flush(); // Opcionalmente limpia el buffer y lo envía
?>
