<?php


require_once "./config/db.php";

$message = "";
$message_type = "";
$uploaded_picture = "";

if (isset($_POST["signup"])) {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $password2 = $_POST["password2"];

    /* =========================
       VALIDATION
    ========================= */

    if ($name === "") {

        $message = "Please enter your name.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } elseif ($password !== $password2) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } else {



        if (
            isset($_FILES["picture"]) &&
            $_FILES["picture"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            $file = $_FILES["picture"];

            if ($file["error"] !== UPLOAD_ERR_OK) {

                $message = "There was an error uploading the image.";
                $message_type = "error";

            } elseif ($file["size"] > 2 * 1024 * 1024) {

                $message = "Image must be smaller than 2MB.";
                $message_type = "error";

            } else {

                $extension = strtolower(
                    pathinfo($file["name"], PATHINFO_EXTENSION)
                );

                $allowed_extensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];

                if (!in_array($extension, $allowed_extensions, true)) {

                    $message = "Only JPG, JPEG, PNG and WEBP files are allowed.";
                    $message_type = "error";

                } else {

                    $uploadDir = __DIR__ . "/uploads/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $newname =
                        bin2hex(random_bytes(16))
                        . "."
                        . $extension;

                    $destination = $uploadDir . $newname;

                    if (
                        move_uploaded_file(
                            $file["tmp_name"],
                            $destination
                        )
                    ) {

                        $uploaded_picture = $newname;

                    } else {

                        $message = "Could not save uploaded image.";
                        $message_type = "error";
                    }
                }
            }
        }

        if ($message === "") {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO signup
                (name, email, password, password2, picture)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $hash,
                $hash,
                $uploaded_picture
            );

            if ($stmt->execute()) {

                $message = "Account created successfully!";
                $message_type = "success";

            } else {

                $message = "DATA NOT INSERTED: " . $conn->error;
                $message_type = "error";
            }

            $stmt->close();
        }
    }
}









?>