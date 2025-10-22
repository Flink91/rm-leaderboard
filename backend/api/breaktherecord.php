<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// CORS Preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Include the database configuration file
include '../config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    $conn->close();
    die();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "
            SELECT rmc.*, players.displayName
            FROM `rmc`
            INNER JOIN `players` ON `rmc`.`accountId` = `players`.`accountId`
            INNER JOIN (
                SELECT `accountId`, MAX(`goals`) AS maxGoals
                FROM `rmc`
                WHERE `objective` = 'author' AND `category` = 'standard' AND `submitTime` BETWEEN '2025-10-25 18:00:00' AND '2025-10-31 22:59:59'
                GROUP BY `accountId`
            ) AS best_runs ON `rmc`.`accountId` = best_runs.`accountId`
            AND `rmc`.`goals` = best_runs.`maxGoals`
            ORDER BY `rmc`.`goals` DESC, `rmc`.`belowGoals` DESC, `rmc`.`submitTime` ASC;
        ";

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute();
            $result = $stmt->get_result();

            $lb = array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $lb[] = $row;
                }
            }
            echo json_encode($lb);

            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
        }
        $conn->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
        $conn->close();
        die();
}
?>
