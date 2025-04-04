// Message Panel Functionality
function showMessage(type) {
    const messagePanel = document.getElementById('messagePanel');
    const aboutContent = document.getElementById('aboutContent');
    const contactContent = document.getElementById('contactContent');
    const messageTitle = document.getElementById('messageTitle');
    const dialogBox = document.getElementById('dialogBox');
    
    // Get clicked button
    const clickedButton = document.querySelector(`.dialog-button[onclick="showMessage('${type}')"]`);
    const buttonRect = clickedButton.getBoundingClientRect();
    
    // Create clone for animation
    const clone = clickedButton.cloneNode(true);
    clone.classList.add('transforming');
    document.body.appendChild(clone);
    
    // Set initial position
    clone.style.top = buttonRect.top + 'px';
    clone.style.left = buttonRect.left + 'px';
    clone.style.width = buttonRect.width + 'px';
    clone.style.height = buttonRect.height + 'px';
    
    // Force reflow
    clone.offsetHeight;
    
    // Animate to message panel position
    requestAnimationFrame(() => {
        clone.style.transform = `translate(calc(100vw - 420px), ${-buttonRect.top + 20}px) rotate(360deg)`;
        clone.style.opacity = '0';
    });

    // Hide dialog box with fade
    dialogBox.classList.add('hide');
    
    // Show message panel after button animation
    setTimeout(() => {
        messagePanel.classList.add('show');
        
        // Hide all sections first
        aboutContent.classList.remove('active');
        contactContent.classList.remove('active');
        
        // Show appropriate section
        if (type === 'about') {
            messageTitle.textContent = 'About Us';
            aboutContent.classList.add('active');
        } else if (type === 'contact') {
            messageTitle.textContent = 'Contact Us';
            contactContent.classList.add('active');
        }
        
        // Remove clone after animation
        setTimeout(() => {
            clone.remove();
            dialogBox.classList.remove('show', 'hide');
        }, 300);
    }, 300);
}

function closeMessage() {
    const messagePanel = document.getElementById('messagePanel');
    messagePanel.classList.remove('show');
}

// Dialog functionality
function toggleDialog() {
    const dialog = document.getElementById('dialogBox');
    const messagePanel = document.getElementById('messagePanel');
    
    if (messagePanel.classList.contains('show')) {
        messagePanel.classList.remove('show');
        setTimeout(() => {
            dialog.classList.toggle('show');
        }, 300);
    } else {
        dialog.classList.toggle('show');
    }
    
    const btn = document.querySelector('.floating-btn i');
    btn.style.transform = dialog.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
}

// Close dialog when clicking outside
document.addEventListener('click', (e) => {
    const dialog = document.getElementById('dialogBox');
    const floatingBtn = document.querySelector('.floating-btn');
    const messagePanel = document.getElementById('messagePanel');
    
    // Don't close if clicking inside message panel
    if (messagePanel.contains(e.target)) {
        return;
    }
    
    // Close dialog if clicking outside
    if (!dialog.contains(e.target) && !floatingBtn.contains(e.target) && dialog.classList.contains('show')) {
        dialog.classList.remove('show');
        document.querySelector('.floating-btn i').style.transform = 'rotate(0deg)';
    }
});

// Add input animations and validation
document.querySelectorAll('.search-input input, .search-input select').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
        this.parentElement.style.transition = 'all 0.3s ease';
    });

    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
});

// Set default date to today
document.getElementById('travel-date').valueAsDate = new Date();

// Search functionality
function searchRides() {
    const leavingFrom = document.getElementById('leaving-from').value;
    const goingTo = document.getElementById('going-to').value;
    const travelDate = document.getElementById('travel-date').value;
    const passengers = document.getElementById('passengers').value;

    if (!leavingFrom || !goingTo) {
        showNotification('Please enter both departure and destination locations.', 'error');
        return;
    }

    // Add loading state
    const searchButton = document.querySelector('.search-button');
    searchButton.classList.add('loading');
    searchButton.innerHTML = 'Searching...';

    // Create search parameters
    const searchParams = new URLSearchParams({
        from: leavingFrom,
        to: goingTo,
        date: travelDate,
        passengers: passengers
    });

    // Redirect to search results page
    setTimeout(() => {
        window.location.href = `/customer/search-results.html?${searchParams.toString()}`;
    }, 800);
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add location suggestions
const popularLocations = [
];

function setupLocationAutocomplete(inputId) {
    const input = document.getElementById(inputId);
    const datalist = document.createElement('datalist');
    datalist.id = `${inputId}-list`;
    
    popularLocations.forEach(location => {
        const option = document.createElement('option');
        option.value = location;
        datalist.appendChild(option);
    });
    
    input.setAttribute('list', datalist.id);
    document.body.appendChild(datalist);
}

setupLocationAutocomplete('leaving-from');
setupLocationAutocomplete('going-to'); 