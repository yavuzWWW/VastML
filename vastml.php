<?php
$compiledCode = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vastml = $_POST["vastml"];

    // ----------------------------
    // Define your custom tags here
    // ----------------------------
    $tags = [



    	// Example: a simple <hello> tag
        '/<hello>/' => function() {
            return "

            <!--VastML Hello Function-->

            <h2>Hello from VastML!</h2><br><br>
            <!--VastML Hello Function End-->


            ";
        },



        // Upload tag
        '/<upload to="([^"]+)">/' => function($upload) {
            $dir = $upload[1];
            return '


            <!--VastML Upload Function-->
<form action="" method="post" enctype="multipart/form-data">
  <input type="file" name="fileToUpload">
  <input type="submit" name="uploadBtn" value="Upload">
</form><br><br>
<?php
if(isset($_POST["uploadBtn"])) {
  $file = __DIR__ . "'.$dir.'/" . basename($_FILES["fileToUpload"]["name"]);
  if(!is_dir(dirname($file))) mkdir(dirname($file), 0755, true);
  if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $file)) {
    echo "File uploaded!";
  } else {
    echo "Upload failed.";
  }
}
?>
<!--VastML Upload Function End-->


';
        },

        //login tag
        '/<login data="([^"]+)">/' => function($login) {
        	$loginData = $login[1];
        	return '


        	<!--VastML Login Function-->
        		<?php
session_start(); 
// Start a session so we can keep the user logged in across pages

// If the form is submitted (when user clicks login)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST["username"];  // Get the username entered in the form
    $password = $_POST["password"];  // Get the password entered in the form

    $file = file("'.$loginData.'/", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 
    // Read the file "users.txt" into an array. Each line becomes one array item.
    // FILE_IGNORE_NEW_LINES = removes "\n" at the end of each line
    // FILE_SKIP_EMPTY_LINES = skips empty lines

    $login_success = false; // We use this to check later if login worked

    // Loop through each line of users.txt
    foreach ($file as $line) {
        list($fileUser, $filePass) = explode(":", $line);
        // Split each line into two parts: username and password (separated by :)

        if ($username === $fileUser && $password === $filePass) {
            // If username and password match
            $_SESSION["user"] = $username;  // Save username in session
            $login_success = true;          // Mark login as successful
            break;                          // Stop checking
        }
    }

    if ($login_success) {
        echo "✅ Login successful! Welcome, " . $_SESSION["user"];
        // Normally you would redirect to another page, e.g. header("Location: dashboard.php");
    } else {
        echo "❌ Wrong username or password!";
    }
}
?>
<!-- Simple Login Section -->
<div>
<h2>Login</h2>
<form method="post">
    <label>Username:</label><br>
    <input type="text" name="username" placeholder="Enter username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" placeholder="Enter password" required><br><br>

    <button type="submit" name="loginBtn">Login</button>
</form>
</div>
<!--VastML Login Function End-->


'; 

}

        



    ];

    // ----------------------------
    // Apply all tag replacements
    // ----------------------------
    $compiledCode = $vastml;
    foreach ($tags as $pattern => $callback) {
        $compiledCode = preg_replace_callback($pattern, $callback, $compiledCode);
    }
}
?>

<!--compiler ui-->
<!DOCTYPE html>
<html>
<head><title>VastML Compiler</title></head>
<body>
<h1>VastML Compiler</h1>
<form method="post">
  <h3>VastML Input</h3>
  <textarea name="vastml" rows="10" cols="80" placeholder="Paste the VastML Code here"><?php 
  if(!empty($_POST["vastml"])) echo htmlspecialchars($_POST["vastml"]);else{echo '
<html>
<upload to="register">
<hello>
<login data="user.txt">
</html>
  ';} ?></textarea><br>
  <button type="submit">Compile</button>
</form>

<?php if ($compiledCode): ?>
<h3>Compiled Output</h3>
<textarea rows="15" cols="80"><?php echo htmlspecialchars($compiledCode); ?></textarea>
<?php endif; ?>
</body>
</html>
