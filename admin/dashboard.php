<?php
session_start();
include '../includes/config.php';
include '../includes/restriction_admin.php';
include '../includes/session_utils.php';

// Strict session check for admin pages
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !checkSessionExpiration()) {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? 1;

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Base ─────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:       #da0416;
            --red-dark:  #a30512;
            --navy:      #0e2431;
            --navy-deep: #0e0f31;
            --card-bg:   #f9fafb;
            --border:    #e8e8e8;
            --text:      #0e2431;
            --muted:     #6b7a8d;
            --white:     #ffffff;
            --body-bg:   #e5ecfb;
        }

        html { font-size: 62.5%; scroll-behavior: smooth; }

        body {
            background-color: var(--body-bg);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            min-height: 100vh;
        }

        /* ── Layout wrapper ───────────────────────────── */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 0 0 4rem 0;
            overflow-x: hidden;
        }

        /* ── Toggle button ────────────────────────────── */
        .toggle-btn {
            background: none;
            color: var(--navy);
            border: none;
            font-size: 2.2rem;
            position: fixed;
            top: 1.4rem;
            left: 1.4rem;
            z-index: 1100;
            cursor: pointer;
            transition: color 0.2s;
        }
        .toggle-btn:hover { color: var(--red); }

        /* ── Cover Photo ──────────────────────────────── */
        .cover-section {
            position: relative;
            height: 32rem;
            background-size: cover;
            background-position: center;
            background-color: var(--navy);
        }

        .cover-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(14, 36, 49, 0.15) 0%,
                rgba(14, 36, 49, 0.65) 100%
            );
        }

        /* ── Profile Image ────────────────────────────── */
        .profile-img-wrapper {
            position: absolute;
            bottom: -6.5rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }

        .profile-img-wrapper img {
            width: 13rem;
            height: 13rem;
            border-radius: 50%;
            border: 0.4rem solid var(--white);
            object-fit: cover;
            box-shadow: 0 0.8rem 2.4rem rgba(14, 36, 49, 0.22);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .profile-img-wrapper img:hover {
            transform: scale(1.04);
            box-shadow: 0 1.2rem 3.2rem rgba(218, 4, 22, 0.22);
        }

        /* Red ring accent */
        .profile-img-wrapper::before {
            content: '';
            position: absolute;
            inset: -0.5rem;
            border-radius: 50%;
            border: 0.25rem solid var(--red);
            opacity: 0.6;
            animation: pulse-ring 2.5s ease-in-out infinite;
        }

        @keyframes pulse-ring {
            0%, 100% { transform: scale(1);   opacity: 0.6; }
            50%       { transform: scale(1.07); opacity: 0.2; }
        }

        /* ── Profile Identity ─────────────────────────── */
        .profile-identity {
            padding-top: 8rem;
            text-align: center;
            padding-bottom: 2.4rem;
            border-bottom: 0.1rem solid var(--border);
            background: var(--white);
        }

        .profile-identity h2 {
            font-size: 2.6rem;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -0.02em;
            margin-bottom: 0.4rem;
        }

        .profile-identity .caption {
            font-size: 1.4rem;
            color: var(--muted);
            font-style: italic;
            margin-bottom: 1.6rem;
        }

        /* Admin badge */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            color: #fff;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.4rem 1.2rem;
            border-radius: 2rem;
            box-shadow: 0 0.4rem 1.2rem rgba(218, 4, 22, 0.3);
        }

        /* ── Page Body ────────────────────────────────── */
        .page-body {
            max-width: 110rem;
            margin: 3rem auto 0;
            padding: 0 2.4rem;
        }

        /* ── Section heading ──────────────────────────── */
        .section-heading {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-heading span {
            color: var(--red);
        }

        .section-heading i {
            font-size: 1.8rem;
            color: var(--red);
        }

        /* ── Info Cards ───────────────────────────────── */
        .info-card {
            background: var(--white);
            border: 0.1rem solid var(--border);
            border-radius: 1.2rem;
            padding: 2.4rem;
            box-shadow: 0 0.2rem 1.2rem rgba(14, 36, 49, 0.06);
            height: 100%;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }

        .info-card:hover {
            box-shadow: 0 0.8rem 2.4rem rgba(14, 36, 49, 0.1);
            transform: translateY(-0.2rem);
        }

        /* Card top accent bar */
        .info-card::before {
            content: '';
            display: block;
            height: 0.35rem;
            border-radius: 0.35rem 0.35rem 0 0;
            background: linear-gradient(90deg, var(--red), var(--red-dark));
            margin: -2.4rem -2.4rem 2rem;
            border-radius: 1.2rem 1.2rem 0 0;
        }

        .info-card h5 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .info-card h5 i {
            color: var(--red);
            font-size: 1.5rem;
        }

        .info-card p {
            font-size: 1.4rem;
            color: var(--muted);
            line-height: 1.8;
        }

        /* ── Info List ────────────────────────────────── */
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-list li {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 0.1rem solid var(--border);
            font-size: 1.4rem;
        }

        .info-list li:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-list .info-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: rgba(218, 4, 22, 0.08);
            color: var(--red);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .info-list .info-label {
            font-weight: 600;
            color: var(--navy);
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: block;
            line-height: 1.2;
            margin-bottom: 0.2rem;
        }

        .info-list .info-value {
            color: var(--muted);
            font-size: 1.4rem;
        }

        /* ── Quick Stats Row ──────────────────────────── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
            gap: 1.6rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            border: 0.1rem solid var(--border);
            border-radius: 1.2rem;
            padding: 2rem 2.4rem;
            display: flex;
            align-items: center;
            gap: 1.6rem;
            box-shadow: 0 0.2rem 0.8rem rgba(14, 36, 49, 0.05);
            transition: box-shadow 0.25s, transform 0.25s;
        }

        .stat-card:hover {
            box-shadow: 0 0.6rem 1.8rem rgba(14, 36, 49, 0.1);
            transform: translateY(-0.2rem);
        }

        .stat-icon {
            width: 4.8rem;
            height: 4.8rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
            box-shadow: 0 0.4rem 1.2rem rgba(218, 4, 22, 0.3);
        }

        .stat-icon.navy {
            background: linear-gradient(135deg, var(--navy), var(--navy-deep));
            box-shadow: 0 0.4rem 1.2rem rgba(14, 36, 49, 0.3);
        }

        .stat-label {
            font-size: 1.2rem;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1;
        }

        /* ── Edit Button ──────────────────────────────── */
        .btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            color: #fff;
            font-size: 1.4rem;
            font-weight: 600;
            padding: 0.9rem 2rem;
            border-radius: 0.6rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 0.4rem 1.4rem rgba(218, 4, 22, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .btn-edit:hover {
            transform: translateY(-0.2rem);
            box-shadow: 0 0.8rem 2rem rgba(218, 4, 22, 0.45);
            color: #fff;
        }

        .btn-edit:active {
            transform: translateY(0);
        }

        /* ── Divider ──────────────────────────────────── */
        .section-divider {
            height: 0.1rem;
            background: var(--border);
            margin: 3rem 0;
        }

        /* ── Responsive ───────────────────────────────── */
        @media (max-width: 768px) {
            html { font-size: 56%; }

            .cover-section { height: 22rem; }

            .profile-identity { padding-top: 7.5rem; }

            .page-body { padding: 0 1.6rem; }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        /* ── Fade-in animation ────────────────────────── */
        .fade-up {
            opacity: 0;
            transform: translateY(2rem);
            animation: fadeUp 0.5s ease forwards;
        }
        .fade-up:nth-child(1) { animation-delay: 0.05s; }
        .fade-up:nth-child(2) { animation-delay: 0.15s; }
        .fade-up:nth-child(3) { animation-delay: 0.25s; }
        .fade-up:nth-child(4) { animation-delay: 0.35s; }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">

            <!-- ── Cover Photo ── -->
            <div class="cover-section"
                 style="background-image: url('<?= htmlspecialchars($user['cover_photo'] ?? '../assets/images/default-cover.jpg') ?>');">
                <div class="profile-img-wrapper">
                    <img src="<?= htmlspecialchars($user['profile_photo'] ?? '../assets/images/default-avatar.png') ?>"
                         alt="Profile Photo">
                </div>
            </div>

            <!-- ── Identity Block ── -->
            <div class="profile-identity">
                <h2><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'])) ?></h2>
                <p class="caption"><?= htmlspecialchars($user['caption'] ?? '') ?></p>
                <span class="admin-badge"><i class="fas fa-shield-halved"></i> Administrator</span>
            </div>

            <!-- ── Page Body ── -->
            <div class="page-body">

                <!-- Quick Stats -->
                <div class="stats-row" style="margin-top: 3rem;">
                    <div class="stat-card fade-up">
                        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="stat-label">Email</div>
                            <div class="stat-value" style="font-size:1.3rem; word-break:break-all;">
                                <?= htmlspecialchars($user['email'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card fade-up">
                        <div class="stat-icon navy"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="stat-label">Phone</div>
                            <div class="stat-value" style="font-size:1.6rem;">
                                <?= htmlspecialchars($user['phone_number'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card fade-up">
                        <div class="stat-icon"><i class="fas fa-cake-candles"></i></div>
                        <div>
                            <div class="stat-label">Age</div>
                            <div class="stat-value"><?= htmlspecialchars($user['age'] ?? '—') ?></div>
                        </div>
                    </div>
                    <div class="stat-card fade-up">
                        <div class="stat-icon navy"><i class="fas fa-location-dot"></i></div>
                        <div>
                            <div class="stat-label">City</div>
                            <div class="stat-value" style="font-size:1.5rem;">
                                <?= htmlspecialchars($user['city'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- About + Personal Info -->
                <div class="row g-4 mb-4">
                    <!-- About -->
                    <div class="col-lg-6">
                        <div class="info-card fade-up">
                            <h5><i class="fas fa-user"></i> About Me</h5>
                            <p><?= nl2br(htmlspecialchars($user['bio'] ?? 'No bio provided yet.')) ?></p>
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="col-lg-6">
                        <div class="info-card fade-up">
                            <h5><i class="fas fa-id-card"></i> Personal Info</h5>
                            <ul class="info-list">
                                <li>
                                    <div class="info-icon"><i class="fas fa-map-pin"></i></div>
                                    <div>
                                        <span class="info-label">Address</span>
                                        <span class="info-value">
                                            <?= htmlspecialchars($user['street_address'] . ', ' . $user['city']) ?>
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="info-icon"><i class="fas fa-calendar"></i></div>
                                    <div>
                                        <span class="info-label">Date of Birth</span>
                                        <span class="info-value">
                                            <?= $user['birth_date'] ? date("F j, Y", strtotime($user['birth_date'])) : 'N/A' ?>
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="info-icon"><i class="fas fa-earth-asia"></i></div>
                                    <div>
                                        <span class="info-label">Place of Birth</span>
                                        <span class="info-value">
                                            <?= htmlspecialchars($user['birth_place'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="info-icon"><i class="fas fa-flag"></i></div>
                                    <div>
                                        <span class="info-label">Nationality</span>
                                        <span class="info-value">
                                            <?= htmlspecialchars($user['nationality'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="info-icon"><i class="fas fa-hands-praying"></i></div>
                                    <div>
                                        <span class="info-label">Religion</span>
                                        <span class="info-value">
                                            <?= htmlspecialchars($user['religion'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Button -->
                <div class="text-center mt-2 mb-4">
                    <a href="edit_profile.php" class="btn-edit">
                        <i class="fas fa-pen"></i> Edit Profile
                    </a>
                </div>

            </div><!-- /page-body -->
        </div><!-- /main-content -->
    </div><!-- /wrapper -->

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>