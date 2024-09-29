<?php
// Initialize an array to store posts in session
session_start(); // Make sure to start the session
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = [];
}

// Handle form submission for posting messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = htmlspecialchars($_POST['message']); // Prevent XSS
    $timestamp = date('Y-m-d H:i'); // Get current date and time

    // Save the post data (message and timestamp) into the session
    $_SESSION['posts'][] = [
        'message' => $message,
        'timestamp' => $timestamp,
    ];
}

// Check for delete request
if (isset($_POST['post_index'])) {
    $postIndex = intval($_POST['post_index']);

    // Check if the posts session variable is set
    if (isset($_SESSION['posts'][$postIndex])) {
        // Remove the post from the session and reindex the array
        unset($_SESSION['posts'][$postIndex]);
        $_SESSION['posts'] = array_values($_SESSION['posts']);
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
            background-color: gray;
            margin: 0;
            padding: 20px;
            color: black;
        }

        h1 {
            margin: -0.2rem 0 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 2.5rem; /* Responsive font size */
        }

        /* Responsive input fields */
        .message,
        .search {
            width: 100%; /* Full width on smaller screens */
            max-width: 25rem; /* Limit max width */
        }

        .box {
            width: 35rem; /* Fixed size */
            height: 28rem; /* Fixed size */
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
            width: 100%; /* Full width on smaller screens */
            max-width: 35rem; /* Limit max width */
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
            width: 100%; /* Full width on smaller screens */
            max-width: 30rem; /* Limit max width */
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

        .alignbox {
            text-decoration: underline;
        }

        /* Media Queries for Smaller Screens */
        @media (max-width: 600px) {
            h1 {
                font-size: 1.5rem; /* Smaller font size for mobile */
            }

            .box,
            .message,
            .search,
            textarea,
            input[type="text"] {
                max-width: 90%; /* Wider on mobile */
            }
        }

        .align1 {
            margin-left: 40rem;
            margin-top: -18rem;
        }

        .align2 {
            margin-top: -17.5rem;
            margin-bottom: -2rem;
            position: relative;
        }

        img {
            width: 16rem;
            height: auto;
        }
    </style>
</head>

<body>
    <?php
    // Get the username from the query string
    $username = isset($_GET['username']) ? htmlspecialchars($_GET['username']) : 'Guest';
    ?>
    <h1>Welcome, <?php echo $username; ?>!</h1>

    <form action="form.php" method="post">
        <img src="richfield_logo (1).PNG" alt="">
        <div class="alignbox">
            <textarea id="message" name="message" placeholder="Text message here..." required></textarea>
            <br>
            <button type="submit">Post</button>
        </div>
    </form>

    <br>
    <div class="align1">
        <h1>Your Posts</h1>
        <div class="box">
            <?php if (!empty($_SESSION['posts'])): ?>
                <?php foreach ($_SESSION['posts'] as $index => $post): ?>
                    <div class="text">
                        <p><?= htmlspecialchars($post['message']); ?></p>
                        <i><?= htmlspecialchars($post['timestamp']); ?></i>
                        <form action="form.php" method="post" style="display:inline;">
                            <input type="hidden" name="post_index" value="<?= $index; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this post?');">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php
    // Database configuration
    $servername = "localhost";
    $usernameDb = "root"; // Your database username
    $passwordDb = ""; // Your database password
    $dbname = "socialnetwork"; // Your database name

    // Create connection
    $conn = new mysqli($servername, $usernameDb, $passwordDb, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initialize variables
    $searchResults = [];
    $searchError = "";

    // Check if the search form is submitted
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
        $searchUsername = htmlspecialchars($_GET['search']);

        // Prepare and bind
        $stmt = $conn->prepare("SELECT username, created_at FROM users WHERE username LIKE ?");
        $likeUsername = "%" . $searchUsername . "%";
        $stmt->bind_param("s", $likeUsername);

        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $searchResults[] = $row;
                }
            } else {
                $searchError = "No users found with that username.";
            }
        } else {
            $searchError = "Error executing query: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
    ?>

    <br><br>
    <div class="align2">
        <form action="" method="get">
            <input type="text" name="search" placeholder="Search for your username here..." required>
            <button type="submit">Search</button>
        </form>
    </div>

    <br><br>
    
    <!-- Display search results -->
    <?php if (!empty($searchResults)): ?>
        <?php foreach ($searchResults as $user): ?>
            <div class="box">
                <div class="text2">
                    <p>Name: <?php echo htmlspecialchars($user['username']); ?></p>
                    <i>Logged On: <?php echo htmlspecialchars($user['created_at']); ?></i>
                    <br>
                    <a href="#">View Profile</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php if ($searchError): ?>
            <p><?php echo htmlspecialchars($searchError); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
