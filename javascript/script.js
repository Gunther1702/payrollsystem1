document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.getElementById('toggleLoginPassword').addEventListener('click', function() {
        const passwordField = document.getElementById('loginPassword');
        this.classList.toggle('fa-eye-slash');
        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
    });

    document.getElementById('toggleSignupPassword').addEventListener('click', function() {
        const passwordField = document.getElementById('signupPassword');
        this.classList.toggle('fa-eye-slash');
        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
    });

    document.getElementById('toggleResetPassword').addEventListener('click', function() {
        const passwordField = document.getElementById('resetPassword');
        this.classList.toggle('fa-eye-slash');
        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
    });

    // Show/hide forms and clear inputs
    function clearInputs(form) {
        form.reset();
        const emailInput = form.querySelector('input[type="text"]');
        if (emailInput) {
            emailInput.value = '';
        }
    }

    document.getElementById('showSignup').addEventListener('click', function(e) {
        e.preventDefault();
        clearInputs(document.getElementById('loginForm'));
        document.getElementById('loginForm').style.display = 'none';
        const signupForm = document.getElementById('signupForm');
        signupForm.style.display = 'block';
        signupForm.classList.add('fade-in');
    });

    document.getElementById('showLogin').addEventListener('click', function(e) {
        e.preventDefault();
        clearInputs(document.getElementById('signupForm'));
        document.getElementById('signupForm').style.display = 'none';
        const loginForm = document.getElementById('loginForm');
        loginForm.style.display = 'block';
        loginForm.classList.add('fade-in');
    });

    document.getElementById('showResetPassword').addEventListener('click', function(e) {
        e.preventDefault();
        clearInputs(document.getElementById('loginForm'));
        document.getElementById('loginForm').style.display = 'none';
        const resetForm = document.getElementById('resetPasswordForm');
        resetForm.style.display = 'block';
        resetForm.classList.add('fade-in');
    });

    document.getElementById('showLoginFromReset').addEventListener('click', function(e) {
        e.preventDefault();
        clearInputs(document.getElementById('resetPasswordForm'));
        document.getElementById('resetPasswordForm').style.display = 'none';
        const loginForm = document.getElementById('loginForm');
        loginForm.style.display = 'block';
        loginForm.classList.add('fade-in');
    });

    // Validate email input based on selected role
    document.getElementById('role').addEventListener('change', function() {
        const isEmployee = this.value === 'employee';
        const emailInput = document.getElementById('loginUsername');
        emailInput.value = ''; // Clear previous input
        emailInput.placeholder = isEmployee ? 'Enter a valid Gmail' : 'Username';
    });

    document.getElementById('signupRole').addEventListener('change', function() {
        const isEmployee = this.value === 'employee';
        const emailInput = document.getElementById('signupUsername');
        emailInput.value = ''; // Clear previous input
        emailInput.placeholder = isEmployee ? 'Enter a valid Gmail' : 'Username';
    });

    // Function to validate username for admin role
    function isAdminUsernameValid(username) {
        return /^[a-zA-Z0-9.]+$/.test(username);
    }

    // Handle Login
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const username = document.getElementById('loginUsername').value;
        const password = document.getElementById('loginPassword').value;
        const role = document.getElementById('role').value;

        // Validate username for special characters if the role is admin
        if (role === 'admin' && !isAdminUsernameValid(username)) {
            Swal.fire({
                title: 'Error!',
                text: 'Admin username cannot contain special characters.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Validate Gmail for employee role
        if (role === 'employee' && !username.endsWith('@gmail.com')) {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a valid Gmail address.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        const body = `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&role=${encodeURIComponent(role)}`;

        fetch('log_in.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = data.redirect;
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });

    // Handle Signup
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const username = document.getElementById('signupUsername').value;

        // Validate username for special characters if the role is admin
        if (document.getElementById('signupRole').value === 'admin' && !isAdminUsernameValid(username)) {
            Swal.fire({
                title: 'Error!',
                text: 'Admin username cannot contain special characters.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Validate Gmail for employee role
        if (document.getElementById('signupRole').value === 'employee' && !username.endsWith('@gmail.com')) {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a valid Gmail address.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        fetch('sign_up.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("New record created successfully")) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Account created successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Clear all fields after success
                    document.getElementById('signupForm').reset();
                    document.getElementById('signupForm').style.display = 'none';
                    document.getElementById('loginForm').style.display = 'block';
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });

    // Handle Reset Password
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const username = document.getElementById('resetUsername').value;
        const newPassword = document.getElementById('resetPassword').value;

        const body = `username=${encodeURIComponent(username)}&new_password=${encodeURIComponent(newPassword)}`;

        fetch('reset_password_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                title: data.success ? 'Success!' : 'Error!',
                text: data.message,
                icon: data.success ? 'success' : 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                if (data.success) {
                    document.getElementById('resetPasswordForm').reset();
                    document.getElementById('resetPasswordForm').style.display = 'none';
                    document.getElementById('loginForm').style.display = 'block';
                }
            });
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
});
