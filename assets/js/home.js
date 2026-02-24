// Check if user is admin (from PHP session)
const isAdmin = <?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'true' : 'false'; ?>;

// Make elements clickable if admin
document.addEventListener('DOMContentLoaded', function() {
    if (isAdmin) {
        // Greeting click handler
        const greetingElement = document.getElementById('editableGreeting');
        greetingElement.style.cursor = 'pointer';
        greetingElement.addEventListener('click', function() {
            openGreetingModal();
        });

        // Typing text click handler
        const typingTextContainer = document.getElementById('editableTypingText');
        typingTextContainer.style.cursor = 'pointer';
        typingTextContainer.addEventListener('click', function() {
            openTypingTextsModal();
        });

        // Hero image click handler
        const heroImage = document.getElementById('hero-displayed-image');
        heroImage.style.cursor = 'pointer';
        heroImage.addEventListener('click', function() {
            openHeroImagesModal();
        });
    }
});

// Modal control functions
function openGreetingModal() {
    // Fetch current greeting data and populate modal
    fetch('api/home-content.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_greeting'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            document.getElementById('greeting_text').value = data.data.greeting_text;
            document.getElementById('name_text').value = data.data.name_text;
        }
        document.getElementById('greetingModal').style.display = 'block';
    });
}

function openTypingTextsModal() {
    // Fetch current typing texts and populate modal
    const container = document.getElementById('typingTextsContainer');
    container.innerHTML = '';
    
    fetch('api/home-content.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_typing_texts'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            data.data.forEach((text, index) => {
                container.appendChild(createTypingTextInput(text, index));
            });
        }
        document.getElementById('typingTextsModal').style.display = 'block';
    });
}

function openHeroImagesModal() {
    // Fetch current hero images and populate modal
    const container = document.getElementById('heroImagesContainer');
    container.innerHTML = '';
    
    fetch('api/home-content.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_hero_images'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            data.data.forEach((image, index) => {
                container.appendChild(createHeroImageInput(image, index));
            });
        }
        document.getElementById('heroImagesModal').style.display = 'block';
    });
}

function openImageUploadModal() {
    document.getElementById('imageUploadModal').style.display = 'block';
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
}

// Helper functions to create form inputs (same as before)
function createTypingTextInput(textData, index) {
    // Same as previous implementation
}

function createHeroImageInput(imageData, index) {
    // Same as previous implementation
}

// Add event listeners for modal close buttons
document.querySelectorAll('.close-modal').forEach(button => {
    button.addEventListener('click', closeAllModals);
});

// Close modal when clicking outside content
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        closeAllModals();
    }
});