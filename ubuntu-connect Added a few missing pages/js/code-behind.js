document.addEventListener("DOMContentLoaded", function() {
    //Client-Side Login Validation
    const loginForm = document.getElementById("loginForm");
    if (loginForm) 
    {
        loginForm.addEventListener("submit", function(e) 
        {
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value.trim();

            
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === "" || password === "") 
            {
                e.preventDefault(); // Stop form submission
                alert("Please fill in All fields to log in.");
                return false;
            }

            //checks if the email is valid
            if (!emailPattern.test(email)) 
            {
            e.preventDefault();
            alert("Please enter a valid email address.");
            return;
            }
        });
    }

    //Client-Side Registration Validation & Password Matching
    const registerForm = document.querySelector("form[action='connect-register.php']");
    if (registerForm) {
        // Add an ID to the password field in your HTML if needed, or target by name
        const passwordInput = registerForm.querySelector("input[name='password']");
        
        //Gets input from users from the form before submitting
        registerForm.addEventListener("submit", function(e) {
            const username = registerForm.querySelector("input[name='username']").value.trim();
            const email = registerForm.querySelector("input[name='email']").value.trim();
            const password = passwordInput.value;

            // Whitespace check
            if (username === "" || email === "" || password === "") {
                e.preventDefault();
                alert("Error: Please fill in all fields before registering.");
                return false;
            }


            //Ensures that the password is minimum 6 characters long
            if (password.length < 6) {
                e.preventDefault();
                alert("Security Warning: Password must be at least 6 characters long.");
                return false;
            }
        });
    }
});