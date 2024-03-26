//defining the PasswordEncrypter object
const PasswordEncrypter = {

    getSHA256Hash: async function (input) {
        const textAsBuffer = new TextEncoder().encode(input);
        const hashBuffer = await window.crypto.subtle.digest("SHA-256", textAsBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hash = hashArray
            .map((item) => item.toString(16).padStart(2, "0"))
            .join("");
        return hash;
    },
    getSalt: async function (input) {
        const textAsBuffer = new TextEncoder().encode(input);
        const hashBuffer = await window.crypto.subtle.digest("SHA-256", textAsBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hash = hashArray
            .map((item) => item.toString(16).padStart(2, "0"))
            .join("");
        var middle = Math.floor(hash.length / 2);
        var salt = hash.substring(0, middle);
        return salt;
    },
};

//need to export the PasswordEncrypter object for use in other files or modules
//module.exports = PasswordEncrypter;
export { PasswordEncrypter };
