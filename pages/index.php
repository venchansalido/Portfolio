<?php
session_start(); // Start session to access session variables

// Include configuration and session utilities
include '../includes/config.php';
include '../includes/session_utils.php'; // Add this line

// Check for admin session (if needed)
$isAdmin = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Check session expiration for logged-in users
if (isset($_SESSION['user_id']) && !checkSessionExpiration()) {
    // Session expired - clear session and redirect
    session_unset();
    session_destroy();
    if ($current_page != 'index.php') {
        header('Location: index.php');
        exit;
    }
}

// Get the current page filename (e.g., "index.php", "about.php")
$current_page = basename($_SERVER['PHP_SELF']);


// ADMIN DATA FETCH FOR ABOUT PAGE
$adminId = 1;


// HOME PAGE CONTENT
$query = "SELECT * FROM home ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$homeData = $result->fetch_assoc();

// Fetch hero images
$imageQuery = "SELECT image_path FROM home_images";
$imageResult = $conn->query($imageQuery);

$imagePaths = [];
while ($row = $imageResult->fetch_assoc()) {
    $imagePaths[] = $row['image_path'];
}



// GALLERY FOR HOME PAGE
$galleryQuery = "SELECT image_path FROM user_gallery WHERE user_id = ?";
$galleryStmt = $conn->prepare($galleryQuery);
$galleryStmt->bind_param("i", $adminId);
$galleryStmt->execute();
$galleryResult = $galleryStmt->get_result();
$galleryImages = [];



// ABOUT PAGE
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


// SKILL SECTION
$skillsStmt = $conn->prepare("SELECT * FROM user_skills WHERE user_id = ?");
$skillsStmt->bind_param("i", $adminId);
$skillsStmt->execute();
$skillsResult = $skillsStmt->get_result();
$skills = $skillsResult->fetch_all(MYSQLI_ASSOC);

// EDU SECTION
$eduStmt = $conn->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY FIELD(level, 'Primary', 'Secondary', 'Tertiary')");
$eduStmt->bind_param("i", $adminId);
$eduStmt->execute();
$eduResult = $eduStmt->get_result();
$education = $eduResult->fetch_all(MYSQLI_ASSOC);

// GALLERY FOR ABOUT PAGE

while ($row = $galleryResult->fetch_assoc()) {
    $galleryImages[] = $row['image_path'];
}

// Default image if none found
if (empty($galleryImages)) {
    $galleryImages[] = '../assets/images/v1.jpg';
}




// PROJECTS AND TIMELINE
$timelineItems = [];
$query = "SELECT * FROM timeline_items ORDER BY created_at ASC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $timelineItems[] = $row;
    }
}

$projects = [];
$query = "SELECT p.*, 
                 (SELECT COUNT(*) FROM project_gallery WHERE project_id = p.id) as gallery_count
          FROM projects p 
          WHERE p.is_hidden = 0
          ORDER BY p.created_at DESC";
$result = $conn->query($query);
if ($result) {
    while ($project = $result->fetch_assoc()) {
        // Get gallery images for each project
        $stmt = $conn->prepare("SELECT image_path FROM project_gallery WHERE project_id = ?");
        $stmt->bind_param("i", $project['id']);
        $stmt->execute();
        $galleryResult = $stmt->get_result();
        $project['gallery_images'] = [];
        while ($row = $galleryResult->fetch_assoc()) {
            $project['gallery_images'][] = $row['image_path'];
        }
        $projects[] = $project;
    }
}



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Pragati sable, portfolio, Pragati, full stack dev, personal portfolio lifecodes, portfolio design, portfolio website, personal portfolio" />
    <meta name="description" content="Welcome to Pragati's Portfolio. Full-Stack Web Developer and Android App Developer" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://kit.fontawesome.com/95a2002ecf.js" crossorigin="anonymous"></script>
    <!-- Favicon -->
    <link id='favicon' rel="shortcut icon" href="../assets/images/favicon.jpg" type="image/x-png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
      <!-- SweetAlert2 Library -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <!-- Custom CSS -->
