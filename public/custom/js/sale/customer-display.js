function updateCustomerDisplay(items, totals, ticker = null) {
    const tbody = document.getElementById('items-tbody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="no-items">No items added yet</td></tr>';
    } else {
        items.forEach(item => {
            const row = tbody.insertRow();
            row.insertCell(0).textContent = item.name;
            row.insertCell(1).textContent = item.quantity;
            row.insertCell(2).textContent = item.unitPrice;
            row.insertCell(3).textContent = item.total;
        });
    }

    document.getElementById('display-total').textContent = (totals?.grandTotal) || '0.00';

    if (ticker && document.querySelector('.ticker-content')) {
        document.querySelector('.ticker-content').textContent = ticker;
    }
}

function initializeFullscreenButton() {
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    
    if (!fullscreenBtn) return;
    
    fullscreenBtn.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            // Enter fullscreen
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    });

    // Update button icon based on fullscreen state
    document.addEventListener('fullscreenchange', updateFullscreenButton);
    document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
    document.addEventListener('msfullscreenchange', updateFullscreenButton);

    function updateFullscreenButton() {
        const isFullscreen = document.fullscreenElement || 
                           document.webkitFullscreenElement || 
                           document.msFullscreenElement;
        
        const icon = fullscreenBtn.querySelector('i');
        if (isFullscreen) {
            icon.className = 'bx bx-exit-fullscreen';
            fullscreenBtn.title = 'Exit Fullscreen';
        } else {
            icon.className = 'bx bx-fullscreen';
            fullscreenBtn.title = 'Enter Fullscreen';
        }
    }
}

// Image Slider Functionality with Enhanced Features
function initializeImageSlider() {
    const slider = document.getElementById('image-slider');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const dotsContainer = document.getElementById('slider-dots');
    
    let currentSlide = 0;
    let slides = [];
    let dots = [];
    let slideInterval;
    const SLIDE_DURATION = 10000; // 10 seconds (increased from 5 seconds)

    // Load images from customization settings
    async function loadSliderImages() {
        try {
            showLoadingState();
            
            const response = await fetch('/api/customization/images');
            const data = await response.json();
            
            if (data.success && data.images && data.images.length > 0) {
                initializeSlider(data.images);
            } else {
                showNoImagesState();
            }
        } catch (error) {
            console.error('Error loading slider images:', error);
            showErrorState();
        }
    }

    function showLoadingState() {
        slider.innerHTML = '<div class="slide"><div class="no-image">Loading images...</div></div>';
        hideControls();
    }

    function showNoImagesState() {
        slider.innerHTML = '<div class="slide"><div class="no-image">No promotional images available</div></div>';
        hideControls();
    }

    function showErrorState() {
        slider.innerHTML = '<div class="slide"><div class="no-image">Unable to load images</div></div>';
        hideControls();
    }

    function hideControls() {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (dotsContainer) dotsContainer.style.display = 'none';
    }

    function showControls() {
        if (prevBtn) prevBtn.style.display = 'flex';
        if (nextBtn) nextBtn.style.display = 'flex';
        if (dotsContainer) dotsContainer.style.display = 'flex';
    }

    function initializeSlider(images) {
        slider.innerHTML = '';
        dotsContainer.innerHTML = '';
        slides = [];
        dots = [];
        currentSlide = 0;

        if (images.length === 0) {
            showNoImagesState();
            return;
        }

        images.forEach((image, index) => {
            // Create slide
            const slide = document.createElement('div');
            slide.className = 'slide';
            slide.setAttribute('data-image-key', image.key);
            
            const img = document.createElement('img');
            img.src = image.url;
            img.alt = `Promotional image ${index + 1}`;
            img.onerror = function() {
                // If image fails to load, show placeholder
                this.src = '/noimage';
                this.alt = 'Image not found';
            };
            img.onload = function() {
                // Image loaded successfully
                slide.classList.add('loaded');
            };
            
            slide.appendChild(img);
            slider.appendChild(slide);
            slides.push(slide);

            // Create dot only if there are multiple images
            if (images.length > 1) {
                const dot = document.createElement('div');
                dot.className = 'dot';
                dot.setAttribute('data-slide-index', index);
                if (index === 0) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(index));
                dotsContainer.appendChild(dot);
                dots.push(dot);
            }
        });

        // Show/hide controls based on number of images
        if (images.length > 1) {
            showControls();
            updateSlider();
            startAutoSlide();
        } else {
            hideControls();
        }
    }

    function goToSlide(slideIndex) {
        if (slides.length <= 1 || slideIndex === currentSlide) return;
        
        currentSlide = slideIndex;
        updateSlider();
        resetAutoSlide(); // Reset timer when manually changing slides
    }

    function nextSlide() {
        if (slides.length <= 1) return;
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
    }

    function prevSlide() {
        if (slides.length <= 1) return;
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        updateSlider();
    }

    function updateSlider() {
        if (slides.length === 0) return;
        
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    }

    function startAutoSlide() {
        if (slides.length > 1) {
            slideInterval = setInterval(nextSlide, SLIDE_DURATION);
        }
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }

    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoSlide();
        });
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (slides.length <= 1) return;
        
        if (e.key === 'ArrowLeft') {
            prevSlide();
            resetAutoSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            resetAutoSlide();
        }
    });

    // Pause auto-slide on hover
    if (slider) {
        slider.addEventListener('mouseenter', stopAutoSlide);
        slider.addEventListener('mouseleave', startAutoSlide);
        
        // Touch events for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoSlide();
        });
        
        slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoSlide();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    nextSlide();
                } else {
                    // Swipe right - previous slide
                    prevSlide();
                }
                resetAutoSlide();
            }
        }
    }

    // Initialize
    loadSliderImages();

    // Cleanup on page unload
    window.addEventListener('beforeunload', stopAutoSlide);
}

// Export for potential reuse
window.ImageSlider = {
    initialize: initializeImageSlider,
    reload: function() {
        const slider = document.getElementById('image-slider');
        if (slider) {
            initializeImageSlider();
        }
    }
};

window.addEventListener('message', function (event) {
    if (event.data.type === 'UPDATE_CUSTOMER_DISPLAY') {
        updateCustomerDisplay(event.data.items, event.data.totals, event.data.ticker);
    } else if (
        event.data.type === 'WAREHOUSE_CHANGED' ||
        event.data.type === 'INITIAL_WAREHOUSE_INFO'
    ) {
        document.getElementById('warehouse-name').textContent = event.data.warehouseName || 'N/A';
    }
});

if (window.opener) {
    window.opener.postMessage({ type: 'CUSTOMER_DISPLAY_READY' }, '*');
    window.opener.postMessage({ type: 'REQUEST_INITIAL_WAREHOUSE' }, '*');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeFullscreenButton();
    initializeImageSlider();
});