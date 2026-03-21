// ==============================
// タイピング練習（typing.js）完全版
// - 出題：順番（学習）/ シャッフル（実力） 2ボタン切替
// - 途中リセット
// - 進捗表示（例：11/30）
// - ランク：正解率 + CPM（時間込み）
// - 結果画面に「自己ベスト更新！」表示（localStorageの既存記録と比較）
// - 出題モードを localStorage に保存（次回も同じモード）
// - 誤打分析：typing画面の結果にのみ表示（record.htmlには渡さない）
// - 記録保存：record.htmlへは score/miss/rank のみ渡す
// ==============================

// ==============================
// 設定
// ==============================
// const TIME_LIMIT = 300; // 秒
const DEFAULT_TIME_LIMIT = 300; // 秒（デフォルト）
const TIME_STORAGE_KEY = "wt_typing_time_limit"; // localStorageに保存
const RANKING_TIME_LIMIT = 100; // ランキング専用は100秒固定
const RANKING_COOLDOWN_MS = 60 * 1000; // 再挑戦まで60秒
const PLAYER_NAME_STORAGE_KEY = "wt_player_name";
const SHOW_VISIBLE_SPACES = true; // true のとき半角スペースを ␣ で表示

const MODE_STORAGE_KEY = "wt_typing_question_mode"; // "sequence" | "shuffle"
const ENABLE_SYMBOL_ONLY = false; // trueにすると「記号だけ」集計

// URLパラメータ
const params = new URLSearchParams(window.location.search);
const initialCourse = params.get("course");

// ==============================
// DOM取得
// ==============================
const courseTitleEl = document.getElementById("currentCourse");
const titleEl = document.getElementById("questionTitle");
const questionEl = document.getElementById("question");
const timeEl = document.getElementById("time");
const timeSelectEl = document.getElementById("timeSelect");

const scoreEl = document.getElementById("score");
const bestScoreEl = document.getElementById("bestScore");
const progressArea = document.getElementById("progressArea");
const progressBar = document.getElementById("progressBar");
const capsWarningEl = document.getElementById("capsWarning");
const imeWarningEl = document.getElementById("imeWarning");

const resetBtn = document.getElementById("resetBtn");
const qProgressEl = document.getElementById("qProgress");

// 出題モード（2ボタン）
const orderBtn = document.getElementById("orderBtn");
const shuffleBtn = document.getElementById("shuffleBtn");

// ==============================
// 状態管理
// ==============================
let questions = [];
let originalQuestions = [];

let currentText = "";
let charIndex = 0;
let score = 0;

let timeLimit = DEFAULT_TIME_LIMIT; // ★選択された制限時間（秒）
let time = timeLimit;
// let time = TIME_LIMIT;

let timerId = null;
let totalTyped = 0;
let missCount = 0;

let isStarted = false;
let isFinished = false;
let isLoaded = false;
let canStart = false;

let currentCourseKey = null;
let currentJsonPath = null;
let isRankingMode = false;

// 出題モード
let questionOrderMode = "sequence"; // "sequence" or "shuffle"

// 進捗
let totalQuestions = 0;
let currentQuestionNumber = 0;
let initialTotalQuestions = 0;

// ==============================
// 誤打分析（typing画面だけで表示）
// ==============================
let missCharCount = {}; // 期待文字ベース { ";": 12 }
let missPairCount = {}; // 期待→入力 { ";->:": 7 }

function isRankingCourse(courseKey) {
  return typeof courseKey === "string" && courseKey.endsWith("_rank");
}

function getRankingCourseConfig(courseKey) {
  if (!isRankingCourse(courseKey)) return null;

  const baseCourseKey = courseKey.slice(0, -5);
  const baseLabel =
    baseCourseKey
      .split("_")
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(" ") || "Ranking";

  return {
    label: `${baseLabel} / Ranking`,
    json: `data/${baseCourseKey.replaceAll("_", "-")}-rank.json`,
  };
}

function getCourseConfig(courseKey) {
  if (
    typeof COURSE_MAP !== "undefined" &&
    COURSE_MAP &&
    Object.prototype.hasOwnProperty.call(COURSE_MAP, courseKey)
  ) {
    return COURSE_MAP[courseKey];
  }

  return getRankingCourseConfig(courseKey);
}

