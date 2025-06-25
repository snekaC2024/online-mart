function setActiveTab(selectedTab, contentId) {
    // Get all tabs and remove the active class
    const tabs = document.querySelectorAll('.bottom-nav div');
    tabs.forEach(tab => tab.classList.remove('active'));

    // Add the active class to the clicked tab
    selectedTab.classList.add('active');

    // Hide all content sections
    const contentSections = document.querySelectorAll('.content-section');
    contentSections.forEach(section => section.classList.remove('active'));

    // Show the content section corresponding to the clicked tab
    const activeContent = document.getElementById(contentId);
    if (activeContent) {
        activeContent.classList.add('active');
    }
}

// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Get the favorite icon and cart icon elements
    const favoriteIcon = document.querySelector(".fa-heart");
    const cartIcon = document.querySelector(".fa-shopping-cart");
    const userIcon = document.querySelector(".fa-user-circle");
    const adminIcon = document.querySelector(".fa-key");


    // Add click event listener to the user icon
    userIcon.addEventListener("click", () => {
        window.location.href = "register-buyer.php";
    });

    // Add click event listener to the favorite icon
    favoriteIcon.addEventListener("click", () => {
        window.location.href = "register-buyer.php";
    });

    // Add click event listener to the cart icon
    cartIcon.addEventListener("click", () => {
        window.location.href = "register-buyer.php";
    });

    adminIcon.addEventListener("click", () => {
        window.location.href = "admin_dashboard.php";
    });

});


// search-items

 // Real-time search functionality
 function searchProducts() {
    const query = document.getElementById("productSearch").value.toLowerCase();
    const catalogCards = document.querySelectorAll(".catalog-card");
    
    catalogCards.forEach(card => {
        const title = card.querySelector("h3").innerText.toLowerCase();
        
        // If the search query matches the title, show the card, otherwise hide it
        if (title.includes(query)) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
    }

// Search bar functionality
document.getElementById("productSearch").addEventListener("input", function () {
    const query = this.value.toLowerCase(); // Get the search query and convert it to lowercase
    const productCards = document.querySelectorAll(".productCard"); // Get all product cards

    productCards.forEach(card => {
        const productName = card.querySelector(".productDetails h3").textContent.toLowerCase();
        const productCategory = card.getAttribute("data-category")?.toLowerCase() || ""; // Get the data-category attribute

        // Show or hide product based on search query matching name or category
        if (productName.includes(query) || productCategory.includes(query)) {
            card.style.display = "block"; // Show matching product
        } else {
            card.style.display = "none"; // Hide non-matching product
        }
    });
});


document.addEventListener('DOMContentLoaded', () => {
    // Get all catalog items and product cards
    const catalogItems = document.querySelectorAll('.catalog-item');
    const productCards = document.querySelectorAll('.productCard');

    // Add click event to each catalog item
    catalogItems.forEach(item => {
        item.addEventListener('click', () => {
            const category = item.querySelector('span').textContent.toLowerCase();

            // Show or hide product cards based on category
            productCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Default: Show all products
    productCards.forEach(card => {
        card.style.display = 'block';
    });
});


// filters category

document.getElementById('apply-filters').addEventListener('click', function () {
    const selectedCategory = document.getElementById('categories').value; // Get selected category

    // Get all product cards
    const productCards = document.querySelectorAll('.productCard');

    // Loop through product cards and apply filters
    productCards.forEach(card => {
        const productCategory = card.getAttribute('data-category'); // Product's category

        // Initialize visibility flag
        let isVisible = true;

        // Check category filter
        if (selectedCategory !== 'all' && selectedCategory !== productCategory) {
            isVisible = false;
        }


        // Set card visibility based on the filter conditions
        card.style.display = isVisible ? 'block' : 'none';
    });
});

