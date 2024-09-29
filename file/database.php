<?php
 
// Include the database connection
include 'database.php'; // Ensure this points to your database connection script

// Initialize an array to store posts in session
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the posted message
    $message = htmlspecialchars($_POST['message']); // Prevent XSS

    // Get the current date and time
    $timestamp = date('Y-m-d H:i');

    // Save the post data (message and timestamp) into the session
    $_SESSION['posts'][] = [
        'message' => $message,
        'timestamp' => $timestamp
    ];
}

// Fetch users from the database
$users = [];
$result = $conn->query("SELECT username, email FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'username' => htmlspecialchars($row['username']),
            'email' => htmlspecialchars($row['email']),
            'created_at' => date('Y-m-d H:i', strtotime($row['created_at'])) // Format timestamp
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .box {
            width: 35rem;
            max-height: 25rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            overflow-y: auto; /* Enables vertical scrolling */
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .text,
        .text2 {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            background-color: #f1f1f1;
        }

        .text:hover,
        .text2:hover {
            background-color: #e1e1e1;
        }

        textarea {
            width: 100%;
            height: 100px;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            resize: none; /* Disable resizing */
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        input[type="text"] {
            width: calc(100% - 22px); /* Adjust width to fit padding */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        input[type="text"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1>Welcome (Name)</h1>

    <form action="" method="post"> <!-- Ensure this submits to the same page -->
        <label for="message">Message</label>
        <textarea id="message" name="message" required></textarea>
        <br><br>
        <button type="submit">Post</button>
    </form>

    <h1>Your Posts</h1>
    <div class="box">
        <?php if (!empty($_SESSION['posts'])): ?>
            <?php foreach ($_SESSION['posts'] as $post): ?>
                <div class="text">
                    <p><?= $post['message']; ?></p>
                    <i><?= $post['timestamp']; ?></i>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts yet.</p>
        <?php endif; ?>
    </div>

    <h1>Search Users</h1>
    <input type="text" placeholder="Search...">

    <h1>Search Results:</h1>
    <div class="box">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="text2">
                    <p>name: <b><?= $user['username']; ?></b></p>
                    <p>email: <b><?= $user['email']; ?></b></p>
                    <i>Joined on: <?= $user['created_at']; ?></i>
                    <br>
                    <a href="#">view profile</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>

</body>
</html>
