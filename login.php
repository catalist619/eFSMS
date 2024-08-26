<?php
session_start();
$login_error_message = '';
$available_chance = 0; // Initialize variable to store available chance

include 'conn.php';

// Query to get the available chance from FieldChance table
$sql_chance = "SELECT available_chance FROM FieldChance ORDER BY id DESC LIMIT 1";
$result_chance = $conn->query($sql_chance);

if ($result_chance->num_rows > 0) {
    $row = $result_chance->fetch_assoc();
    $available_chance = $row['available_chance'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check the Student table
    $sql_student = "SELECT id, password, privilege, phone_number FROM Student WHERE email = ?";
    $stmt_student = $conn->prepare($sql_student);
    $stmt_student->bind_param("s", $email);
    $stmt_student->execute();
    $stmt_student->store_result();

    // Query to check the Staff table
    $sql_staff = "SELECT id, password, privilege FROM Staff WHERE email = ?";
    $stmt_staff = $conn->prepare($sql_staff);
    $stmt_staff->bind_param("s", $email);
    $stmt_staff->execute();
    $stmt_staff->store_result();

    if ($stmt_student->num_rows > 0) {
        // User found in Student table
        $stmt_student->bind_result($id, $hashed_password, $privilege, $phone_number);
        $stmt_student->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['privilege'] = $privilege;
            $_SESSION['email'] = $email;
            $_SESSION['phone_number'] = $phone_number;

            // Redirect based on user privilege
            if ($privilege == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $login_error_message = "Invalid password.";
        }
    } elseif ($stmt_staff->num_rows > 0) {
        // User found in Staff table
        $stmt_staff->bind_result($id, $hashed_password, $privilege);
        $stmt_staff->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['privilege'] = $privilege;
            $_SESSION['email'] = $email;

            // Redirect based on user privilege
            if ($privilege == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: staff_dashboard.php");
            }
            exit();
        } else {
            $login_error_message = "Invalid password.";
        }
    } else {
        $login_error_message = "No account found with that email.";
    }

    $stmt_student->close();
    $stmt_staff->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
        background-image: url('image/a0.jpg');
        background-repeat: no-repeat;
        background-position: center;
        background-size: auto;
        }
        .login-form {
            max-width: 600px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .login-form h2 {
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .chance-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 100px;
            height: 100px;
            background-color: #343a40;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 1em;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
        }

        .chance-number {
            font-size: 1.5em;
        }
    </style>
</head>
<body>
                    <div class="chance-indicator">
                    Available chance
                    <div class="chance-number"><?php echo $available_chance; ?></div>
                </div>
    <div class="container">
        <div class="login-form">
            <h2>Sign in to eFSMS catalist</h2>
            <?php if ($login_error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $login_error_message; ?>
                </div>
            <?php endif; ?>
            <form id="loginForm" action="" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="error-message" id="email_error"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="error-message" id="password_error"></div>
                </div>
                <input type="hidden" name="action" value="login">
                <button type="submit" class="btn btn-warning">Login</button><br><br>
                <p>Don’t have an account? <a href="registration.php">Sign up</a></p>
            </form>
        </div>
    </div>
    <script src="js/jquery-3.7.1.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
