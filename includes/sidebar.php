<!-- Sidebar HTML and CSS -->
<div class="sidebar" id="sidebar">
    <!-- Make the logo clickable and redirect to dashboard.php -->
    <a style="text-align: center;" href="../admin/dashboard.php">
        <img src="../assets/images/SAL.jpg" alt="Logo" class="logo">
    </a>
    
    <h3>Dashboard</h3>
    
    <a href="../admin/home.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
    <a href="../admin/profile.php" class="nav-link"><i class="fas fa-user"></i> About Me</a>
    <a href="../admin/skills_edu.php" class="nav-link"><i class="fas fa-user"></i> Skills & Education</a>
    <a href="../admin/projects.php" class="nav-link"><i class="fas fa-boxes"></i> Projects</a>
    <a href="../admin/timeline.php" class="nav-link"><i class="fas fa-boxes"></i> Timeline</a>
    
    <div class="back-btn">
        <a href="../pages/index.php"><i class="fas fa-arrow-left"></i> View Page</a>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #121212, #1e1e1e);
        font-family: 'Roboto', sans-serif;
        margin: 0;
        height: 100vh;
        color: white;
    }

    .wrapper {
        display: flex;
        flex: 1;
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(12px);
        padding: 30px 20px;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 15px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .sidebar .logo {
        width: 80px;
        border-radius: 50%;
        margin-bottom: 30px;
        align-self: center;
        box-shadow: 0 0 10px rgba(0, 255, 153, 0.5);
    }

    .sidebar h3 {
        text-align: center;
        margin-bottom: 40px;
        font-weight: 900;
        background: linear-gradient(90deg, #00ff99, #66ccff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-link {
        color: #00ff99;
        font-weight: 600;
        margin: 15px 0;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .nav-link i {
        margin-right: 10px;
    }

    .nav-link:hover {
        color: #66ccff;
        transform: translateX(5px);
    }

    .nav-link.active {
        color: #ffffff;
        border-bottom: 2px solid #00ff99;
        background-color: rgba(0, 255, 153, 0.1);
        box-shadow: 0 0 10px #00ff99;
        border-radius: 4px;
    }

    .back-btn {
        margin-top: auto;
        padding-top: 20px;
    }

    .back-btn a {
        display: block;
        text-align: center;
        background: linear-gradient(90deg, #00ff99, #66ccff);
        padding: 10px;
        border-radius: 6px;
        font-weight: bold;
        color: black;
        text-decoration: none;
    }

    .main-content {
        margin-left: 250px;
        flex: 1;
        padding: 40px;
        overflow-y: auto;
    }

    .main-content h1 {
        font-size: 2.5rem;
        background: linear-gradient(90deg, #00ff99, #66ccff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: glow 2s infinite;
    }

    @keyframes glow {
        0% { text-shadow: 0 0 5px #00ff99, 0 0 10px #66ccff; }
        50% { text-shadow: 0 0 10px #00ff99, 0 0 20px #66ccff; }
        100% { text-shadow: 0 0 5px #00ff99, 0 0 10px #66ccff; }
    }

    .toggle-btn {
        display: none;
        position: absolute;
        top: 20px;
        left: 20px;
        font-size: 1.8rem;
        background: none;
        border: none;
        color: #66ccff;
        z-index: 1001;
    }

    @media (max-width: 768px) {
        .sidebar {
            left: -250px;
        }

        .sidebar.active {
            left: 0;
        }

        .main-content {
            margin-left: 0;
            padding: 60px 20px;
        }

        .toggle-btn {
            display: block;
        }
    }

    .back-btn a:hover {
    background: linear-gradient(90deg, #66ccff, #00ff99);
    color: white;
    transform: scale(1.05);
    box-shadow: 0 0 10px #66ccff, 0 0 20px #00ff99;
    transition: all 0.3s ease;
}

</style>

<!-- Toggle Script -->
<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    menuToggle?.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    document.addEventListener("DOMContentLoaded", () => {
        const currentPage = window.location.pathname.split("/").pop();
        const navLinks = document.querySelectorAll(".nav-link");

        navLinks.forEach(link => {
            const linkPage = link.getAttribute("href").split("/").pop();
            if (linkPage === currentPage) {
                link.classList.add("active");
            }
        });
    });
</script>
