var employeeObj;
var userPermissionsObj;

window.onload = function () {
    //event for clicking on the help image
    document.querySelector("#helpImage").addEventListener("click", function () {
        alert("Welcome, you are now in the Bullseye Web dashboard. Click on any links that are accessible to you in the above navigation bar in order to do that specific task.");
    });

    //adding event listener for the F1 keydown event
    var addEvent = document.addEventListener ? function (target, type, action) {
        if (target) {
            target.addEventListener(type, action, false);
        }
    } : function (target, type, action) {
        if (target) {
            target.attachEvent('on' + type, action, false);
        }
    }

    addEvent(document, 'keydown', function (e) {
        e = e || window.event;
        var key = e.which || e.keyCode;
        //112 is the keycode for F1
        if (key === 112) {
            alert("Welcome, you are now in the Bullseye Web dashboard. Click on any links that are accessible to you in the above navigation bar in order to do that specific task.");
        }
    });

    //getting the username div
    let usernameDiv = document.querySelector("#user").innerHTML;

    let usernameArray = usernameDiv.split(" ");

    //just need the 2nd element of this array (the username itself)
    let username = usernameArray[1];

    //if the username is NOT Customer, then call the get employee ftn

    if (username !== 'Customer') {
        //call the getEmployee function
        employeeObj = getEmployee(username)
    }

    document.querySelector("#activeLink").addEventListener("click", function () {
        window.location.href = './homepage.php?username=' + username;
    });
};

//function to get an employee (ex. an updated employee object if needed)
function getEmployee(username) {

    //getting the location div
    let locationDiv = document.querySelector("#location");

    let user = {
        username: username
    };
    //using GET here - to get one employee
    let method = "GET";
    let url = "bullseyeService/employees/" + user.username;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data) {
                //alert("Employee exists.");

                employeeObj = resp.data;

                locationDiv.innerHTML = "Location: " + employeeObj.siteName;

                //get the employee's user permissions
                getUserPermissions(employeeObj);

                //checking the employee's PositionID
                //if the employee is a store manager/employee
                if (employeeObj.positionID === 3) {
                    //remove attribute from the following navbar links
                    document.querySelector('#storeEmployees').removeAttribute('disabled');
                }

                //else if - the employee is a trucking/delivery (ex. Acadia)
                else if (employeeObj.positionID === 5) {
                    //remove attribute from the following navbar links
                    document.querySelector('#acadia').removeAttribute('disabled');
                }

                //else if - the employee is the admin
                else if (employeeObj.positionID === 99999999) {
                    //remove attribute from the following navbar links
                    document.querySelector('#storeEmployees').removeAttribute('disabled');
                    document.querySelector('#acadia').removeAttribute('disabled');
                }

                //return resp.data;
            }

            //else - employee/user likely doesn't exist
            else {
                alert(resp.error);

                //return resp.error;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(user));
}

//function to get an employee's user permissions
function getUserPermissions(employeeObj) {

    //using GET here - to get one employee
    let method = "GET";
    let url = "bullseyeService/userpermissions/employee/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            //console.log(resp.data)

            //should be data if the employee's user permissions exist
            if (resp.data) {
                //alert("Employee's user permissions found.");

                userPermissionsObj = resp.data;
                //return resp.data;
            }

            //else - user permissions for employee likely doesn't exist
            else {
                alert(resp.error);

                //return resp.error;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}