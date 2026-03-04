// course-config.js
// =====================================================
// コース定義（courseKey → 表示名 / JSONパス）
// - typing.js は COURSE_MAP[courseKey].json を fetch します
// - URLパラメータ ?course=xxx の xxx が courseKey になります
// =====================================================

const COURSE_MAP = {
  // ===== HTML =====
  html: {
    label: "HTML / Basic コース",
    json: "data/html-basic.json",
  },
  html_intermediate: {
    label: "HTML / Intermediate コース",
    json: "data/html-intermediate.json",
  },

  // ===== CSS =====
  css: {
    label: "CSS / Basic コース",
    json: "data/css-basic.json",
  },
  css_intermediate: {
    label: "CSS / Intermediate コース",
    json: "data/css-intermediate.json",
  },
  css_practice: {
    label: "CSS / Practice コース",
    json: "data/css-practice.json",
  },

  // ===== JavaScript =====
  js: {
    label: "JavaScript / Basic コース",
    json: "data/js-basic.json",
  },
  js_intermediate: {
    label: "JavaScript / Intermediate コース",
    json: "data/js-intermediate.json",
  },

  // ===== PHP =====
  php: {
    label: "PHP / Basic コース",
    json: "data/php-basic.json",
  },
  php_intermediate: {
    label: "PHP / Intermediate コース",
    json: "data/php-intermediate.json",
  },

  // ===== MySQL =====
  mysql: {
    label: "MySQL / Basic コース",
    json: "data/mysql-basic.json",
  },
  mysql_intermediate: {
    label: "MySQL / Intermediate コース",
    json: "data/mysql-intermediate.json",
  },

  // ===== CMS / FW / その他 =====
  // 互換用：これまで ?course=wordpress でアクセスしていた場合に備えて残す（Basicへ）
  wordpress: {
    label: "WordPress / Basic コース",
    json: "data/wordpress-basic.json",
  },

  // 新規：WordPress を basic / intermediate に分割
  wordpress_basic: {
    label: "WordPress / Basic コース",
    json: "data/wordpress-basic.json",
  },
  wordpress_intermediate: {
    label: "WordPress / Intermediate コース",
    json: "data/wordpress-intermediate.json",
  },

  bootstrap: {
    label: "Bootstrap / Basic コース",
    json: "data/bootstrap.json",
  },
  tailwind: {
    label: "Tailwind CSS / Basic コース",
    json: "data/tailwind.json",
  },

  // ===== フロントエンドFW =====
  react: {
    label: "React / Basic コース",
    json: "data/react.json",
  },
  vue: {
    label: "Vue / Basic コース",
    json: "data/vue.json",
  },
  next: {
    label: "Next.js / Basic コース",
    json: "data/next.json",
  },

  // ===== 言語 =====
  ts: {
    label: "TypeScript / Basic コース",
    json: "data/typescript.json",
  },
  "python-basic": {
    label: "Python / Basic コース",
    json: "data/python-basic.json",
  },
  django: {
    label: "Django / Basic コース",
    json: "data/django.json",
  },

  // ===== 補助 =====
  symbols: {
    label: "Programming Symbols コース",
    json: "data/symbols.json",
  },
};
window.COURSE_MAP = COURSE_MAP;
