/**
 * Loader utility for showing loading states
 */

class LoaderManager {
  constructor() {
    this.loaderHTML = `
      <div id="global-loader" class="loader-container">
        <div class="loader">
          <div class="spinner"></div>
          <p class="loading-text">Loading...</p>
        </div>
      </div>
    `;
    
    this.styleElement = document.createElement('style');
    this.styleElement.textContent = `
      .loader-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
      }
      
      .loader-container.active {
        opacity: 1;
        visibility: visible;
      }
      
      .loader {
        background-color: white;
        padding: 20px 40px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        text-align: center;
      }
      
      .spinner {
        width: 40px;
        height: 40px;
        margin: 0 auto 10px;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: #0275d8;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      
      .loading-text {
        margin: 0;
        color: #333;
        font-size: 16px;
      }
      
      @keyframes spin {
        to { transform: rotate(360deg); }
      }
      
      .button-loader {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
        margin-right: 5px;
        vertical-align: middle;
      }
    `;
    
    this.init();
  }
  
  init() {
    // Add styles to document
    document.head.appendChild(this.styleElement);
    
    // Create loader element
    const loaderDiv = document.createElement('div');
    loaderDiv.innerHTML = this.loaderHTML;
    document.body.appendChild(loaderDiv.firstElementChild);
    
    this.loaderElement = document.getElementById('global-loader');
  }
  
  /**
   * Show global loader
   * @param {string} message - Optional custom message
   */
  show(message = 'Loading...') {
    if (this.loaderElement) {
      const messageElem = this.loaderElement.querySelector('.loading-text');
      if (messageElem) {
        messageElem.textContent = message;
      }
      this.loaderElement.classList.add('active');
    }
  }
  
  /**
   * Hide global loader
   */
  hide() {
    if (this.loaderElement) {
      this.loaderElement.classList.remove('active');
    }
  }
  
  /**
   * Add loading state to a button
   * @param {HTMLElement} button - Button element
   * @param {boolean} isLoading - Whether button is in loading state
   * @param {string} originalText - Original button text
   */
  setButtonLoading(button, isLoading, originalText) {
    if (!button) return;
    
    if (isLoading) {
      button.disabled = true;
      
      // Store original text if not provided
      if (!originalText) {
        button.dataset.originalText = button.innerHTML;
      }
      
      // Add spinner and loading text
      const spinner = document.createElement('span');
      spinner.className = 'button-loader';
      button.innerHTML = '';
      button.appendChild(spinner);
      button.appendChild(document.createTextNode(originalText || 'Loading...'));
    } else {
      button.disabled = false;
      
      // Restore original text
      if (button.dataset.originalText) {
        button.innerHTML = button.dataset.originalText;
        delete button.dataset.originalText;
      } else if (originalText) {
        button.innerHTML = originalText;
      }
    }
  }
  
  /**
   * Add loading state to a section of the page
   * @param {string} containerId - ID of the container element
   * @param {boolean} isLoading - Whether section is in loading state
   */
  setSectionLoading(containerId, isLoading) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const existingLoader = container.querySelector('.section-loader');
    
    if (isLoading) {
      // Only add loader if it doesn't exist
      if (!existingLoader) {
        // Store original position if needed
        if (container.style.position !== 'relative') {
          container.dataset.originalPosition = container.style.position;
          container.style.position = 'relative';
        }
        
        const loaderDiv = document.createElement('div');
        loaderDiv.className = 'section-loader';
        loaderDiv.innerHTML = `
          <div class="spinner"></div>
          <p>Loading content...</p>
        `;
        
        // Add section loader styles
        loaderDiv.style.cssText = `
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(255, 255, 255, 0.7);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          z-index: 5;
          border-radius: inherit;
        `;
        
        container.appendChild(loaderDiv);
      }
    } else if (existingLoader) {
      // Remove loader
      existingLoader.remove();
      
      // Restore original position if needed
      if (container.dataset.originalPosition) {
        container.style.position = container.dataset.originalPosition;
        delete container.dataset.originalPosition;
      }
    }
  }
}

// Create global loader instance
const loader = new LoaderManager();

// Add interceptors to API service if available
document.addEventListener('DOMContentLoaded', function() {
  if (typeof api !== 'undefined') {
    // Override fetch methods to show loader
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
      // Show loader for API requests
      if (args[0].includes('/api/')) {
        loader.show();
      }
      
      return originalFetch.apply(this, args)
        .finally(() => {
          // Hide loader
          loader.hide();
        });
    };
  }
});

// Helper function to wrap async functions with loading UI
function withLoading(asyncFn, buttonElement, loadingText = 'Loading...') {
  return async function(...args) {
    try {
      if (buttonElement) {
        loader.setButtonLoading(buttonElement, true, loadingText);
      } else {
        loader.show();
      }
      
      const result = await asyncFn(...args);
      return result;
    } finally {
      if (buttonElement) {
        loader.setButtonLoading(buttonElement, false);
      } else {
        loader.hide();
      }
    }
  };
} 