function getRankingCooldownStorageKey(courseKey) {
  return `wt_ranking_cooldown_${courseKey}`;
}

function getLatestRankingResultStorageKey(courseKey) {
  return `wt_latest_ranking_result_${courseKey}`;
}

function setRankingCooldown(courseKey) {
  if (!isRankingCourse(courseKey)) return;

  try {
    localStorage.setItem(
      getRankingCooldownStorageKey(courseKey),
      String(Date.now() + RANKING_COOLDOWN_MS),
    );
  } catch {}
}

function getRankingCooldownRemainingMs(courseKey) {
  if (!isRankingCourse(courseKey)) return 0;

  try {
    const cooldownUntil = Number(
      localStorage.getItem(getRankingCooldownStorageKey(courseKey)),
    );
    if (!Number.isFinite(cooldownUntil) || cooldownUntil <= 0) return 0;

    return Math.max(0, cooldownUntil - Date.now());
  } catch {
    return 0;
  }
}

function getRankingCooldownMessage(courseKey) {
  const remainingMs = getRankingCooldownRemainingMs(courseKey);
  if (remainingMs <= 0) {
    return "Enterキーで開始してください(日本語入力はOFF)";
  }

  const remainingSec = Math.ceil(remainingMs / 1000);
  return `ランキングモードは${remainingSec}秒後に再挑戦できます`;
}

function applyCourseModeSettings(courseKey) {
  isRankingMode = isRankingCourse(courseKey);

  if (isRankingMode) {
    timeLimit = RANKING_TIME_LIMIT;
    questionOrderMode = "sequence";
  } else {
    loadModeFromStorage();
    loadTimeFromStorage();
  }

  if (timeSelectEl) {
    timeSelectEl.disabled = isRankingMode;
    timeSelectEl.value = String(timeLimit);
  }

  if (orderBtn) orderBtn.disabled = isRankingMode;
  if (shuffleBtn) shuffleBtn.disabled = isRankingMode;

  applyModeUI();
  applyTimeUI();
}

function getDisplayedScore() {
  if (isRankingMode) {
    return score;
  }
  return score;
}

function updateDisplayedScore() {
  if (scoreEl) scoreEl.textContent = String(getDisplayedScore());
}

function getStoredPlayerName() {
  try {
    const saved = localStorage.getItem(PLAYER_NAME_STORAGE_KEY);
    return saved ? saved.trim() : "";
  } catch {
    return "";
  }
}

function savePlayerName(name) {
  try {
    localStorage.setItem(PLAYER_NAME_STORAGE_KEY, name);
  } catch {}
}

function ensurePlayerName() {
  const savedName = getStoredPlayerName();
  if (savedName) return savedName;

  const input = window.prompt("ランキングに表示する名前を入力してください", "");
  const playerName = String(input || "").trim();

  if (!playerName) return "";

  savePlayerName(playerName);
  return playerName;
}

// ==============================
// スコア送信
// ==============================
async function postFinalScore({
  score,
  course,
  time,
  name,
  correctCount,
  totalCount,
  remainingTime,
}) {
  const saveScoreUrl = new URL("./api/save-score.php", location.href);
  const response = await fetch(saveScoreUrl.toString(), {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      score,
      course,
      time,
      name,
      correct_count: correctCount,
      total_count: totalCount,
      remaining_time: remainingTime,
    }),
  });

  console.log("save-score response:", response);

  const result = await response.json();
  console.log("save-score result:", result);

  if (!response.ok) {
    throw new Error(
      result?.error || `save-score failed: ${response.status}`,
    );
  }

  return result;
}

async function fetchCourseRanking(courseKey) {
  const url = new URL("api/get-ranking.php", location.href);
  url.searchParams.set("course", courseKey);
  if (isRankingCourse(courseKey)) {
    url.searchParams.set("time", String(RANKING_TIME_LIMIT));
  }

  const response = await fetch(url.toString());
  if (!response.ok) {
    throw new Error(`get-ranking failed: ${response.status}`);
  }

  const data = await response.json();
  if (!Array.isArray(data)) {
    throw new Error(data.error || "ランキングデータの取得に失敗しました");
  }

  return data;
}

function getRankingDisplayCourseKey(courseKey) {
  if (!isRankingCourse(courseKey)) return courseKey;
  return courseKey.slice(0, -5);
}

