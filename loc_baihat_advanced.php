<?php
// Kết nối database
$conn = new mysqli('localhost', 'root', 'root', 'userphp');
$conn->set_charset("utf8");

// Lấy danh sách ca sĩ
$ca_si = $conn->query("SELECT idCS, HoTenCS, urlHinhCS FROM webnhac_casi ORDER BY HoTenCS ASC");

// Xử lý lọc
$ds_baihat = [];
$ten_casi = '';
if (isset($_GET['casi_id']) && $_GET['casi_id'] != '') {
    $casi_id = intval($_GET['casi_id']);
    // Lấy tên ca sĩ
    $result = $conn->query("SELECT HoTenCS, urlHinhCS, GioiThieuCS FROM webnhac_casi WHERE idCS = $casi_id");
    $info_casi = $result->fetch_assoc();
    $ten_casi = $info_casi['HoTenCS'];
    $urlHinhCS = $info_casi['urlHinhCS'];
    $gioithieu = $info_casi['GioiThieuCS'];
    // Lấy danh sách bài hát
    $ds_baihat = $conn->query("SELECT TenBH, urlBH FROM webnhac_baihat WHERE idCS = $casi_id ORDER BY TenBH ASC");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lọc bài hát theo ca sĩ</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,60,60,0.18);
            padding: 32px 40px 40px 40px;
        }
        h1 {
            text-align: center;
            color: #f76d6d;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }
        form {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        select {
            padding: 10px 18px;
            border-radius: 8px;
            border: 1px solid #f76d6d;
            font-size: 1rem;
            outline: none;
            margin-right: 10px;
        }
        button {
            background: #f76d6d;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #fda085;
        }
        .casi-info {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 24px;
            background: #fff7f0;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 2px 8px rgba(247,109,109,0.08);
        }
        .casi-info img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #f76d6d;
            background: #fff;
        }
        .casi-info .bio {
            flex: 1;
        }
        .casi-info .bio h2 {
            margin: 0 0 10px 0;
            color: #f76d6d;
            font-size: 1.3rem;
        }
        .casi-info .bio .desc {
            color: #444;
            font-size: 0.98rem;
            max-height: 90px;
            overflow: auto;
        }
        .song-list {
            margin-top: 10px;
        }
        .song-list ul {
            list-style: none;
            padding: 0;
        }
        .song-list li {
            background: #f6d365;
            margin-bottom: 10px;
            padding: 12px 18px;
            border-radius: 8px;
            color: #333;
            font-size: 1.08rem;
            display: flex;
            align-items: center;
            transition: background 0.2s;
        }
        .song-list li:hover {
            background: #fda085;
            color: #fff;
        }
        .song-list li:before {
            content: '🎵';
            margin-right: 12px;
            font-size: 1.2rem;
        }
        @media (max-width: 600px) {
            .container { padding: 12px; }
            .casi-info { flex-direction: column; align-items: center; }
            .casi-info img { margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lọc bài hát theo ca sĩ</h1>
        <form method="get">
            <select name="casi_id" required>
                <option value="">-- Chọn ca sĩ --</option>
                <?php while ($row = $ca_si->fetch_assoc()): ?>
                    <option value="<?= $row['idCS'] ?>" <?= (isset($_GET['casi_id']) && $_GET['casi_id'] == $row['idCS']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['HoTenCS']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Lọc</button>
        </form>

        <?php if (!empty($ten_casi)): ?>
            <div class="casi-info">
                <?php
                // Always use avatar from ui-avatars.com for the singer
                $img_src = 'https://ui-avatars.com/api/?name=' . urlencode($ten_casi) . '&background=f76d6d&color=fff&size=110';
                ?>
                <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($ten_casi) ?>">
                <div class="bio">
                    <h2><?= htmlspecialchars($ten_casi) ?></h2>
                    <div class="desc"><?= !empty($gioithieu) ? mb_substr(strip_tags($gioithieu), 0, 350) . '...' : 'Chưa có giới thiệu.' ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($ds_baihat) && $ds_baihat->num_rows > 0): ?>
            <div class="song-list">
                <h3>Danh sách bài hát:</h3>
                <ul>
                    <?php
                    // Lấy lại danh sách bài hát với đầy đủ thông tin
                    $ds_baihat_full = $conn->query("SELECT TenBH, LoiBH, urlBH, NgayCapNhat, SoLanNghe, SoLanDown FROM webnhac_baihat WHERE idCS = " . intval($_GET['casi_id']) . " ORDER BY TenBH ASC");
                    while ($baihat = $ds_baihat_full->fetch_assoc()): ?>
                        <li style="flex-direction:column;align-items:flex-start;">
                            <div style="font-weight:bold;font-size:1.1em;">
                                <?= htmlspecialchars($baihat['TenBH']) ?>
                                <?php if (!empty($baihat['urlBH'])): ?>
                                    &nbsp; <a href="<?= htmlspecialchars($baihat['urlBH']) ?>" target="_blank" style="color:#fff;background:#f76d6d;padding:2px 8px;border-radius:6px;font-size:0.95em;text-decoration:none;margin-left:10px;">Nghe</a>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($baihat['LoiBH'])): ?>
                                <div style="margin:6px 0 0 0; color:#555; font-size:0.98em; white-space:pre-line;">
                                    <?= nl2br(htmlspecialchars(strip_tags($baihat['LoiBH']))) ?>
                                </div>
                            <?php endif; ?>
                            <div style="margin-top:6px; color:#888; font-size:0.93em;">
                                <span>Ngày cập nhật: <?= htmlspecialchars($baihat['NgayCapNhat']) ?></span> |
                                <span>Lượt nghe: <?= htmlspecialchars($baihat['SoLanNghe']) ?></span> |
                                <span>Lượt tải: <?= htmlspecialchars($baihat['SoLanDown']) ?></span>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php elseif(isset($_GET['casi_id'])): ?>
            <div class="song-list">
                <p style="color:#f76d6d;">Không có bài hát nào cho ca sĩ này.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 