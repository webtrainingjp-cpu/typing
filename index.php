<?php
$page_title = 'プログラミング向けタイピング練習｜HTML・CSSからReact・Djangoまで対応｜WebTraining';
$page_description = 'Web制作・プログラミング学習者向けのタイピング練習ツール。HTML・CSS・JavaScript・PHPからReact・Djangoまで対応。';

require 'header.php';
?>

<section class="ranking-hero mb-5">
  <div class="ranking-hero__content">
    <div class="ranking-hero__copy">
      <p class="ranking-hero__eyebrow">Weekly Battle</p>
      <h2 class="ranking-hero__title roboto-mono ">Ranking challenge</h2>
      <p class="ranking-hero__text">
        60秒・全員同一問題で勝負！通常の練習モードとは別ルールで、
        固定問題にどこまで速く正確に打てるかを競うランキング専用モードです。
      </p>
      <div class="ranking-hero__meta">
        <span>固定問題</span>
        <span>60秒一本勝負</span>
        <span>再挑戦は60秒後</span>
      </div>
    </div>

    <div class="ranking-hero__actions">
      <a href="typing-rank.html?course=html_rank" class="ranking-hero__button">
        HTMLで挑戦
      </a>
      <a href="typing-rank.html?course=css_rank" class="ranking-hero__button">
        CSSで挑戦
      </a>
      <a href="typing-rank.html?course=js_rank" class="ranking-hero__button">
        JavaScriptで挑戦
      </a>
      <a href="typing-rank.html?course=php_rank" class="ranking-hero__button">
        PHPで挑戦
      </a>
    </div>
  </div>
</section>
<div class="row g-5 mb-5">
  <div class="col-md-7">
    <div class="section-lead section-lead-basic mb-5">
      <h2 class="title section-lead__title">基礎を固めるタイピング</h2>
      <p class="section-lead__text">
        HTML・CSS・JavaScriptの基本構造をタイピングで身につけよう
      </p>
    </div>
    <div class="basic-zone">
      <!-- HTML -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>HTML / Basic</h3>
          <p>HTMLの基本タグ構造をタイピングで習得します。</p>
          <a href="typing.html?course=html" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>
      <!-- HTML -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>HTML / Intermediate</h3>
          <p>HTMLのセマンティクス要素とform要素をタイピングで習得します。</p>
          <a href="typing.html?course=html_intermediate" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- CSS Basic -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>CSS / Basic</h3>
          <p>CSSの基本プロパティを中心に練習します。</p>
          <a href="typing.html?course=css" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- CSS Intermediate -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>CSS / Intermediate</h3>
          <p>レイアウト・擬似要素・レスポンシブ対応を練習します。</p>
          <a href="typing.html?course=css_intermediate" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- CSS Practice -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>CSS / Practice</h3>
          <p>Grid・レスポンシブ・アニメーションを組み合わせた実戦CSSを練習します。</p>
          <a href="typing.html?course=css_practice" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- JavaScript Basic -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>JavaScript / Basic</h3>
          <p>変数・条件分岐・DOM操作の基礎を練習します。</p>
          <a href="typing.html?course=js" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- JavaScript Intermediate -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>JavaScript / Intermediate</h3>
          <p>API・非同期処理・実践的なDOM制御を練習します。</p>
          <a href="typing.html?course=js_intermediate" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- PHP Basic -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>PHP / Basic</h3>
          <p>PHPの文法・配列・条件分岐を練習します。</p>
          <a href="typing.html?course=php" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- PHP Intermediate -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>PHP / Intermediate</h3>
          <p>関数・セッション・DB操作の基礎を学びます。</p>
          <a href="typing.html?course=php_intermediate" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- WordPress Basic -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>WordPress / Basic</h3>
          <p>テーマ制作の基本関数（ループ・テンプレート・the_系）を練習します。</p>
          <a href="typing.html?course=wordpress_basic" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- WordPress Intermediate -->
      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>WordPress / Intermediate</h3>
          <p>get_the_* 系・WP_Query・取得→加工→出力を練習します。</p>
          <a href="typing.html?course=wordpress_intermediate" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- MySQL Basic -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>MySQL / Basic</h3>
          <p>SELECT・INSERTなどSQLの基本構文を練習します。</p>
          <a href="typing.html?course=mysql" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <!-- MySQL Intermediate -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>MySQL / Intermediate</h3>
          <p>JOIN・サブクエリなど実務SQLを練習します。</p>
          <a href="typing.html?course=mysql_intermediate" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>


      <!-- Bootstrap -->

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Bootstrap</h3>
          <p>よく使うクラスを素早く入力できるようにします。</p>
          <a href="typing.html?course=bootstrap" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="section-lead section-lead-modern mb-5">
      <h2 class="title section-lead__title title-modern">実践コードに挑戦</h2>
      <p class="section-lead__text">
        ReactやNext.jsなど実務レベルのコード入力に挑戦しよう
      </p>
    </div>

    <div class="modern-zone">

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Programming Symbols</h3>
          <p>コーディングで頻出する記号入力を集中的に練習します。</p>
          <a href="typing.html?course=symbols" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>


      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>React</h3>
          <p>JSX構文・コンポーネント記述を練習します。</p>
          <a href="typing.html?course=react" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>




      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Vue</h3>
          <p>ディレクティブやテンプレート構文に慣れます。</p>
          <a href="typing.html?course=vue" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>


      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Next.js</h3>
          <p>サーバーサイド連携やルーティング構文を練習します。</p>
          <a href="typing.html?course=next" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Python</h3>
          <p>Python基礎コードの練習</p>
          <a href="typing.html?course=python-basic" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Django</h3>
          <p>PythonによるWebテンプレート構文を練習します。</p>
          <a href="typing.html?course=django" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>TypeScript</h3>
          <p>型定義・ジェネリクスなどの構文に慣れます。</p>
          <a href="typing.html?course=ts" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>

      <div class="card wt-glass-card">
        <div class="card-body d-flex flex-column">
          <h3>Tailwind CSS</h3>
          <p>ユーティリティクラスの高速入力を練習します。</p>
          <a href="typing.html?course=tailwind" class="btn btn-outline-light mt-auto">
            タイピング練習
          </a>
        </div>
      </div>
    </div>
  </div>

</div>
<?php require 'footer.php'; ?>