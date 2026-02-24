<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Venard Header</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/header.css">
</head>
<body>
  <header class="venard-header">
    <div class="logo-section">
      <a href="index.php">
        <img src="../assets/images/venard.jpg" alt="Logo" class="venard-logo-img">
      </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="venard-nav">
      <div class="nav-overlay" id="navOverlay"></div>
      <ul id="venard-nav-menu">
        <!-- ✅ FIX: Removed hardcoded class="active" from Home link.
             The scroll spy will set active-tab dynamically on page load. -->
        <li><a href="#home">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#skills">Skills</a></li>
        <li><a href="#education">Education</a></li>
        <li><a href="#work">Stuffs</a></li>
        <li><a href="#experience">Timeline</a></li>

        <!-- Mobile Only Dashboard -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
          <li><a href="../admin/dashboard.php" class="dashboard-link-mobile">Dashboard</a></li>
        <?php endif; ?>

        <!-- Mobile Only Logout Button -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <li>
            <form id="logoutFormMobile" action="../user/logout.php" method="POST" class="mobile-only">
              <button style="font-weight: bold;" type="submit" class="logout-btn">Logout</button>
            </form>
          </li>
        <?php endif; ?>
      </ul>
    </nav>

    <!-- User Dropdown for Desktop -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="user-dropdown" id="userDropdown">
      <i class="fas fa-user-circle user-icon" id="userIcon"></i>
      <div class="dropdown-content" id="userDropdownContent">
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="../admin/dashboard.php">Dashboard</a>
        <?php endif; ?>
        <form id="logoutForm" action="../user/logout.php" method="POST">
          <button type="submit">Logout</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Hamburger -->
    <div class="venard-hamburger" id="venard-hamburger">&#9776;</div>
  </header>

  <script>
    // ─── Hamburger / Sidebar ───────────────────────────────────────
    const hamburger = document.getElementById('venard-hamburger');
    const navMenu   = document.getElementById('venard-nav-menu');
    const navOverlay = document.getElementById('navOverlay');

    hamburger.addEventListener('click', (e) => {
      e.stopPropagation();
      navMenu.classList.toggle('active');
      navOverlay.classList.toggle('active');
      hamburger.classList.toggle('active');
    });

    navOverlay.addEventListener('click', () => {
      navMenu.classList.remove('active');
      navOverlay.classList.remove('active');
      hamburger.classList.remove('active');
    });

    document.addEventListener('click', (e) => {
      if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
        navMenu.classList.remove('active');
        navOverlay.classList.remove('active');
        hamburger.classList.remove('active');
      }
    });

    // ─── User Dropdown ─────────────────────────────────────────────
    const userIcon = document.getElementById('userIcon');
    const dropdownContent = document.getElementById('userDropdownContent');

    if (userIcon) {
      userIcon.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownContent.classList.toggle('show-dropdown');
      });
      document.addEventListener('click', (e) => {
        if (dropdownContent && dropdownContent.classList.contains('show-dropdown') &&
            !dropdownContent.contains(e.target) && e.target !== userIcon) {
          dropdownContent.classList.remove('show-dropdown');
        }
      });
    }

    // ─── Logout Confirmation ───────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
      const logoutForms = document.querySelectorAll('#logoutForm, #logoutFormMobile');
      logoutForms.forEach(form => {
        if (form) {
          form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
              title: 'Are you sure?',
              text: 'You will be logged out of your account.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, logout!'
            }).then((result) => {
              if (result.isConfirmed) {
                fetch('../user/logout.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                  body: new URLSearchParams(new FormData(form))
                })
                .then(r => r.json())
                .then(data => {
                  if (data.success) {
                    Swal.fire({
                      icon: 'success',
                      title: 'Logged out!',
                      text: 'You have been successfully logged out.',
                      timer: 2000,
                      showConfirmButton: false
                    }).then(() => { window.location.reload(); });
                  }
                })
                .catch(error => {
                  Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong during logout.' });
                });
              }
            });
          });
        }
      });
    });

    // ─── Scroll Spy + Smooth Scroll ───────────────────────────────
    // ✅ FIX: Uses a SINGLE active class ('active-tab') everywhere.
    //         Clears ALL nav links before setting the new one,
    //         so the static Home link can never stay lit while scrolling elsewhere.
    document.addEventListener('DOMContentLoaded', function () {
      const sections  = document.querySelectorAll('section[id]');
      const navLinks  = document.querySelectorAll('.venard-nav ul li a');

      function clearActive() {
        navLinks.forEach(link => {
          link.classList.remove('active');      // remove old static class too
          link.classList.remove('active-tab');  // remove scroll-spy class
        });
      }

      function updateActiveNav() {
        let currentSection = '';
        sections.forEach(section => {
          if (window.scrollY >= section.offsetTop - 200) {
            currentSection = section.getAttribute('id');
          }
        });

        clearActive();

        if (currentSection) {
          const target = document.querySelector(`.venard-nav ul li a[href="#${currentSection}"]`);
          if (target) target.classList.add('active-tab');
        }
      }

      // Run immediately so Home gets active-tab on load instead of the removed static class
      updateActiveNav();
      window.addEventListener('scroll', updateActiveNav);

      // Smooth scrolling
      navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
          const targetId = this.getAttribute('href');
          if (targetId && targetId.startsWith('#')) {
            e.preventDefault();
            const targetSection = document.querySelector(targetId);
            if (targetSection) {
              window.scrollTo({ top: targetSection.offsetTop - 80, behavior: 'smooth' });
              history.pushState(null, null, targetId);
              clearActive();
              this.classList.add('active-tab');
            }
          } else {
            window.location.href = targetId;
          }
        });
      });

      // Close sidebar on mobile when a nav link is clicked
      navLinks.forEach(link => {
        link.addEventListener('click', () => {
          navMenu.classList.remove('active');
          navOverlay.classList.remove('active');
          hamburger.classList.remove('active');
        });
      });
    });
  </script>
</body>
</html>