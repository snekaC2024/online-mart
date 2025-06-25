<?php
session_start();
// Check if the user is an admin
$isAdmin = isset($_SESSION['admin']);  // This assumes the admin session is stored with 'admin'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Market</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Gray Background Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent dark gray */
            z-index: 998;
            display: none;
        }

        /* Guided Demo Box */
        .demo-container {
            position: absolute;
            width: 220px;
            background-color: rgb(106, 175, 235);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            display: none;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 10px;
            z-index: 999;
            transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
            opacity: 0;
            transform: scale(0.8);
        }

        .demo-icon {
            font-size: 30px;
            margin-bottom: 5px;
        }

        .demo-text {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .next-btn {
            margin-top: 8px;
            padding: 5px 10px;
            background: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>


    <!-- Cookie Consent Popup -->
    <div id="cookieConsent" class="popup">
        <div class="popup-content">
            <p>We use cookies to improve your experience. By using our site, you agree to our cookie policy.</p>
            <button id="acceptCookies">Accept</button>
        </div>
    </div>

    <!-- Login Popup -->
    <div id="loginPopup" class="popup">
        <div class="popup-content">
            <button id="closeLoginPopup" class="close-btn">&times;</button>
            <h2>Login</h2>
            <p>Please log in to continue.</p>
            <button id="loginButton">Login</button>
        </div>
    </div>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="platform-name">
            <h1>Farm Flow</h1>
        </div>
        <div class="top-icons">
            <i class="fas fa-user-circle user-icon"></i>
            <i class="fas fa-heart favorite-icon"></i>
            <i class="fas fa-shopping-cart cart-icon"></i>
            <i class="fas fa-bell notifications-icon"></i>
            <a href="admin_dashboard.php">
                <i class="fas fa-key admin-icon" id="adminIcon"></i>
            </a>
        </div>
    </div>

     <!-- Guided Demo Section -->
     <div id="demoBox" class="demo-container">
        <i id="demoIcon" class="fas fa-key demo-icon"></i>
        <p id="demoText" class="demo-text">This is the Admin Login. Admins manage the platform.</p>
        <button id="nextButton" class="next-btn">Next</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" id="productSearch" placeholder="Search for products..." onkeyup="searchProducts()">
            <button><i class="fas fa-search"></i></button>
        </div>

        <!-- Advertisement Section -->
        <div class="advertisement-section">
            <h2>Today's Offers</h2>
            <div class="advertisement-cards">
                <div class="ad-card">
                    <img src="images/organic-apples.jpg" alt="Organic Apples">
                    <h3>Organic Apples</h3>
                    <p>Fresh and healthy apples directly from farms</p>
                    <p class="price">₹120.00 (per kg)</p>
                </div>
                <div class="ad-card">
                    <img src="images/fresh_milk.jpg" alt="Fresh Milk">
                    <h3>Fresh Milk</h3>
                    <p>Pure and nutritious dairy milk</p>
                    <p class="price">₹60.00 (per litre)</p>
                </div>
                <div class="ad-card">
                    <img src="images/basmati-rice.jpg" alt="Basmati Rice">
                    <h3>Basmati Rice</h3>
                    <p>High-quality basmati rice</p>
                    <p class="price">₹90.00 (per kg)</p>
                </div>
                <div class="ad-card">
                    <img src="images/organi-honey.jpg" alt="Organic Honey">
                    <h3>Organic Honey</h3>
                    <p>Pure and natural honey for your health</p>
                    <p class="price">₹250.00 (per 500g)</p>
                </div>
            </div>
        </div>

        <!-- Catalog Section -->
        <div class="catalog-section">
            <h2>Explore Categories</h2>
            <div class="catalog-cards">
                <div class="catalog-card">
                    <img src="images/fruits.jpg" alt="Fruits">
                    <h3>Fruits</h3>
                    <p>Fresh and organic fruits</p>
                </div>
                <div class="catalog-card">
                    <img src="images/vegetable.jpg" alt="Vegetables">
                    <h3>Vegetables</h3>
                    <p>Healthy and green vegetables</p>
                </div>
                <div class="catalog-card">
                    <img src="images/dairy.jpg" alt="Dairy">
                    <h3>Dairy</h3>
                    <p>Pure dairy products</p>
                </div>
                <div class="catalog-card">
                    <img src="images/grains.jpg" alt="Grains">
                    <h3>Grains</h3>
                    <p>Quality grains for every meal</p>
                </div>

                <div class="catalog-card">
                    <img src="images/herbs.jpg" alt="Herbs">
                    <h3>Herbs</h3>
                    <p>Fresh, aromatic herbs for cooking</p>
                </div>

                <div class="catalog-card">
                  <img src="images/farm-fresh-flour.jpg" alt="Farm Fresh Flour">
                  <h3>Farm Fresh Flour</h3>
                  <p>Locally milled whole grain flour</p>
                </div>
                <div class="catalog-card">
                   <img src="images/nuts.jpg" alt="Nuts">
                   <h3>Nuts</h3>
                   <p>Freshly harvested nuts for snacking</p>
                </div>
                <div class="catalog-card">
                  <img src="images/eggs.jpg" alt="Farm Fresh Eggs">
                  <h3>Farm Fresh Eggs</h3>
                  <p>Fresh eggs from free-range chickens on the farm</p>
                </div>
                <div class="catalog-card">
                   <img src="images/natural-beauty-products.jpg" alt="Natural Beauty Products">
                   <h3>Natural Beauty Products</h3>
                   <p>Handcrafted soaps, lotions, and skincare products</p>
                </div>

                <div class="catalog-card">
                  <img src="images/seeding.jpg" alt="Seedlings">
                  <h3>Seedlings</h3>
                  <p>Healthy, farm-grown seedlings for your garden</p>
                </div>
                
                <div class="catalog-card">
                    <img src="images/oil.jpg" alt="Oil">
                    <h3>Coconut Oil</h3>
                    <p>Cold-pressed, organic coconut oil</p>
                </div>
                <div class="catalog-card">
                  <img src="images/compost.jpg" alt="Compost">
                  <h3>Compost</h3>
                  <p>Organic compost made from farm waste for better soil</p>
                </div>
                <div class="catalog-card">
                    <img src="images/beekeeping.jpg" alt="Beekeeping">
                    <h3>Beekeeping</h3>
                    <p>Honey and beeswax products</p>
                </div>

                <div class="catalog-card">
                  <img src="images/fruit_juices.jpg" alt="Fruit Juice">
                  <h3>Fruit Juices</h3>
                  <p>100% natural, no added preservatives</p>
                </div>
                <div class="catalog-card">
                    <img src="images/processed.jpg" alt="Processed Products">
                    <h3>Processed Products</h3>
                    <p>Delicious jams and pickles</p>
                </div>
                <div class="catalog-card">
                   <img src="images/veg-snacks.jpg" alt="Vegetarian Snacks">
                   <h3>Vegetarian Snacks</h3>
                   <p>Delicious and healthy snacks</p>
                </div>
                <div class="catalog-card">
                    <img src="images/flowers.jpg" alt="Flowers & Plants">
                    <h3>Flowers & Plants</h3>
                    <p>Decorative plants and flowers</p>
                </div>
                <div class="catalog-card">
                    <img src="images/animal_feed.jpg" alt="Animal Feed">
                    <h3>Animal Feed</h3>
                    <p>Nutritious feed for animals</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="buyers-icon" id="customerIcon" onclick="window.location.href='register-buyer.php'">
            <i class="fas fa-users buyers"></i>
        </div>
        <div class="home-icon active">
            <i class="fas fa-home"></i>
        </div>
        <div class="farmers-icon" id="farmerIcon" onclick="window.location.href='register-farmer.php'">
            <i class="fas fa-tractor farmers"></i>
        </div>
    </div>

     <!-- JavaScript for Smooth Animation & Positioning -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let step = 0;
        const demoBox = document.getElementById("demoBox");
        const demoIcon = document.getElementById("demoIcon"); // Icon inside demo box
        const demoText = document.getElementById("demoText");
        const nextButton = document.getElementById("nextButton");
        const loginPopup = document.getElementById("loginPopup");

        // List of icons with positioning adjustments
        const icons = [
            { element: document.getElementById("adminIcon"), iconClass: "fas fa-key", text: "This is the Admin Login. Admins manage the platform.", adjustY: 50 },
            { element: document.getElementById("customerIcon"), iconClass: "fas fa-users", text: "This is the Customer Login. Customers can browse and buy products.", adjustY: -40 },
            { element: document.getElementById("farmerIcon"), iconClass: "fas fa-tractor", text: "This is the Farmer Login. Farmers can list and sell their products.", adjustY: -140},
            { element: document.getElementById("buyerIcon"), iconClass: "fas fa-users", text: "Buyers can register and place orders.", adjustY: -80, adjustX: -50 },
            { element: document.getElementById("homeIcon"), iconClass: "fas fa-home", text: "This is the Home button.", adjustY: -80, adjustX: -20 },
            { element: document.getElementById("bottomFarmerIcon"), iconClass: "fas fa-tractor", text: "Farmers can register and sell fresh produce.", adjustY: -80, adjustX: -50 }
        ];

        function positionDemoBox(targetIcon, iconClass, text, adjustX = 0, adjustY = 0) {
            const iconRect = targetIcon.getBoundingClientRect();
            const screenWidth = window.innerWidth;
            const screenHeight = window.innerHeight;

            let leftPos = iconRect.left + window.scrollX + adjustX + 30;
            let topPos = iconRect.top + window.scrollY + adjustY;

            // Prevent going off-screen (left)
            if (leftPos < 10) leftPos = 10;

            // Prevent going off-screen (right)
            if (leftPos + 220 > screenWidth) leftPos = screenWidth - 280;

            // If the icon is near the bottom, move box above
            if (topPos + 350 > screenHeight) {
                topPos = iconRect.top + window.scrollY - 200;
            }

            // Set new position and update content
            demoBox.style.left = leftPos + "px";
            demoBox.style.top = topPos + "px";
            demoBox.style.display = "flex";
            demoBox.style.opacity = "1";
            demoBox.style.transform = "scale(1)";
            
            demoIcon.className = iconClass + " demo-icon"; // Update icon
            demoText.innerHTML = text;
        }

        // Start immediately (No 5s delay)
        positionDemoBox(icons[step].element, icons[step].iconClass, icons[step].text, icons[step].adjustX || 0, icons[step].adjustY || 0);

        nextButton.addEventListener("click", function() {
            step++;

            if (step < icons.length) {
                demoBox.style.opacity = "0"; // Fade out
                demoBox.style.transform = "scale(0.8)";

                setTimeout(() => {
                    positionDemoBox(icons[step].element, icons[step].iconClass, icons[step].text, icons[step].adjustX || 0, icons[step].adjustY || 0);

                    demoBox.style.opacity = "1"; // Fade in
                    demoBox.style.transform = "scale(1)";

                    if (step === icons.length - 1) {
                        nextButton.innerText = "Finish";
                    }
                }, 500);
            } else {
                demoBox.style.opacity = "0"; // Hide when done
                setTimeout(() => {
                    demoBox.style.display = "none";
                    
                    // Show login popup after demo
                    setTimeout(() => {
                        loginPopup.style.display = "flex";
                    }, 2000); // Show after 2 seconds
                }, 500);
            }
        });
    });
