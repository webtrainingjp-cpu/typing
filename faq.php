<?php
$page_title = 'FAQ｜よくある質問｜プログラミング向けタイピング練習｜WebTraining';
$page_description = 'typing.webtraining.jp に関するよくある質問ページです。対応端末、制限時間、スコア、推奨環境などについてご案内しています。';

require 'header.php';
?>

<main class="container mb-5" style="max-width: 980px;">
    <section class="bg-body-tertiary rounded-4 p-4 p-md-5 shadow-sm">

        <h1 class="h3 mb-4">よくある質問（FAQ）</h1>

        <div class="accordion" id="faqAccordion">

            <!-- Q1 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1">
                        このサイトは無料で使えますか？
                    </button>
                </h2>
                <div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        はい、typing.webtraining.jp は無料でご利用いただけます。
                        会員登録なども不要で、ページを開いてすぐにタイピング練習を始められます。
                    </div>
                </div>
            </div>

            <!-- Q2 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">
                        スマートフォンやタブレットでも使えますか？
                    </button>
                </h2>
                <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        基本はパソコン専用です。タブレット（iPadなど）での利用も可能です。
                        スマートフォンでも表示は可能ですが、画面サイズやキーボードの都合上、
                        本格的なタイピング練習には向いていません。
                    </div>
                </div>
            </div>

            <!-- Q3 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">
                        推奨される利用環境はありますか？
                    </button>
                </h2>
                <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        パソコン（Windows / macOS）での利用を推奨しています。
                        ブラウザは Google Chrome、Microsoft Edge、Safari の最新版をご利用ください。
                        物理キーボードでの入力を前提とした設計になっています。
                    </div>
                </div>
            </div>

            <!-- Q4 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a4">
                        制限時間は変更できますか？
                    </button>
                </h2>
                <div id="a4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        現在は、あらかじめ設定された制限時間(120秒)での練習となっています。
                        今後のアップデートで、練習時間の調整機能を追加する可能性があります。
                    </div>
                </div>
            </div>

            <!-- Q5 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q5">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a5">
                        スコアや結果は保存されますか？
                    </button>
                </h2>
                <div id="a5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        はい、スコアや結果は <strong>ご利用中のブラウザ内に自分の記録として保存</strong> されます。<br><br>

                        コースごとに練習結果が記録され、
                        「自分のタイピング記録」ページから、過去のスコアや実行日時を確認できます。<br><br>

                        なお、この記録は <strong>ご利用の端末・ブラウザごと</strong> に保存されるため、
                        別のパソコンやブラウザでは共有されません。
                    </div>

                </div>
            </div>

            <!-- Q6 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q6">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a6">
                        WebTrainingの受講生でなくても使えますか？
                    </button>
                </h2>
                <div id="a6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        はい、WebTrainingの受講生でなくてもご利用いただけます。
                        どなたでも自由にお使いいただけるタイピング練習ツールです。
                    </div>
                </div>
            </div>

            <!-- Q7 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q7">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a7">
                        WebTrainingの教材とどう組み合わせると効果的ですか？
                    </button>
                </h2>
                <div id="a7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        学習前や課題制作前のウォーミングアップとして使うのがおすすめです。
                        実際にコードを書く前に指を慣らすことで、学習効率の向上につながります。
                    </div>
                </div>
            </div>

            <!-- Q8 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q8">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a8">
                        不具合や要望はどこから連絡できますか？
                    </button>
                </h2>
                <div id="a8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        お問い合わせページよりご連絡ください。
                        内容を確認のうえ、今後の改善に役立てていきます。
                    </div>
                </div>
            </div>

            <!-- Q9 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q9">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a9">
                        ランキングが増えすぎた場合はどうなりますか？
                    </button>
                </h2>
                <div id="a9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        今後追加予定の「みんなのランキング」では、見やすさと公平性を保つため、
                        記録の管理方法を工夫する予定です。<br><br>

                        例えば、<br>
                        ・一定期間ごとの更新（リフレッシュ）<br>
                        ・上位記録のみを表示<br>
                        ・コースごとに独立したランキング管理<br><br>

                        などを想定しています。<br><br>

                        まずは自分の成長を確認できる「自分のタイピング記録」を中心にご利用ください。
                    </div>

                </div>
            </div>

            <!-- Q10 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q10">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a10">
                        ランキング登録名のルールはありますか？
                    </button>
                </h2>
                <div id="a10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        今後追加予定の「みんなのランキング」では、表示名（ニックネーム）を登録できる仕組みを予定しています。<br><br>

                        その際は、ランキングを見やすく、安心して利用できるよう、
                        登録名には一定のルールを設ける予定です。<br><br>

                        使用可能な文字例：<br>
                        ・英字（A〜Z / a〜z）<br>
                        ・数字（0〜9）<br>
                        ・日本語（ひらがな・カタカナ・漢字）<br><br>

                        不適切な表現や装飾的な記号、絵文字などは使用できない場合があります。<br><br>

                        実装時には、あらためて詳細をご案内します。
                    </div>

                </div>
            </div>


            <!-- Q11 -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="q11">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a11">
                        同じ名前で何度も登録できますか？
                    </button>
                </h2>
                <div id="a11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        今後追加予定の「みんなのランキング」では、繰り返しチャレンジできる仕組みを想定しています。<br><br>

                        同じ表示名で複数回挑戦した場合でも、
                        より良い記録を残しやすい形で表示方法を調整する予定です。<br><br>

                        まずは自分のペースで練習を重ね、
                        「自分のタイピング記録」で成長を確認してみてください。
                    </div>

                </div>
            </div>



        </div>

    </section>
</main>

<?php require 'footer.php'; ?>