<?php
// =====================================================
// Ranking API（ランキング取得API）
// ・DBからランキングを取得してJSONで返す
// ・course / scope に応じて条件を切り替える
// =====================================================

// JSONレスポンスを返すAPIとして宣言
header('Content-Type: application/json; charset=UTF-8');


// =====================================================
// ① パラメータ取得
// =====================================================

// course（例: css）
// → DBでは css_rank 形式なので後で変換する
$course = isset($_GET['course']) ? trim((string) $_GET['course']) : '';

// scope（weekly / 将来 all など拡張想定）
$scope = isset($_GET['scope']) ? trim((string) $_GET['scope']) : 'weekly';

// DBの値に合わせる（例: css → css_rank）
if ($course !== '' && !str_ends_with($course, '_rank')) {
    $course .= '_rank';
}


// =====================================================
// ② 必須カラムチェック（自動補完）
// =====================================================
// NOTE:
// 環境によっては古いテーブル構造の可能性があるため
// name / created_at が無ければ自動追加する

function ensureScoresColumns(PDO $pdo): void
{
    $requiredColumns = [
        'name' => "ALTER TABLE scores ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT '' AFTER course",
        'created_at' => "ALTER TABLE scores ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER time",
        'progress_current' => "ALTER TABLE scores ADD COLUMN progress_current INT NOT NULL DEFAULT 0 AFTER time",
        'progress_total' => "ALTER TABLE scores ADD COLUMN progress_total INT NOT NULL DEFAULT 0 AFTER progress_current",
    ];

    // DB構造からカラム存在チェック
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

        // 無ければ追加（初期環境でも動くようにするため）
        if (!$exists) {
            $pdo->exec($ddl);
        }
    }
}


try {
    // =====================================================
    // ③ DB接続
    // =====================================================
    $pdo = new PDO(
        'mysql:host=localhost;dbname=typing_app;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // エラーを例外にする
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 配列で取得
        ]
    );

    ensureScoresColumns($pdo);


    // =====================================================
    // ④ WHERE条件を動的生成
    // =====================================================
    // NOTE:
    // 条件を配列で組み立てることで
    // courseあり/なし・weeklyなど柔軟に対応できる

    $conditions = [];
    $params = [];

    // コース条件（SQLインジェクション対策でバインド）
    if ($course !== '') {
        $conditions[] = 'course = :course';
        $params[':course'] = $course;
    }

    // 週次ランキング（直近7日）
    if ($scope === 'weekly') {
        $conditions[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
    }

    // WHERE句を組み立て
    $whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';


    // =====================================================
    // ⑤ ランキング取得
    // =====================================================
    // NOTE:
    // score DESC → スコア高い順
    // created_at ASC → 同点なら早い人が上

    $stmt = $pdo->prepare(
        "SELECT score, course, name, time, progress_current, progress_total, created_at
         FROM scores
         {$whereClause}
         ORDER BY score DESC, created_at ASC
         LIMIT 10"
    );

    $stmt->execute($params);

    $rows = $stmt->fetchAll();


    // =====================================================
    // ⑥ JSONで返却
    // =====================================================
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
