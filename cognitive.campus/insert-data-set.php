<?php
include("includes/db-connect.php");

function insertDataset($csvFilePath, $db) {
    // Open the CSV file
    if (($handle = fopen($csvFilePath, "r")) !== false) {
        // Skip the header row
        fgetcsv($handle);

        // Prepare SQL query
        $query = "INSERT INTO coursera_courses 
                  (course_name, university, difficulty_level, course_rating, course_url, course_description, skills) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        // Read and insert rows
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            $stmt->execute([
                $row[0], // Course Name
                $row[1], // University
                $row[2], // Difficulty Level
                (float)$row[3], // Course Rating
                $row[4], // Course URL
                $row[5], // Course Description
                $row[6]  // Skills
            ]);
        }

        fclose($handle);
        echo "Dataset successfully inserted.";
    } else {
        echo "Unable to open the file.";
    }
}

// Call the function
$csvFilePath = "E:\\xampp\\htdocs\\cognitive.campus\\dataset\\Coursera.csv";
 // Update with your dataset file path
insertDataset($csvFilePath, $db);
