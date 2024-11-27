<?php

$db_server="localhost";
$db_user="miguel";
$db_password="";
$db_database="db_bdm";
$conn="";

$conn=mysqli_connect($db_server,$db_user,$db_password,$db_database);





if($conn){
    echo "si se pudo";
}else{
    echo "llama";
}


?>