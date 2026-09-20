<?php
// --- 1. LOGIKA PHP (BACKEND) ---
$ekspresi = '';
$hasil = '0';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num1'], $_POST['num2'], $_POST['op'])) {
    $n1 = (float)$_POST['num1'];
    $n2 = (float)$_POST['num2'];
    $op = $_POST['op'];
    $simbol = ['+' => '+', '-' => '−', '*' => '×', '/' => '÷', '%' => '%'][$op] ?? $op;
    $ekspresi = "$n1 $simbol $n2 =";

    switch ($op) {
        case '+': $res = $n1 + $n2; break;
        case '-': $res = $n1 - $n2; break;
        case '*': $res = $n1 * $n2; break;
        case '/':
            if ($n2 == 0) { $isError = true; $hasil = 'Error'; }
            else { $res = $n1 / $n2; }
            break;
        case '%': $res = $n1 * ($n2 / 100); break;
        default:  $isError = true; $hasil = 'Error'; break;
    }
    if (!$isError) $hasil = (string)round($res, 8);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator iOS</title>
    <style>
        /* CSS Responsif & Ringkas - iOS Dark Mode */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, sans-serif; }
        body { background: #121214; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 12px; }
        
        .calculator {
            background: #000;
            width: 100%;
            max-width: 340px;
            padding: clamp(14px, 4vw, 20px);
            border-radius: clamp(28px, 8vw, 40px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
        }
        .display { text-align: right; padding: 15px 6px 10px; min-height: 100px; display: flex; flex-direction: column; justify-content: flex-end; }
        .expr { color: #8e8e93; font-size: clamp(0.85rem, 3vw, 1.05rem); min-height: 20px; }
        .val { color: #fff; font-size: clamp(2.2rem, 11vw, 3.4rem); font-weight: 300; line-height: 1.1; word-break: break-all; }
        .val.error { color: #ff453a; font-size: clamp(1.8rem, 8vw, 2.4rem); }

        /* Grid Responsif (Tombol selalu bulat & proporsional dengan aspect-ratio: 1) */
        .keypad { display: grid; grid-template-columns: repeat(4, 1fr); gap: clamp(8px, 2.5vw, 12px); margin-top: 10px; }
        button {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 50%;
            border: none;
            font-size: clamp(1.2rem, 5.5vw, 1.6rem);
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        button:active { opacity: 0.75; }
        
        .btn-fn   { background: #a5a5a5; color: #000; font-weight: 500; }
        .btn-num  { background: #333333; color: #fff; }
        .btn-op   { background: #ff9f0a; color: #fff; font-size: clamp(1.4rem, 6vw, 1.8rem); }
        .btn-zero { grid-column: span 2; aspect-ratio: auto; height: 100%; border-radius: 40px; justify-content: flex-start; padding-left: clamp(18px, 5vw, 24px); }
    </style>
</head>
<body>

    <div class="calculator">
        <!-- Display iOS Bertingkat -->
        <div class="display">
            <div class="expr" id="expr"><?= htmlspecialchars($ekspresi) ?></div>
            <div class="val <?= $isError ? 'error' : '' ?>" id="val"><?= htmlspecialchars($hasil) ?></div>
        </div>

        <!-- Form Hidden POST ke PHP -->
        <form method="POST" id="formCalc" style="display:none;">
            <input type="hidden" name="num1" id="num1">
            <input type="hidden" name="op" id="op">
            <input type="hidden" name="num2" id="num2">
        </form>

        <!-- Grid Tombol Responsif -->
        <div class="keypad">
            <button class="btn-fn" id="btnAC">AC</button>
            <button class="btn-fn" id="btnSign">±</button>
            <button class="btn-fn" id="btnPct">%</button>
            <button class="btn-op" data-op="/">÷</button>

            <button class="btn-num" data-num="7">7</button>
            <button class="btn-num" data-num="8">8</button>
            <button class="btn-num" data-num="9">9</button>
            <button class="btn-op" data-op="*">×</button>

            <button class="btn-num" data-num="4">4</button>
            <button class="btn-num" data-num="5">5</button>
            <button class="btn-num" data-num="6">6</button>
            <button class="btn-op" data-op="-">−</button>

            <button class="btn-num" data-num="1">1</button>
            <button class="btn-num" data-num="2">2</button>
            <button class="btn-num" data-num="3">3</button>
            <button class="btn-op" data-op="+">+</button>

            <button class="btn-num btn-zero" data-num="0">0</button>
            <button class="btn-num" id="btnDot">.</button>
            <button class="btn-op" id="btnEquals">=</button>
        </div>
    </div>

    <!-- --- 2. LOGIKA JAVASCRIPT (CLIENT-SIDE) --- -->
    <script>
        const val = document.getElementById('val');
        const expr = document.getElementById('expr');
        let num1 = "<?= ($hasil !== '0' && !$isError) ? $hasil : '' ?>";
        let currentOp = null;
        let isWaitingSecondNum = false;

        // Input Angka (0-9)
        document.querySelectorAll('[data-num]').forEach(btn => {
            btn.onclick = () => {
                val.classList.remove('error');
                const n = btn.dataset.num;
                if (val.innerText === '0' || isWaitingSecondNum) {
                    val.innerText = n;
                    isWaitingSecondNum = false;
                } else {
                    val.innerText += n;
                }
            };
        });

        // Input Titik Desimal (.)
        document.getElementById('btnDot').onclick = () => {
            if (isWaitingSecondNum) { val.innerText = '0.'; isWaitingSecondNum = false; }
            else if (!val.innerText.includes('.')) val.innerText += '.';
        };

        // Tombol Operasi (+, −, ×, ÷, %)
        document.querySelectorAll('[data-op]').forEach(btn => {
            btn.onclick = () => {
                num1 = val.innerText;
                currentOp = btn.dataset.op;
                expr.innerText = `${num1} ${btn.innerText}`;
                isWaitingSecondNum = true;
            };
        });

        // Tombol Reset (AC)
        document.getElementById('btnAC').onclick = () => {
            val.innerText = '0';
            expr.innerText = '';
            num1 = '';
            currentOp = null;
            val.classList.remove('error');
        };

        // Tombol Plus/Minus (±) & Persen (%)
        document.getElementById('btnSign').onclick = () => {
            if (val.innerText !== '0') val.innerText = val.innerText.startsWith('-') ? val.innerText.slice(1) : '-' + val.innerText;
        };
        document.getElementById('btnPct').onclick = () => {
            val.innerText = String(parseFloat(val.innerText) / 100);
        };

        // Tombol Sama Dengan (=) -> Submit POST ke PHP
        document.getElementById('btnEquals').onclick = () => {
            if (currentOp && num1 !== '') {
                document.getElementById('num1').value = num1;
                document.getElementById('op').value = currentOp;
                document.getElementById('num2').value = val.innerText;
                document.getElementById('formCalc').submit();
            }
        };
    </script>
</body>
</html>