<!-- Custom CSS with cache busting -->
<link rel="stylesheet" href="../assets/css/pages.css?v=<?= filemtime('../assets/css/pages.css') ?>">
<link rel="stylesheet" href="../assets/css/modals.css?v=<?= filemtime('../assets/css/modals.css') ?>">

    <title>Portfolio | Venard</title>
    <style>

    </style>
</head>
<body>
<header>
<?php include('../includes/header.php'); ?>

</header>
<!-- navbar ends -->


<!-- hero section starts -->
<section class="home" id="home">
    <div id="particles-js"></div>

    <div class="content">
    <h2><?= $homeData['greetings'] ?> <span></span></h2>
    <p>I am into <span class="typing-text" style="font-weight: bold; color:rgb(255, 0, 0);" data-words="<?= htmlspecialchars($homeData['typing_text']) ?>"></span></p>



  <a href="#about" class="btn"><span>About Me</span>
        <i class="fas fa-arrow-circle-right"></i>
    </a>
    <div class="socials">
        <ul class="social-icons">
            <li><a class="facebook" aria-label="Facebook" href="<?= $homeData['facebook_link'] ?>" target="_blank"><i class="fab fa-facebook"></i></a></li>
            <li><a class="instagram" aria-label="Instagram" href="<?= $homeData['instagram_link'] ?>" target="_blank"><i class="fab fa-instagram"></i></a></li>
            <li><a class="dev" aria-label="LinkedIn" href="<?= $homeData['youtube_link'] ?>" target="_blank"><i class="fa-brands fa-linkedin"></i></a></li>

        </ul>
    </div>
</div>
    
    <!-- Image Section -->
<div class="image">
  <img draggable="false" class="tilt" id="hero-displayed-image" src="../assets/images/vv.jpg" alt="Hero Image">
  <div class="gallery-controls">
    <button id="hero-prev-btn" class="control-btn"><i class="fas fa-step-backward"></i></button>
    <button id="hero-play-pause" class="control-btn"><i class="fas fa-pause"></i></button>
    <button id="hero-next-btn" class="control-btn"><i class="fas fa-step-forward"></i></button>
  </div>
</div>
</section>



<section class="about" id="about">
    <div id="particles-js"></div>
    <br><br><br><br><br><br><br><br>
    <h2 class="heading"><i class="fas fa-user-alt"></i> About <span>Me</span></h2>
    
    <div class="row">
        <!-- Image Section -->
<div class="image">
    <img draggable="false" class="tilt" id="displayed-image" src="<?= htmlspecialchars($galleryImages[0]) ?>" alt="Profile Picture">
    <div class="gallery-controls">
        <button id="prev-btn" class="control-btn"><i class="fas fa-step-backward"></i></button>
        <button id="about-play-pause" class="control-btn"><i class="fas fa-pause"></i></button>
        <button id="next-btn" class="control-btn"><i class="fas fa-step-forward"></i></button>
    </div>
</div>

        <!-- Content Section -->
        <div class="content">
            <h3><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'])) ?></h3>
            <br>
            <span style="font-style: italic;" class="tag"><?= htmlspecialchars($user['caption'] ?? 'No caption provided.') ?></span><br><br>

            <p>
                <?= nl2br(htmlspecialchars($user['bio'] ?? "No bio provided yet.")) ?>
            </p>

            <br><br>

            <div class="box-container">
                <div class="box">
                    <p><span>Address:</span> <?= htmlspecialchars($user['street_address'] . ', ' . $user['city']) ?></p>
                    <p><span>Phone:</span> <?= htmlspecialchars($user['phone_number'] ?? 'N/A') ?></p>
                    <p><span>Age:</span> <?= htmlspecialchars($user['age'] ?? 'N/A') ?></p>
                    <p><span>Date of Birth:</span> <?= $user['birth_date'] ? date("F j, Y", strtotime($user['birth_date'])) : 'N/A' ?></p>
                    <p><span>Place of Birth:</span> <?= htmlspecialchars($user['birth_place'] ?? 'N/A') ?></p>
                    <p><span>Nationality:</span> <?= htmlspecialchars($user['nationality'] ?? 'N/A') ?></p>
                    <p><span>Religion:</span> <?= htmlspecialchars($user['religion'] ?? 'N/A') ?></p>
                    <p><span>Email:</span> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                </div>
            </div>
            
