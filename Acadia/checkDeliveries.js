var employeeObj;
var userPermissionsObj;

window.onload = function () {
    //event for clicking on the help image
    document.querySelector("#helpImage").addEventListener("click", function () {
        alert("This is the page for checking Acadia deliveries for Bullseye. Please select a date from the date time picker input below, then click on the button to view delivery info for the selected date.");
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
            alert("This is the page for checking Acadia deliveries for Bullseye. Please select a date from the date time picker input below, then click on the button to view delivery info for the selected date.");
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

    //setting the default date for the date input to today
    document.querySelector('#deliveryDate').valueAsDate = new Date();

    document.querySelector("#viewBtn").addEventListener("click", checkDeliveries);
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
    let url = "../bullseyeService/employees/" + user.username;
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

//function to check the deliveries
function checkDeliveries() {
    //get the input from the calendar
    let selectedDate = document.querySelector("#deliveryDate").value;

    console.log(selectedDate);

    let plainObj = {
        shipDate: selectedDate
    };

    //using GET here - to get all the store orders for the selected date
    let method = "POST";
    let url = "../bullseyeService/txns/storeorders"
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if there is at least 1 store order on the date
            if (resp.data) {
                //alert("At least one store order exists on the selected date.");

                //results - should be an array
                let txnsArray = resp.data;

                //send the txnsarray to build the txns table ftn
                buildTxnsTable(txnsArray);

                //using GET here - to get the delivery weight on the selected dae
                let method = "POST";
                let url = "../bullseyeService/txnitems/deliveryWeight"
                let xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        console.log(xhr.responseText);
                        let resp = JSON.parse(xhr.responseText);
                        console.log(resp.data)

                        //should be data if the delivery weight exists
                        if (resp.data) {
                            //alert("Delivery weight exists.");

                            //results - should be an array
                            let txnItemObj = resp.data;

                            //get the delivery weight div
                            let weightDiv = document.querySelector("#weightContainer");

                            //reset the text inside it
                            weightDiv.innerHTML = "";

                            //get the recommended vehicle type
                            let vehicleType = getRecommendedVehicleType(txnItemObj.weight);

                            weightDiv.innerHTML += "<p>Delivery Weight for all Store Orders on " + selectedDate + ": " + txnItemObj.weight + "</p>";

                            weightDiv.innerHTML += "<p>Vehicle Needed for " + selectedDate + ": " + vehicleType + "</p>";
                        }

                        //else - delivery weight likely doesn't exist
                        else {
                            alert(resp.error);

                            //return resp.error;
                        }
                    }
                };
                xhr.open(method, url, true);
                xhr.send(JSON.stringify(plainObj));
            }

            //else - employee/user likely doesn't exist
            else {
                alert(resp.error);

                //return resp.error;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(plainObj));
}

//function for building the txns table
function buildTxnsTable(txnObjects) {

    //console.log(txnObjects);

    let html =
        "<table id='txnsTable'><tr><th>Order ID</th><th>Origin Site</th><th>Destination Site</th><th>Status</th><th>Order Type</th><th>Created Date</th><th>Ship Date</th></tr>";
    for (let i = 0; i < txnObjects.length; i++) {
        let row = txnObjects[i];
        html += "<tr><td>" + row.txnID + "</td>";
        html += "<td>" + row.originSite + "</td>";
        html += "<td>" + row.destinationSite + "</td>";
        html += "<td>" + row.status + "</td>";
        html += "<td>" + row.txnType + "</td>";
        html += "<td>" + row.createdDate + "</td>";
        html += "<td>" + row.shipDate + "</td></tr>";
    }

    html += "</table>";
    let tableContainerDiv = document.querySelector("#tableContainer");
    tableContainerDiv.innerHTML = html;
}

//function that gets the recommended vehicle type
function getRecommendedVehicleType(weight) {

    let vehicleType = "";

    if (weight > 0 && weight <= 1000) {
        vehicleType = "Van";
    }

    else if (weight > 1000 && weight <= 5000) {
        vehicleType = "Small";
    }

    else if (weight > 5000 && weight <= 10000) {
        vehicleType = "Medium";
    }

    //else - should be a heavy vehicle
    else {
        vehicleType = "Heavy";
    }

    //return the string vehicle type
    return vehicleType;
}