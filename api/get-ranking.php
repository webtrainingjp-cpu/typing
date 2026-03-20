<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=UTF-8');

$config = require __DIR__ . '/config.php';
$db = $config['db'] ?? [];

$course = isset($_GET['course']) ? trim((string) $_GET['course']) : '';
$scope = isset($_GET['scope']) ? trim((string) $_GET['scope']) : 'weekly';
$time = isset($_GET['time']) ? (int) $_GET['time'] : 0;
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

    $params = [];

    $buildConditions = static function (string $alias) use ($course, $scope, $time, &$params): array {
        $conditions = ["{$alias}.name <> ''"];

        if ($course !== '') {
            $conditions[] = "{$alias}.course = :course";
            $params[':course'] = $course;
        }

        if ($time > 0) {
            $conditions[] = "{$alias}.time = :time";
            $params[':time'] = $time;
        }

        if ($scope === 'weekly') {
            $conditions[] = "{$alias}.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        }

        return $conditions;
    };

    $mainWhere = 'WHERE ' . implode(' AND ', $buildConditions('s'));
    $latestWhere = implode(' AND ', $buildConditions('s2'));

    $stmt = $pdo->prepare(
        "SELECT
            name,
            score,
            course,
            time,
            progress_current,
            progress_total,
            created_at
        FROM scores s
        {$mainWhere}
          AND s.created_at = (
            SELECT MAX(s2.created_at)
            FROM scores s2
            WHERE s2.name = s.name
              AND {$latestWhere}
          )
        ORDER BY score DESC, created_at ASC
        LIMIT 10"
    );

    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $myRank = null;

    if ($course !== '' && $myName !== '' && $myScore !== null && $myCreatedAt !== '') {
        $rankParams = [
            ':course' => $course,
            ':my_score' => $myScore,
            ':my_created_at' => $myCreatedAt,
        ];

        $latestRankConditions = [
            'latest.course = :course',
            "latest.name <> ''",
        ];
        $latestInnerConditions = [
            's2.course = latest.course',
            's2.name = latest.name',
            "s2.name <> ''",
        ];

        if ($time > 0) {
            $latestRankConditions[] = 'latest.time = :time';
            $latestInnerConditions[] = 's2.time = :time';
            $rankParams[':time'] = $time;
        }

        if ($scope === 'weekly') {
            $latestRankConditions[] = 'latest.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
            $latestInnerConditions[] = 's2.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        }

        $latestRankWhere = implode(' AND ', $latestRankConditions);
        $latestInnerWhere = implode(' AND ', $latestInnerConditions);

        $rankStmt = $pdo->prepare(
            "SELECT COUNT(*) + 1 AS rank
             FROM (
                SELECT latest.name, latest.score, latest.created_at
                FROM scores latest
                WHERE {$latestRankWhere}
                  AND latest.created_at = (
                    SELECT MAX(s2.created_at)
                    FROM scores s2
                    WHERE {$latestInnerWhere}
                  )
             ) ranked
             WHERE (
                ranked.score > :my_score
                OR (ranked.score = :my_score AND ranked.created_at < :my_created_at)
             )"
        );
        $rankStmt->execute($rankParams);
        $rankRow = $rankStmt->fetch();

        $calculatedRank = isset($rankRow['rank']) ? (int) $rankRow['rank'] : 1;
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
