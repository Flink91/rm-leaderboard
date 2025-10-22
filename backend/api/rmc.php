<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// CORS Preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Include the database configuration file
include '../config.php';
include '../methods/authentication.php';

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
        // Get the time parameter from the query string
        $time = isset($_GET['year']) ? $_GET['year'] : 'all';
        $objective = isset($_GET['objective']) ? $_GET['objective'] : 'author';
        $category = isset($_GET['category']) ? $_GET['category'] : 'standard';

        if ($time !== 'all') {
            // Query for a specific year
            $sql = "
                SELECT rmc.*, players.displayName
                FROM `rmc`
                INNER JOIN `players` ON `rmc`.`accountId` = `players`.`accountId`
                INNER JOIN (
                    SELECT `accountId`, MAX(`goals`) AS maxGoals
                    FROM `rmc`
                    WHERE `objective` = ? AND YEAR(`submitTime`) = ? AND `category` = ?
                    GROUP BY `accountId`
                ) AS best_runs ON `rmc`.`accountId` = best_runs.`accountId`
                AND `rmc`.`goals` = best_runs.`maxGoals`
                WHERE YEAR(`rmc`.`submitTime`) = ?
                ORDER BY `rmc`.`goals` DESC, `rmc`.`belowGoals` DESC, `rmc`.`submitTime` ASC;
            ";
        } else {
            // Query across all years
            $sql = "
                SELECT rmc.*, players.displayName
                FROM `rmc`
                INNER JOIN `players` ON `rmc`.`accountId` = `players`.`accountId`
                INNER JOIN (
                    SELECT `accountId`, MAX(`goals`) AS maxGoals
                    FROM `rmc`
                    WHERE `objective` = ? AND `category` = ?
                    GROUP BY `accountId`
                ) AS best_runs ON `rmc`.`accountId` = best_runs.`accountId`
                AND `rmc`.`goals` = best_runs.`maxGoals`
                ORDER BY `rmc`.`goals` DESC, `rmc`.`belowGoals` DESC, `rmc`.`submitTime` ASC;
            ";
        }

        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            if ($time !== 'all') {
                $stmt->bind_param("sisi", $objective, $time, $category, $time);
            } else {
                $stmt->bind_param("ss", $objective, $category);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $rmcData = array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $rmcData[] = $row;
                }
            }
            echo json_encode($rmcData);

            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
        }
        $conn->close();
        break;

    case 'POST':
        // Check player token (in Authorization header)
        $headers = getallheaders();
        if (!array_key_exists('Authorization', $headers)) {
            echo json_encode(["success" => false, "error" => "Authorization header is missing"]);
            $conn->close();
            die();
        }

        if (substr($headers['Authorization'], 0, 6) !== 'Token ') {
            echo json_encode(["success" => false, "error" => "Token keyword is missing"]);
            $conn->close();
            die();
        }

        // Get Player Token
        $token = trim(substr($headers['Authorization'], 6));
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['accountId']) || !isset($data['goal']) || !isset($data['below_goal'])) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "accountId, goal and below_goal must be indicated in the body"]);
            $conn->close();
            die();
        }

        $accountId = $data['accountId'];
        $objective = isset($data['objective']) ? $data['objective'] : "author";
        $category = isset($data['category']) ? $data['category'] : "standard";
        $goals = $data['goal'];
        $belowGoals = $data['below_goal'];

        if (!IsTokenValid($token, $openplanetSecret, $conn)) {
            echo json_encode(["success" => false, "message" => "Invalid / Expired Openplanet token received."]);
            $conn->close();
            die();
        }

        if ($stmt = $conn->prepare("SELECT * FROM `players` WHERE `accountId` = ?")) {
            $stmt->bind_param("s", $accountId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Failed to find player in our database."]);
                $stmt->close();
                $conn->close();
                die();
            }

            while ($row = $result->fetch_assoc()) {
                $player = $row;
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
            $conn->close();
            die();
        }

        if ($player["banned"] == 1) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "You've been banned from posting to the leaderboard"]);
            $conn->close();
            die();
        }

        $stmt = $conn->prepare("INSERT INTO `rmc` (`accountId`, `objective`, `submitTime`, `goals`, `belowGoals`, `category`) VALUES (?, ?, UTC_TIMESTAMP(), ?, ?, ?)");
        $stmt->bind_param("ssiis", $accountId, $objective, $goals, $belowGoals, $category);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "RMC run was uploaded successfully to the leaderboard"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method not allowed"]);
        $conn->close();
        die();
}
?>
