<?php
use SimpleExcel\SimpleExcel;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['import'])) {
    // Validate file upload
    if(!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        die("Error uploading file. Error code: " . $_FILES['excel_file']['error']);
    }

    $uploadedFile = $_FILES['excel_file']['tmp_name'];
    $originalName = basename($_FILES['excel_file']['name']);
    
    // Validate file extension
    $allowedExtensions = ['csv'];
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if(!in_array($fileExtension, $allowedExtensions)) {
        die("Invalid file type. Only CSV files are allowed.");
    }

    // Create a safe filename
    $safeFilename = 'upload_' . time() . '.csv';
    
    if(move_uploaded_file($uploadedFile, $safeFilename)) {
        require_once('SimpleExcel/SimpleExcel.php'); 
        
        try {
            $excel = new SimpleExcel('csv');                  
            $excel->parser->loadFile($safeFilename);           
            $foo = $excel->parser->getField(); 

            $count = 1;
            $db = mysqli_connect('localhost','fcgameserver_qplay','fcgameserver_qplay','fcgameserver_qplay');
            
            if(mysqli_connect_errno()) {
                die("Database connection failed: " . mysqli_connect_error());
            }

            // Prepare statement for better security
            $query = "INSERT INTO question(`innercontestid`,`levelid`,`question`,`A`,`B`,`C`,`D`,`answer`,`marks`,`fifty_I`,`fifty_II`,`bestofthree`,`status`,datetime) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            
            if(!$stmt) {
                die("Prepare failed: " . mysqli_error($db));
            }

            while(count($foo) > $count) {
                $innercontestid = mysqli_real_escape_string($db, $foo[$count][0]);
                $level = mysqli_real_escape_string($db, $foo[$count][1]);
                $question = mysqli_real_escape_string($db, $foo[$count][2]);
                $a = mysqli_real_escape_string($db, $foo[$count][3]);
                $b = mysqli_real_escape_string($db, $foo[$count][4]);
                $c = mysqli_real_escape_string($db, $foo[$count][5]);
                $d = mysqli_real_escape_string($db, $foo[$count][6]);
                $answer = mysqli_real_escape_string($db, $foo[$count][7]);
                $marks = mysqli_real_escape_string($db, $foo[$count][8]);
                $fiftyi = mysqli_real_escape_string($db, $foo[$count][9]);
                $fiftyii = mysqli_real_escape_string($db, $foo[$count][10]);
                $bestofthree = mysqli_real_escape_string($db, $foo[$count][11]);
                $statussss = 1;
                date_default_timezone_set('Asia/Kolkata');
                $date = date('Y-m-d H:i:s');

                // Bind parameters
                mysqli_stmt_bind_param($stmt, 'ssssssssssssss', 
                    $innercontestid, $level, $question, $a, $b, $c, $d, 
                    $answer, $marks, $fiftyi, $fiftyii, $bestofthree, 
                    $statussss, $date);

                if(!mysqli_stmt_execute($stmt)) {
                    // Log error but continue with next row
                    error_log("Error inserting row $count: " . mysqli_error($db));
                    echo "Error with row $count: " . htmlspecialchars($question) . "<br>";
                }
                
                $count++;
            }
            
            mysqli_stmt_close($stmt);
            mysqli_close($db);
            
            // Delete the uploaded file after processing
            unlink($safeFilename);
            
            header("Location: https://qplay15.com/question");
            exit();
            
        } catch(Exception $e) {
            die("Error processing file: " . $e->getMessage());
        }
    } else {
        die("Error moving uploaded file.");
    }
}

// Debugging function to display errors
function debugData($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>