function saveLatestRankingResult(courseKey, result) {
  if (!courseKey || !result) return;

  try {
    localStorage.setItem(
      getLatestRankingResultStorageKey(courseKey),
      JSON.stringify(result),
    );
  } catch {}
}

function findRankingPosition(list, playerName) {
  if (!playerName) return null;

  let currentRank = 0;
  let prevScore = null;

  for (let i = 0; i < list.length; i++) {
    const rowScore = Number(list[i].score) || 0;
    if (prevScore === null || rowScore !== prevScore) {
      currentRank = i + 1;
      prevScore = rowScore;
    }

    if (String(list[i].name || "") === String(playerName)) {
      return currentRank;
    }
  }

  return null;
}

// ==============================
// 出題モード：保存/復元
// ==============================
function loadModeFromStorage() {
  try {
    const saved = localStorage.getItem(MODE_STORAGE_KEY);
    if (saved === "sequence" || saved === "shuffle") {
      questionOrderMode = saved;
    }
  } catch {}
}
function saveModeToStorage() {
  try {
    localStorage.setItem(MODE_STORAGE_KEY, questionOrderMode);
  } catch {}
}
function applyModeUI() {
  if (!orderBtn || !shuffleBtn) return;
  const isShuffle = questionOrderMode === "shuffle";
  orderBtn.classList.toggle("active", !isShuffle);
  shuffleBtn.classList.toggle("active", isShuffle);
  orderBtn.setAttribute("aria-pressed", String(!isShuffle));
  shuffleBtn.setAttribute("aria-pressed", String(isShuffle));
}

function loadTimeFromStorage() {
  try {
    const saved = Number(localStorage.getItem(TIME_STORAGE_KEY));
    if ([60, 120, 180, 300].includes(saved)) timeLimit = saved;
  } catch {}
}
function saveTimeToStorage() {
  try {
    localStorage.setItem(TIME_STORAGE_KEY, String(timeLimit));
  } catch {}
}
function applyTimeUI() {
  if (timeSelectEl) timeSelectEl.value = String(timeLimit);
  if (timeEl) timeEl.textContent = String(timeLimit); // 初期表示も合わせる
}

// ==============================
// 初期：モード復元
// ==============================
loadModeFromStorage();
loadTimeFromStorage();
applyModeUI();
applyTimeUI();

// ==============================
// 初期コース判定
// ==============================
const initialCourseConfig = initialCourse ? getCourseConfig(initialCourse) : null;

if (initialCourse && initialCourseConfig) {
  setCourse(initialCourse, initialCourseConfig.json);
} else {
  if (courseTitleEl) courseTitleEl.textContent = "コースを選択してください";
  if (questionEl) questionEl.textContent = "左のコースを選択してください";
  updateProgress();
}
// if (initialCourse && window.COURSE_MAP && COURSE_MAP[initialCourse]) {
//   setCourse(initialCourse, COURSE_MAP[initialCourse].json);
// } else {
//   if (courseTitleEl) courseTitleEl.textContent = "コースを選択してください";
//   if (questionEl) questionEl.textContent = "左のコースを選択してください";
//   updateProgress();
// }

// ==============================
// コース設定
// ==============================
function setCourse(courseKey, jsonPath) {
  currentCourseKey = courseKey;
  currentJsonPath = jsonPath;
  applyCourseModeSettings(courseKey);

  const courseConfig = getCourseConfig(courseKey);

  if (courseTitleEl && courseConfig?.label) {
    courseTitleEl.textContent = courseConfig.label;
  }

  // 左ナビ active 制御
  document.querySelectorAll(".course-btn").forEach((btn) => {
    btn.classList.toggle("active", btn.dataset.json === jsonPath);
  });

  loadQuestions(jsonPath);
}

