// =========================================================
// record.html 完全安定版（時間別保存対応・安全ガード強化）
// - ?course=xxx&score=123&miss=10&rank=A&time=300 が来たら保存
// - scoreが無い場合でも安全に表示
// - 表示：サマリー / ベスト5 / 履歴10件
// =========================================================

(function () {
  try {
    // ==============================
    // URLパラメータ
    // ==============================
    const params = new URLSearchParams(location.search);
    const courseKey = params.get("course");
    const rawScore = params.get("score");
    const rawMiss = params.get("miss");
    const rawRank = params.get("rank");
    const rawTime = params.get("time");
    const safeTime = Number(rawTime) || 300;

    // ==============================
    // DOM
    // ==============================
    const courseTitleEl = document.getElementById("courseTitle");
    const recordListEl = document.getElementById("recordList");
    const rankingAreaEl = document.getElementById("rankingArea");
    const resetBtn = document.getElementById("resetBtn");
    const summaryAreaEl = document.getElementById("summaryArea");

    // ==============================
    // course未指定ならデフォルトへ
    // ==============================
    if (!courseKey) {
      location.href = "record.html?course=html";
      return;
    }

    if (courseTitleEl) {
      courseTitleEl.textContent =
        courseKey.replace(/_/g, " ").toUpperCase() + " の記録";
    }
    // ==============================
    // タイピングに戻る（元コースへ）
    // ==============================
    const backBtn = document.getElementById("backTypingBtn");

    if (backBtn && courseKey) {
      backBtn.href = `typing.html?course=${courseKey}`;
    }

    // ==============================
    // localStorage
    // ==============================
    function storageKey(key) {
      return "typing_record_" + key;
    }
    function getRecords(key) {
      try {
        const raw = localStorage.getItem(storageKey(key));
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
      } catch {
        return [];
      }
    }

    function saveRecord(key, time, data) {
      const list = getRecords(key);

      list.push({
        score: data.score,
        miss: data.miss,
        rank: data.rank,
        time: time,
        date: Date.now(),
      });

      list.sort((a, b) => b.date - a.date);

      const sliced = list.slice(0, 20);
      localStorage.setItem(storageKey(key), JSON.stringify(sliced));
      return sliced;
    }

    // ==============================
    // 初期取得
    // ==============================
    let records = getRecords(courseKey, safeTime);
    if (!Array.isArray(records)) records = [];

    // ==============================
    // 新規保存処理（scoreがある場合のみ）
    // ==============================
    if (rawScore !== null) {
      const score = Number(rawScore);
      const miss = Number(rawMiss);

      const safeScore = Number.isFinite(score) ? score : 0;
      const safeMiss = Number.isFinite(miss) ? miss : 0;
      const safeRank = rawRank ? String(rawRank) : "-";

      records = saveRecord(courseKey, safeTime, {
        score: safeScore,
        miss: safeMiss,
        rank: safeRank,
        time: safeTime,
      });

      // パラメータ削除（二重保存防止）
      params.delete("score");
      params.delete("miss");
      params.delete("rank");
      history.replaceState(null, "", `record.html?${params.toString()}`);
    }

    // ==============================
    // 表示処理
    // ==============================
    renderSummary(records);
    renderRanking(records);
    renderRecords(records);

    function renderSummary(list) {
      if (!summaryAreaEl) return;
      if (!list.length) {
        summaryAreaEl.innerHTML = "";
        return;
      }

      const latest = list[0];
      const bestScore = Math.max(...list.map((r) => Number(r.score) || 0));
      const avgScore =
        list.reduce((s, r) => s + (Number(r.score) || 0), 0) / list.length;

      summaryAreaEl.innerHTML = `
          <div class="card bg-dark border-light mb-3">
            <div class="card-body">
              <div>ベストスコア： <strong>${bestScore}</strong></div>
              <div>直近スコア： <strong>${Number(latest.score) || 0}</strong></div>
              <div>直近ミス数： <strong>${Number(latest.miss) || 0}</strong></div>
              <div>直近ランク： <strong>${escapeHTML(latest.rank || "-")}</strong></div>
              <div>平均スコア： <strong>${avgScore.toFixed(1)}</strong></div>
            </div>
          </div>
        `;
    }

    function renderRanking(list) {
      if (!rankingAreaEl) return;
      if (!list.length) {
        rankingAreaEl.innerHTML = "";
        return;
      }

      const ranking = [...list]
        .sort((a, b) => (Number(b.score) || 0) - (Number(a.score) || 0))
        .slice(0, 5);

      rankingAreaEl.innerHTML = `
          <h5>ベスト記録</h5>
          <ul class="list-group">
            ${ranking
              .map(
                (r, i) => `
                <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                  <span>${i + 1}位</span>
                  <span>
                    Score: <strong>${Number(r.score) || 0}</strong> /
                    Miss: ${Number(r.miss) || 0} /
                    Rank: ${escapeHTML(r.rank || "-")}
                  </span>
                </li>
              `,
              )
              .join("")}
          </ul>
        `;
    }

    function renderRecords(list) {
      if (!recordListEl) return;
      if (!list.length) {
        recordListEl.innerHTML = "まだ記録はありません";
        return;
      }

      recordListEl.innerHTML = `
          <ul class="list-group">
            ${list
              .map((r) => {
                const d = new Date(Number(r.date) || Date.now());
                const ds =
                  d.getFullYear() +
                  "/" +
                  String(d.getMonth() + 1).padStart(2, "0") +
                  "/" +
                  String(d.getDate()).padStart(2, "0") +
                  " " +
                  String(d.getHours()).padStart(2, "0") +
                  ":" +
                  String(d.getMinutes()).padStart(2, "0");

                return `
                  <li class="list-group-item bg-dark text-light">
                    <div class="d-flex justify-content-between">
                    <span class="text-secondary small">
                      ${ds}（${r.time || safeTime}秒）
                    </span>
                    <span>Rank: <strong>${escapeHTML(r.rank || "-")}</strong></span>
                    </div>
                    <div>
                      Score: <strong>${Number(r.score) || 0}</strong> /
                      Miss: ${Number(r.miss) || 0}
                    </div>
                  </li>
                `;
              })
              .join("")}
          </ul>
        `;
    }
    // ==============================
    // 左ナビ タブ切り替え
    // ==============================
    const tabBasic = document.getElementById("tab-basic");
    const tabModern = document.getElementById("tab-modern");

    const basicCourses = document.getElementById("basic-courses");
    const modernCourses = document.getElementById("modern-courses");
    const navCourseLinks = document.querySelectorAll(
      "#basic-courses a, #modern-courses a",
    );

    function getNavCourseKey(link) {
      try {
        const href = link.getAttribute("href") || "";
        const url = new URL(href, location.href);
        return url.searchParams.get("course");
      } catch {
        return null;
      }
    }

    function resolveActiveCourseKey(key) {
      const fallbackMap = {
        html: "html",
        html_basic: "html",
        css: "css",
        css_basic: "css",
        js: "js",
        js_basic: "js",
        javascript: "js",
        javascript_basic: "js",
        php: "php",
        php_basic: "php",
        wordpress: "wordpress",
        wordpress_basic: "wordpress",
        mysql: "mysql",
        mysql_basic: "mysql",
      };

      return fallbackMap[key] || key;
    }

    function applyActiveNavLink(key) {
      if (!navCourseLinks.length) return;

      navCourseLinks.forEach((link) => {
        link.classList.add("course-btn");
        link.classList.remove("active");
      });

      // まず完全一致を優先
      let activeLink = [...navCourseLinks].find(
        (link) => getNavCourseKey(link) === key,
      );

      // 完全一致が無い場合だけ、basic系の単一キーにフォールバック
      if (!activeLink) {
        const fallbackKey = resolveActiveCourseKey(key);
        activeLink = [...navCourseLinks].find(
          (link) => getNavCourseKey(link) === fallbackKey,
        );
      }

      if (activeLink) activeLink.classList.add("active");
    }

    if (tabBasic && tabModern) {
      tabBasic.addEventListener("click", function () {
        basicCourses.style.display = "block";
        modernCourses.style.display = "none";

        tabBasic.classList.add("active");
        tabModern.classList.remove("active");
      });

      tabModern.addEventListener("click", function () {
        basicCourses.style.display = "none";
        modernCourses.style.display = "block";

        tabModern.classList.add("active");
        tabBasic.classList.remove("active");
      });
    }

    // ==============================
    // URLパラメータでタブ初期状態
    // ==============================
    const modernList = [
      "react",
      "vue",
      "next",
      "ts",
      "typescript",
      "tailwind",
      "python-basic",
      "django",
      "symbols",
    ];

    if (modernList.includes(courseKey)) {
      basicCourses.style.display = "none";
      modernCourses.style.display = "block";

      tabModern.classList.add("active");
      tabBasic.classList.remove("active");
    }

    applyActiveNavLink(courseKey);

    // ==============================
    // リセット
    // ==============================
    if (resetBtn) {
      resetBtn.onclick = function () {
        if (!confirm("このコースの記録を削除しますか？")) return;
        localStorage.removeItem(storageKey(courseKey, safeTime));
        records = [];
        renderSummary(records);
        renderRanking(records);
        renderRecords(records);
      };
    }

    // ==============================
    // XSS対策
    // ==============================
    function escapeHTML(str) {
      return String(str)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
    }
  } catch (err) {
    console.error("record.html error:", err);
    document.body.innerHTML =
      "<div style='color:white;padding:20px'>エラーが発生しました。コンソールを確認してください。</div>";
  }
})();
