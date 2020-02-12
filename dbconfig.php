<?php
    function connect_db() {
        $server = 'localhost'; 
        $user = 'raj';
        $pass = 'Raj@199704';
        $database = 'couponusers'; // name of your database
        $connection = mysqli_connect($server, $user, $pass, $database);
        return $connection;
    }
?>