// ==============================
// 問題データ読み込み
// ==============================
function loadQuestions(jsonPath) {
  resetGame();

  // ベスト表示
  const best = getBestScore(currentCourseKey, timeLimit);
  if (bestScoreEl) bestScoreEl.textContent = best;

  if (questionEl) questionEl.textContent = "問題を読み込んでいます…";

  fetch(jsonPath)
    .then((res) => res.json())
    .then((data) => {
      originalQuestions = Array.isArray(data) ? [...data] : [];
      questions = buildQuestionList(originalQuestions);

      initialTotalQuestions = originalQuestions.length;
      totalQuestions = initialTotalQuestions;
      currentQuestionNumber = 0;
      updateProgress();

      isLoaded = true;
      canStart = true;
      if (questionEl) {
        questionEl.textContent = isRankingMode
          ? getRankingCooldownMessage(currentCourseKey)
          : "Enterキーで開始してください(日本語入力はOFF)";
      }
    })
    .catch(() => {
      if (questionEl)
        questionEl.textContent = "問題データを読み込めませんでした";
    });
}

// ==============================
// リセット
// ==============================
function resetGame() {
  clearInterval(timerId);

  isStarted = false;
  isFinished = false;
  isLoaded = false;
  canStart = false;

  questions = [];
  currentText = "";
  charIndex = 0;
  score = 0;
  time = timeLimit;
  totalTyped = 0;
  missCount = 0;

  updateDisplayedScore();
  if (timeEl) timeEl.textContent = String(time);

  if (progressBar) progressBar.style.width = "100%";
  if (progressArea) progressArea.style.visibility = "hidden";
  if (titleEl) titleEl.textContent = "";

  totalQuestions = 0;
  currentQuestionNumber = 0;
  initialTotalQuestions = 0;
  updateProgress();

  missCharCount = {};
  missPairCount = {};
}

// ==============================
// 同じコースで最初から（途中リセット）
// ==============================
function restartSameCourse(message) {
  if (!currentJsonPath) return;

  clearInterval(timerId);
  resetGame();

  if (!originalQuestions.length) {
    loadQuestions(currentJsonPath);
    return;
  }

  questions = buildQuestionList(originalQuestions);

  totalQuestions = initialTotalQuestions;
  currentQuestionNumber = 0;
  updateProgress();

  isLoaded = true;
  canStart = true;

  if (questionEl) {
    questionEl.textContent =
      message ||
      "リセットしました。Enterキーで開始してください(日本語入力はOFF)";
  }
}

// ==============================
// スタート
// ==============================
function startGame() {
  if (isStarted || !canStart || !isLoaded) return;

  if (isRankingMode) {
    const playerName = ensurePlayerName();
    if (!playerName) {
      if (questionEl) {
        questionEl.textContent =
          "ランキングに参加するには名前の入力が必要です。Enterキーでもう一度開始できます";
      }
      return;
    }
  }

  if (isRankingMode) {
    const remainingMs = getRankingCooldownRemainingMs(currentCourseKey);
    if (remainingMs > 0) {
      canStart = true;
      if (questionEl) {
        questionEl.textContent = getRankingCooldownMessage(currentCourseKey);
      }
      return;
    }
  }

  isStarted = true;
  canStart = false;
  isFinished = false;

  time = timeLimit;
  if (timeEl) timeEl.textContent = String(time);
  updateDisplayedScore();

  if (progressArea) progressArea.style.visibility = "visible";
  if (progressBar) progressBar.style.width = "100%";

  nextQuestion();
  startTimer();
}

// ==============================
// 次の問題（shift）
// ==============================
function nextQuestion() {
  if (questions.length === 0) {
    finishGame();
    return;
  }

  updateProgress();

  const q = questions.shift();
  currentText = q?.text ?? "";
  charIndex = 0;

  if (titleEl) titleEl.textContent = q?.title ?? "";
  renderText();
}

// ==============================
// 描画
// ==============================
function renderText() {
  if (!questionEl) return;
  const currentIndex = Math.max(
    0,
    Math.min(Number.isFinite(charIndex) ? charIndex : 0, currentText.length),
  );

  // 1文字ずつ span にすることで、現在位置だけにカーソル表示を付けられる
  // 半角スペースは見分けやすくするため、必要に応じて ␣ に置き換える
  questionEl.innerHTML = Array.from(currentText)
    .map((char, index) => {
      const classes = [];

      if (index < currentIndex) {
        classes.push("correct");
      } else if (index === currentIndex) {
        classes.push("current");
      }

      const isSpace = char === " ";
      if (isSpace) {
        classes.push("space");
      }

      const displayChar =
        isSpace && SHOW_VISIBLE_SPACES ? "␣" : escapeHTML(char);

      return `<span class="${classes.join(" ")}">${displayChar}</span>`;
    })
    .join("");
}