<!-- Replace the resume button section in index.php with this -->
<div class="resumebtn">
    <?php if (!empty($user['resume_path'])): ?>
        <a href="<?= htmlspecialchars($user['resume_path']) ?>" 
           class="btn" 
           target="_blank">
            <span>View Resume</span>
            <i class="fas fa-chevron-right"></i>
        </a>
    <?php else: ?>
        <a href="#" class="btn disabled" style="opacity: 0.6; cursor: not-allowed;">
            <span>Resume Not Available</span>
            <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>
</div>
        </div>
    </div>
</section><br><br><br><br>

<section class="skills" id="skills">
    <h2 class="heading"><i class="fas fa-laptop-code"></i> Skills & <span>Interests</span></h2>
    <br><br><div class="container">
          <div class="row" id="skillsContainer">
          <?php foreach ($skills as $skill): ?>
              <div class="bar">
                  <div class="info">
                      <img src="<?= htmlspecialchars($skill['icon_path']) ?>" height="50" width="50" alt="<?= htmlspecialchars($skill['skill_name']) ?>">
                      <span><?= htmlspecialchars($skill['skill_name']) ?></span>
                  </div>
              </div>
          <?php endforeach; ?>
          </div>
    </div>
</section>

<section class="education" id="education">
    <br><br><br><br><h1 class="heading"><i class="fas fa-graduation-cap"></i> My <span>Education</span></h1>
    <p class="qoute">Education is not the learning of facts, but the training of the mind to think.</p>
    <div class="box-container">
    <?php foreach ($education as $edu): ?>
        <div class="box">
            <div class="image">
                <img draggable="false" src="<?= htmlspecialchars($edu['image_path']) ?>" alt="">
            </div>
            <div class="content">
                <h3><?= htmlspecialchars($edu['level']) ?></h3>
                <p><?= htmlspecialchars($edu['school_name']) ?></p>
                <?php if (!empty($edu['course'])): ?><br><p><?= htmlspecialchars($edu['course']) ?></p><?php endif; ?>
                <br><p>Address: <?= htmlspecialchars($edu['address']) ?></p>
                <h4><?= htmlspecialchars($edu['start_year']) ?> - <?= htmlspecialchars($edu['end_year']) ?></h4>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</section><br><br>


<section class="work " id="work">
    <br><br><br><br><br><br><br><br>
    <h2 class="heading"><i class="fas fa-laptop-code"></i> Projects &<span> Stuffs</span></h2>
    <br><br><br><br><br><br>
    
    <div class="box-container">
        <?php foreach ($projects as $project): ?>
            <div class="box tilt">
                <?php if ($project['thumbnail_path']): ?>
                    <img draggable="false" src="<?= htmlspecialchars($project['thumbnail_path']) ?>" alt="<?= htmlspecialchars($project['title']) ?>" />
                <?php else: ?>
                    <div class="no-image-placeholder" style="height: 200px; background: #333; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="font-size: 50px; color: #666;"></i>
                    </div>
                <?php endif; ?>
                <div class="content">
                    <div class="tag">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                    </div>
                    <div class="desc">
                        <p><?= htmlspecialchars($project['description']) ?></p>
                        <div class="btns">
                            <?php if (!empty($project['gallery_images'])): ?>
                                <button class="btn view-sample" data-project-id="<?= $project['id'] ?>" 
                                        data-gallery='<?= json_encode($project['gallery_images']) ?>'>
                                    <i class="fas fa-eye"></i> View Sample
                                </button>
                            <?php endif; ?>
                            <?php if ($project['url']): ?>
                                <a href="<?= htmlspecialchars($project['url']) ?>" class="btn" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> Visit Site
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- work project section ends -->

<!-- experience section starts -->
<br><br><br><br><br><br><br><br><section class="experience" id="experience">

  <h2 class="heading"><i class="fas fa-briefcase"></i> My Timeline </h2><br><br><br><br>

  <div class="timeline">
    <?php foreach ($timelineItems as $item): ?>
      <div class="container <?= $item['position'] ?>">
        <div class="content">
          <div class="tag">
            <h2><?= htmlspecialchars($item['title']) ?> (<?= htmlspecialchars($item['status']) ?>)</h2>
          </div>
          <div class="desc">
              <p><?= htmlspecialchars($item['description']) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>



