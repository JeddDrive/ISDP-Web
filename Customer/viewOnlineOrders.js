var employeeObj;
var userPermissionsObj;

window.onload = function () {
    //event for clicking on the help image
    document.querySelector("#helpImage").addEventListener("click", function () {
        alert("This is the page for viewing online orders. To see the status of your order, please search by your order ID or e-mail address, and then click on the button below the search box.");
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
            alert("This is the page for viewing online orders. To see the status of your order, please search by your order ID or e-mail address, and then click on the button below the search box.");
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

    document.querySelector("#viewBtn").addEventListener("click", conductSearch);
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

//function to conduct the online order search
function conductSearch() {

    //first, get the value from the search box
    let searchValue = document.querySelector("#searchBox").value;

    //parsing the search box value
    let searchValueNumber = Number(searchValue);

    console.log(searchValue, searchValueNumber);

    //if the search value is an empty string
    if (searchValue == "") {
        alert("Please enter an order ID or e-mail address to view your online order details and status.");
    }

    //else if the search value number is not a number, meaning that are doing a search by the user's e-mail address
    else if (isNaN(searchValueNumber)) {

        //call search by notes ftn
        searchByNotes(searchValue);
    }

    //else - the search value number IS a number, meaning will do a search by txn/order ID
    else {

        //call search by txn ID ftn
        searchByTxnID(searchValueNumber);
    }
}

//function to search for a txn based on the txn ID
function searchByTxnID(searchValueNumber) {

    let txn = {
        onlineOrderID: searchValueNumber
    };

    //using GET here - to get one txn
    let method = "GET";
    let url = "../bullseyeService/txns/onlineOrder/" + txn.onlineOrderID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the txn exists
            if (resp.data) {
                //alert("Online Order exists.");

                let txnObj = resp.data;

                //build the table - just for the one txn object
                buildTableOneObject(txnObj);

                //return resp.data;
            }

            //else - txn likely doesn't exist
            else {
                alert(resp.error);

                //return resp.error;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(txn));
}

//function to search for a txn based on their notes or email
function searchByNotes(searchValue) {

    let txn = {
        notes: searchValue
    };

    //console.log(searchValue, txn.notes)

    //using GET here - to get one txn
    let method = "GET";
    let url = "../bullseyeService/txns/notes/" + txn.notes;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if at least one txn exists and matches the notes
            if (resp.data) {
                //alert("Txn exists.");

                let arrayOfObjects = resp.data;

                //build the table - for the array of objects
                buildTableArrayObjects(arrayOfObjects);

                //return resp.data;
            }

            //else - no txns matching the notes field exist
            else {
                alert(resp.error);

                //return resp.error;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(txn));

}

//txnObj should be a single txn object, already JSON parsed
function buildTableOneObject(txnObj) {

    let html =
        "<table><tr><th>Order ID</th><th>Status</th><th>Store</th><th>Order Type</th><th>Created Date</th><th>Ship Date</th><th>Notes</th></tr>";
    html += "<tr><td>" + txnObj.txnID + "</td>";
    html += "<td>" + txnObj.status + "</td>";
    html += "<td>" + txnObj.destinationSite + "</td>";
    html += "<td>" + txnObj.txnType + "</td>";
    html += "<td>" + txnObj.createdDate + "</td>";
    html += "<td>" + txnObj.shipDate + "</td>";
    html += "<td>" + txnObj.notes + "</td></tr>";
    html += "</table>";
    let theTable = document.querySelector("#tableContainer");
    theTable.innerHTML = html;
}

//text is a JSON string containing an array
function buildTableArrayObjects(arrayOfObjects) {

    //getting an array of objects using JSON.parse
    //let arrayOfObjects = JSON.parse(text);
    let html =
        "<table><tr><th>Order ID</th><th>Status</th><th>Store</th><th>Order Type</th><th>Created Date</th><th>Ship Date</th><th>Notes</th></tr>";
    for (let i = 0; i < arrayOfObjects.length; i++) {
        let row = arrayOfObjects[i];
        html += "<tr><td>" + row.txnID + "</td>";
        html += "<td>" + row.status + "</td>";
        html += "<td>" + row.destinationSite + "</td>";
        html += "<td>" + row.txnType + "</td>";
        html += "<td>" + row.createdDate + "</td>";
        html += "<td>" + row.shipDate + "</td>";
        html += "<td>" + row.notes + "</td></tr>";
    }
    html += "</table>";
    let theTable = document.querySelector("#tableContainer");
    theTable.innerHTML = html;
}