// ==============================
// タイマー
// ==============================
function startTimer() {
  clearInterval(timerId);

  timerId = setInterval(() => {
    time--;
    if (timeEl) timeEl.textContent = String(time);
    updateDisplayedScore();
    if (progressBar) progressBar.style.width = (time / timeLimit) * 100 + "%";

    if (time <= 0) {
      clearInterval(timerId);
      finishGame();
    }
  }, 1000);
}

// ==============================
// 入力判定（IME / CapsLock）
// ==============================
document.addEventListener("keydown", (e) => {
  if (isStarted && e.code === "Space") e.preventDefault();

  // IME警告
  if (imeWarningEl && e.isComposing) {
    imeWarningEl.classList.remove("d-none");
    return;
  } else if (imeWarningEl) {
    imeWarningEl.classList.add("d-none");
  }

  // CapsLock警告
  if (capsWarningEl && e.getModifierState?.("CapsLock")) {
    capsWarningEl.classList.remove("d-none");
  } else if (capsWarningEl) {
    capsWarningEl.classList.add("d-none");
  }

  if (!isStarted || isFinished || !currentText) return;
  if (e.key.length !== 1) return;

  totalTyped++;

  if (e.key === currentText[charIndex]) {
    charIndex++;
    score++;
    updateDisplayedScore();
    renderText();
    if (charIndex === currentText.length) {
      currentQuestionNumber++;
      nextQuestion();
    }
  } else {
    missCount++;

    const expectedChar = currentText[charIndex] ?? "";
    const typedChar = e.key ?? "";

    if (!ENABLE_SYMBOL_ONLY || isSymbol(expectedChar)) {
      logMistype(expectedChar, typedChar);
    }

    const cur = document.querySelector(".current");
    if (cur) {
      cur.classList.add("error");
      setTimeout(() => cur.classList.remove("error"), 150);
    }
  }
});

// Enterで開始
document.addEventListener("keyup", (e) => {
  if (canStart && e.code === "Enter") startGame();
});

