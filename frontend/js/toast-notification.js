/**
 * Toast Notification
 * A utility for displaying toast notifications
 */

class ToastNotification {
  constructor(options = {}) {
    this.defaultOptions = {
      position: 'top-right',
      autoClose: 5000,
      hideProgressBar: false,
      closeOnClick: true,
      pauseOnHover: true,
      draggable: true,
      ...options
    };
    
    this.container = null;
    this.toasts = [];
    this.createContainer();
  }

  /**
   * Create container for toast notifications
   */
  createContainer() {
    if (document.querySelector('.toast-container')) {
      this.container = document.querySelector('.toast-container');
      return;
    }

    this.container = document.createElement('div');
    this.container.className = 'toast-container';
    
    // Add position class
    this.container.classList.add(this.defaultOptions.position);
    
    // Add styles if not present
    if (!document.getElementById('toast-styles')) {
      const style = document.createElement('style');
      style.id = 'toast-styles';
      style.textContent = `
        .toast-container {
          position: fixed;
          z-index: 9999;
          display: flex;
          flex-direction: column;
          padding: 15px;
          max-width: 350px;
          box-sizing: border-box;
          font-family: 'Arial', sans-serif;
        }
        .toast-container.top-right {
          top: 0;
          right: 0;
        }
        .toast-container.top-left {
          top: 0;
          left: 0;
        }
        .toast-container.bottom-right {
          bottom: 0;
          right: 0;
        }
        .toast-container.bottom-left {
          bottom: 0;
          left: 0;
        }
        .toast-container.top-center {
          top: 0;
          left: 50%;
          transform: translateX(-50%);
        }
        .toast-container.bottom-center {
          bottom: 0;
          left: 50%;
          transform: translateX(-50%);
        }
        .toast {
          position: relative;
          background: #fff;
          border-radius: 4px;
          box-shadow: 0 1px 10px 0 rgba(0, 0, 0, 0.1), 0 2px 15px 0 rgba(0, 0, 0, 0.05);
          margin-bottom: 16px;
          display: flex;
          max-height: 800px;
          overflow: hidden;
          width: 100%;
          transition: all 0.3s ease;
          animation: toast-in-right 0.7s;
        }
        .toast.success {
          border-left: 5px solid #07bc0c;
        }
        .toast.error {
          border-left: 5px solid #e74c3c;
        }
        .toast.info {
          border-left: 5px solid #3498db;
        }
        .toast.warning {
          border-left: 5px solid #f1c40f;
        }
        .toast-icon {
          flex: 0 0 30px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 16px;
          padding: 0 10px;
        }
        .toast.success .toast-icon {
          background-color: rgba(7, 188, 12, 0.1);
          color: #07bc0c;
        }
        .toast.error .toast-icon {
          background-color: rgba(231, 76, 60, 0.1);
          color: #e74c3c;
        }
        .toast.info .toast-icon {
          background-color: rgba(52, 152, 219, 0.1);
          color: #3498db;
        }
        .toast.warning .toast-icon {
          background-color: rgba(241, 196, 15, 0.1);
          color: #f1c40f;
        }
        .toast-content {
          flex: 1;
          padding: 12px 10px;
        }
        .toast-title {
          font-weight: bold;
          margin-bottom: 5px;
          color: #333;
          font-size: 16px;
        }
        .toast-message {
          font-size: 14px;
          color: #555;
          margin: 0;
          word-break: break-word;
        }
        .toast-close {
          background: transparent;
          border: none;
          cursor: pointer;
          padding: 0;
          margin: 4px;
          height: 24px;
          width: 24px;
          font-size: 16px;
          color: #999;
          align-self: flex-start;
          transition: color 0.3s ease;
        }
        .toast-close:hover {
          color: #333;
        }
        .toast-progress {
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 4px;
          background-color: rgba(0, 0, 0, 0.1);
        }
        .toast-progress-bar {
          height: 100%;
          width: 0%;
          transition: width linear;
        }
        .toast.success .toast-progress-bar {
          background-color: #07bc0c;
        }
        .toast.error .toast-progress-bar {
          background-color: #e74c3c;
        }
        .toast.info .toast-progress-bar {
          background-color: #3498db;
        }
        .toast.warning .toast-progress-bar {
          background-color: #f1c40f;
        }
        @keyframes toast-in-right {
          from {
            transform: translateX(100%);
          }
          to {
            transform: translateX(0);
          }
        }
        @keyframes toast-in-left {
          from {
            transform: translateX(-100%);
          }
          to {
            transform: translateX(0);
          }
        }
        .toast.removing {
          opacity: 0;
          max-height: 0;
          margin-bottom: 0;
          padding-top: 0;
          padding-bottom: 0;
        }
      `;
      document.head.appendChild(style);
    }
    
    document.body.appendChild(this.container);
  }

