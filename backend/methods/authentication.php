<?php

define("OPENPLANET_AUTH_URL", "https://openplanet.dev/api/auth/validate");

function IsTokenValid(string $token, string $secret, mysqli $db) {
    if (empty($token)) {
        return false;
    } else if (str_starts_with($token, 'Bearer ')) {
        $token = substr($token, 7);
    }

    $payload = [
        'token'  => $token,
        'secret' => $secret,
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  =>
                "Content-Type: application/x-www-form-urlencoded\r\n" .
                "User-Agent: PHP/".phpversion()." RMC_API/1.0 (Greep & FlinkTM)\r\n",
            'content' => http_build_query($payload),
            'timeout' => 10,
            'ignore_errors' => true,
        ]
    ];

    $context = stream_context_create($options);
    $openplanetResponse = @file_get_contents(OPENPLANET_AUTH_URL, false, $context);

    if ($openplanetResponse === false) {
        echo("Openplanet request failed");
        return false;
    }

    $openplanetData = json_decode($openplanetResponse, true) ?: [];

    if (empty($openplanetData)) {
        echo("Openplanet JSON response is empty!");
        return false;
    }

    if (isset($openplanetData["error"])) {
        echo("Error validating Openplanet token: " . $openplanetData["error"]);
        return false;
    }

    if (!isset($openplanetData["account_id"])) {
        echo("Openplanet Token JSON is missing the account ID");
        return false;
    }

    $statement = "INSERT INTO `players` (`accountId`, `displayName`, `lastLogon`, `lastPluginVersion`, `lastToken`)
            VALUES (?, ?, NOW(), '', ?)
            ON DUPLICATE KEY UPDATE
            displayName = VALUES(`displayName`), lastLogon = NOW(), lastPluginVersion = VALUES(`lastPluginVersion`), lastToken = VALUES(`lastToken`)";

    if ($stmt = $db->prepare($statement)) {
        $stmt->bind_param("sss", $openplanetData['account_id'], $openplanetData['display_name'], $token);
        $stmt->execute();
        $stmt->close();
    } else {
        echo("Failed to prepare statement when getting token from Openplanet");
        return false;
    }

    return true;
}

?>