// ==============================
// 終了処理（誤打はtypingでのみ表示）
// ==============================
async function finishGame() {
  if (isFinished) return;
  isFinished = true;

  clearInterval(timerId);
  if (progressArea) progressArea.style.visibility = "hidden";
  if (titleEl) titleEl.textContent = "";

  const accuracy = totalTyped ? (score / totalTyped) * 100 : 0;
  const cpm = (score / timeLimit) * 60;
  const rankingCorrectCount = score;
  const finalScore = rankingCorrectCount + Math.max(0, time);

  const { rank, color } = getRank(accuracy, cpm);

  if (isRankingMode) {
    setRankingCooldown(currentCourseKey);
  }

  let rankingPosition = null;
  let rankingError = null;

  try {
    const savedResult = await postFinalScore({
      score: isRankingMode ? finalScore : score,
      course: currentCourseKey || "",
      time: timeLimit,
      name: getStoredPlayerName(),
      correctCount: isRankingMode ? rankingCorrectCount : score,
      totalCount: totalTyped,
      remainingTime: time,
    });

    if (isRankingMode && currentCourseKey) {
      saveLatestRankingResult(getRankingDisplayCourseKey(currentCourseKey), {
        score: Number(savedResult?.score) || finalScore,
        created_at: String(savedResult?.created_at || ""),
        name: String(savedResult?.name || getStoredPlayerName() || ""),
      });
    }

    if (isRankingMode && currentCourseKey) {
      const rankingList = await fetchCourseRanking(currentCourseKey);
      rankingPosition = findRankingPosition(rankingList, getStoredPlayerName());
    }
  } catch (err) {
    console.error("save-score error:", err);
    rankingError = err;
  }

  // ★時間別で自己ベスト判定
  const prevBestScore = getBestScore(currentCourseKey, timeLimit);
  const compareScore = isRankingMode ? finalScore : score;
  const isNewBest = compareScore > prevBestScore;
  const bestMessage = isNewBest
    ? `<div class="fw-bold mt-1 mb-1 text-danger">自己ベスト更新！</div>`
    : "";

  // ★time も渡す
  const qParams = new URLSearchParams({
    course: currentCourseKey || "",
    score: String(score),
    miss: String(missCount),
    rank: rank,
    time: String(timeLimit),
  });

  // 誤打TOP（表示用）
  const topMissChars = topEntries(missCharCount, 8);
  const topMissPairs = topEntries(missPairCount, 8);

  const missCharsHtml = topMissChars.length
    ? `<div class="mt-3 miss_text"><div class="fw-bold mb-1">誤打が多い文字 TOP</div><ol class="mb-0">${topMissChars
        .map(([ch, cnt]) => {
          const label = escapeHTML(formatKeyForDisplay(String(ch)));
          return `<li><code>${label}</code>：${cnt}回</li>`;
        })
        .join("")}
</ol></div>`
    : "";

  const missPairsHtml = topMissPairs.length
    ? `<div class="mt-3 miss_rank"><div class="fw-bold mb-1">よく間違える組み合わせ TOP</div><ol class="mb-0">${topMissPairs
        .map(([pair, cnt]) => {
          const [ex, ty] = String(pair).split("->");
          const exLabel = escapeHTML(formatKeyForDisplay(ex || ""));
          const tyLabel = escapeHTML(formatKeyForDisplay(ty || ""));
          return `<li><code>${exLabel}</code> → <code>${tyLabel}</code>：${cnt}回</li>`;
        })
        .join("")}
</ol>
</div>`
    : "";

  if (!questionEl) return;

  const rankingCourseKey = getRankingDisplayCourseKey(currentCourseKey || "");
  const rankingButtonHtml =
    isRankingMode && rankingCourseKey
      ? `<a href="ranking.html?course=${encodeURIComponent(
          rankingCourseKey,
        )}" class="btn btn-warning px-4 py-2 mt-2">ランキングを見る</a>`
      : "";

  const rankingPositionHtml =
    isRankingMode && rankingPosition
      ? `<div class="rank-result__place">あなたは現在 <strong>${rankingPosition}位</strong> です！</div>`
      : isRankingMode && rankingError
        ? `<div class="rank-result__place">順位を取得できませんでした</div>`
        : "";

  if (isRankingMode) {
    questionEl.innerHTML = `<div class="rank-result">
<div class="rank-result__label">ランキングチャレンジ終了</div>
<div class="rank-result__score-label">今回のスコア</div>
<div class="rank-result__score">${finalScore}</div>
${rankingPositionHtml}
<div class="rank-result__actions">
${rankingButtonHtml}
<button id="retryBtn" class="btn btn-outline-light px-4 py-2 mt-2">同じコースでもう一度</button>
</div>
</div>`;

    const retryBtn = document.getElementById("retryBtn");
    if (retryBtn) retryBtn.onclick = () => loadQuestions(currentJsonPath);
    return;
  }

  questionEl.innerHTML = `<div class="text-center" style="line-height:1em"><div class="fw-bold mb-1">タイピング終了</div>
<div>制限時間：${timeLimit}秒</div>
<div>速度：${cpm.toFixed(1)} CPM</div>
<div>正解数：${score}&nbsp;&nbsp;ミス数：${missCount}</div>
<div>正解率：${accuracy.toFixed(1)}%</div>
<div class="mb-1 fs-4 fw-bold ${color}">評価ランク：${rank}</div>
${bestMessage}
<div class="mb-1"><a href="record.html?${qParams.toString()}" class="btn btn-success px-4 py-2 mt-2">入力記録を保存する</a></div>
<div class="text-warning" style="font-size:0.6em">※ 保存すると、あとから自分の記録を確認できます</div>
<div class="d-flex gap-3">${missCharsHtml}${missPairsHtml}</div>
<button id="retryBtn" class="btn btn-outline-light px-4 py-2 mb-2">同じコースでもう一度</button><br /></div>`;

  const retryBtn = document.getElementById("retryBtn");
  if (retryBtn) retryBtn.onclick = () => loadQuestions(currentJsonPath);
}

// ==============================
// 自己ベスト（過去最高スコア）取得
// ==============================
function getBestScore(courseKey, timeLimit) {
  if (!courseKey) return 0;

  const key = `typing_record_${courseKey}_${timeLimit}`;
  let list = [];

  try {
    list = JSON.parse(localStorage.getItem(key)) || [];
  } catch {}

  if (!Array.isArray(list) || list.length === 0) return 0;

  return Math.max(...list.map((r) => Number(r.score) || 0));
}