<?php include('../includes/footer.php'); ?>


<!-- footer section ends -->


<!-- ==== ALL MAJOR JAVASCRIPT CDNS STARTS ==== -->
<!-- jquery cdn -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- typed.js cdn -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.5/typed.min.js" integrity="sha512-1KbKusm/hAtkX5FScVR5G36wodIMnVd/aP04af06iyQTkD17szAMGNmxfNH+tEuFp3Og/P5G32L1qEC47CZbUQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- particle.js links -->
<script src="../assets/js/particles.min.js?v=<?= filemtime('../assets/js/particles.min.js') ?>"></script>
<script src="../assets/js/app.js?v=<?= filemtime('../assets/js/app.js') ?>"></script>

<!-- vanilla tilt.js links -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js" integrity="sha512-SttpKhJqONuBVxbRcuH0wezjuX+BoFoli0yPsnrAADcHsQMW8rkR84ItFHGIkPvhnlRnE2FaifDOUw+EltbuHg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- scroll reveal anim -->
<script src="https://unpkg.com/scrollreveal"></script>

<script
      type="text/javascript"
      src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"
    ></script>

<!-- ==== ALL MAJOR JAVASCRIPT CDNS ENDS ==== -->

<script src="../assets/js/script.js?v=<?= filemtime('../assets/js/script.js') ?>"></script>




<script>

// HOME PAGE GALLERY WITH AUTO-SLIDE AND PLAY/PAUSE
const heroImages = <?php echo json_encode($imagePaths); ?>;

let currentHeroImageIndex = 0;
let heroAutoSlideInterval;
let heroIsPlaying = true;
const heroDisplayedImage = document.getElementById('hero-displayed-image');
const heroPrevButton = document.getElementById('hero-prev-btn');
const heroNextButton = document.getElementById('hero-next-btn');
const heroPlayPauseButton = document.getElementById('hero-play-pause');

// Function to update the displayed hero image
function updateHeroImage() {
    if (heroImages.length > 0) {
        heroDisplayedImage.src = heroImages[currentHeroImageIndex];
    }
}

// Function to go to next hero image
function nextHeroImage() {
    currentHeroImageIndex = (currentHeroImageIndex + 1) % heroImages.length;
    updateHeroImage();
}

// Function to go to previous hero image
function prevHeroImage() {
    currentHeroImageIndex = (currentHeroImageIndex - 1 + heroImages.length) % heroImages.length;
    updateHeroImage();
}

// Start auto-slide for hero images
function startHeroAutoSlide() {
    if (heroImages.length > 1) {
        heroAutoSlideInterval = setInterval(nextHeroImage, 3000);
        heroIsPlaying = true;
        heroPlayPauseButton.innerHTML = '<i class="fas fa-pause"></i>';
    }
}

// Stop auto-slide for hero images
function stopHeroAutoSlide() {
    clearInterval(heroAutoSlideInterval);
    heroIsPlaying = false;
    heroPlayPauseButton.innerHTML = '<i class="fas fa-play"></i>';
}

// Toggle play/pause
function toggleHeroPlayPause() {
    if (heroIsPlaying) {
        stopHeroAutoSlide();
    } else {
        startHeroAutoSlide();
    }
}

// Handle next button click
heroNextButton.addEventListener('click', () => {
    nextHeroImage();
    if (heroIsPlaying) {
        clearInterval(heroAutoSlideInterval);
        startHeroAutoSlide();
    }
});

// Handle previous button click
heroPrevButton.addEventListener('click', () => {
    prevHeroImage();
    if (heroIsPlaying) {
        clearInterval(heroAutoSlideInterval);
        startHeroAutoSlide();
    }
});

// Handle play/pause button
heroPlayPauseButton.addEventListener('click', toggleHeroPlayPause);

// Load the first image and start auto-slide
updateHeroImage();
if (heroImages.length > 1) {
    startHeroAutoSlide();
}

