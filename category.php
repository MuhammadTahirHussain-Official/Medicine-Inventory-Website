<?php
// Create a new connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "pharmacy");

// Check if the connection was successful
if ($conn->connect_error) {
    // If there is a connection error, display a message and stop execution
    die("<div class='message'>Database error</div>");
}

// Check if the 'category' parameter is set in the URL
if (!isset($_GET['category'])) {
    // If 'category' is not set, display a message and stop execution
    die("<div class='message'>Invalid category</div>");
}

// Safely get the 'category' value from the URL and prevent SQL injection
$category = $conn->real_escape_string($_GET['category']);

// Define the columns we want to retrieve from the database
$columns = [
    'medicine_id', 'medicine_name', 'generic_name',
    'batch_no', 'expiry_date', 'manufacturer',
    'supplier_name', 'current_stock', 'selling_rate'
];

// Create a SQL query to select the specified columns from the 'data' table
$sql = "SELECT " . implode(",", $columns) . " 
        FROM data 
        WHERE categories='$category' 
        ORDER BY medicine_name";

// Execute the SQL query
$result = $conn->query($sql);

// Check if any rows were returned from the query
if ($result->num_rows > 0) {
    // Start creating an HTML table to display the results
    echo "<table>";
    echo "<thead><tr>";
    
    // Create table headers for each column
    foreach ($columns as $c) {
        echo "<th>" . ucwords(str_replace('_', ' ', $c)) . "</th>";
    }
    echo "</tr></thead><tbody>";

    // Loop through each row returned from the query
    while ($row = $result->fetch_assoc()) {
        // Create a table row that can be clicked to view more details about the medicine
        echo "<tr onclick=\"viewMedicine('" . $row['medicine_id'] . "')\">";
        
        // Loop through each column to display the data in table cells
        foreach ($columns as $c) {
            echo "<td>" . htmlspecialchars($row[$c]) . "</td>";
        }
        echo "</tr>";
    }

    // Close the table body and the table
    echo "</tbody></table>";
} else {
    // If no medicines were found, display a message
    echo "<div class='message'>No medicines found</div>";
}

// Close the database connection
$conn->close();
?>
