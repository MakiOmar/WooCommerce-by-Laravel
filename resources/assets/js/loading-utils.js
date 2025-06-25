/**
 * Loading Indicators Utility
 * Provides easy-to-use functions for showing/hiding loading states
 */

class LoadingManager {
    constructor() {
        this.activeLoaders = new Set();
    }

    /**
     * Show loading indicator for an input field
     * @param {string|HTMLElement} input - Input element or selector
     * @param {Object} options - Configuration options
     */
    showInputLoading(input, options = {}) {
        const element = typeof input === 'string' ? document.querySelector(input) : input;
        if (!element) return;

        const container = element.closest('.search-input-container') || element.parentElement;
        const indicator = this.createLoadingIndicator(container);
        
        // Add loading classes
        element.classList.add('input-loading');
        element.disabled = true;
        
        // Show indicator
        indicator.classList.add('show');
        
        // Store reference
        this.activeLoaders.add({
            element: element,
            indicator: indicator,
            type: 'input'
        });

        return indicator;
    }

    /**
     * Hide loading indicator for an input field
     * @param {string|HTMLElement} input - Input element or selector
     */
    hideInputLoading(input) {
        const element = typeof input === 'string' ? document.querySelector(input) : input;
        if (!element) return;

        // Find and remove loader
        const loader = Array.from(this.activeLoaders).find(l => l.element === element);
        if (loader) {
            element.classList.remove('input-loading');
            element.disabled = false;
            loader.indicator.classList.remove('show');
            this.activeLoaders.delete(loader);
        }
    }

    /**
     * Show loading state for a button
     * @param {string|HTMLElement} button - Button element or selector
     * @param {string} loadingText - Text to show while loading
     */
    showButtonLoading(button, loadingText = 'Loading...') {
        const element = typeof button === 'string' ? document.querySelector(button) : button;
        if (!element) return;

        const originalText = element.innerHTML;
        const indicator = this.createLoadingIndicator(element, 'button');
        
        // Store original content
        element.setAttribute('data-original-text', originalText);
        
        // Add loading classes
        element.classList.add('btn-loading');
        element.disabled = true;
        
        // Update content
        element.innerHTML = `
            <span class="loading-indicator show">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </span>
            <span class="btn-text">${loadingText}</span>
        `;
        
        // Store reference
        this.activeLoaders.add({
            element: element,
            originalText: originalText,
            type: 'button'
        });

        return indicator;
    }

    /**
     * Hide loading state for a button
     * @param {string|HTMLElement} button - Button element or selector
     */
    hideButtonLoading(button) {
        const element = typeof button === 'string' ? document.querySelector(button) : button;
        if (!element) return;

        const loader = Array.from(this.activeLoaders).find(l => l.element === element);
        if (loader) {
            element.classList.remove('btn-loading');
            element.disabled = false;
            element.innerHTML = loader.originalText;
            this.activeLoaders.delete(loader);
        }
    }

    /**
     * Show loading overlay for full page operations
     * @param {string} message - Loading message
     */
    showOverlay(message = 'Loading...') {
        let overlay = document.querySelector('.loading-overlay');
        
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div class="mt-2">${message}</div>
                </div>
            `;
            document.body.appendChild(overlay);
        }

        overlay.classList.add('show');
        this.activeLoaders.add({ element: overlay, type: 'overlay' });
        
        return overlay;
    }

    /**
     * Hide loading overlay
     */
    hideOverlay() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            const loader = Array.from(this.activeLoaders).find(l => l.element === overlay);
            if (loader) {
                this.activeLoaders.delete(loader);
            }
        }
    }

    /**
     * Show loading state for a table row
     * @param {string|HTMLElement} row - Table row element or selector
     */
    showRowLoading(row) {
        const element = typeof row === 'string' ? document.querySelector(row) : row;
        if (!element) return;

        element.classList.add('table-row-loading');
        this.activeLoaders.add({ element: element, type: 'row' });
    }

    /**
     * Hide loading state for a table row
     * @param {string|HTMLElement} row - Table row element or selector
     */
    hideRowLoading(row) {
        const element = typeof row === 'string' ? document.querySelector(row) : row;
        if (!element) return;

        element.classList.remove('table-row-loading');
        const loader = Array.from(this.activeLoaders).find(l => l.element === element);
        if (loader) {
            this.activeLoaders.delete(loader);
        }
    }

    /**
     * Create a loading indicator element
     * @param {HTMLElement} container - Container element
     * @param {string} type - Type of indicator
     * @returns {HTMLElement} Loading indicator element
     */
    createLoadingIndicator(container, type = 'input') {
        let indicator = container.querySelector('.loading-indicator');
        
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'loading-indicator';
            indicator.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            container.appendChild(indicator);
        }
        
        return indicator;
    }

    /**
     * Hide all active loaders
     */
    hideAll() {
        this.activeLoaders.forEach(loader => {
            switch (loader.type) {
                case 'input':
                    this.hideInputLoading(loader.element);
                    break;
                case 'button':
                    this.hideButtonLoading(loader.element);
                    break;
                case 'overlay':
                    this.hideOverlay();
                    break;
                case 'row':
                    this.hideRowLoading(loader.element);
                    break;
            }
        });
    }

    /**
     * Get count of active loaders
     * @returns {number} Number of active loaders
     */
    getActiveCount() {
        return this.activeLoaders.size;
    }
}

// Create global instance
window.loadingManager = new LoadingManager();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LoadingManager;
} 