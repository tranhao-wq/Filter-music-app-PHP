<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'userphp');
$conn->set_charset("utf8");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the list of artists
$ca_si_result = $conn->query("SELECT idCS, HoTenCS, urlHinhCS FROM webnhac_casi ORDER BY HoTenCS ASC");
$ca_si = [];
while ($row = $ca_si_result->fetch_assoc()) {
    $ca_si[] = $row;
}

// Initialize variables for song list and artist info
$ds_baihat = [];
$ten_casi = '';
$urlHinhCS = '';
$gioithieu = '';

// Handle filtering by artist
if (isset($_GET['casi_id']) && $_GET['casi_id'] != '') {
    $casi_id = intval($_GET['casi_id']);

    // Get artist information
    $stmt_artist = $conn->prepare("SELECT HoTenCS, urlHinhCS, GioiThieuCS FROM webnhac_casi WHERE idCS = ?");
    $stmt_artist->bind_param("i", $casi_id);
    $stmt_artist->execute();
    $result_artist = $stmt_artist->get_result();
    $info_casi = $result_artist->fetch_assoc();

    if ($info_casi) {
        $ten_casi = $info_casi['HoTenCS'];
        $urlHinhCS = $info_casi['urlHinhCS'];
        $gioithieu = $info_casi['GioiThieuCS'];

        // Get the list of songs for the selected artist
        $stmt_songs = $conn->prepare("SELECT TenBH, LoiBH, urlBH, NgayCapNhat, SoLanNghe FROM webnhac_baihat WHERE idCS = ? ORDER BY TenBH ASC");
        $stmt_songs->bind_param("i", $casi_id);
        $stmt_songs->execute();
        $ds_baihat = $stmt_songs->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Nhạc Ấn Tượng</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #f76d6d;
            --secondary-color: #fda085;
            --accent-color: #f6d365;
            --text-dark: #333;
            --text-light: #fff;
            --bg-gradient: linear-gradient(120deg, var(--accent-color) 0%, var(--secondary-color) 100%);
            --card-bg: #fff;
            --shadow: 0 8px 32px rgba(60,60,60,0.18);
            --border-radius-lg: 18px;
            --border-radius-md: 12px;
            --border-radius-sm: 8px;
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 800px; /* Increased max-width for more content */
            margin: 40px auto;
            background: var(--card-bg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            padding: 32px 40px 40px 40px;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            letter-spacing: 2px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }

        select, button {
            padding: 12px 20px; /* Slightly larger padding */
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        select {
            border: 1px solid var(--primary-color);
            flex-grow: 1; /* Allow select to grow */
            max-width: 300px;
        }

        button {
            background: var(--primary-color);
            color: var(--text-light);
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .casi-info {
            display: flex;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 30px;
            background: #fff7f0; /* Lighter background for info box */
            border-radius: var(--border-radius-md);
            padding: 20px;
            box-shadow: 0 2px 8px rgba(247,109,109,0.08);
        }
        .casi-info img {
            width: 120px; /* Slightly larger avatar */
            height: 120px;
            object-fit: cover;
            border-radius: var(--border-radius-md);
            border: 3px solid var(--primary-color);
            background: var(--card-bg);
            flex-shrink: 0;
        }
        .casi-info .bio {
            flex: 1;
        }
        .casi-info .bio h2 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
            font-size: 1.6rem; /* Larger artist name */
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        .casi-info .bio .desc {
            color: #555;
            font-size: 0.95rem;
            max-height: 120px; /* Increased height for bio */
            overflow-y: auto; /* Scroll if content overflows */
            line-height: 1.5;
        }

        .song-list {
            margin-top: 20px;
        }
        .song-list h3 {
            color: var(--primary-color);
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            text-align: center;
        }
        .song-list ul {
            list-style: none;
            padding: 0;
        }
        .song-list li {
            background: var(--accent-color);
            margin-bottom: 15px; /* More space between songs */
            padding: 15px 20px;
            border-radius: var(--border-radius-sm);
            color: var(--text-dark);
            font-size: 1.1rem;
            display: flex;
            flex-direction: column; /* Stack song details */
            gap: 8px;
            transition: background 0.2s, transform 0.2s;
            position: relative;
            overflow: hidden; /* For pseudo-elements */
        }
        .song-list li:hover {
            background: var(--secondary-color);
            color: var(--text-light);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .song-list li::before {
            content: '🎶'; /* Changed emoji for song */
            position: absolute;
            top: 15px;
            left: 20px;
            font-size: 1.5rem;
            color: rgba(255,255,255,0.7); /* Lighter icon */
            opacity: 0.2;
            z-index: 0;
            transform: rotate(-15deg);
        }
        .song-title {
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.25rem;
            color: var(--text-dark); /* Ensure title remains dark */
            position: relative; /* To be above pseudo-element */
            z-index: 1;
        }
        .song-list li:hover .song-title {
            color: var(--text-light); /* Title changes color on hover */
        }

        .song-details {
            font-size: 0.9em;
            color: #666;
            display: flex;
            flex-wrap: wrap; /* Allow details to wrap */
            gap: 15px;
            position: relative;
            z-index: 1;
        }
        .song-list li:hover .song-details {
            color: rgba(255,255,255,0.9); /* Details lighter on hover */
        }
        .song-details span {
            display: flex;
            align-items: center;
        }
        .song-details span:before {
            margin-right: 5px;
            font-size: 1.1em;
        }
        .song-details .listen-count:before { content: '🎧'; }
        .song-details .update-date:before { content: '📅'; }
        .song-details .lyrics-toggle:before { content: '🎤'; }


        .lyrics-content {
            background: rgba(255,255,255,0.8);
            border-radius: var(--border-radius-sm);
            padding: 10px 15px;
            margin-top: 10px;
            font-size: 0.9em;
            max-height: 150px;
            overflow-y: auto;
            border-left: 3px solid var(--primary-color);
            display: none; /* Hidden by default */
            color: #444;
            white-space: pre-wrap; /* Preserve line breaks in lyrics */
            position: relative;
            z-index: 1;
        }
        .lyrics-content p {
            margin: 0;
        }

        .play-button {
            background: var(--primary-color);
            color: var(--text-light);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9em;
            text-decoration: none;
            margin-left: auto; /* Push to the right */
            transition: background 0.2s ease-in-out;
            align-self: flex-start; /* Align with title */
            position: relative;
            z-index: 1;
        }
        .play-button:hover {
            background: #e04a4a;
        }

        .no-songs-message {
            text-align: center;
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-top: 30px;
            background: #fff7f0;
            padding: 20px;
            border-radius: var(--border-radius-md);
            box-shadow: 0 2px 8px rgba(247,109,109,0.08);
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }
            form {
                flex-direction: column;
                align-items: stretch;
            }
            select, button {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
            .casi-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .casi-info img {
                margin-bottom: 15px;
            }
            .song-list li {
                padding: 15px;
            }
            .song-title {
                font-size: 1.15rem;
            }
            .play-button {
                margin-left: 0; /* Reset for stacking */
                width: 100%;
                text-align: center;
            }
            .song-details {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Web Nhạc Ấn Tượng</h1>
        <form method="get">
            <select name="casi_id" required>
                <option value="">-- Chọn ca sĩ --</option>
                <?php foreach ($ca_si as $row): ?>
                    <option value="<?= $row['idCS'] ?>" <?= (isset($_GET['casi_id']) && $_GET['casi_id'] == $row['idCS']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['HoTenCS']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Lọc Bài Hát</button>
        </form>

        <?php if (!empty($ten_casi)): ?>
            <div class="casi-info">
                <?php
                // Generate avatar using ui-avatars.com, prioritizing urlHinhCS if available and valid
                $img_src = !empty($urlHinhCS) && filter_var($urlHinhCS, FILTER_VALIDATE_URL) ? htmlspecialchars($urlHinhCS) : 'https://ui-avatars.com/api/?name=' . urlencode($ten_casi) . '&background=f76d6d&color=fff&size=120';
                ?>
                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($ten_casi) ?>">
                <div class="bio">
                    <h2><?= htmlspecialchars($ten_casi) ?></h2>
                    <div class="desc"><?= !empty($gioithieu) ? nl2br(htmlspecialchars(mb_substr(strip_tags($gioithieu), 0, 450) . (mb_strlen(strip_tags($gioithieu)) > 450 ? '...' : ''))) : 'Chưa có giới thiệu về ca sĩ này.' ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($ds_baihat) && $ds_baihat->num_rows > 0): ?>
            <div class="song-list">
                <h3>Danh sách bài hát của <?= htmlspecialchars($ten_casi) ?></h3>
                <ul>
                    <?php while ($baihat = $ds_baihat->fetch_assoc()): ?>
                        <li>
                            <div class="song-title">
                                <?= htmlspecialchars($baihat['TenBH']) ?>
                                <?php if (!empty($baihat['urlBH'])): ?>
                                    <a href="<?= htmlspecialchars($baihat['urlBH']) ?>" target="_blank" class="play-button">Nghe</a>
                                <?php endif; ?>
                            </div>
                            <div class="song-details">
                                <?php if (!empty($baihat['SoLanNghe'])): ?>
                                    <span class="listen-count">Lượt nghe: <?= number_format($baihat['SoLanNghe']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($baihat['NgayCapNhat']) && $baihat['NgayCapNhat'] != '0000-00-00'): ?>
                                    <span class="update-date">Cập nhật: <?= date('d/m/Y', strtotime($baihat['NgayCapNhat'])) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($baihat['LoiBH'])): ?>
                                    <span class="lyrics-toggle" onclick="toggleLyrics(this)">Xem lời bài hát</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($baihat['LoiBH'])): ?>
                                <div class="lyrics-content">
                                    <?= nl2br(htmlspecialchars(strip_tags($baihat['LoiBH']))) ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php elseif(isset($_GET['casi_id']) && !empty($ten_casi)): ?>
            <div class="no-songs-message">
                <p>Hiện không có bài hát nào được tìm thấy cho ca sĩ <strong><?= htmlspecialchars($ten_casi) ?></strong>.</p>
            </div>
        <?php elseif(isset($_GET['casi_id'])): ?>
             <div class="no-songs-message">
                <p>Không tìm thấy thông tin ca sĩ.</p>
            </div>
        <?php else: ?>
            <div class="no-songs-message">
                <p>Vui lòng chọn một ca sĩ từ danh sách để xem các bài hát của họ.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleLyrics(element) {
            const lyricsContent = element.closest('li').querySelector('.lyrics-content');
            if (lyricsContent) {
                lyricsContent.style.display = lyricsContent.style.display === 'block' ? 'none' : 'block';
                element.textContent = lyricsContent.style.display === 'block' ? 'Ẩn lời bài hát' : 'Xem lời bài hát';
            }
        }
    </script>
</body>
</html>