// ========================================
// ABOUT PAGE GALLERY WITH AUTO-SLIDE AND PLAY/PAUSE
// ========================================
const images = <?php echo json_encode($galleryImages); ?>;
let currentImageIndex = 0;
let aboutAutoSlideInterval;
let aboutIsPlaying = true;

const displayedImage = document.getElementById('displayed-image');
const prevButton = document.getElementById('prev-btn');
const nextButton = document.getElementById('next-btn');
const aboutPlayPauseButton = document.getElementById('about-play-pause');

// Function to update about gallery image
function updateAboutImage() {
    if (images.length > 0) {
        displayedImage.src = images[currentImageIndex];
    }
}

// Function to go to next about image
function nextAboutImage() {
    currentImageIndex = (currentImageIndex + 1) % images.length;
    updateAboutImage();
}

// Function to go to previous about image
function prevAboutImage() {
    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
    updateAboutImage();
}

// Start auto-slide for about images
function startAboutAutoSlide() {
    if (images.length > 1) {
        aboutAutoSlideInterval = setInterval(nextAboutImage, 3000);
        aboutIsPlaying = true;
        aboutPlayPauseButton.innerHTML = '<i class="fas fa-pause"></i>';
    }
}

// Stop auto-slide for about images
function stopAboutAutoSlide() {
    clearInterval(aboutAutoSlideInterval);
    aboutIsPlaying = false;
    aboutPlayPauseButton.innerHTML = '<i class="fas fa-play"></i>';
}

// Toggle play/pause
function toggleAboutPlayPause() {
    if (aboutIsPlaying) {
        stopAboutAutoSlide();
    } else {
        startAboutAutoSlide();
    }
}

// Handle next button click
nextButton.addEventListener('click', () => {
    nextAboutImage();
    if (aboutIsPlaying) {
        clearInterval(aboutAutoSlideInterval);
        startAboutAutoSlide();
    }
});

// Handle previous button click
prevButton.addEventListener('click', () => {
    prevAboutImage();
    if (aboutIsPlaying) {
        clearInterval(aboutAutoSlideInterval);
        startAboutAutoSlide();
    }
});

// Handle play/pause button
aboutPlayPauseButton.addEventListener('click', toggleAboutPlayPause);

// Initialize about gallery
updateAboutImage();
if (images.length > 1) {
    startAboutAutoSlide();
}
</script>




<!-- Add Bootstrap JS before your custom script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Gallery Lightbox Modal -->
<div id="galleryModal" class="vg-overlay" role="dialog" aria-modal="true" aria-label="Project Gallery">
  <div class="vg-backdrop" id="vgBackdrop"></div>

  <div class="vg-box">

    <!-- Header bar -->
    <div class="vg-header">
      <span class="vg-title" id="vgTitle">Project Gallery</span>
      <div class="vg-header-right">
        <span class="vg-counter" id="vgCounter">1 / 1</span>
        <button class="vg-close" id="vgClose" aria-label="Close gallery">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Image stage -->
    <div class="vg-stage">
      <img id="vgImage" src="" alt="Project screenshot" class="vg-img" draggable="false" />
      <!-- subtle loader ring shown while image loads -->
      <div class="vg-loader" id="vgLoader"></div>
    </div>

    <!-- Controls bar (below image — same vibe as your carousel) -->
    <div class="vg-controls">
      <button class="vg-btn" id="vgPrev" aria-label="Previous image">
        <i class="fas fa-step-backward"></i>
      </button>

      <!-- Dot indicators -->
      <div class="vg-dots" id="vgDots"></div>

      <button class="vg-btn" id="vgNext" aria-label="Next image">
        <i class="fas fa-step-forward"></i>
      </button>
    </div>

  </div>
</div>

<style>
/* ── Overlay ─────────────────────────────────────────── */
.vg-overlay {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}
.vg-overlay.vg-open {
  display: flex;
}

/* Dark backdrop */
.vg-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(5, 8, 30, 0.88);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

