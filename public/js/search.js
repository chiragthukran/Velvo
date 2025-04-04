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
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('travel-date')) {
        document.getElementById('travel-date').valueAsDate = new Date();
    }
    
    // Check if user is already logged in
    checkLoginStatus();
});

// Check login status
function checkLoginStatus() {
    fetch('backend/auth/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update nav based on role
                const navRight = document.querySelector('.nav-right');
                navRight.innerHTML = '';
                
                const greeting = document.createElement('span');
                greeting.className = 'user-greeting';
                greeting.textContent = `Welcome, ${data.user.name}!`;
                
                const dashboardLink = document.createElement('a');
                dashboardLink.href = data.user.role === 'driver' ? 'driver/dashboard.html' : 'customer/dashboard.html';
                dashboardLink.className = 'nav-button primary';
                dashboardLink.textContent = 'Dashboard';
                
                const logoutLink = document.createElement('a');
                logoutLink.href = '#';
                logoutLink.className = 'nav-button';
                logoutLink.textContent = 'Logout';
                logoutLink.onclick = function() {
                    logout();
                    return false;
                };
                
                navRight.appendChild(greeting);
                navRight.appendChild(dashboardLink);
                navRight.appendChild(logoutLink);
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
        });
}

// Logout function
function logout() {
    fetch('backend/auth/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error logging out:', error);
        });
}

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
        pickup: leavingFrom,
        dropoff: goingTo,
        date: travelDate,
        passengers: passengers
    });

    // Check if the search results container already exists
    let searchResultsContainer = document.getElementById('search-results-container');
    
    if (!searchResultsContainer) {
        // Create search results container
        searchResultsContainer = document.createElement('div');
        searchResultsContainer.id = 'search-results-container';
        searchResultsContainer.className = 'search-results-container';
        
        // Add it after the hero container
        const heroContainer = document.querySelector('.hero-container');
        heroContainer.parentNode.insertBefore(searchResultsContainer, heroContainer.nextSibling);
    }
    
    // Show loading
    searchResultsContainer.innerHTML = '<div class="loading">Searching for rides...</div>';
    
    // Fetch search results
    fetch(`backend/public/search_rides.php?${searchParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            // Reset search button
            searchButton.classList.remove('loading');
            searchButton.innerHTML = 'Search';
            
            // Clear the container
            searchResultsContainer.innerHTML = '';
            
            // Create header
            const header = document.createElement('div');
            header.className = 'search-results-header';
            header.innerHTML = `<h2>Available Rides from ${leavingFrom} to ${goingTo}</h2>`;
            searchResultsContainer.appendChild(header);
            
            if (data.success && data.rides.length > 0) {
                // Create results list
                const resultsList = document.createElement('div');
                resultsList.className = 'search-results-list';
                
                data.rides.forEach(ride => {
                    const rideCard = createRideCard(ride);
                    resultsList.appendChild(rideCard);
                });
                
                searchResultsContainer.appendChild(resultsList);
                
                // Scroll to results
                searchResultsContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Show no results message
                const noResults = document.createElement('div');
                noResults.className = 'no-results';
                noResults.innerHTML = `
                    <div class="no-results-icon"><i class="fas fa-search"></i></div>
                    <h3>No Rides Found</h3>
                    <p>We couldn't find any rides matching your search criteria. Try different locations or dates.</p>
                `;
                searchResultsContainer.appendChild(noResults);
                
                // Scroll to results
                searchResultsContainer.scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(error => {
            console.error('Error searching for rides:', error);
            
            // Reset search button
            searchButton.classList.remove('loading');
            searchButton.innerHTML = 'Search';
            
            // Show error message
            searchResultsContainer.innerHTML = `
                <div class="search-error">
                    <div class="search-error-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3>Error</h3>
                    <p>There was an error processing your search. Please try again.</p>
                </div>
            `;
            
            // Scroll to results
            searchResultsContainer.scrollIntoView({ behavior: 'smooth' });
        });
}

// Create a ride card for search results
function createRideCard(ride) {
    const card = document.createElement('div');
    card.className = 'ride-card';
    
    const rideDate = new Date(ride.ride_date);
    const formattedDate = rideDate.toLocaleDateString('en-US', { 
        weekday: 'short',
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    });
    
    card.innerHTML = `
        <div class="ride-header">
            <div class="ride-title">${ride.pickup_location} to ${ride.dropoff_location}</div>
            <div class="ride-status">Available</div>
        </div>
        <div class="ride-details">
            <p><i class="fas fa-calendar"></i> ${formattedDate} at ${ride.departure_time}</p>
            <p><i class="fas fa-chair"></i> ${ride.available_seats} seats available</p>
            <p>&#8377;${ride.fare} per seat</p>
            <p><i class="fas fa-user"></i> Driver: ${ride.driver_name}</p>
        </div>
        <div class="ride-actions">
            <button class="ride-action-btn btn-book" onclick="handleBookingClick(${ride.ride_id})">
                Book Seat
            </button>
        </div>
    `;
    
    return card;
}

// Handle booking click
function handleBookingClick(rideId) {
    // Check if user is logged in
    fetch('backend/auth/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.user.role === 'customer') {
                    // Redirect to customer dashboard with ride ID
                    window.location.href = `customer/dashboard.html?ride_id=${rideId}`;
                } else {
                    showNotification('Only customers can book rides. Please login with a customer account.', 'error');
                }
            } else {
                // Redirect to login page
                showLoginPrompt(rideId);
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
}

// Show login prompt
function showLoginPrompt(rideId) {
    // Create and display a prompt
    const promptOverlay = document.createElement('div');
    promptOverlay.className = 'prompt-overlay';
    
    promptOverlay.innerHTML = `
        <div class="login-prompt">
            <h3>Login Required</h3>
            <p>You need to login or create an account to book a ride.</p>
            <div class="prompt-actions">
                <a href="login.html?redirect=ride&id=${rideId}" class="btn-login">Login</a>
                <a href="signup.html?redirect=ride&id=${rideId}" class="btn-signup">Sign Up</a>
                <button class="btn-cancel" onclick="closeLoginPrompt()">Cancel</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(promptOverlay);
    
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
}

// Close login prompt
function closeLoginPrompt() {
    const promptOverlay = document.querySelector('.prompt-overlay');
    if (promptOverlay) {
        promptOverlay.remove();
        
        // Restore body scrolling
        document.body.style.overflow = '';
    }
}

// Notification system
function showNotification(message, type = 'info') {
    let notificationContainer = document.querySelector('.notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = message;
    
    notificationContainer.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
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