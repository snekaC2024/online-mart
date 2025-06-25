// Go back to the previous page
function goBack() {
    window.history.back();
}

// Toggle favorite icon color
function toggleFavorite(icon) {
    const heartIcon = icon.querySelector("i");
    heartIcon.classList.toggle("fas");
    heartIcon.classList.toggle("far");
    heartIcon.style.color = heartIcon.classList.contains("fas") ? "red" : "black";
}

// JavaScript to dynamically set progress bar colors
 document.addEventListener('DOMContentLoaded', function() {
    const ratings = [5, 4, 3, 2, 1]; // Star counts
    const colors = ['#007bff', '#3b5998', '#00aaff', '#00cccc', '#cccccc']; // Colors for each rating
    const percentages = [70, 50, 30, 10, 10]; // Example percentage for each rating

    ratings.forEach((rating, index) => {
        const progressBar = document.getElementById(`progress-bar-${rating}`);
        const progressElement = progressBar.querySelector('.progress');
        const percentage = percentages[index];
        progressElement.style.width = `${percentage}%`;
        progressElement.style.backgroundColor = colors[index];
    });
});

// redirect to register
function redirectToRegister() {
    // actual path to your register page
    window.location.href = 'register-buyer.php';
}

// Optional: Go back function for the header
function goBack() {
    window.history.back();
}