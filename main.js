//importing the password encrypter and validator modules from the utils folder
import { PasswordEncrypter } from './utils/PasswordEncrypter.js';
import { PasswordValidator } from './utils/PasswordValidator.js';

window.onload = function () {
    document.querySelector("#btnLogin").addEventListener("click", checkLogin);

    document.querySelector("#passwordCheckbox").addEventListener("click", hideShowPassword);

    document.querySelector("#btnCustomer").addEventListener("click", function () {
        window.location.href = './homepage.php?username=Customer';
    });

    document.querySelector("#forgotPasswordLink").addEventListener("click", forgotPassword);

    //event for clicking on the help image
    document.querySelector("#helpImage").addEventListener("click", function () {
        alert("Please enter your username and password to login. You can also click on 'Forgot Password?' in order to reset your password if needed.");
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
            alert("Please enter your username and password to login. You can also click on 'Forgot Password?' in order to reset your password if needed.");
        }
    });
};

/* let test = await PasswordEncrypter.getSHA256Hash('test');

console.log(test, test.length);

let test2 = await PasswordEncrypter.getSalt('test');

console.log(test2, test2.length);

let test3 = PasswordValidator.validatePassword('testText');

console.log(test3) */

function checkLogin() {
    let password = document.querySelector("#txtPassword").value;
    let username = document.querySelector("#txtUsername").value.toLowerCase();

    //if the username field is empty, display this msg
    if (username === "") {
        alert("Username can't be empty. Must enter a valid username to login.")
    }

    //else - username field is not empty
    else {
        let user = {
            username: username,
            password: password
        };
        //using GET here - to get one employee
        let method = "GET";
        let url = "bullseyeService/employees/" + username;
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                //console.log(xhr.responseText);
                let resp = JSON.parse(xhr.responseText);
                console.log(resp.data)

                //should be data if the employee/username exists
                //also if the employee is NOT locked and IS active
                if (resp.data && resp.data.locked === 0 && resp.data.active === 1) {
                    //alert("Employee exists.");

                    //if employee is locked but has 0 login attempts
                    //meaning that they were previously locked but are no longer locked
                    //then want to update their login attempts back to 3
                    if (resp.data.locked === 0 && resp.data.loginAttempts === 0) {
                        updateLoginAttemptsToThree(resp.data);
                    }

                    //call the check credentials ftn, sending in the data which should be an employee object
                    checkCredentials(resp.data);
                }

                //else if - employee exists but is locked
                else if (resp.data !== null && resp.data.locked !== 0) {
                    alert("User account is locked. Please contact your administrator at admin@bullseye.ca to unlock your account.");
                }

                //else if - employee exists but is inactive
                else if (resp.data !== null && resp.data.active !== 1) {
                    alert("User account is inactive. Please contact your administrator at admin@bullseye.ca to reactivate your account.");
                }

                //else - employee/user likely doesn't exist
                else {
                    alert(resp.error);
                }
            }
        };
        xhr.open(method, url, true);
        xhr.send(JSON.stringify(user));
    }
}

//function to check an employee's login and password credentials if they exist
function checkCredentials(employeeObj) {
    //console.log(employeeObj);

    //getting the password from the textbox
    let password = document.querySelector("#txtPassword").value;

    //using GET here - to get the password salt for the one employee
    let method = "GET";
    let url = "bullseyeService/passwordSalt/employee/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = async function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data) {
                //alert("Employee's password salt exists.");

                //now that we have the password salt, can get the hash for the employee's password + the salt
                let userHash = await PasswordEncrypter.getSHA256Hash(password + resp.data.passwordSalt);

                //console.log(employeeObj.password)
                console.log(userHash, employeeObj.password);

                //if the hash returned matches the employee's hashed password in the DB
                //successful login
                if (userHash === employeeObj.password) {

                    //if employee has less than 3 login attempts, then update it back to 3
                    if (employeeObj.loginAttempts < 3) {
                        updateLoginAttemptsToThree(employeeObj);
                    }

                    //if - this isn't an employee's first login
                    if (employeeObj.madeFirstLogin === 1) {

                        alert("Password is a match. You are now logged in.");

                        //if this isn't an employee's first login, then take them to the homepage
                        window.location.href = './homepage.php?' + 'username=' + employeeObj.username;
                    }

                    //else - it is their first login, then prompt to to change/reset their password
                    else {
                        alert("Password is a match, but because this is your first login, please change the default password.");

                        window.location.href = './resetPassword.php?' + 'username=' + employeeObj.username;
                    }

                }

                //else - incorrect hash, meaning incorrect password
                else {
                    //subtract their login attempts by 1
                    updateLoginAttemptsMinusOne(employeeObj);

                    //need to get the employee's updated login attempts now
                    let updatedEmployeeObj = getEmployee(employeeObj);

                    employeeObj.loginAttempts -= 1;

                    console.log(employeeObj);
                    console.log(updatedEmployeeObj);

                    //if the employee's login attempts are not yet at 0
                    if (employeeObj.loginAttempts > 0) {
                        alert("Incorrect Password. You have " + employeeObj.loginAttempts + " login attempts remaining.")
                    }

                    //else - their login attempts has reached 0, then lock the account
                    else {
                        let goodUpdateLocked = updateEmployeeToLocked(employeeObj);

                        if (goodUpdateLocked) {
                            alert("Your account has been locked because of too many incorrect login attempts. Please contact your Administrator at admin@bullseye.ca for assistance.")
                        }
                    }
                }
            }

            //else - employee/user likely doesn't exist
            else {
                alert(resp.error);
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}

