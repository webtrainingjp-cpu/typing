<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');

$config = require __DIR__ . '/config.php';
$db = $config['db'] ?? [];

$course = isset($_GET['course']) ? trim((string) $_GET['course']) : '';
$scope = isset($_GET['scope']) ? trim((string) $_GET['scope']) : 'weekly';
$time = isset($_GET['time']) && (int) $_GET['time'] > 0 ? (int) $_GET['time'] : 100;
$myName = isset($_GET['my_name']) ? trim((string) $_GET['my_name']) : '';
$myScore = isset($_GET['my_score']) ? (int) $_GET['my_score'] : null;
$myCreatedAt = isset($_GET['my_created_at']) ? trim((string) $_GET['my_created_at']) : '';

if ($course !== '' && !str_ends_with($course, '_rank')) {
    $course .= '_rank';
}

try {
    try {
        $pdo = new PDO(
            $db['dsn'] ?? '',
            $db['user'] ?? '',
            $db['pass'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'DB接続エラー: ' . $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $weeklyFilter = $scope === 'weekly'
        ? ' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        : '';

    $topSql = "
        SELECT
            s.name,
            s.score,
            s.course,
            s.time,
            s.progress_current,
            s.progress_total,
            s.created_at
        FROM scores s
        INNER JOIN (
            SELECT name, MAX(created_at) AS latest_created_at
            FROM scores
            WHERE name <> ''
              AND course = :course_filter
              AND time = :time_filter
              {$weeklyFilter}
            GROUP BY name
        ) latest
            ON latest.name = s.name
           AND latest.latest_created_at = s.created_at
        WHERE s.name <> ''
          AND s.course = :course_main
          AND s.time = :time_main
          {$weeklyFilter}
        ORDER BY s.score DESC, s.created_at ASC
        LIMIT 10
    ";

    $topStmt = $pdo->prepare($topSql);
    $topStmt->bindValue(':course_filter', $course, PDO::PARAM_STR);
    $topStmt->bindValue(':time_filter', $time, PDO::PARAM_INT);
    $topStmt->bindValue(':course_main', $course, PDO::PARAM_STR);
    $topStmt->bindValue(':time_main', $time, PDO::PARAM_INT);
    $topStmt->execute();
    $rows = $topStmt->fetchAll();

    $myRank = null;

    if ($course !== '' && $myName !== '' && $myScore !== null && $myCreatedAt !== '') {
        $rankSql = "
            SELECT COUNT(*) + 1 AS user_rank
            FROM (
                SELECT
                    s.name,
                    s.score,
                    s.created_at
                FROM scores s
                INNER JOIN (
                    SELECT name, MAX(created_at) AS latest_created_at
                    FROM scores
                    WHERE name <> ''
                      AND course = :rank_course_filter
                      AND time = :rank_time_filter
                      {$weeklyFilter}
                    GROUP BY name
                ) latest
                    ON latest.name = s.name
                   AND latest.latest_created_at = s.created_at
                WHERE s.name <> ''
                  AND s.course = :rank_course_main
                  AND s.time = :rank_time_main
                  {$weeklyFilter}
            ) ranked
            WHERE ranked.score > :my_score
               OR (ranked.score = :my_score AND ranked.created_at < :my_created_at)
        ";

        $rankStmt = $pdo->prepare($rankSql);
        $rankStmt->bindValue(':rank_course_filter', $course, PDO::PARAM_STR);
        $rankStmt->bindValue(':rank_time_filter', $time, PDO::PARAM_INT);
        $rankStmt->bindValue(':rank_course_main', $course, PDO::PARAM_STR);
        $rankStmt->bindValue(':rank_time_main', $time, PDO::PARAM_INT);
        $rankStmt->bindValue(':my_score', $myScore, PDO::PARAM_INT);
        $rankStmt->bindValue(':my_created_at', $myCreatedAt, PDO::PARAM_STR);
        $rankStmt->execute();
        $rankRow = $rankStmt->fetch();

        $calculatedRank = isset($rankRow['user_rank']) ? (int) $rankRow['user_rank'] : 1;
        $myRank = [
            'name' => $myName,
            'score' => $myScore,
            'rank' => $calculatedRank,
            'in_top_10' => $calculatedRank <= 10,
            'created_at' => $myCreatedAt,
        ];
    }

    if ($myRank !== null) {
        echo json_encode([
            'ranking' => $rows,
            'my_rank' => $myRank,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
