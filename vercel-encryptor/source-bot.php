<?php

define('INNER_WIDTH', 44);

function color($color, $text) {
    $colors = [
        'green'  => "\e[1;32m",
        'red'    => "\e[1;31m",
        'yellow' => "\e[1;33m",
        'blue'   => "\e[1;34m",
        'cyan'   => "\e[1;36m",
        'white'  => "\e[1;37m",
        'reset'  => "\e[0m"
    ];
    $code = isset($colors[$color]) ? $colors[$color] : $colors['reset'];
    return $code . $text . $colors['reset'];
}

function get_visual_length($text) {
    $plain = preg_replace('/\x1b\[[0-9;]*m/', '', $text);
    return mb_strwidth($plain, 'UTF-8');
}

function center_text($text) {
    $width = INNER_WIDTH;
    $visual_len = get_visual_length($text);
    $padding = ($width - $visual_len) / 2;
    if ($padding < 0) $padding = 0;
    return str_repeat(" ", floor($padding)) . $text . str_repeat(" ", ceil($padding));
}

function draw_box($lines, $color = 'cyan') {
    $width = INNER_WIDTH;
    $top = color($color, "┌" . str_repeat("─", $width) . "┐\n");
    $bottom = color($color, "└" . str_repeat("─", $width) . "┘\n");
    echo $top;
    foreach ($lines as $line) {
        $visual_len = get_visual_length($line);
        $spacing = $width - $visual_len;
        if ($spacing < 0) $spacing = 0;
        echo color($color, "│") . $line . str_repeat(" ", $spacing) . color($color, "│\n");
    }
    echo $bottom;
}

function cooldown($seconds) {
    for ($i = $seconds; $i > 0; $i--) {
        echo "\r " . color('yellow', "[⏳] COOLDOWN : ") . color('white', $i . " detik... ");
        sleep(1);
    }
    echo "\r" . str_repeat(' ', INNER_WIDTH + 4) . "\r";
}

function show_banner() {
    system("clear");
    draw_box([
        color('cyan', center_text("AUTO CLAIM BINTANG BOT TELEGRAM")),
        color('blue', center_text("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━")),
        " " . color('white', "Author   : Mr.Tr3v!0n"),
        " " . color('white', "Channel  : t.me/config_geratis"),
        " " . color('white', "Key Link : https://sfile.co/wUTLNwAtMg4"),
        " " . color('white', "Status   : Premium Auto-Device Version")
    ], 'cyan');
    echo "\n";
}

function generate_auto_client_id($init_data) {
    parse_str($init_data, $parsed);
    $user_id = "default_device";

    if (isset($parsed['user'])) {
        $user_obj = json_decode($parsed['user'], true);
        if (isset($user_obj['id'])) {
            $user_id = $user_obj['id'];
        }
    }

    $hash = md5($user_id);
    return sprintf('%08s-%04s-%04s-%04s-%12s',
        substr($hash, 0, 8),
        substr($hash, 8, 4),
        substr($hash, 12, 4),
        substr($hash, 16, 4),
        substr($hash, 20, 12)
    );
}

show_banner();

echo " " . color('yellow', "[?] Masukkan Access Key : ");
$access_key = trim(fgets(STDIN));

if ($access_key !== "claim_bintang") {
    echo "\n";
    draw_box([
        " " . color('red', "[❌] ACCESS DENIED: Key Salah!"),
        " " . color('white', "Silakan dapatkan key yang valid di:"),
        " " . color('cyan', "https://sfile.co/wUTLNwAtMg4")
    ], 'red');
    exit;
}

show_banner();

echo " " . color('green', "[✓] KUNCI BERHASIL DIVERIFIKASI!\n\n");
echo " " . color('yellow', "[?] Masukkan Telegram Init Data : ");
$init_data = trim(fgets(STDIN));

if (empty($init_data)) {
    echo "\n";
    draw_box([
        " " . color('red', "[❌] ERROR: Data Tidak Boleh Kosong!"),
        " " . color('white', "Program otomatis dihentikan.")
    ], 'red');
    exit;
}

$client_id = generate_auto_client_id($init_data);

show_banner();

echo " " . color('green', "[✓] SINKRONISASI BERHASIL - MODE AUTO CLAIM AKTIF\n\n");
sleep(1);

$id_klaim = 1;
$url = "https://spinhub.cc/api/tasks/1/claim";

while (true) {
    echo " " . color('blue', "[🔄] Klaim Ke-{$id_klaim} : ") . color('white', "Mengirim paket data...\r");

    $headers = [
        "Host: spinhub.cc",
        "Connection: keep-alive",
        'sec-ch-ua: "Chromium";v="137", "Not/A)Brand";v="24"',
        "Content-Type: application/json",
        "X-Client-Id: " . $client_id,
        "sec-ch-ua-mobile: ?1",
        "User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36",
        "X-Telegram-Init-Data: " . $init_data,
        "sec-ch-ua-platform: \"Android\"",
        "Accept: */*",
        "Origin: https://spinhub.cc",
        "Sec-Fetch-Site: same-origin",
        "Sec-Fetch-Mode: cors",
        "Sec-Fetch-Dest: empty",
        "Accept-Language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    echo "\r" . str_repeat(' ', INNER_WIDTH + 4) . "\r";

    if (curl_errno($ch)) {
        draw_box([
            " " . color('red', "[❌] CONNECTION FAILURE / RTO"),
            " " . color('white', " Detail: " . curl_error($ch))
        ], 'red');
    } else {
        $data = json_decode($response, true);

        if ($data === null) {
            draw_box([
                " " . color('red', "[❌] RESPONSE INVALID"),
                " " . color('white', " Server sedang sibuk atau down.")
            ], 'red');
        } else {
            if (isset($data['ok']) && $data['ok'] === true) {
                $reward  = $data['reward'] ?? 0;
                $balance = $data['balance'] ?? 0;

                draw_box([
                    " " . color('green', "[💰] SUCCESS: Klaim Berhasil!"),
                    " " . color('white', " Urutan  : Klaim Ke-{$id_klaim}"),
                    " " . color('white', " Reward  : +{$reward}"),
                    " " . color('white', " Saldo   : {$balance}")
                ], 'green');

            } else {
                $errorMsg = $data['error'] ?? 'unknown_error';

                if ($errorMsg === "on_cooldown") {
                    draw_box([
                        " " . color('yellow', "[⚠️] NOTICE: Tugas Selesai / Cooldown"),
                        " " . color('white', " Kuota tugas Anda telah habis."),
                        " " . color('white', " Script otomatis berhenti. Sampai jumpa!")
                    ], 'yellow');
                    exit;
                } else {
                    $cleanError = ucwords(str_replace('_', ' ', $errorMsg));
                    draw_box([
                        " " . color('red', "[❌] CLAIM FAILED / REJECTED"),
                        " " . color('white', " Alasan : " . $cleanError)
                    ], 'red');
                }
            }
        }
    }

    $id_klaim++;
    cooldown(3);
}
