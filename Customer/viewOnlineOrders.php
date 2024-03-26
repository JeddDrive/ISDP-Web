<!DOCTYPE html>
<?php
session_start();
?>
<html>

<head>
    <!--Metadata-->
    <meta charset="UTF-8">
    <meta name="description" content="This is the view online orders page for the Bullseye Inventory Management System">
    <link href="../Images/bullseye-logo.ico" rel="icon">
    <title>Bullseye - View Online Orders</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to my selected Google Fonts - Outfit, Poppins, and Ubuntu -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <!-- Style Sheets -->
    <link href="../mainStyle.css" rel="stylesheet" type="text/css">
    <link href="viewOnlineOrders.css" rel="stylesheet" type="text/css">

    <!-- Scripts for Bootstrap (ex. need this for bootstrap navbar button) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- JavaScript -->
    <script type="module" src="viewOnlineOrders.js"></script>

</head>

<body>
    <!-- Navbar - includes all tasks done in web, using dropdown lists -->
    <div>
        <nav class="navbar navbar-expand-md navbar-dark" id="navbarOne">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="../Images/bullseye-nobackground.png" alt="Navbar Logo (Bullseye)" class="d-inline-block align-text-top img-fluid" id="navbarOneImage"></a>
                <h3 id="navbarHeading">Bullseye</h3>
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- Navbar links in this div container here -->
                <!-- For the active page, add the "active" class and "activeLink" id to the <a> tag as seen below -->
                <!-- For non-active pages, just do "nav-link" for the class in the <a> tags -->
                <div class="navbar-collapse collapse" id="collapseNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Customers
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item dropdownItem" href="placeOnlineOrder.php">Place Online
                                        Order</a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdownItem" href="#">View Online
                                        Orders</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="acadia" disabled>
                                Acadia
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item dropdownItem" href="../Acadia/checkDeliveries.php">Check
                                        Deliveries</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" role="button" aria-expanded="false" id="storeEmployees" disabled>
                                Store Employees
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" id="homeLink" href="../homepage.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="logoutLink" href="../index.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <!-- Div for the help image -->
    <div><img src="../Images/help.png" alt="Help Image" id="helpImage"></div>
    <?php
    //getting the username from the last page
    //var_dump($_POST);
    //var_dump($_SESSION);
    $username = $_SESSION['username'];
    echo "<div id='userInfo'><p id='user'>User: " . $username . "</p>";
    echo "<p id='location'>Location: " . $username . "</p></div>";
    ?>
    <br>
    <h2 id="mainHeading">View Online Orders</h2>
    <!-- div container containing some basic info -->
    <div id="mainContainer">
        <p for="searchBox" id="searchLabel">Search by order ID, your name, or your e-mail address:</p><br>
        <div class="input-group mb-3">
            <input type="text" placeholder="Search here" id="searchBox">
        </div>
        <button type="button" class="btn btn-info" id="viewBtn">Search for Online Order</button>
        <!-- div container for the table -->
        <div id="tableContainer"></div>
    </div>
</body>

</html>