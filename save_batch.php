<?php
include("db.php");

// Function to generate batch code
function generateBatchCode($batchName, $batchYear, $batchType) {
    // Get last two digits of year
    $yearSuffix = substr($batchYear, -2);
    
    // Get time code (F for full time, P for part time)
    $timeCode = ($batchType === 'full time') ? 'F' : 'P';
    
    // Clean batch name (remove spaces, special characters, convert to uppercase)
    $cleanName = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $batchName));
    
    // Limit name to 3 characters for consistency
    $namePrefix = substr($cleanName, 0, 3);
    
    // Generate batch code: Name(3chars) + Year(2chars) + TimeCode(1char)
    $batchCode = $namePrefix . $yearSuffix . $timeCode;
    
    return $batchCode;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['batchName'] ?? '');
    $year = intval($_POST['batchYear'] ?? 0);
    $type = trim($_POST['batchType'] ?? '');
    $desc = trim($_POST['batchDesc'] ?? '');

    // Validation
    if ($name === '' || strlen($name) > 100) {
        header("Location: admin_dashboard.php?error=invalid_name#batches");
        exit;
    }
    
    if ($year < 2020 || $year > 2030) {
        header("Location: admin_dashboard.php?error=invalid_year#batches");
        exit;
    }
    
    if (!in_array($type, ['full time', 'part time'])) {
        header("Location: admin_dashboard.php?error=invalid_type#batches");
        exit;
    }
    
    if (strlen($desc) > 255) {
        header("Location: admin_dashboard.php?error=description_long#batches");
        exit;
    }

    // Check for duplicate batch (same name, year, and type)
    $checkStmt = $conn->prepare("SELECT BatchID FROM Batch WHERE BatchName = ? AND BatchYear = ? AND BatchType = ?");
    $checkStmt->bind_param("sis", $name, $year, $type);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: admin_dashboard.php?error=duplicate#batches");
        exit;
    }

    // Generate batch code automatically
    $batchCode = generateBatchCode($name, $year, $type);

    $stmt = $conn->prepare("INSERT INTO Batch (BatchName, BatchYear, BatchType, BatchCode, Description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $name, $year, $type, $batchCode, $desc);
    $stmt->execute();
}
header("Location: admin_dashboard.php#batches");
exit;