// ==============================
// 進捗表示
// ==============================
function updateProgress() {
  if (!qProgressEl) return;
  qProgressEl.textContent = `${currentQuestionNumber}/${totalQuestions}`;
}

// ==============================
// 誤打ログ
// ==============================
function logMistype(expectedChar, typedChar) {
  const ex = String(expectedChar ?? "");
  const ty = String(typedChar ?? "");
  missCharCount[ex] = (missCharCount[ex] || 0) + 1;

  const key = `${ex}->${ty}`;
  missPairCount[key] = (missPairCount[key] || 0) + 1;
}
function topEntries(mapObj, limit = 10) {
  return Object.entries(mapObj)
    .sort((a, b) => (Number(b[1]) || 0) - (Number(a[1]) || 0))
    .slice(0, limit);
}
function isSymbol(ch) {
  return ch && !/[a-zA-Z0-9\s]/.test(ch);
}
function formatKeyForDisplay(str) {
  return String(str)
    .replaceAll(" ", "␠")
    .replaceAll("\n", "↵")
    .replaceAll("\t", "⇥");
}

// ==============================
// ランク判定
// ==============================
function getRank(accuracy, cpm) {
  if (accuracy < 70) return { rank: "D", color: "text-danger" };
  if (accuracy >= 95 && cpm >= 120) return { rank: "S", color: "text-warning" };
  if (accuracy >= 90 && cpm >= 90) return { rank: "A", color: "text-success" };
  if (accuracy >= 80 && cpm >= 60) return { rank: "B", color: "text-info" };
  if (accuracy >= 70 && cpm >= 40)
    return { rank: "C", color: "text-secondary" };
  return { rank: "D", color: "text-danger" };
}

// ==============================
// ユーティリティ
// ==============================
function shuffle(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]];
  }
  return array;
}
function buildQuestionList(list) {
  const copied = [...list];
  if (isRankingMode) return copied;
  return questionOrderMode === "shuffle" ? shuffle(copied) : copied;
}
function escapeHTML(str) {
  return String(str)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

// ==============================
// 出題モード切替
// ==============================
function switchMode(mode) {
  if (isRankingMode) return;
  if (mode !== "sequence" && mode !== "shuffle") return;
  if (questionOrderMode === mode) return;

  questionOrderMode = mode;
  saveModeToStorage();
  applyModeUI();

  if (originalQuestions.length > 0) {
    restartSameCourse(
      "出題モードを切り替えました。Enterキーで開始してください(日本語入力はOFF)",
    );
  }
}
if (orderBtn) orderBtn.addEventListener("click", () => switchMode("sequence"));
if (shuffleBtn)
  shuffleBtn.addEventListener("click", () => switchMode("shuffle"));

// ==============================
// 左ナビ切替（course-btn）
// ==============================
document.querySelectorAll(".course-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const json = btn.dataset.json;
    const key = Object.keys(COURSE_MAP || {}).find(
      (k) => COURSE_MAP[k].json === json,
    );
    if (!key) return;

    clearInterval(timerId);
    resetGame();
    setCourse(key, json);
  });
});

// ==============================
// リセットボタン
// ==============================
if (resetBtn) {
  resetBtn.addEventListener("click", () => {
    if (isStarted && !isFinished) {
      const ok = confirm("途中経過は破棄されます。最初からやり直しますか？");
      if (!ok) return;
    }
    restartSameCourse();
  });
}

if (timeSelectEl) {
  timeSelectEl.addEventListener("change", () => {
    if (isRankingMode) {
      timeSelectEl.value = String(RANKING_TIME_LIMIT);
      return;
    }

    const val = Number(timeSelectEl.value);
    if (![60, 120, 180, 300].includes(val)) return;

    // プレイ中なら確認してリセット
    if (isStarted && !isFinished) {
      const ok = confirm(
        "制限時間を変更すると最初からやり直しになります。変更しますか？",
      );
      if (!ok) {
        // 元に戻す
        timeSelectEl.value = String(timeLimit);
        return;
      }
    }

    timeLimit = val;
    saveTimeToStorage();
    restartSameCourse(
      "制限時間を変更しました。Enterキーで開始してください(日本語入力はOFF)",
    );
  });
}
