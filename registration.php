<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include 'conn.php';
    
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $privilege = 'student'; // Default privilege as student

    $sql = "INSERT INTO Student (first_name, middle_name, surname, email, phone_number, password, privilege)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $first_name, $middle_name, $surname, $email, $phone_number, $password, $privilege);

    if ($stmt->execute()) {
        echo "New record created successfully";
        header("Location: login.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{
        background-image: url('image/a0.jpg');
        background-repeat: no-repeat;
        background-position: center;
        background-size: auto;
        }
        .registration-form {
    max-width: 600px;
    margin: 100px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

.registration-form h2 {
    background-color: #343a40; /* Darker background color */
    color: #ffffff; /* White text */
    padding: 15px;
    text-align: center; /* Center the text */
    border-radius: 10px 10px 0 0; /* Rounded corners at the top */
    margin: -20px -20px 20px -20px; /* Remove default margin and padding */
}
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-form">
            <h2>Join eFSMS today</h2>
            <form id="registrationForm" action="registration.php" method="POST">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                    <div class="error-message" id="first_name_error"></div>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name">
                    <div class="error-message" id="middle_name_error"></div>
                </div>
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" class="form-control" id="surname" name="surname" required>
                    <div class="error-message" id="surname_error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="error-message" id="email_error"></div>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                    <div class="error-message" id="phone_number_error"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="error-message" id="password_error"></div>
                    <div class="password-feedback" id="password_feedback"></div>
                </div>
                <!-- <div class="form-group">
                    <label for="privilege">Privilege</label>
                    <select class="form-control" id="privilege" name="privilege" required>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div> -->
                <button type="submit" class="btn btn-primary">Register</button><br><br>
                <p>Have an account already? <a href="login.php">Log in</a></p>
                <!-- <a href="login.php" class="btn btn-warning">Login</a> -->
                </form>
        </div>
    </div>
    <script src="js/jquery-3.7.1.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