</script>

<!-- JavaScript for Cookie Consent & Login Popup -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const cookieConsent = document.getElementById("cookieConsent");
    const acceptCookies = document.getElementById("acceptCookies");
    const loginPopup = document.getElementById("loginPopup");
    const closeLoginPopup = document.getElementById("closeLoginPopup");

    // Check if cookies are already accepted
    if (localStorage.getItem("cookiesAccepted")) {
        console.log("Cookies already accepted, not showing popup.");
    } else {
        // Show cookie consent popup after 5 seconds
        setTimeout(function() {
            console.log("Displaying cookie consent popup.");
            cookieConsent.style.display = "flex";
        }, 5000);
    }

    // Accept Cookies and close popup
    if (acceptCookies) {
        acceptCookies.addEventListener("click", function() {
            localStorage.setItem("cookiesAccepted", "true");
            console.log("Cookies accepted.");
            cookieConsent.style.display = "none";
        });
    }

    // Automatically show login popup after demo ends
    setTimeout(function() {
        if (loginPopup.style.display !== "none") {
            console.log("Displaying login popup.");
            loginPopup.style.display = "flex";
        }
    }, 15000); // Show after 15 seconds

    // Close login popup when clicking close button
    if (closeLoginPopup) {
        closeLoginPopup.addEventListener("click", function() {
            console.log("Login popup closed.");
            loginPopup.style.display = "none";
        });
    }

    // Login Button functionality
    const loginButton = document.getElementById("loginButton");
    if (loginButton) {
        loginButton.addEventListener("click", function() {
            console.log("Redirecting to login page.");
            window.location.href = "login-buyer.php"; // Change this if needed
        });
    }
});
</script>

<script src="script.js"></script>
    
</body>
</html>
