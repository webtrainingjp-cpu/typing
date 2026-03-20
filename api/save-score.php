<?php
// =====================================================
// Save API（スコア保存API）
// ・フロントから送られたJSONを受け取りDBに保存
// ・ランキング用データをINSERTする
// =====================================================

// JSONレスポンスとして返す
header('Content-Type: application/json; charset=UTF-8');

$config = require __DIR__ . '/config.php';
$db = $config['db'] ?? [];


// =====================================================
// ① JSON入力取得
// =====================================================
// NOTE:
// fetch(API) で送られた body(JSON) を取得する
// php://input は「生のリクエストボディ」

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// JSONが壊れている場合はエラー
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Invalid JSON payload',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}


// =====================================================
// ② パラメータ取得＆バリデーション
// =====================================================
// NOTE:
// score / course / time は必須
// name は任意（空でもOK）

$course = isset($data['course']) ? (string) $data['course'] : null;
$time = isset($data['time']) ? (int) $data['time'] : null;
$name = isset($data['name']) ? trim((string) $data['name']) : '';
$correct_count = isset($data['correct_count']) ? (int) $data['correct_count'] : 0;
$total_count = isset($data['total_count']) ? (int) $data['total_count'] : 0;
$remaining_time = isset($data['remaining_time']) ? (int) $data['remaining_time'] : 0;
$score = $correct_count + max(0, $remaining_time);

// 必須項目チェック
if ($course === null || $time === null || $course === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'course and time are required',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}


try {
    // =====================================================
    // ④ DB接続
    // =====================================================
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
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =====================================================
    // ⑤ INSERT（スコア保存）
    // =====================================================
    // NOTE:
    // prepared statement を使いSQLインジェクション対策
    // created_at は NOW() で保存（週次ランキング用）

    $stmt = $pdo->prepare(
        'INSERT INTO scores (
            score,
            course,
            name,
            time,
            progress_current,
            progress_total,
            created_at
         ) VALUES (
            :score,
            :course,
            :name,
            :time,
            :progress_current,
            :progress_total,
            NOW()
         )'
    );

    $stmt->execute([
        ':score' => $score,
        ':course' => $course,
        ':name' => $name,
        ':time' => $time,
        ':progress_current' => $correct_count,
        ':progress_total' => $total_count,
    ]);

    $insertId = (int) $pdo->lastInsertId();
    $resultStmt = $pdo->prepare(
        'SELECT score, course, name, created_at
         FROM scores
         WHERE id = :id'
    );
    $resultStmt->execute([
        ':id' => $insertId,
    ]);
    $savedRow = $resultStmt->fetch();


    // =====================================================
    // ⑥ 成功レスポンス
    // =====================================================
    echo json_encode([
        'ok' => true,
        'score' => $savedRow['score'] ?? $score,
        'course' => $savedRow['course'] ?? $course,
        'name' => $savedRow['name'] ?? $name,
        'created_at' => $savedRow['created_at'] ?? null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {

    // =====================================================
    // エラー時レスポンス
    // =====================================================
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
