<?php
session_start();
include '../includes/config.php';

// Handle the registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Confirm password field

    // Sanitize input to prevent XSS attacks
    $last_name = htmlspecialchars($last_name);
    $first_name = htmlspecialchars($first_name);
    $middle_name = htmlspecialchars($middle_name);
    $email = htmlspecialchars($email);
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $confirm_password = htmlspecialchars($confirm_password);

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password before saving it
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if the username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (last_name, first_name, middle_name, email, username, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $last_name, $first_name, $middle_name, $email, $username, $hashed_password);
            
            if ($stmt->execute()) {
                // Set a success message in the session to display on login page
                $_SESSION['success_message'] = "Registration successful. Please log in.";
                header("Location: login.php");
                exit;
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Futuristic Dark Theme */
        body {
    background: url('../assets/images/bit.jpg') no-repeat center center fixed;
    background-size: cover;
    color: white;
    font-family: 'Roboto', sans-serif;
    height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.logo {
    width: 100px; /* Adjust size as needed */
    margin-bottom: 0;
    border-radius: 50%;
    box-shadow: 0 0 15px rgba(0, 255, 153, 0.5);
}


        .container {
            text-align: center;
            padding: 40px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }

        h2 {
            font-size: 3rem;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #00ff99, #66ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s infinite;
        }

        /* Error Message */
        .error {
            color: #ff4d4d;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        /* Input Fields */
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 92%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 10px rgba(0, 255, 153, 0.5);
        }

        /* Password Toggle Icon */
        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .password-toggle:hover {
            opacity: 1;
        }

        /* Register Button */
        button {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            background: linear-gradient(90deg, #00ff99, #66ccff);
            color: black;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 255, 153, 0.7);
        }

        /* Footer Link */
        footer {
            margin-top: 20px;
            font-size: 1rem;
        }

        footer a {
            color: #00ff99;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: #66ccff;
        }

        /* Futuristic Animation */
        @keyframes glow {
            0% { text-shadow: 0 0 5px #00ff99, 0 0 10px #66ccff; }
            50% { text-shadow: 0 0 10px #00ff99, 0 0 20px #66ccff; }
            100% { text-shadow: 0 0 5px #00ff99, 0 0 10px #66ccff; }
        }
    </style>
</head>
<body>
    <div class="container">
    <img src="../assets/images/SAL.jpg" alt="Logo" class="logo">
        <h2>Register</h2>

        <!-- Error Message -->
        <?php if (isset($error)) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="create.php" method="POST">
            <!-- Input fields -->
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="middle_name" placeholder="Middle Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Username" required>
            
            <!-- Password field with toggle -->
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
            </div>
            
            <!-- Confirm Password field with toggle -->
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
            </div>

            <button type="submit">Register</button>
        </form>

        <footer>
            <p>Back to <a href="../pages/index.php">Home</a></p>
        </footer>
    </div>

    <script>
        function togglePassword(fieldId) {
            var passwordField = document.getElementById(fieldId);

            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
</body>
</html>