<?php 
    function registerAccount() {
        require 'model/pdo/config.php';
        if (is_session_started() === FALSE) session_start();

        // Checking if the posts are not empty, if they aren't make sure password is equal to confirmed password.
        // If the passwords are equal, assign the post values to a variable.
        if (!empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['mail_address']) && !empty($_POST['password']) && !empty($_POST['confirm_password'])) {
            if (($_POST['password']) == ($_POST['confirm_password'])) {
                $first = $_POST['first_name'];
                $last = $_POST['last_name'];
                $mail = $_POST['mail_address'];
                $pass = $_POST['password'];
                $hash = hash('sha256', $pass);
    
                // Prevent duplicates by checking if the user already exists.
                $fetchExistingAccount = 'SELECT * FROM account WHERE sMailAddress = :post_mail';
                $stmt = $pdo->prepare($fetchExistingAccount);
                $stmt->execute([
                    ':post_mail' => $mail
                ]);
    
                // When a user with the same mail is found, redirect and display a message.
                if ($stmt->rowCount() > 0) {
                    $_SESSION['err_accExists'] = 'Account already exists.';
                    header('location: template/account.php?register');
                } elseif ($stmt->rowCount() == 0) {
                    // If the submitted mail doesn't exist, insert the new user.
                    $insertAccount = 'INSERT INTO account (sFirstname, sLastname, sMailaddress, sPassword)
                    VALUES (:post_first, :post_last, :post_mail, :post_pass)';
                    $stmt = $pdo->prepare($insertAccount);
                    $stmt->execute([
                        ':post_first' => $first,
                        ':post_last' => $last,
                        ':post_mail' => $mail,
                        ':post_pass' => $hash
                    ]);
    
                    $_SESSION['suc_accCreated'] = 'Account successfully made.';
                    header('location: template/account.php');
                }
            } else {
                $_SESSION['err_matchPass'] = 'Passwords does not match confirmation.';
                header('location: template/account.php');
            }
        } else {
            $_SESSION['err_reqFields'] = 'All fields are required.';
            header('location: template/account.php?register');
        }
    }

    function authenticateAccount() {
        require 'model/pdo/config.php';
        if (is_session_started() === FALSE) session_start();

        // Checking if the posts are not empty, if they aren't assign the post values to a variable.
        if (!empty($_POST['mail_address']) && ($_POST['password'])) {
            $mail = $_POST['mail_address'];
            $pass = $_POST['password']; 
            $hash = hash('sha256', $pass);

            // Make sure the account exists by checking the mail address.
            $fetchExistingAccount = 'SELECT idUser, sFirstname, sLastname, sPassword FROM account WHERE sMailaddress = :post_mail';
            $stmt = $pdo->prepare($fetchExistingAccount);
            $stmt->execute([
                ':post_mail' => $mail
            ]);

            // When no account is found, redirect and display a message.
            if ($stmt->rowCount() == 0) {
                $_SESSION['err_noAccount'] = 'Account does not exist.';
                header('location: template/account.php?register');
            } elseif ($stmt->rowCount() > 0) {
                // If the account exists, fetch it's data.
                $account = $stmt->fetch(PDO::FETCH_ASSOC);
                // Check if the hashed password from the post, equals the stored password. Then start serssions.
                if ($hash == $account['sPassword']) {
                    $_SESSION['signedin'] = TRUE;
                    $_SESSION['id'] = $account['idUser'];
                    $_SESSION['fname'] = $account['sFirstname'];
                    // Roles could be added to the session aswell.
                    
                    header('Location: template/profile.php');
                } else {
                    $_SESSION['err_incPass'] = 'Incorrect password.';
                    header('location: template/account.php');
                }
            }
        } else {
            $_SESSION['err_reqFields'] = 'All fields are required.';
            header('location: template/account.php');
        }
    }

    function disconnectAccount() {

    }
    
    // Checking whether a session is started or not.
    function is_session_started() {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }
?>