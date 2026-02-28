<?php

include '../includes/config.php';

// Get the current page filename (e.g., "index.php", "about.php")
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
 /* Modal Styles */
.login-modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.6);
  padding-top: 60px;
}

.login-modal-content {
  background-color: #fefefe;
  margin: 5% auto;
  padding: 20px;
  border-radius: 15px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  position: relative;
}

.login-modal-content .login-modal-close {
  position: absolute;
  right: 15px;
  top: 10px;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.login-modal-content .login-modal-logo {
  width: 80px;
  margin-bottom: 15px;
}

.login-modal-content input[type="text"],
.login-modal-content input[type="password"] {
  width: 95%;
  padding: 10px;
  margin: 10px 0;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.login-modal-content button {
  width: 95%;
  padding: 10px;
  background-color: #333;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-bottom: 15px;
}

.login-modal-content button:hover {
  background-color: #555;
}

.login-modal-error {
  color: red;
  margin-bottom: 15px;
}

/* Quote Box Styles */
.quote-box {
  text-align: left;
}

.quote-text {
  font-style: italic;
  margin-bottom: 10px;
  line-height: 1.6;
}

.quote-author {
  font-weight: bold;
  text-align: right;
}

.refresh-quote {
  display: inline-block;
  margin-top: 15px;
  cursor: pointer;
  color: #777;
  transition: color 0.3s;
}

.refresh-quote:hover {
  color: #333;
}

/* Update the box-container layout to accommodate three boxes */
.box-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

.box {
  flex: 1;
  min-width: 250px;
  margin: 10px;
}

@media (max-width: 992px) {
  .box {
    flex-basis: 100%;
  }
}
</style>
<section class="footer">

<div class="box-container">

<div class="box left">
    <h3>Venard's Portfolio</h3>
    <p>Thank you for visiting my personal portfolio website. Connect with me over socials. <br/> <br/> Keep Rising 🚀. Connect with me over live chat!</p>
</div>

<div class="box middle">
    <h3>Contact info</h3>
    <p> <i class="fas fa-phone"></i>+63 935-136-3586</p>
    <p> <i class="fas fa-envelope"></i>venardjhoncsalido@gmail.com</p>
    <p> <i class="fas fa-map-marked-alt"></i>Zamboanga City, Philippines</p>
    <div class="share">
      <a class="facebook" aria-label="Facebook" href="https://www.facebook.com/profile.php?id=100009715320640/" target="_blank"><i class="fab fa-facebook"></i></a>
      <a class="instagram" aria-label="Instagram" href="https://www.instagram.com/venplaystrings/"><i class="fab fa-instagram" target="_blank"></i></a>
      <a class="dev" aria-label="Dev" href="https://www.linkedin.com/in/venard-jhon-cabahug-salido-08041434b/" target="_blank"><i class="fa-brands fa-linkedin"></i></a>
    </div>
</div>

<div class="box right quote-box">
    <h3>Daily Inspiration</h3>
    <div id="quote-container">
        <p class="quote-text">Loading inspiration...</p>
        <p class="quote-author"></p>
    </div>
    <p class="refresh-quote"><i class="fas fa-sync-alt"></i> New Quote</p>
</div>

</div>

<!-- Credit line — heart is the hidden login trigger (no visual hint) -->
<h1 class="credit <?= !isset($_SESSION['user_id']) ? 'login-trigger' : '' ?>">
    Venard <i class="fa fa-heart pulse"></i> Salido
</h1>

</section>

<!-- Login Modal -->
<div id="loginModal" class="login-modal">
  <div class="login-modal-content">
    <span class="login-modal-close">&times;</span>
    <img src="../assets/images/venlogo.jpg" alt="Logo" class="login-modal-logo">
    <h2>Admin Login</h2>
    <form id="loginForm">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p id="loginError" class="login-error"></p>
    </form>

  </div>
</div>

<!-- SweetAlert2 Library (load once for all pages) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {

  // ── Easter egg: click the heart 5× to open login modal ──────────
  <?php if (!isset($_SESSION['user_id'])): ?>
  (function () {
    const CLICKS_NEEDED = 5;
    const RESET_DELAY   = 2000; // ms — resets if you pause too long between clicks
    let   count         = 0;
    let   resetTimer    = null;

    const heart = document.querySelector('.login-trigger .fa-heart');
    if (!heart) return;

    // Pointer cursor so it feels clickable, but no other visual hint
    heart.style.cursor = 'pointer';

    heart.addEventListener('click', function (e) {
      e.stopPropagation(); // don't bubble up to window listener below

      count++;

      // Small pop animation on every click for tactile feedback
      heart.style.transition = 'transform 0.1s ease';
      heart.style.transform  = 'scale(1.6)';
      setTimeout(() => { heart.style.transform = 'scale(1)'; }, 120);

      // Reset counter if user pauses too long
      clearTimeout(resetTimer);
      resetTimer = setTimeout(() => { count = 0; }, RESET_DELAY);

      if (count >= CLICKS_NEEDED) {
        count = 0;
        clearTimeout(resetTimer);

        // Triumphant burst before the modal opens
        heart.style.color      = '#ff4d4d';
        heart.style.transform  = 'scale(2)';
        setTimeout(() => {
          heart.style.transform = 'scale(1)';
          heart.style.color     = ''; // revert to original CSS color

          // Open the modal
          const modal = document.getElementById('loginModal');
          if (modal) {
            modal.style.display = 'block';
            const loginError = document.getElementById('loginError');
            if (loginError) loginError.textContent = '';
          }
        }, 200);
      }
    });
  })();
  <?php endif; ?>
  // ── End Easter egg ───────────────────────────────────────────────

  // Modal close handling
  const modal = document.getElementById("loginModal");
  if (modal) {
    const closeModal = modal.querySelector(".login-modal-close");
    if (closeModal) {
      closeModal.addEventListener("click", () => {
        modal.style.display = "none";
        const loginError = document.getElementById("loginError");
        if (loginError) loginError.textContent = "";
      });
    }

    window.addEventListener("click", (event) => {
      if (event.target == modal) {
        modal.style.display = "none";
        const loginError = document.getElementById("loginError");
        if (loginError) loginError.textContent = "";
      }
    });
  }

  // Login form handling
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);
      try {
        const response = await fetch("../user/login.php", {
          method: "POST",
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          if (modal) modal.style.display = "none";
          Swal.fire({
            icon: 'success',
            title: 'Login Successful!',
            text: 'Welcome back!',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = result.redirect;
          });
        } else {
          const loginError = document.getElementById("loginError");
          if (loginError) {
            loginError.textContent = result.message || "Invalid username or password.";
          }
        }
      } catch (error) {
        console.error("Error logging in:", error);
        const loginError = document.getElementById("loginError");
        if (loginError) {
          loginError.textContent = "Something went wrong. Please try again.";
        }
      }
    });
  }

  // Quote API integration
  const fetchQuote = async () => {
    const quoteText = document.querySelector('.quote-text');
    const quoteAuthor = document.querySelector('.quote-author');
    
    quoteText.innerHTML = "Loading inspiration...";
    quoteAuthor.innerHTML = "";
    
    try {
      const response = await fetch('https://api.quotable.io/random');
      if (!response.ok) throw new Error('Network response was not ok');
      const data = await response.json();
      quoteText.innerHTML = `"${data.content}"`;
      quoteAuthor.innerHTML = `- ${data.author}`;
    } catch (primaryError) {
      console.error('Error fetching from primary API:', primaryError);
      try {
        const backupResponse = await fetch('https://zenquotes.io/api/random');
        if (!backupResponse.ok) throw new Error('Backup API response was not ok');
        const backupData = await backupResponse.json();
        if (backupData && backupData[0]) {
          quoteText.innerHTML = `"${backupData[0].q}"`;
          quoteAuthor.innerHTML = `- ${backupData[0].a}`;
        } else {
          throw new Error('Invalid backup API response format');
        }
      } catch (backupError) {
        console.error('Error fetching from backup API:', backupError);
        const fallbackQuotes = [
          { text: "The best way to predict the future is to create it.", author: "Abraham Lincoln" },
          { text: "The only way to do great work is to love what you do.", author: "Steve Jobs" },
          { text: "Life is what happens when you're busy making other plans.", author: "John Lennon" },
          { text: "Believe you can and you're halfway there.", author: "Theodore Roosevelt" },
          { text: "It does not matter how slowly you go as long as you do not stop.", author: "Confucius" },
          { text: "Success is not final, failure is not fatal: It is the courage to continue that counts.", author: "Winston Churchill" },
          { text: "You miss 100% of the shots you don't take.", author: "Wayne Gretzky" },
          { text: "In the middle of every difficulty lies opportunity.", author: "Albert Einstein" },
          { text: "Your time is limited, so don't waste it living someone else's life.", author: "Steve Jobs" },
          { text: "Happiness is not something ready made. It comes from your own actions.", author: "Dalai Lama" },
          { text: "Don't watch the clock; do what it does. Keep going.", author: "Sam Levenson" },
          { text: "Hardships often prepare ordinary people for an extraordinary destiny.", author: "C.S. Lewis" },
          { text: "Act as if what you do makes a difference. It does.", author: "William James" },
          { text: "What lies behind us and what lies before us are tiny matters compared to what lies within us.", author: "Ralph Waldo Emerson" },
          { text: "You are never too old to set another goal or to dream a new dream.", author: "C.S. Lewis" }
        ];
        const randomQuote = fallbackQuotes[Math.floor(Math.random() * fallbackQuotes.length)];
        quoteText.innerHTML = `"${randomQuote.text}"`;
        quoteAuthor.innerHTML = `- ${randomQuote.author}`;
      }
    }
  };
  
  fetchQuote();
  
  const refreshButton = document.querySelector('.refresh-quote');
  if (refreshButton) {
    refreshButton.addEventListener('click', fetchQuote);
  }

  // Check for logout success message
  <?php if (isset($_SESSION['logout_success'])): ?>
    Swal.fire({
      icon: 'success',
      title: 'Logged Out!',
      text: 'You have been successfully logged out.',
      timer: 2000,
      showConfirmButton: false
    });
    <?php unset($_SESSION['logout_success']); ?>
  <?php endif; ?>
});
</script>