<!DOCTYPE html>
<?php
session_start();
?>
<html>

<head>
    <!--Metadata-->
    <meta charset="UTF-8">
    <meta name="description" content="This is the reset/forgot password page for the Bullseye Inventory Management System">
    <link href="Images/bullseye-logo.ico" rel="icon">
    <title>Bullseye - Reset Password</title>

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
    <script type="module" src="resetPassword.js"></script>

</head>

<body>
    <!-- Page Heading - Title and Image -->
    <div id="loginImageAndHeadingContainer">
        <span><img src="Images/bullseye-nobackground.png" alt="Bullseye Sporting Goods Logo" class="img-fluid" id="loginImage"></span>
        <span id="loginHeading">Bullseye Sporting Goods</span>
    </div>
    <!-- Div for the help image -->
    <div><img src="Images/help.png" alt="Help Image" id="helpImage"></div>
    <!-- Reset Password form -->
    <form id="frmResetPassword" autocomplete="off" onSubmit="return false;" class="form-horizontal Form-main" method="GET">
        <div class="text-center">
            <h2>Reset Password:</h2>
            <?php
            //getting the username from the last page
            //var_dump($_GET);
            //var_dump($_SESSION);
            $username = $_GET['username'];
            echo "<p id='username'>Username: " . $username . "</p>";
            ?>
            <br>
        </div>
        <div class="mb-3 row">
            <label for="txtPassword" class="col-sm-2 col-form-label">New Password:</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="txtPasswordOne">
            </div>
            <div class="mb-3 row">
                <label for="txtPassword" class="col-sm-2 col-form-label">Confirm Password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="txtPasswordTwo">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-12 text-center">
                    <button ID="btnResetPassword" type="submit" class="btn btn-secondary btn-lg">Reset Password</button>
                    <button id="btnCancel" type="submit" class="btn btn-secondary btn-lg">Cancel
                    </button>
                </div>
            </div>
            <div class="text-center">
                <input class="form-check-input" type="checkbox" id="passwordCheckbox"><label for="passwordCheckbox">Show Password</label>
            </div>
            <!--End of Login Form-->
    </form>
</body>

</html>