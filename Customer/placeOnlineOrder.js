var employeeObj;
var userPermissionsObj;
var cartArray = [];
var globalGrandTotal = 0;

window.onload = function () {

    //event for clicking on the help image
    document.querySelector("#helpImage").addEventListener("click", function () {
        alert("This is the page for placing an online order. To place an order, first pick your desired Bullseye site location then click on the 'View Inventory' button below, then add items that are in stock to your online order.");
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
            alert("This is the page for placing an online order. To place an order, first pick your desired Bullseye site location then click on the 'View Inventory' button below, then add items that are in stock to your online order.");
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

    document.querySelector("#viewInventoryBtn").addEventListener("click", getSiteInventory);

    //get all sites function
    getAllSites();

    //add event handler for selections in the table container
    document.querySelector("#tableContainer").addEventListener("click", handleRowClick);

    //call this ftn, sending in a false, to disable the add and delete buttons on window load
    setAddDeleteButtonState(false);

    hideItemsPanel();
    //showItemsPanel();

    //event handler for add button clicks
    document.querySelector("#addButton").addEventListener("click", addToCart);

    //event handler for delete button clicks
    document.querySelector("#deleteButton").addEventListener("click", removeFromCart);

    //event handler for the cancel button clicks
    document.querySelector("#cancelButton").addEventListener("click", cancelOrder);

    //event handler for the submit button clicks
    document.querySelector("#submitButton").addEventListener("click", submitOrder);

    //console.log(cartArray);
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

//getAllSites function, which calls the populateSitesDropdown function so long as there is good data
function getAllSites() {

    //URL here(refer to .htaccess file if needed)
    let url = "../bullseyeService/sites/stores";

    //method is GET
    let method = "GET";
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {

            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);

            //if - data is populated
            if (resp.data) {

                //console.log(resp.data);

                //send data to this ftn
                populateSitesDropdown(resp.data);
            }

            //else - error is populated
            else {
                alert(resp.error + " status code: " + xhr.status);
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send();
}

//populate teams dropdown function
//passing in teamObjects into this ftn
function populateSitesDropdown(siteObjects) {

    console.log(siteObjects);

    //teams dropdown
    let sitesDropdown = document.querySelector("#sites");

    //this line below is no longer needed!
    //let jsonObject = JSON.parse(roundObjects);

    let html = "";

    for (var prop in siteObjects) {
        html += "<option value='" + siteObjects[prop].siteID + "'>" + siteObjects[prop].siteID + " - " + siteObjects[prop].name + "</option>";
    }

    //add html to the teams dropdown
    sitesDropdown.innerHTML = html;
}

//ftn to get store/site inventory
function getSiteInventory() {

    //get the value (siteID) from the dropdown list
    let siteID = Number(document.querySelector("#sites").value);

    console.log(siteID);

    //URL here(refer to .htaccess file if needed)
    let url = "../bullseyeService/inventory/site/" + siteID;

    //method is GET
    let method = "GET";
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {

            console.log(this.responseText);
            let resp = JSON.parse(xhr.responseText);

            //console.log(resp);

            //if - data is populated
            if (resp.data) {

                console.log(resp.data);

                //send the data (array of objects) to this ftn
                buildInventoryTable(resp.data);

                //want to reset the innerhtml for the cart items list
                document.querySelector("#cartItemsList").innerHTML = '';

                //also reset the cart items array
                cartArray = [];
            }

            //else - error is populated
            else {
                alert(resp.error + " status code: " + xhr.status);
            }
        }
    };
    xhr.open(method, url, true);

    //can't send in object for a get
    //xhr.send(JSON.stringify(plainObject));
    xhr.send();
}

//function for building the inventory table
function buildInventoryTable(inventoryObjects) {

    //console.log(inventoryObjects);

    let html =
        "<table id='inventoryTable'><tr><th>Item ID</th><th>Item Name</th><th>Site Name</th><th>Price</th><th>Quantity Available</th><th>Select Quantity</th></tr>";
    for (let i = 0; i < inventoryObjects.length; i++) {
        let row = inventoryObjects[i];
        html += "<tr><td>" + row.itemID + "</td>";
        html += "<td>" + row.name + "</td>";
        html += "<td>" + row.siteName + "</td>";
        html += "<td>$" + new Intl.NumberFormat().format(row.price) + "</td>";
        html += "<td>" + row.quantity + "</td>";
        html += '<td class="nudQuantityCell"><input type="number"' + 'class="nudQuantity" name="nudQuantity" value="1" min="1" max="' + row.quantity + '"></td></tr>';
    }

    html += "</table>";
    let theTable = document.querySelector("#inventoryTable");
    let tableContainerDiv = document.querySelector("#tableContainer");
    tableContainerDiv.innerHTML = html;
}

function handleRowClick(evt) {
    clearSelections();

    //console.log(evt);

    //if evt target is a TD (table cell)
    if (evt.target.nodeName === "TD") {

        //add the selectedRow class to the evt target
        evt.target.parentElement.classList.add("selectedRow");

        //call this ftn, sending in a true, to enable the add and delete buttons if a row is clicked
        setAddDeleteButtonState(true);
    }
}

//function to remove the selectedRow class from a table row
function clearSelections() {

    //get all the rows
    let trs = document.querySelectorAll("tr");
    for (let i = 0; i < trs.length; i++) {

        //remove selectedRow class from the row
        trs[i].classList.remove("selectedRow");
    }
}

//function to enable or disable the add and delete buttons
function setAddDeleteButtonState(selectedRowState) {

    //if state is true
    if (selectedRowState) {
        document.querySelector("#deleteButton").removeAttribute("disabled");
        document.querySelector("#addButton").removeAttribute("disabled");
    }

    //else - state is false
    else {
        document.querySelector("#deleteButton").setAttribute("disabled", "disabled");
        document.querySelector("#addButton").setAttribute("disabled", "disabled");
    }
}

function hideItemsPanel() {

    //add the hidden class to the add update panel
    document.querySelector("#cartItemsPanel").classList.add("hidden");
}

function showItemsPanel() {

    //remove the hidden class from the add update panel
    document.querySelector("#cartItemsPanel").classList.remove("hidden");
}

//function to add an item to the cart
function addToCart() {
    let selectedRowItem = document.querySelector(".selectedRow");

    console.log(selectedRowItem);

    if (selectedRowItem !== null) {

        //first td cell of the selected row
        let itemID = Number(selectedRowItem.querySelectorAll("#tableContainer td")[0].innerHTML);

        //second td cell
        let itemName = selectedRowItem.querySelectorAll("#tableContainer td")[1].innerHTML;

        //third td cell
        //let siteName = selectedRowItem.querySelectorAll("#tableContainer td")[2].innerHTML;

        //fourth td cell - are removing the $sign from this string using substring.(1)
        let price = Number(selectedRowItem.querySelectorAll("#tableContainer td")[3].innerHTML.substring(1));

        //fifth td cell - the quantity available
        let quantityAvailable = Number(selectedRowItem.querySelectorAll("#tableContainer td")[4].innerHTML);

        //sixth td cell
        let quantitySelectedCell = selectedRowItem.querySelectorAll("#tableContainer td")[5];

        let quantitySelected = quantitySelectedCell.querySelector("input").valueAsNumber;

        console.log(itemID, itemName, price, quantityAvailable, quantitySelectedCell, quantitySelected);

        //looping thru the cart array - looking for duplicate items
        for (let i = 0; i < cartArray.length; i++) {
            let cartItem = cartArray[i];

            //if the itemID is a match, meaning that this item is already in the cart
            if (itemID === cartItem.itemID) {
                alert("Can't add another instance of an item to your cart that is already inside your cart.")

                //exit the function
                return;
            }
        }

        //if the quantity selected is greater than the quantity available
        if (quantitySelected > quantityAvailable) {
            alert("Quantity to add to your cart must be less than or equal to the quantity available at the selected Bullseye store.")

            //clear the selected row
            clearSelections();

            quantitySelectedCell.querySelector("input").valueAsNumber = 1;
        }

        //else if - the quantity selected is less than 1 or not a number
        else if (quantitySelected < 1 || isNaN(quantitySelected)) {
            alert("Please use the mini arrow buttons in a selected item row to select a desired valid quantity for that item in an online order.")

            //clear the selected row
            clearSelections();

            quantitySelectedCell.querySelector("input").valueAsNumber = 1;
        }

        //else - the quantity selected should be valid
        else {

            //constructing a plain txnItem object
            let txnItem = {
                itemID: itemID,
                itemName: itemName,
                price: price,
                totalPrice: price * quantitySelected,
                quantity: quantitySelected
            };

            //add the object to the cart array
            cartArray.push(txnItem);

            console.log(cartArray);

            //display the items panel
            showItemsPanel();

            displayCartItems();
        }
    }
}

//function to remove an item from the cart
function removeFromCart() {
    let selectedRowItem = document.querySelector(".selectedRow");

    console.log(selectedRowItem);

    if (selectedRowItem !== null) {

        //first td cell of the selected row
        let itemID = Number(selectedRowItem.querySelectorAll("#tableContainer td")[0].innerHTML);

        //second td cell
        let itemName = selectedRowItem.querySelectorAll("#tableContainer td")[1].innerHTML;

        //third td cell
        //let siteName = selectedRowItem.querySelectorAll("#tableContainer td")[2].innerHTML;

        //fourth td cell - are removing the $sign from this string using substring.(1)
        let price = Number(selectedRowItem.querySelectorAll("#tableContainer td")[3].innerHTML.substring(1));

        //fifth td cell - the quantity available
        let quantityAvailable = Number(selectedRowItem.querySelectorAll("#tableContainer td")[4].innerHTML);

        //sixth td cell
        let quantitySelectedCell = selectedRowItem.querySelectorAll("#tableContainer td")[5];

        let quantitySelected = quantitySelectedCell.querySelector("input").valueAsNumber;

        console.log(itemID, itemName, price, quantityAvailable, quantitySelectedCell, quantitySelected);

        let itemRemoved = false;

        //looping thru the cart array - looking for duplicate items
        for (let i = 0; i < cartArray.length; i++) {
            let cartItem = cartArray[i];

            //if the itemID is a match, meaning that this item is already in the cart
            if (itemID === cartItem.itemID) {

                //remove the item from the cart array
                cartArray.pop(cartItem);

                //if the cart array no longer has any items, then hide it
                if (cartArray.length < 1) {
                    hideItemsPanel();
                }

                alert("Item " + itemID + " - " + itemName + " with a quantity of " + quantitySelected + " has been completely removed from your cart.");

                itemRemoved = true;

                if (cartArray.length > 0) {
                    displayCartItems();
                }

                //exit the function
                return;
            }
        }

        if (itemRemoved === false) {
            alert("Can't remove that item since it's currently not in your cart.")
        }
    }
}

//function to display the cart items
function displayCartItems() {

    //getting the ul element to put the cart items in
    let cartItemsList = document.querySelector("#cartItemsList");

    //also getting the div for the price details
    let priceDetails = document.querySelector("#priceDetails");

    //reset the innerhtml for this element
    cartItemsList.innerHTML = '';

    //var for subtotal
    let subTotal = 0;

    //looping thru the cart array - looking for duplicate items
    for (let i = 0; i < cartArray.length; i++) {
        let cartItem = cartArray[i];

        cartItemsList.innerHTML += "<li>" + cartItem.itemID + " - " + cartItem.itemName + " - $" + new Intl.NumberFormat().format(cartItem.price) + " - x" + cartItem.quantity + "</li>"

        //add to the subtotal
        subTotal += cartItem.totalPrice;

        console.log(subTotal, cartItem.totalPrice);
    }

    //calculate the tax amount
    let taxAmount = Math.round(subTotal * 0.15 * 100) / 100;

    //calculate the grand total
    let grandTotal = Math.round((subTotal + taxAmount) * 100) / 100;

    console.log(taxAmount, grandTotal);

    //first, reset the innerhtml for the price details div
    priceDetails.innerHTML = '';

    //display these totals
    priceDetails.innerHTML += "<p>Subtotal: $" + new Intl.NumberFormat().format(subTotal) + "</p>";
    priceDetails.innerHTML += "<p>HST (15%): $" + new Intl.NumberFormat().format(taxAmount) + "</p>";
    priceDetails.innerHTML += "<p id='grandTotal'>Grand Total: $" + new Intl.NumberFormat().format(grandTotal) + "</p>";

    //and are assigning the grand total to the global var for this
    globalGrandTotal = grandTotal;

}

//function to cancel an order
function cancelOrder() {

    //empty the array
    cartArray = [];

    //hide the items panel
    hideItemsPanel();

    //clear any row selections
    clearSelections();

    alert("Your order has been cancelled, and all items that were in your cart have been removed.")

}

function subtractHours(date, hours) {
    date.setHours(date.getHours() - hours);

    return date;
}


//function to submit an order
function submitOrder() {

    //getting the name, email, and phone inputs
    let name = document.querySelector("#name").value;
    let email = document.querySelector("#email").value;
    let phone = document.querySelector("#phone").value;

    //get the value (siteID) from the dropdown list - this will be both the siteIDTo and the siteIDFrom
    let siteID = Number(document.querySelector("#sites").value);

    //get the current date
    let currentDate1 = new Date(Date.now());

    currentDate1 = subtractHours(currentDate1, 3);

    let currentDate2 = currentDate1.toISOString().slice(0, 19).replace('T', ' ');

    //get the date 24 hours from now
    let tomorrowDate1 = new Date(Date.now() + 24 * 60 * 60 * 1000);

    tomorrowDate1 = subtractHours(tomorrowDate1, 3);

    let tomorrowDate2 = tomorrowDate1.toISOString().slice(0, 19).replace('T', ' ');

    console.log(name, email, phone, siteID, globalGrandTotal, currentDate2, tomorrowDate2);

    //if any of these are empty, alert the user and exit the ftn
    if (name === "" || email === "" || phone === "" || name === undefined || email === undefined || phone === undefined) {
        alert("Please enter your name, email, and phone number to proceed with submitting your order.")

        return;

    }

    //can now concat a string for notes
    let notesString = "Name: " + name + " Email: " + email + " Phone: " + phone + " Order Total: " + globalGrandTotal;

    console.log(notesString);

    //using GET here - to get the last txn
    let method = "GET";
    let url = "../bullseyeService/txns/lastTxn"
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the last txn exists
            if (resp.data) {
                //alert("Last txn exists.");

                let lastTxnObj = resp.data;

                //need the latest barcode from this obj
                let newBarCode = Number(lastTxnObj.barCode) + 1;

                //also the new txn ID should be this (the last txn + 1)
                let newTxnID = lastTxnObj.txnID + 1;

                console.log(newBarCode);

                //now will create a plain txn object
                let plainTxnObj = {
                    txnID: newTxnID,
                    siteIDTo: siteID,
                    siteIDFrom: siteID,
                    status: "New",
                    shipDate: tomorrowDate2,
                    txnType: "Online Order",
                    barCode: newBarCode,
                    createdDate: currentDate2,
                    notes: notesString
                };

                //using POST here - to insert this new online order
                let method = "POST";
                let url = "../bullseyeService/txns/" + newTxnID;
                let xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        console.log(xhr.responseText);
                        let resp = JSON.parse(xhr.responseText);
                        console.log(resp.data)

                        //should be data if the insert succeeded
                        if (resp.data) {
                            //alert(resp.data);

                            //looping thru the cart array - need to insert a txnitem for each item in the cart
                            for (let i = 0; i < cartArray.length; i++) {
                                let cartItem = cartArray[i];

                                //constructing a plain txnitems obj here
                                let plainTxnItemObj = {
                                    txnID: newTxnID,
                                    itemID: cartItem.itemID,
                                    quantity: cartItem.quantity,
                                    notes: ""
                                };

                                //using POST here - to insert this new online order
                                let method = "POST";
                                let url = "../bullseyeService/txnitems/" + newTxnID;
                                let xhr = new XMLHttpRequest();
                                xhr.onreadystatechange = function () {
                                    if (xhr.readyState === XMLHttpRequest.DONE) {
                                        //console.log(xhr.responseText);
                                        let resp = JSON.parse(xhr.responseText);
                                        console.log(resp.data)

                                        //should be data if the insert succeeded
                                        if (resp.data && i === cartArray.length - 1) {
                                            //alert(resp.data)

                                            //hide the panel and empty the array
                                            hideItemsPanel();
                                            cartArray = [];
                                            getSiteInventory();

                                            alert("Your online order has been successfully submitted. It will be ready for pickup at " + plainTxnObj.shipDate + ". \n\nPlease record or write down your order ID: " + newTxnID + ".")

                                        }

                                        //else - insert failed
                                        else if (resp.error) {
                                            alert(resp.error);
                                        }
                                    }
                                };
                                xhr.open(method, url, true);
                                xhr.send(JSON.stringify(plainTxnItemObj));
                            }

                        }

                        //else - insert failed
                        else {
                            alert(resp.error);
                        }
                    }
                };
                xhr.open(method, url, true);
                xhr.send(JSON.stringify(plainTxnObj));
            }

            //else - employee/user likely doesn't exist
            else {
                alert(resp.error);
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send();
}