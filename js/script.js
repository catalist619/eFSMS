
//Registration form
$(document).ready(function () {
    $('#password').on('input', function () {
        var password = $(this).val();
        var feedback = '';
        if (password.length < 8) {
            feedback = 'Password must be at least 8 characters long.';
        } else if (!/[A-Z]/.test(password)) {
            feedback = 'Password must contain at least one uppercase letter.';
        } else if (!/[a-z]/.test(password)) {
            feedback = 'Password must contain at least one lowercase letter.';
        } else if (!/[0-9]/.test(password)) {
            feedback = 'Password must contain at least one number.';
        } else if (!/[@$!%*?&]/.test(password)) {
            feedback = 'Password must contain at least one special character.';
        } else {
            feedback = 'Password is strong.';
        }
        $('#password_feedback').text(feedback);
    });

    $('#registrationForm').on('submit', function (e) {
        var isValid = true;

        // Clear previous error messages
        $('.error-message').text('');

        // Validate first name
        if ($('#first_name').val().trim() === '') {
            $('#first_name_error').text('First name is required.');
            isValid = false;
        }

        // Validate surname
        if ($('#surname').val().trim() === '') {
            $('#surname_error').text('Surname is required.');
            isValid = false;
        }

        // Validate email
        var email = $('#email').val().trim();
        if (email === '') {
            $('#email_error').text('Email is required.');
            isValid = false;
        } else if (!validateEmail(email)) {
            $('#email_error').text('Invalid email format.');
            isValid = false;
        }

        // Validate password
        var password = $('#password').val();
        if (!validatePassword(password)) {
            // $('#password_error').text('Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.');
            isValid = false;
        }

        // If any validation fails, prevent form submission
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Email validation function
    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    // Password validation function
    function validatePassword(password) {
        var re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return re.test(password);
    }
});



//Login form
$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        var isValid = true;

        // Clear previous error messages
        $('.error-message').text('');

        // Validate email
        var email = $('#email').val().trim();
        if (email === '') {
            $('#email_error').text('Email is required.');
            isValid = false;
        } else if (!validateEmail(email)) {
            $('#email_error').text('Invalid email format.');
            isValid = false;
        }

        // Validate password
        var password = $('#password').val();
        if (password === '') {
            $('#password_error').text('Password is required.');
            isValid = false;
        }

        // If any validation fails, prevent form submission
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Email validation function
    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }
});

