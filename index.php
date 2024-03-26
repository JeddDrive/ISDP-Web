<!DOCTYPE html>
<?php
session_start();
session_unset();
/* $servername = "localhost";
$username = "nicholas";
$password = "nicholas";

try {
    $conn = new PDO("mysql:host=$servername;dbname=bullseyedb2024", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} */
?>
<html>

<head>
    <!--Metadata-->
    <meta charset="UTF-8">
    <meta name="description" content="This is the home login page for the Bullseye Inventory Management System">
    <link href="Images/bullseye-logo.ico" rel="icon">
    <title>Bullseye Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to my selected Google Fonts - Outfit, Poppins, and Ubuntu -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <!-- Style Sheets -->
    <link href="mainStyle.css" rel="stylesheet" type="text/css">

    <!-- Scripts for Bootstrap (ex. need this for bootstrap navbar button) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- JavaScript -->
    <script type="module" src="main.js"></script>

</head>

<body>
    <!-- Page Heading - Title and Image -->
    <div id="loginImageAndHeadingContainer">
        <span><img src="Images/bullseye-nobackground.png" alt="Bullseye Sporting Goods Logo" class="img-fluid" id="loginImage"></span>
        <span id="loginHeading">Bullseye Sporting Goods</span>
    </div>
    <!-- Div for the help image -->
    <div><img src="Images/help.png" alt="Help Image" id="helpImage"></div>
    <!-- Login form -->
    <form id="frmLogIn" autocomplete="off" onSubmit="return false;" class="form-horizontal Form-main" method="GET">
        <div class="text-center">
            <h2>Login:</h2>
            <br>
        </div>
        <div class="mb-3 row">
            <label for="txtUserName" class="col-sm-2 col-form-label">Username:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="txtUsername" name="username">
            </div>
        </div>
        <div class="mb-3 row">
            <label for="txtPassword" class="col-sm-2 col-form-label">Password:</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="txtPassword" name="password">
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-12 text-center">
                <button ID="btnLogin" type="submit" class="btn btn-secondary btn-lg">Login</button>
                <button id="btnCustomer" type="submit" class="btn btn-secondary btn-lg">Continue as Customer</button>
            </div>
        </div>
        <div class="text-center">
            <input class="form-check-input" type="checkbox" id="passwordCheckbox"><label for="passwordCheckbox">Show Password</label>
        </div>
        <div id="forgotPasswordDiv">
            <a id="forgotPasswordLink" type="submit">Forgot Password?</a>
        </div>
        <!--End of Login Form-->
    </form>
</body>

</html>