  /**
   * Create a toast notification
   * @param {Object} options - Toast options
   * @returns {HTMLElement} Toast element
   */
  createToast(options) {
    const {
      type = 'info',
      title = '',
      message = '',
      autoClose = this.defaultOptions.autoClose,
      hideProgressBar = this.defaultOptions.hideProgressBar,
      closeOnClick = this.defaultOptions.closeOnClick,
      pauseOnHover = this.defaultOptions.pauseOnHover,
      draggable = this.defaultOptions.draggable
    } = options;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.dataset.id = Date.now().toString();
    
    // Icon based on type
    let iconContent = '';
    switch (type) {
      case 'success':
        iconContent = '✓';
        break;
      case 'error':
        iconContent = '✕';
        break;
      case 'info':
        iconContent = 'ℹ';
        break;
      case 'warning':
        iconContent = '⚠';
        break;
    }
    
    // Create toast icon
    const iconDiv = document.createElement('div');
    iconDiv.className = 'toast-icon';
    iconDiv.textContent = iconContent;
    toast.appendChild(iconDiv);
    
    // Create toast content
    const contentDiv = document.createElement('div');
    contentDiv.className = 'toast-content';
    
    if (title) {
      const titleDiv = document.createElement('div');
      titleDiv.className = 'toast-title';
      titleDiv.textContent = title;
      contentDiv.appendChild(titleDiv);
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'toast-message';
    messageDiv.textContent = message;
    contentDiv.appendChild(messageDiv);
    
    toast.appendChild(contentDiv);
    
    // Create close button
    const closeButton = document.createElement('button');
    closeButton.className = 'toast-close';
    closeButton.innerHTML = '&times;';
    closeButton.addEventListener('click', () => {
      this.removeToast(toast);
    });
    toast.appendChild(closeButton);
    
    // Create progress bar
    if (!hideProgressBar && autoClose) {
      const progressDiv = document.createElement('div');
      progressDiv.className = 'toast-progress';
      
      const progressBar = document.createElement('div');
      progressBar.className = 'toast-progress-bar';
      progressDiv.appendChild(progressBar);
      
      toast.appendChild(progressDiv);
      
      setTimeout(() => {
        progressBar.style.width = '100%';
        progressBar.style.transitionDuration = `${autoClose}ms`;
      }, 10);
    }
    
    // Add event listeners
    if (closeOnClick) {
      toast.addEventListener('click', (e) => {
        if (e.target !== closeButton) {
          this.removeToast(toast);
        }
      });
    }
    
    let pauseTimer = false;
    let timeLeft = autoClose;
    let startTime;
    
    if (pauseOnHover && autoClose) {
      toast.addEventListener('mouseenter', () => {
        pauseTimer = true;
        timeLeft -= Date.now() - startTime;
        
        if (!hideProgressBar) {
          const progressBar = toast.querySelector('.toast-progress-bar');
          progressBar.style.transitionDuration = '0ms';
        }
      });
      
      toast.addEventListener('mouseleave', () => {
        pauseTimer = false;
        startTime = Date.now();
        
        if (!hideProgressBar) {
          const progressBar = toast.querySelector('.toast-progress-bar');
          progressBar.style.transitionDuration = `${timeLeft}ms`;
          progressBar.style.width = '100%';
        }
        
        if (timeLeft > 0) {
          setTimeout(() => {
            if (!pauseTimer) {
              this.removeToast(toast);
            }
          }, timeLeft);
        }
      });
    }
    
    // Set auto close timeout
    if (autoClose) {
      startTime = Date.now();
      setTimeout(() => {
        if (!pauseTimer) {
          this.removeToast(toast);
        }
      }, autoClose);
    }
    
    // Make draggable
    if (draggable) {
      let isDragging = false;
      let startX;
      let startTranslateX = 0;
      
      const onMouseDown = (e) => {
        isDragging = true;
        startX = e.clientX || e.touches[0].clientX;
        startTranslateX = 0;
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('touchmove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
        document.addEventListener('touchend', onMouseUp);
      };
      
      const onMouseMove = (e) => {
        if (!isDragging) return;
        
        const x = e.clientX || e.touches[0].clientX;
        const deltaX = x - startX;
        
        startTranslateX = deltaX;
        toast.style.transform = `translateX(${deltaX}px)`;
        toast.style.opacity = 1 - Math.abs(deltaX) / 200;
      };
      
      const onMouseUp = () => {
        isDragging = false;
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('touchmove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
        document.removeEventListener('touchend', onMouseUp);
        
        if (Math.abs(startTranslateX) >= 100) {
          this.removeToast(toast);
        } else {
          toast.style.transform = '';
          toast.style.opacity = '';
        }
      };
      
      toast.addEventListener('mousedown', onMouseDown);
      toast.addEventListener('touchstart', onMouseDown);
    }
    
    this.container.appendChild(toast);
    this.toasts.push(toast);
    
    return toast;
  }

  /**
   * Remove a toast notification
   * @param {HTMLElement} toast - Toast element
   */
  removeToast(toast) {
    toast.classList.add('removing');
    
    setTimeout(() => {
      if (toast && toast.parentNode) {
        toast.parentNode.removeChild(toast);
        this.toasts = this.toasts.filter(t => t !== toast);
      }
    }, 300);
  }

  /**
   * Display a success toast
   * @param {Object} options - Toast options
   * @returns {HTMLElement} Toast element
   */
  success(options) {
    return this.createToast({
      ...options,
      type: 'success',
      title: options.title || 'Success'
    });
  }

  /**
   * Display an error toast
   * @param {Object} options - Toast options
   * @returns {HTMLElement} Toast element
   */
  error(options) {
    return this.createToast({
      ...options,
      type: 'error',
      title: options.title || 'Error'
    });
  }

  /**
   * Display an info toast
   * @param {Object} options - Toast options
   * @returns {HTMLElement} Toast element
   */
  info(options) {
    return this.createToast({
      ...options,
      type: 'info',
      title: options.title || 'Info'
    });
  }

  /**
   * Display a warning toast
   * @param {Object} options - Toast options
   * @returns {HTMLElement} Toast element
   */
  warning(options) {
    return this.createToast({
      ...options,
      type: 'warning',
      title: options.title || 'Warning'
    });
  }

  /**
   * Clear all toast notifications
   */
  clearAll() {
    this.toasts.forEach(toast => {
      this.removeToast(toast);
    });
  }
}

// Create a singleton instance
window.ToastNotification = new ToastNotification(); 