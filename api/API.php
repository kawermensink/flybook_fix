<?php
require 'database.php';

if($_GET["API_KEY"]=="Flight booker 1.2 plus edition"){
    switch($_GET["action"]){
        case "get":Get();break;
        case "login":Login();break;
        case "book":Book();break;
        default:break;
    }
}
else{
    echo "wrong Key";

}


function Get(){
    global $conn;

    switch($_GET["items"]){
        case "airport":
            $search = '%' . $_GET['search'] . '%';
            $stmt = $conn->prepare('SELECT * FROM airports WHERE airport LIKE :search OR code LIKE :search');
            $stmt->bindParam(':search', $search, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($results) > 0) {
                $obj=["status"=>"200","statusDescription"=>"Ok"];
                array_unshift($results,$obj);
                $jsonObj= json_encode($results);
            }
            else {
                $obj=["status"=>"440","statusDescription"=>"No matching airports found"];
                $jsonObj="[".json_encode($obj)."]";
            }
            echo $jsonObj;
            break;
        case "flights":
            $date = $_GET['date'];
            $from = $_GET['from'];
            $to = $_GET['to'];
            $stmt = $conn->prepare('SELECT * FROM flights WHERE (LEFT(date_time_depart,10)=:date OR LEFT(date_time_arriv,10)=:date) AND airport_from=:from AND airport_to=:to');
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':from', $from, PDO::PARAM_INT);
            $stmt->bindParam(':to', $to, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(count($results) > 0) {
                $obj=["status"=>"200","statusDescription"=>"Ok"];
                array_unshift($results,$obj);
                $jsonObj= json_encode($results);
            }
            else {
                $obj=["status"=>"460","statusDescription"=>"No matching flights found"];
                $jsonObj="[".json_encode($obj)."]";
            }
            echo $jsonObj;
            break;
    }
}

// fixed version
function Login(){
    global $conn;
//    echo "1";
    $email = $_GET['email'];
    $password = md5($_GET['pass']);

    $stmt = $conn->prepare('SELECT * FROM passengers WHERE email=:email AND password=:password');
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($results) > 0) {
        $token = md5(rand());
        $email = $_GET['email'];

        $stmt = $conn->prepare("UPDATE passengers SET token=:token WHERE email=:email");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);

        if($stmt->execute()) {
            $results[0]["token"] = $token;
        }

        $obj = ["status" => "200", "statusDescription" => "Ok"];
        array_unshift($results, $obj);
        $jsonObj = json_encode($results);
    }
    else {
        $obj = ["status" => "450", "statusDescription" => "No user found"];
        $jsonObj = json_encode([$obj]);
    }
    echo $jsonObj;
}


function Book(){
    global $conn;

    $records = $conn->prepare('SELECT * FROM passengers WHERE id="'.($_GET['passenger']).'" and token="'.($_GET['token']).'"');
    $records->execute();
    $results = $records->fetchAll(PDO::FETCH_ASSOC);
    // var_dump( $results);
    if(count($results) > 0) {
        $sql = "INSERT INTO bookings (passenger, flight,status) VALUES ('" . $_GET['passenger'] . "','" . $_GET['flight'] . "', 'OK')";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute()){
            $obj=["status"=>"200","statusDescription"=>"OK"];
            $jsonObj="[".json_encode($obj)."]";
        }
        else{
            $obj=["status"=>"460","statusDescription"=>"problem with the booking"];
            $jsonObj="[".json_encode($obj)."]";

        }
    }
    else {
        $obj=["status"=>"470","statusDescription"=>"User token problem"];
        $jsonObj="[".json_encode($obj)."]";
    }
    echo $jsonObj;
}


?>


