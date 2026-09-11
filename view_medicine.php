<?php

// Establish a connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "pharmacy");

// Check if the connection was successful
if ($conn->connect_error) {
    // If there is a connection error, display an error message
    die("<div class='error'>Database connection failed</div>");
}

// Check if the 'id' parameter is set in the URL and is not empty
if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    // If 'id' is not valid, display an error message
    die("<div class='error'>Invalid request</div>");
}

// Safely escape the 'id' parameter to prevent SQL injection
$id = $conn->real_escape_string($_GET['id']);

// Prepare a SQL query to select data for the specified medicine ID
$sql = "SELECT * FROM data WHERE medicine_id='$id'";

// Execute the query and store the result
$result = $conn->query($sql);

// Check if any rows were returned from the query
if ($result->num_rows === 0) {
    // If no rows were found, display an error message
    die("<div class='error'>Medicine not found</div>");
}

// Fetch the data for the medicine as an associative array
$row = $result->fetch_assoc();

// Define fields that should not be displayed
$hidden_fields = ['created_at'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Medicine Inventory Data</title>
<link rel="icon" type="image/jpeg" href="fav.jpeg">

<style>
/* Basic styling for the body */
body {
    font-family: "Inter", Arial, sans-serif;
    background: #f4f6f8;
    padding: 30px;
    color: #111;
}

/* Styling for the main heading */
h1 {
    font-size: 60px;
    text-align: center;
    margin-bottom: 10px;
}

/* Styling for the subheading */
h2 {
    text-align: center;
    margin-bottom: 20px;
}

/* Styling for the table */
table {
    width: 80%;
    margin: 30px auto;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    border-radius: 8px;
    overflow: hidden;
}

/* Styling for the table header */
thead {
    background: #000;
    color: #fff;
}

/* Styling for table header cells */
thead th {
    padding: 16px;
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

/* Styling for table body cells */
tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}

/* Alternate row background color for better readability */
tbody tr:nth-child(even) {
    background: #f9fafb;
}

/* Highlight row on hover */
tbody tr:hover {
    background: #d6d6d6;
}

/* Styling for the first cell in each row */
td:first-child {
    font-weight: 600;
    width: 35%;
}

/* Styling for success messages */
.success {
    color: #065f46;
    background: #d1fae5;
    padding: 12px;
    text-align: center;
    width: 60%;
    margin: 20px auto;
    border-radius: 6px;
}

/* Styling for error messages */
.error {
    color: #991b1b;
    background: #fee2e2;
    padding: 12px;
    text-align: center;
    width: 60%;
    margin: 20px auto;
    border-radius: 6px;
}

/* Styling for status badges */
.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
}

/* Different styles for different status types */
.approved {
    background: #d1fae5;
    color: #065f46;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.rejected {
    background: #fee2e2;
    color: #991b1b;
}
</style>
</head>

<body>

<!-- Display the medicine name as the main heading -->
<h1><?php echo htmlspecialchars($row['medicine_name']); ?></h1>
<h2>Medicine Inventory Details</h2>

<!-- Create a table to display the medicine details -->
<table>
    <thead>
        <tr>
            <th>Field Name</th>
            <th>Stored Value</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Loop through each field in the medicine data
        foreach ($row as $key => $value) {

            // Skip any fields that are in the hidden_fields array
            if (in_array($key, $hidden_fields)) continue;

            echo "<tr>"; // Start a new table row
            echo "<td>" . ucwords(str_replace("_"," ",$key)) . "</td>"; // Display the field name
            // Check if the field is the quality check status
            if ($key === "quality_check_status") {
                // Display the status with a badge
                echo "<td><span class='badge ".strtolower($value)."'>".htmlspecialchars($value)."</span></td>";
            } else {
                // Display the stored value
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>"; // End the table row
        }
        ?>
    </tbody>
</table>

</body>
</html>

<?php $conn->close(); // Close the database connection ?> 
