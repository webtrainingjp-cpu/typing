<?php
// =====================================================
// Save API（スコア保存API）
// ・フロントから送られたJSONを受け取りDBに保存
// ・ランキング用データをINSERTする
// =====================================================

// JSONレスポンスとして返す
header('Content-Type: application/json; charset=UTF-8');


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

$score = isset($data['score']) ? (int) $data['score'] : null;
$course = isset($data['course']) ? (string) $data['course'] : null;
$time = isset($data['time']) ? (int) $data['time'] : null;
$name = isset($data['name']) ? trim((string) $data['name']) : '';
$progress_current = isset($data['progress_current']) ? (int) $data['progress_current'] : 0;
$progress_total = isset($data['progress_total']) ? (int) $data['progress_total'] : 0;

// 必須項目チェック
if ($score === null || $course === null || $time === null || $course === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'score, course, time are required',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}


// =====================================================
// ③ 必須カラムチェック（環境差分吸収）
// =====================================================
// NOTE:
// get-ranking.php と同じ理由
// 古いDBでも動くようにカラム自動追加

function ensureScoresColumns(PDO $pdo): void
{
    $requiredColumns = [
        'name' => "ALTER TABLE scores ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT '' AFTER course",
        'created_at' => "ALTER TABLE scores ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER time",
        'progress_current' => "ALTER TABLE scores ADD COLUMN progress_current INT NOT NULL DEFAULT 0 AFTER time",
        'progress_total' => "ALTER TABLE scores ADD COLUMN progress_total INT NOT NULL DEFAULT 0 AFTER progress_current",
    ];

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :schema
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column'
    );

    foreach ($requiredColumns as $column => $ddl) {
        $stmt->execute([
            ':schema' => 'typing_app',
            ':table' => 'scores',
            ':column' => $column,
        ]);

        $exists = (int) $stmt->fetchColumn() > 0;

        if (!$exists) {
            $pdo->exec($ddl);
        }
    }
}


try {
    // =====================================================
    // ④ DB接続
    // =====================================================
    $pdo = new PDO(
        'mysql:host=localhost;dbname=typing_app;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // エラーを例外化
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    ensureScoresColumns($pdo);


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
        ':progress_current' => $progress_current,
        ':progress_total' => $progress_total,
    ]);


    // =====================================================
    // ⑥ 成功レスポンス
    // =====================================================
    echo json_encode([
        'ok' => true,
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
