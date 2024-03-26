//defining the PasswordValidator object
const PasswordValidator = {

    //just one method in this module for validating passwords
    validatePassword: function (password) {

        //declaring vars, including a bool and error message to be returned
        let goodPassword = false;
        const input = password;
        let errorMessage = '';

        //regex const containing all of the password requirements
        const hasAllRequirements = new RegExp('^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$%^&+=]).*$');

        if (input.trim() === '') {
            errorMessage = 'Password must not be empty.';
        } else if (!hasAllRequirements.test(input)) {
            errorMessage = 'Password does not meet requirements. It must be between 8 and 64 characters long, contain 1 capital letter, contain 1 number and contain 1 special character.';
        }
        //else - password should be good
        else {
            goodPassword = true;
        }

        //returning a JS plain object containing the boolean and error message
        return { valid: goodPassword, error: errorMessage };
    }
};

//need to export the PasswordValidator object for use in other files or modules
//module.exports = PasswordValidator;
export { PasswordValidator };