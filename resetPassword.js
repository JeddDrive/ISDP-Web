//importing the password encrypter and validator modules from the utils folder
import { PasswordEncrypter } from './utils/PasswordEncrypter.js';
import { PasswordValidator } from './utils/PasswordValidator.js';
//var employeeObj;

window.onload = function () {
    //getting the employee object first - need their employee ID
    //employeeObj = getEmployee();

    document.querySelector("#btnResetPassword").addEventListener("click", resetPassword);

    document.querySelector("#passwordCheckbox").addEventListener("click", hideShowPasswords);

    document.querySelector("#btnCancel").addEventListener("click", function () {
        window.location.href = './index.php';
    });

    //event for clicking on the help image
    document.querySelector("#helpImage").addEventListener("click", function () {
        alert("Please enter a new password here, and ensure that that you confirm it before resetting it." +
            "\n\nPasswords Requirements: At least 1 upper case letter, 1 number, 1 special character, and at least 8 characters long.");
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
            alert("Please enter a new password here, and ensure that that you confirm it before resetting it." +
                "\n\nPasswords Requirements: At least 1 upper case letter, 1 number, 1 special character, and at least 8 characters long.");
        }
    });
};

//function to get an employee (ex. an updated employee object if needed)
function getEmployee() {

    //getting the username div
    let usernameDiv = document.querySelector("#username").innerHTML;

    let usernameArray = usernameDiv.split(" ");

    //just need the 2nd element of this array
    let username = usernameArray[1];

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
    xhr.send(JSON.stringify(user));
}

//function to update an employee's password
function updateEmployeePassword(employeeObj) {
    //console.log(employeeObj);

    //using PUT here - to update an employee's password
    let method = "PUT";
    let url = "bullseyeService/employees/password/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data && xhr.status === 200) {
                //alert("Employee password has been successfully reset.");

                alert("Password has been successfully reset.")

                //and take the user back to the index login page
                window.location.href = './index.php';
            }

            //else - error with the update
            else {
                alert(resp.error + " status code: " + xhr.status);

                //return false;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(employeeObj));
}

//function to update an employee's madeFirstLogin to 1 if needed
function updateEmployeeMadeFirstLogin(employeeObj) {
    //console.log(employeeObj);

    //using PUT here - to update an employee's password
    let method = "PUT";
    let url = "bullseyeService/employees/madeFirstLogin/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data && xhr.status === 200) {
                //alert("Employee's made first login has been updated to 1.");

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

//function to update an employee's password SALT
function updateEmployeePasswordSalt(newSalt, employeeObj) {
    //console.log(employeeObj);

    //need a passwordsalt object
    let passwordSaltObj = {
        employeeID: employeeObj.employeeID,
        passwordSalt: newSalt
    };

    //using PUT here - to update an employee's password
    let method = "PUT";
    let url = "bullseyeService/passwordSalt/employee/" + employeeObj.employeeID;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data && xhr.status === 200) {
                //alert("Employee password has been successfully reset.");

                //alert("Password salt for employee has been successfully reset.")
            }

            //else - error with the update
            else {
                alert(resp.error + " status code: " + xhr.status);

                //return false;
            }
        }
    };
    xhr.open(method, url, true);
    xhr.send(JSON.stringify(passwordSaltObj));
}

//function to reset the password once that button is clicked
function resetPassword() {

    //getting the username div
    let usernameDiv = document.querySelector("#username").innerHTML;

    let usernameArray = usernameDiv.split(" ");

    //just need the 2nd element of this array
    let username = usernameArray[1];

    let user = {
        username: username
    };
    //using GET here - to get one employee
    let method = "GET";
    let url = "bullseyeService/employees/" + user.username;
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = async function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            //console.log(xhr.responseText);
            let resp = JSON.parse(xhr.responseText);
            //console.log(resp.data)

            //should be data if the employee/username exists
            if (resp.data) {
                //alert("Employee exists.");
                let employeeObj = resp.data;

                console.log(employeeObj);

                //getting the two password textbox values
                let txtPasswordNew = document.querySelector("#txtPasswordOne").value;
                let txtPasswordConfirm = document.querySelector("#txtPasswordTwo").value;

                console.log(txtPasswordConfirm);

                //if the passwords match each other
                if (txtPasswordNew === txtPasswordConfirm) {

                    //now validate the new password
                    let validateObj = PasswordValidator.validatePassword(txtPasswordConfirm);

                    console.log(validateObj);

                    //if validateObj.valid is true and the error message is empty
                    if (validateObj.valid === true && validateObj.error === "") {

                        //generating a random string 32 characters long
                        let randomString = generateRandomString(32);

                        //first need a new salt for the employee, sending in the random string
                        let newSalt = await PasswordEncrypter.getSalt(randomString);

                        console.log(newSalt, employeeObj);

                        //now update the password salt for the employee
                        updateEmployeePasswordSalt(newSalt, employeeObj);

                        //now get a new hashed password - based on the password plus the new salt text
                        let newHash = await PasswordEncrypter.getSHA256Hash(txtPasswordConfirm + newSalt);

                        console.log(newHash);

                        console.log(employeeObj)

                        //set the employee obj's password to the new hashed password
                        employeeObj.password = newHash;

                        //if employee's made first login is 0 then also should update that
                        if (employeeObj.madeFirstLogin === 0) {
                            let goodMadeFirstLoginUpdate = updateEmployeeMadeFirstLogin(employeeObj);
                        }

                        //can now finally reset the password
                        updateEmployeePassword(employeeObj);

                    }

                    //else - new password is not valid
                    else {
                        alert(validateObj.error);
                    }
                }

                //else - passwords don't match
                else {
                    alert("Passwords do not match. Please ensure that the confirmed password matches the new password.")
                }

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

//function to hide and show the passwords (both textboxes on this form)
function hideShowPasswords() {
    var x = document.querySelector("#txtPasswordOne")
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }

    var y = document.querySelector("#txtPasswordTwo")
    if (y.type === "password") {
        y.type = "text";
    } else {
        y.type = "password";
    }
}

//function to generate a random string
function generateRandomString(length) {
    let result = '';
    const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    const charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}