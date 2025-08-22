    <?php
    // ----------------------------
    // VastML Live Compiler v0.1.0
    // ----------------------------

    // Path to your VastML source file
    $sourceFile = __DIR__ . "/src/index.vastml";
    if (!file_exists($sourceFile)) {
        die("❌ No VastML source file found in src/index.vastml");
    }

    // Read the VastML content
    $vastml = file_get_contents($sourceFile);

    // ----------------------------
    // Define your custom tags here
    // ----------------------------
    $tags = [

                // Opening <vastml> tag
            '/<vastml>/' => function() {
                return "<html>";
            },


            // Closing </vastml> tag
            '/<\/vastml>/' => function() {
                return "</html>";
            },


            // comment tag (prevents PHP from executing)
            '/<comment>(.*?)<\/comment>/s' => function($matches) {
                return "<?php /* " . $matches[1] . " */ ?>";
            },


            // Example: a simple <hello> tag
            '/<hello>/' => function() {
                return "


    <!--VastML Hello Function-->
    <h2>Hello from VastML!</h2>
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


            // login tag
    '/<login data="([^"]+)">/' => function($login) {
        $loginData = $login[1];
        return '


    <!--VastML Login Function-->
    <?php
    // If the form is submitted (when user clicks login)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["loginBtn"])) {
        
        $username = $_POST["usernamelogin"];  // Get the username entered in the form
        $password = $_POST["passwordlogin"];  // Get the password entered in the form

        $filePath = __DIR__ . "/' . $loginData . '";
        if (file_exists($filePath)) {
            $file = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        } else {
            $file = []; // if file not found, treat as no users
        }

        $login_success = false;

        foreach ($file as $line) {
            list($fileUser, $filePass) = explode(":", $line);
            if ($username === $fileUser && $password === $filePass) {
                $_SESSION["user"] = $username;
                $login_success = true;
                break;
            }
        }

        if ($login_success) {
            echo "✅ Login successful! Welcome, " . $_SESSION["user"];
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
        <input type="text" name="usernamelogin" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="passwordlogin" required><br><br>

        <button type="submit" name="loginBtn">Login</button>
    </form>
    </div>
    <!--VastML Login Function End-->


    ';
    },



        //register tag
        '/<register data="([^"]+)">/' => function($register) {
            $registerData = $register[1];
            return '


        <!--VastML Register Function-->
        <?php
        // Only handle registration if the form was submitted and "action" is "register"
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] === "register") {
            // Get the username and password from the form
            $username = $_POST["username"];
            $password = $_POST["password"];

            // Path to our "database" file
            $filePath = "'.$registerData.'";

            // Read existing users
            $users = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            $userExists = false;

            // Check if username already exists
            foreach ($users as $line) {
                list($fileUser, $filePass) = explode(":", $line);
                if ($username === $fileUser) {
                    $userExists = true;
                    break;
                }
            }

            if ($userExists) {
                echo "❌ Username already exists!";
            } else {
                // Add new user to the file
                $newLine = $username . ":" . $password . "\n";
                file_put_contents($filePath, $newLine, FILE_APPEND);
                echo "✅ Registration successful! You can now login.";
            }
        }
        ?>

        <!-- Simple HTML registration form -->
        <div>
            <h2>Register</h2>
            <form method="post">
                <input type="hidden" name="action" value="register">
                Username: <input type="text" name="username" required><br><br>
                Password: <input type="password" name="password" required><br><br>
                <input type="submit" value="Register">
            </form>
        </div>
        <!--VastML Register Function End-->


            ';
        },



        //logout tag
        '/<logout href="([^"]+)">/' => function($logout) {
            $logoutHref = $logout[1];
            return '


        <!--VastML Logout Function-->
        <?php
        // Check if logout button is clicked
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["logout"])) {
            session_unset();               // ✅ Clear session variables
            session_destroy();             // ✅ Destroy session
            header("Location: '.$logoutHref.'"); // ✅ Redirect to login page
            exit;
        }
        ?>
        <!-- Only show this if user is logged in -->
        <?php if (isset($_SESSION["user"])): ?>
        <div>
            <h2>Welcome, <?php echo $_SESSION["user"]; ?>!</h2>
            <form method="post"> <!-- ✅ no external file -->
                <input type="submit" name="logout" value="Logout">
            </form>
        </div>
        <?php endif; ?>
        <!--VastML Logout Function End-->


            ';
        },



        //auth tag
        '/<auth href="([^"]+)">/' => function($auth) {
            $authTo = $auth[1];
            return '


    <!--VastML Auth Function-->
    <?php
    // Check if the user is logged in
    if (!isset($_SESSION["user"])) {
        // If session is not set, redirect to login page
        header("Location: '.$authTo.'");
        exit;
    }
    ?>
    <div>
        <h2>Welcome, <?php echo $_SESSION["user"]; ?>!</h2>
        <form method="post" action="logout.php">
            <input type="submit" value="Logout">
        </form>
    </div>
    <!--VastML Auth Function End-->


            ';
        },


        // Define if not empty
        '/<var name="([^"]+)">([^<]+)<\/var>/' => function($matches) {
            $varName = $matches[1];
            $varValue = trim($matches[2]);
            return '<?php $GLOBALS["' . $varName . '"] = "' . addslashes($varValue) . '"; ?>';
        },
        // Variable recall (auto echo)
        '/<var name="([^"]*)"><\/var>/' => function($matches) {
            $varName = $matches[1];
            return '$GLOBALS["' . $varName . '"]';
        },



    // Position tag with Flexbox for reliable layout
    '/<position\s+([^>]+)>(.*?)<\/position>/s' => function($matches) {
        $attributes = $matches[1];
        $content = $matches[2];
        $style = 'display:flex;';

        // Set primary alignment based on the 'to' attribute
        if (preg_match('/to="([^"]+)"/', $attributes, $toMatch)) {
            $toValue = $toMatch[1];
            if ($toValue === 'left' || $toValue === 'right') {
                $style .= 'justify-content:flex-' . ($toValue === 'left' ? 'start' : 'end') . ';';
            } elseif ($toValue === 'top' || $toValue === 'bottom') {
                // Corrected logic: 'top' aligns to 'start', 'bottom' to 'end'
                $style .= 'align-items:flex-' . ($toValue === 'top' ? 'start' : 'end') . ';';
            } elseif ($toValue === 'center' || $toValue === 'middle') {
                $style .= 'justify-content:center; align-items:center;';
            }
        }

        // Apply specific margins based on top/bottom/left/right attributes
        if (preg_match('/top="([^"]+)"/', $attributes, $topMatch)) {
            $style .= 'margin-top:' . $topMatch[1] . ';';
        }
        if (preg_match('/bottom="([^"]+)"/', $attributes, $bottomMatch)) {
            $style .= 'margin-bottom:' . $bottomMatch[1] . ';';
        }
        if (preg_match('/left="([^"]+)"/', $attributes, $leftMatch)) {
            $style .= 'margin-left:' . $leftMatch[1] . ';';
        }
        if (preg_match('/right="([^"]+)"/', $attributes, $rightMatch)) {
            $style .= 'margin-right:' . $rightMatch[1] . ';';
        }
        
        // Wrap content in a div with the Flexbox styles
        return '<div style="' . $style . '">' . $content . '</div>';
    }



    ];

    // ----------------------------
    // Apply all tag replacements
    // ----------------------------
    $compiledCode = $vastml;
    foreach ($tags as $pattern => $callback) {
        $compiledCode = preg_replace_callback($pattern, $callback, $compiledCode);
    }

    // ----------------------------
    // Prepend standard PHP header
    // ----------------------------
    $standardPHP = <<<'PHP'
    <?php
    // ----------------------------
    // VastML Standard Header
    if (session_status() === PHP_SESSION_NONE) session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ?>
    PHP;

    $compiledCode = $standardPHP . "\n" . $compiledCode;

    // ----------------------------
    // Debug mode? show compiled code instead of executing
    // ----------------------------
    if (isset($_GET['debug'])) {
        echo "<h2>Compiled PHP Output:</h2><pre>" . htmlspecialchars($compiledCode) . "</pre>";
        exit;
    }

    // ----------------------------
    // Execute the compiled PHP directly
    // ----------------------------
    eval("?>$compiledCode");
