<?php
// ===================== 1. ERROR REPORTING =====================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================== 2. CHECK REQUEST =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<div class='error'>Invalid request</div>");
}

// ===================== 3. DATABASE CONNECTION =====================
$conn = new mysqli("localhost", "root", "", "pharmacy");
if ($conn->connect_error) {
    die("<div class='error'>Database connection failed: ".$conn->connect_error."</div>");
}

// ===================== 4. REQUIRED FIELDS =====================
$required = [
    'medicine_id','medicine_name','generic_name','batch_no',
    'expiry_date','manufacturer','supplier_name','purchase_date',
    'purchase_qty','purchase_rate','mrp','selling_rate',
    'hsn_code','gst_percent','rack_no','box_no',
    'reorder_level','current_stock','unit','packing','location',
    'quality_check_status','categories'
];

$errors = [];

// ===================== 5. VALIDATION =====================
foreach ($required as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        $errors[] = ucfirst(str_replace("_", " ", $field)) . " is required";
    } else {
        if (preg_match('/[^a-zA-Z0-9\s\.-]/', $_POST[$field])) {
            $errors[] = ucfirst(str_replace("_", " ", $field)) . " contains invalid characters";
        }
    }
}

// Numeric validations
if (!is_numeric($_POST['purchase_qty']) || $_POST['purchase_qty'] < 0)
    $errors[] = "Purchase quantity must be a non-negative number";

if (!is_numeric($_POST['purchase_rate']) || $_POST['purchase_rate'] <= 0)
    $errors[] = "Purchase rate must be greater than 0";

if (!is_numeric($_POST['mrp']) || $_POST['mrp'] <= 0)
    $errors[] = "MRP must be greater than 0";

if (!is_numeric($_POST['selling_rate']) || $_POST['selling_rate'] <= 0)
    $errors[] = "Selling rate must be greater than 0";

if ($_POST['gst_percent'] < 0 || $_POST['gst_percent'] > 100)
    $errors[] = "GST must be between 0 and 100";

if (!empty($_POST['discount_percent']) &&
   (!is_numeric($_POST['discount_percent']) || $_POST['discount_percent'] < 0 || $_POST['discount_percent'] > 100))
    $errors[] = "Discount must be between 0 and 100";

// Date validations
$today = date("Y-m-d");
if ($_POST['expiry_date'] <= $today) $errors[] = "Expiry date must be in the future";
if ($_POST['purchase_date'] > $today) $errors[] = "Purchase date cannot be in the future";

// Stop if errors
if (!empty($errors)) {
    echo "<div class='error'><h2>Validation Errors</h2><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul></div>";
    exit;
}

// ===================== 6. SANITISE DATA =====================
function clean($conn, $value) {
    return mysqli_real_escape_string($conn, trim($value));
}

$data = [];
foreach ($_POST as $k => $v) {
    $data[$k] = clean($conn, $v);
}

// ===================== 7. INSERT QUERY USING PREPARED STATEMENT =====================
$sql = "INSERT INTO data (
    medicine_id, medicine_name, generic_name, batch_no, expiry_date,
    manufacturer, supplier_name, purchase_date, purchase_qty, purchase_rate,
    mrp, selling_rate, discount_percent, hsn_code, gst_percent,
    rack_no, box_no, reorder_level, current_stock, unit,
    packing, location, quality_check_status, categories, remarks,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssssidddssdiiiiisssss",
    $data['medicine_id'],
    $data['medicine_name'],
    $data['generic_name'],
    $data['batch_no'],
    $data['expiry_date'],
    $data['manufacturer'],
    $data['supplier_name'],
    $data['purchase_date'],
    $data['purchase_qty'],
    $data['purchase_rate'],
    $data['mrp'],
    $data['selling_rate'],
    $data['discount_percent'],
    $data['hsn_code'],
    $data['gst_percent'],
    $data['rack_no'],
    $data['box_no'],
    $data['reorder_level'],
    $data['current_stock'],
    $data['unit'],
    $data['packing'],
    $data['location'],
    $data['quality_check_status'],
    $data['categories'],
    $data['remarks']
);

$success = false;
if ($stmt->execute()) {
    $success = true;
}

$stmt->close();

// ===================== 8. FETCH LAST INSERTED RECORD =====================
$result = $conn->query("SELECT * FROM data ORDER BY id DESC LIMIT 1");
$last_record = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Medicine Inventory Data</title>

    <style>
    body {
        font-family: "Inter", Arial, sans-serif;
        background: #f4f6f8;
        padding: 30px;
        color: #111;
    }

    h1{
        font-size: 60px;
    }
    
    h1,
    h2 {
        text-align: center;
        margin-bottom: 15px;
    }

    table {
        width: 80%;
        margin: 30px auto;
        border-collapse: collapse;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        border-radius: 8px;
        overflow: hidden;
    }

    thead {
        background: #000;
        color: #fff;
    }

    thead th {
        padding: 16px;
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    tbody tr:hover {
        background: #d6d6d6;
    }

    td:first-child {
        font-weight: 600;
        width: 35%;
    }

    .success {
        color: #065f46;
        background: #d1fae5;
        padding: 12px;
        text-align: center;
        width: 60%;
        margin: 20px auto;
        border-radius: 6px;
    }

    .error {
        color: #991b1b;
        background: #fee2e2;
        padding: 12px;
        text-align: center;
        width: 60%;
        margin: 20px auto;
        border-radius: 6px;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
    }

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

<?php if (!empty($last_record)): ?>
        <div class="success"><h2>Medicine saved successfully</h2></div>
        <h1><?= htmlspecialchars($last_record['medicine_name']) ?></h1>
<table>
    <thead>
        <tr>
            <th>Field Name</th>
            <th>Entered Value</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($last_record as $field => $value): ?>
            <?php if ($field === "created_at") continue; // Skip created_at ?>
            <tr>
                <td><?= ucwords(str_replace("_"," ",$field)) ?></td>
                <td>
                    <?php if ($field === "quality_check_status"): ?>
                        <span class="badge <?= strtolower($value) ?>"><?= htmlspecialchars($value) ?></span>
                    <?php else: ?>
                        <?= htmlspecialchars($value) ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p style="text-align:center;">No data available.</p>
<?php endif; ?>

</body>
</html>