/* ── Modal box ───────────────────────────────────────── */
.vg-box {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 780px;
  background: #0e0f31;
  border-radius: 1.2rem;
  overflow: hidden;
  box-shadow: 0 30px 80px rgba(0,0,0,0.7);
  display: flex;
  flex-direction: column;
  animation: vgSlideIn 0.3s cubic-bezier(0.34, 1.4, 0.64, 1) both;
}
@keyframes vgSlideIn {
  from { opacity: 0; transform: translateY(40px) scale(0.96); }
  to   { opacity: 1; transform: translateY(0)   scale(1);     }
}

/* ── Header ──────────────────────────────────────────── */
.vg-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.4rem 2rem;
  background: rgba(255,255,255,0.04);
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
.vg-title {
  font-family: 'Poppins', sans-serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: 0.04em;
}
.vg-header-right {
  display: flex;
  align-items: center;
  gap: 1.4rem;
}
.vg-counter {
  font-family: 'Poppins', sans-serif;
  font-size: 1.2rem;
  font-weight: 600;
  color: rgba(255,255,255,0.45);
  letter-spacing: 0.05em;
}
.vg-close {
  background: rgba(218, 4, 22, 0.15);
  border: none;
  color: #da0416;
  width: 3.4rem;
  height: 3.4rem;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, transform 0.2s;
}
.vg-close:hover {
  background: #da0416;
  color: #fff;
  transform: rotate(90deg);
}

/* ── Image stage ─────────────────────────────────────── */
.vg-stage {
  position: relative;
  width: 100%;
  background: #07081e;
  min-height: 34rem;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.vg-img {
  max-width: 100%;
  max-height: 60vh;
  width: 100%;
  height: auto;
  object-fit: contain;
  display: block;
  transition: opacity 0.25s ease;
}
.vg-img.vg-fading {
  opacity: 0;
}

/* Loader ring */
.vg-loader {
  position: absolute;
  width: 3.8rem;
  height: 3.8rem;
  border: 3px solid rgba(218, 4, 22, 0.2);
  border-top-color: #da0416;
  border-radius: 50%;
  animation: vgSpin 0.7s linear infinite;
  display: none;
}
.vg-loader.vg-loading {
  display: block;
}
@keyframes vgSpin {
  to { transform: rotate(360deg); }
}

/* ── Controls bar ────────────────────────────────────── */
.vg-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.8rem;
  padding: 1.6rem 2rem;
  background: rgba(255,255,255,0.03);
  border-top: 1px solid rgba(255,255,255,0.07);
}

/* Prev / Next buttons — same style as your carousel .control-btn */
.vg-btn {
  background: linear-gradient(145deg, #da0416, #a30512);
  color: #fff;
  border: none;
  width: 5rem;
  height: 5rem;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  box-shadow: 0 4px 15px rgba(218, 4, 22, 0.35);
  transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
}
.vg-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0.15);
  border-radius: 50%;
  transform: scale(0);
  transition: transform 0.25s;
}
.vg-btn:hover::before { transform: scale(1); }
.vg-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 22px rgba(218, 4, 22, 0.55);
}
.vg-btn:active { transform: translateY(-1px); }
.vg-btn:disabled {
  opacity: 0.35;
  cursor: default;
  transform: none;
  box-shadow: none;
}

/* ── Dot indicators ──────────────────────────────────── */
.vg-dots {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 28rem;
}
.vg-dot {
  width: 0.8rem;
  height: 0.8rem;
  border-radius: 50%;
  background: rgba(255,255,255,0.25);
  border: none;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
  padding: 0;
  flex-shrink: 0;
}
.vg-dot.vg-dot-active {
  background: #da0416;
  transform: scale(1.35);
  box-shadow: 0 0 8px rgba(218, 4, 22, 0.7);
}
.vg-dot:hover:not(.vg-dot-active) {
  background: rgba(255,255,255,0.55);
}

/* ── Keyboard hint ───────────────────────────────────── */
.vg-hint {
  text-align: center;
  font-size: 1.1rem;
  color: rgba(255,255,255,0.2);
  font-family: 'Poppins', sans-serif;
  padding: 0 2rem 1.2rem;
  letter-spacing: 0.03em;
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 600px) {
  .vg-box { border-radius: 0.8rem; }
  .vg-stage { min-height: 22rem; }
  .vg-img { max-height: 45vh; }
  .vg-btn { width: 4.2rem; height: 4.2rem; font-size: 1.5rem; }
  .vg-title { font-size: 1.3rem; }
  .vg-header { padding: 1.2rem 1.5rem; }
  .vg-controls { gap: 1.2rem; padding: 1.4rem 1.5rem; }
}
</style>

