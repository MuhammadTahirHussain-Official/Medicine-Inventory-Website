<?php
// Create a new database connection to the "pharmacy" database
$conn = new mysqli("localhost","root","","pharmacy");

// Check if the connection failed
if($conn->connect_error){
    die("<div class='message error'>Database error</div>");
}

// Define an array of column names to retrieve from the database
$columns = [
    'medicine_id','medicine_name','generic_name','batch_no',
    'expiry_date','manufacturer','supplier_name',
    'purchase_date','current_stock','selling_rate'
];

// Check if a search parameter was passed in the URL
if(isset($_GET['name']) && $_GET['name'] !== ""){
    // Get the search term and escape it to prevent SQL injection
    $name = $conn->real_escape_string($_GET['name']);
    // Build SQL query to search for medicines with matching names
    $sql = "SELECT ".implode(",",$columns)." FROM data 
            WHERE medicine_name LIKE '%$name%'
            ORDER BY medicine_name";
}else{
    // If no search term, retrieve all medicines sorted by name
    $sql = "SELECT ".implode(",",$columns)." FROM data ORDER BY medicine_name";
}

// Execute the SQL query
$result = $conn->query($sql);

// Check if any results were found
if($result->num_rows > 0){
    // Start building the HTML table
    echo "<table>";
    echo "<thead><tr>";
    
    // Create table headers from column names
    foreach($columns as $col){
        echo "<th>".ucwords(str_replace("_"," ",$col))."</th>";
    }
    echo "</tr></thead><tbody>";

    // Loop through each row of results
    while($row = $result->fetch_assoc()){
        // Create a clickable row that calls viewMedicine function with the medicine ID
        echo "<tr onclick=\"viewMedicine('".$row['medicine_id']."')\">";
        // Display each column value in a table cell
        foreach($columns as $col){
            echo "<td>".htmlspecialchars($row[$col])."</td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table>";
}else{
    // Display error message if no medicines were found
    echo "<div class='message error'>No medicines found</div>";
}

// Close the database connection
$conn->close();
?>