//function to update an employee's login credentials to 3 if needed
function updateLoginAttemptsToThree(employeeObj) {
    //console.log(employeeObj);

    //using PUT here - to update an employee's login attempts back to 3 if needed
    let method = "PUT";
    let url = "bullseyeService/employees/loginAttemptsToThree/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data && xhr.status === 200) {
                //alert("Employee's login attempts updated back to three.");

                return true;
            }

            //else - error with the update
            else {
                alert(resp.error + " status code: " + xhr.status);

                return false;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}

//function to subtract 1 from an employee's login attempts
function updateLoginAttemptsMinusOne(employeeObj) {
    //console.log(employeeObj);

    //using PUT here - to update an employee's login attempts back to 3 if needed
    let method = "PUT";
    let url = "bullseyeService/employees/loginAttemptsMinusOne/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data && xhr.status === 200) {
                //alert("Employee's login attempts subtracted by one.");

                return true;
            }

            //else - error with the update
            else {
                alert(resp.error + " status code: " + xhr.status);

                return false;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}

//function to get an employee (ex. an updated employee object if needed)
function getEmployee(employeeObj) {

    //using GET here - to get one employee
    let method = "GET";
    let url = "bullseyeService/employees/" + employeeObj.username;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data) {
                //alert("Employee exists.");

                return resp.data;
            }

            //else - employee/user likely doesn't exist
            else {
                alert(resp.error);

                return resp.error;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}

//function to update an employee to be locked
function updateEmployeeToLocked(employeeObj) {
    //console.log(employeeObj);

    //using PUT here - to update an employee to LOCKED
    let method = "PUT";
    let url = "bullseyeService/employees/locked/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data && xhr.status === 200) {
                alert("Employee has been successfully locked.");

                return true;
            }

            //else - error with the update
            else {
                alert(resp.error + " status code: " + xhr.status);

                return false;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}

//function to hide and show the password
function hideShowPassword() {
    var x = document.querySelector("#txtPassword")
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
}

//function for when clicking on the forgot password link
function forgotPassword() {
    let username = document.querySelector("#txtUsername").value.toLowerCase();

    //if the username field is empty, display this msg
    if (username === "") {
        alert("Username can't be empty. Must first enter a valid username in order to reset your password.")
    }

    //else - username field is not empty
    else {

        let user = {
            username: username
        };
        //using GET here - to get one employee
        let method = "GET";
        let url = "bullseyeService/employees/" + username;
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                //console.log(xhr.responseText);
                let resp = JSON.parse(xhr.responseText);
                console.log(resp.data)

                //should be data if the employee/username exists
                //also if the employee is NOT locked and IS active
                if (resp.data && resp.data.locked === 0 && resp.data.active === 1) {
                    //alert("Employee exists.");

                    //then redirect user to the reset password form
                    alert("Username recognized. Please reset your password on the next page.");

                    window.location.href = './resetPassword.php?' + 'username=' + username;
                }

                //else if - employee exists but is locked
                else if (resp.data !== null && resp.data.locked !== 0) {
                    alert("User account is locked. Please contact your administrator at admin@bullseye.ca to unlock your account.");
                }

                //else if - employee exists but is inactive
                else if (resp.data !== null && resp.data.active !== 1) {
                    alert("User account is inactive. Please contact your administrator at admin@bullseye.ca to reactivate your account.");
                }

                //else - employee/user likely doesn't exist
                else {
                    alert(resp.error);
                }
            }
        };
        xhr.open(method, url, true);
        xhr.send(JSON.stringify(user));
    }
}