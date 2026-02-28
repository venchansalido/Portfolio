<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="../admin/dashboard.php">
            <img src="../assets/images/SAL.jpg" alt="Logo" class="sidebar-logo">
        </a>
        <span class="sidebar-title">Dashboard</span>
    </div>

    <nav class="sidebar-nav">
        <a href="../admin/home.php"       class="nav-link"><i class="fas fa-house"></i><span>Home</span></a>
        <a href="../admin/profile.php"    class="nav-link"><i class="fas fa-user"></i><span>About Me</span></a>
        <a href="../admin/skills_edu.php" class="nav-link"><i class="fas fa-laptop-code"></i><span>Skills & Education</span></a>
        <a href="../admin/projects.php"   class="nav-link"><i class="fas fa-folder-open"></i><span>Projects</span></a>
        <a href="../admin/timeline.php"   class="nav-link"><i class="fas fa-timeline"></i><span>Timeline</span></a>
    </nav>

    <div class="sidebar-footer">
        <button id="logoutBtn" class="logout-btn">
            <i class="fas fa-right-from-bracket"></i>
            <span>Logout</span>
        </button>
        <a href="../pages/index.php" class="view-page-btn">
            <i class="fas fa-arrow-left"></i>
            <span>View Page</span>
        </a>
    </div>
</div>

<style>
/* ── Variables (mirrors pages.css + header.css) ─────── */
:root {
    --red:       #da0416;
    --red-dark:  #a30512;
    --navy:      #0e2431;
    --navy-deep: #0e0f31;
    --body-bg:   #e5ecfb;
    --white:     #ffffff;
    --border:    #e8e8e8;
    --muted:     #6b7a8d;
    --sidebar-w: 25rem;
}

/* ── Body / Wrapper ─────────────────────────────────── */
body {
    background-color: var(--body-bg);
    font-family: 'Poppins', sans-serif;
    margin: 0;
    color: var(--navy);
}

.wrapper {
    display: flex;
    min-height: 100vh;
}

/* ── Sidebar Shell ──────────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-w);
    background: var(--navy-deep);
    display: flex;
    flex-direction: column;
    padding: 0;
    box-shadow: 0.2rem 0 1.6rem rgba(14, 36, 49, 0.18);
    transition: left 0.3s ease;
    z-index: 1000;
    overflow: hidden;
}

/* ── Header (logo + title) ──────────────────────────── */
.sidebar-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem 2rem 2rem;
    border-bottom: 0.1rem solid rgba(255, 255, 255, 0.07);
    gap: 1.2rem;
}

.sidebar-logo {
    width: 7rem;
    height: 7rem;
    border-radius: 50%;
    object-fit: cover;
    border: 0.3rem solid var(--white);
    box-shadow: 0 0.4rem 1.6rem rgba(218, 4, 22, 0.35);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sidebar-logo:hover {
    transform: scale(1.06);
    box-shadow: 0 0.6rem 2rem rgba(218, 4, 22, 0.55);
}

.sidebar-title {
    font-size: 1.3rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: rgba(255, 255, 255, 0.45);
}

/* ── Nav Links ──────────────────────────────────────── */
.sidebar-nav {
    display: flex;
    flex-direction: column;
    padding: 1.6rem 1.2rem;
    flex: 1;
    gap: 0.4rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    padding: 1.2rem 1.6rem;
    border-radius: 0.8rem;
    color: rgba(255, 255, 255, 0.65);
    font-size: 1.4rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    position: relative;
    overflow: hidden;
}

.nav-link i {
    font-size: 1.5rem;
    width: 1.8rem;
    text-align: center;
    flex-shrink: 0;
    transition: color 0.2s;
}

.nav-link span {
    white-space: nowrap;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.08);
    color: var(--white);
    transform: translateX(0.3rem);
}

/* Active state — red left bar + light tint */
.nav-link.active {
    background: rgba(218, 4, 22, 0.15);
    color: var(--white);
    border-left: 0.3rem solid var(--red);
    padding-left: 1.3rem;
}

.nav-link.active i {
    color: var(--red);
}

/* Ripple accent on hover */
.nav-link::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(218,4,22,0.08), transparent);
    opacity: 0;
    transition: opacity 0.2s;
    border-radius: 0.8rem;
}
.nav-link:hover::before { opacity: 1; }

/* ── Footer / View Page ─────────────────────────────── */
.sidebar-footer {
    padding: 1.6rem 1.2rem 2.4rem;
    border-top: 0.1rem solid rgba(255, 255, 255, 0.07);
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    width: 100%;
    padding: 1.1rem 1.6rem;
    background: rgba(255, 255, 255, 0.07);
    color: rgba(255, 255, 255, 0.75);
    font-size: 1.4rem;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    border: 0.1rem solid rgba(255, 255, 255, 0.12);
    border-radius: 0.8rem;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.logout-btn:hover {
    background: rgba(218, 4, 22, 0.2);
    color: var(--white);
    border-color: var(--red);
    transform: translateY(-0.2rem);
}

.view-page-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    width: 100%;
    padding: 1.1rem 1.6rem;
    background: linear-gradient(135deg, var(--red), var(--red-dark));
    color: var(--white);
    font-size: 1.4rem;
    font-weight: 700;
    border-radius: 0.8rem;
    text-decoration: none;
    box-shadow: 0 0.4rem 1.2rem rgba(218, 4, 22, 0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.view-page-btn:hover {
    transform: translateY(-0.2rem);
    box-shadow: 0 0.8rem 2rem rgba(218, 4, 22, 0.5);
    color: var(--white);
}

.view-page-btn:active {
    transform: translateY(0);
}

/* ── Main content offset ────────────────────────────── */
.main-content {
    margin-left: var(--sidebar-w);
    flex: 1;
    overflow-y: auto;
}

/* ── Toggle button ──────────────────────────────────── */
.toggle-btn {
    display: none;
    position: fixed;
    top: 1.4rem;
    left: 1.4rem;
    font-size: 2.2rem;
    background: none;
    border: none;
    color: var(--navy);
    cursor: pointer;
    z-index: 1100;
    transition: color 0.2s;
}
.toggle-btn:hover { color: var(--red); }

/* ── Mobile ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar {
        left: -26rem;
    }

    .sidebar.active {
        left: 0;
    }

    .main-content {
        margin-left: 0;
    }

    .toggle-btn {
        display: block;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Active nav link
    const currentPage = window.location.pathname.split("/").pop();
    document.querySelectorAll(".nav-link").forEach(link => {
        const linkPage = link.getAttribute("href").split("/").pop();
        if (linkPage === currentPage) {
            link.classList.add("active");
        }
    });

    // Logout button
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", async () => {
            try {
                await fetch("/venard/user/logout.php", { method: "POST" });
            } catch (e) {}
            window.location.href = "/venard/index.php";
        });
    }
});
</script>