<script>
(function () {
  // ── State ────────────────────────────────────────────────
  let gallery  = [];
  let current  = 0;

  // ── Elements ─────────────────────────────────────────────
  const overlay  = document.getElementById('galleryModal');
  const backdrop = document.getElementById('vgBackdrop');
  const imgEl    = document.getElementById('vgImage');
  const loader   = document.getElementById('vgLoader');
  const counter  = document.getElementById('vgCounter');
  const dotsWrap = document.getElementById('vgDots');
  const btnPrev  = document.getElementById('vgPrev');
  const btnNext  = document.getElementById('vgNext');
  const btnClose = document.getElementById('vgClose');

  // ── Helpers ──────────────────────────────────────────────
  function buildDots() {
    dotsWrap.innerHTML = '';
    // Only show dots when <= 12 images (beyond that gets cluttered)
    if (gallery.length <= 12) {
      gallery.forEach((_, i) => {
        const d = document.createElement('button');
        d.className = 'vg-dot' + (i === current ? ' vg-dot-active' : '');
        d.setAttribute('aria-label', `Go to image ${i + 1}`);
        d.addEventListener('click', () => goTo(i));
        dotsWrap.appendChild(d);
      });
    }
  }

  function updateDots() {
    const dots = dotsWrap.querySelectorAll('.vg-dot');
    dots.forEach((d, i) => d.classList.toggle('vg-dot-active', i === current));
  }

  function updateCounter() {
    counter.textContent = `${current + 1} / ${gallery.length}`;
  }

  function updateButtons() {
    // Always loop — no disabled state needed, but keep for single-image edge case
    btnPrev.disabled = gallery.length <= 1;
    btnNext.disabled = gallery.length <= 1;
  }

  function loadImage(src) {
    imgEl.classList.add('vg-fading');
    loader.classList.add('vg-loading');

    const tmp = new Image();
    tmp.onload = () => {
      imgEl.src = src;
      imgEl.classList.remove('vg-fading');
      loader.classList.remove('vg-loading');
    };
    tmp.onerror = () => {
      imgEl.src = src; // show broken img rather than hang
      imgEl.classList.remove('vg-fading');
      loader.classList.remove('vg-loading');
    };
    tmp.src = src;
  }

  function goTo(index) {
    current = (index + gallery.length) % gallery.length;
    loadImage(gallery[current]);
    updateDots();
    updateCounter();
  }

  // ── Open / Close ─────────────────────────────────────────
  function openModal(images, title) {
    gallery = images;
    current = 0;

    document.getElementById('vgTitle').textContent = title || 'Project Gallery';
    buildDots();
    updateCounter();
    updateButtons();
    loadImage(gallery[0]);

    overlay.classList.add('vg-open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    overlay.classList.remove('vg-open');
    document.body.style.overflow = '';
  }

  // ── Event listeners ──────────────────────────────────────
  btnClose.addEventListener('click', closeModal);
  backdrop.addEventListener('click', closeModal);

  btnPrev.addEventListener('click', () => goTo(current - 1));
  btnNext.addEventListener('click', () => goTo(current + 1));

  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (!overlay.classList.contains('vg-open')) return;
    if (e.key === 'ArrowLeft')  goTo(current - 1);
    if (e.key === 'ArrowRight') goTo(current + 1);
    if (e.key === 'Escape')     closeModal();
  });

  // ── Wire up "View Sample" buttons ────────────────────────
  // Works with the existing PHP-generated buttons that have
  // data-gallery='[...]' and an optional data-title attribute.
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.view-sample').forEach(btn => {
      btn.addEventListener('click', function () {
        try {
          const images = JSON.parse(this.getAttribute('data-gallery'));
          const title  = this.getAttribute('data-title') || 'Project Gallery';
          if (images && images.length) openModal(images, title);
        } catch (err) {
          console.error('Gallery error:', err);
        }
      });
    });
  });

})();
</script>





</body>
</html>
