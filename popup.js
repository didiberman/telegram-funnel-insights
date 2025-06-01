// Newsletter Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // Elements
  const modal = document.getElementById('newsletter-modal');
  const closeButton = document.getElementById('newsletter-modal-close');
  const form = document.getElementById('newsletter-form');
  const nameInput = document.getElementById('newsletter-name');
  const emailInput = document.getElementById('newsletter-email');
  const submitButton = document.getElementById('newsletter-submit');
  const successMessage = document.getElementById('newsletter-success');
  
  // Open modal function - will be triggered by the existing button click
  function openModal() {
    modal.classList.add('open');
    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
  }
  
  // Close modal function
  function closeModal() {
    modal.classList.remove('open');
    document.body.style.overflow = ''; // Re-enable scrolling
  }
  
  // Event listeners
  if (closeButton) {
    closeButton.addEventListener('click', closeModal);
  }
  
  // Close when clicking on backdrop
  modal.addEventListener('click', function(e) {
    if (e.target === modal || e.target.classList.contains('newsletter-modal-backdrop')) {
      closeModal();
    }
  });
  
  // Form submission
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Basic validation
      if (!emailInput.value || !emailInput.value.includes('@')) {
        return;
      }
      
      // Show loading state
      const originalButtonContent = submitButton.innerHTML;
      submitButton.innerHTML = `
        <svg class="spinner" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      `;
      submitButton.disabled = true;
      
      // Create form data
      const formData = new FormData(form);
      
      // Submit the form
      fetch('https://didibeing.social/sub.php', {
        method: 'POST',
        mode: 'no-cors',
        body: formData
      })
      .then(() => {
        // Track conversion if fbq is available
        if (typeof fbq === 'function') {
          fbq('track', 'Lead');
        }
        
        // Show success message
        form.style.display = 'none';
        successMessage.style.display = 'block';
        
        // Create sparkles effect if the function exists
        if (typeof createSparkles === 'function') {
          createSparkles();
        }
        
        // Start countdown for redirect
        let seconds = 4;
        const countdownInterval = setInterval(() => {
          seconds--;
          if (seconds === 0) {
            clearInterval(countdownInterval);
            window.location.href = 'day1.html';
          }
        }, 1000);
        
        // Redirect after delay
        setTimeout(() => window.location.href = 'day1.html', 4000);
      })
      .catch(() => {
        // Handle error but still redirect
        form.style.display = 'none';
        successMessage.style.display = 'block';
        setTimeout(() => window.location.href = 'day1.html', 4000);
      });
    });
  }
  
  // Input focus effects
  const inputs = [nameInput, emailInput];
  inputs.forEach(input => {
    if (input) {
      input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
      });
      
      input.addEventListener('blur', function() {
        this.parentElement.classList.remove('focused');
      });
    }
  });
  
  // Connect the existing buttons to open the modal
  const optinButtons = document.querySelectorAll('.btn-optin');
  optinButtons.forEach(button => {
    button.addEventListener('click', openModal);
  });
  
  // Expose the openModal function globally
  window.openNewsletterModal = openModal;
});
