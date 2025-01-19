/**
 * Creates a customizable notification panel
 * @param {string|Function} dataDisplay - Content to display in the notification panel (HTML string or function returning HTML)
 * @param {Object} config - Configuration options
 * @param {string} [config.position='right'] - Panel position ('left' or 'right')
 * @param {string} [config.top='50'] - Distance from top in pixels
 * @param {string} [config.width='500px'] - Panel width
 * @param {string} [config.height='50%'] - Panel height
 * @param {string} [config.title='Notification'] - Panel title
 * @param {number} [config.zIndex=1200] - Base z-index for the panel
 * @param {string} [config.theme='system'] - Theme ('light', 'dark', or 'system')
 * @param {string} [bootstrapVersion='5'] - Bootstrap version ('3', '4', or '5')
 * @param {Object} [config.icon] - Custom icon configuration
 * @param {string} [config.icon.name='bell'] - Icon name ('bell' or 'custom')
 * @param {string} [config.icon.svg] - Custom SVG string if icon.name is 'custom'
 * @param {Object} [config.colors] - Custom color configuration
 * @param {string} [config.colors.light] - Light theme background color
 * @param {string} [config.colors.dark] - Dark theme background color
 * @returns {Object} Control methods: {open, close, toggle, isOpen}
 */
const showNotiPanel = (dataDisplay = null, config = {}) => {
    // Default configuration
    const defaultConfig = {
        position: 'right',
        top: '80',
        width: '400px',
        height: '50%',
        title: 'Notification',
        zIndex: 1200,
        theme: 'light',
        bootstrapVersion: 5, // Default to Bootstrap 5
        icon: {
            name: 'bell',
            svg: null
        },
        colors: {
            light: '#ffffff',
            dark: '#1a1a1a'
        }
    };

    // Merge default config with provided config
    const finalConfig = { ...defaultConfig, ...config };
    const baseZIndex = finalConfig.zIndex;

    // If dataDisplay is null, don't create anything
    if (dataDisplay === null) return;

    // Detect Bootstrap version from document if not specified
    const detectBootstrapVersion = () => {
        if (typeof bootstrap !== 'undefined') {
            if (bootstrap.Tooltip.VERSION?.startsWith('5')) return 5;
            if (bootstrap.Tooltip.VERSION?.startsWith('4')) return 4;
        }
        if (typeof $.fn?.tooltip?.Constructor?.VERSION !== 'undefined') {
            if ($.fn.tooltip.Constructor.VERSION?.startsWith('3')) return 3;
        }
        return finalConfig.bootstrapVersion;
    };

    const bootstrapVersion = detectBootstrapVersion();

    // Theme handling functions
    const getSystemTheme = () => 
        window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

    const getCurrentTheme = () => 
        finalConfig.theme === 'system' ? getSystemTheme() : finalConfig.theme;

    // Get icon SVG
    const getIconSvg = () => {
        if (finalConfig.icon.name === 'custom' && finalConfig.icon.svg) {
            return finalConfig.icon.svg;
        }
        return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" 
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>`;
    };

    // CSS Prefix handling
    const getCssPrefixes = (property, value) => {
        const prefixes = ['-webkit-', '-moz-', '-ms-', '-o-', ''];
        return prefixes.map(prefix => `${prefix}${property}: ${value};`).join('\n');
    };

    // Create style element with cross-browser compatibility
    const style = document.createElement('style');
    style.textContent = `
        .custom-noti-panel {
            position: fixed;
            ${finalConfig.position}: -${finalConfig.width};
            top: ${finalConfig.top}px;
            width: ${finalConfig.width};
            height: ${finalConfig.height};
            ${getCssPrefixes('transition', 'all 0.3s ease')}
            z-index: ${baseZIndex};
            background-color: var(--panel-bg);
            ${getCssPrefixes('box-shadow', '0 8px 24px rgba(0, 0, 0, 0.12)')}
            ${getCssPrefixes('border-radius', finalConfig.position === 'right' ? '16px 0 0 16px' : '0 16px 16px 0')}
        }

        /* Cross-browser scrollbar styles */
        .custom-noti-body {
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        .custom-noti-body::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-noti-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-noti-body::-webkit-scrollbar-thumb {
            background-color: var(--border-color);
            border-radius: 3px;
        }

        /* Theme variables with fallbacks */
        .custom-noti-panel.light {
            --panel-bg: ${finalConfig.colors.light};
            --text-color: #1a1a1a;
            --border-color: rgba(0, 0, 0, 0.1);
            --hover-bg: rgba(0, 0, 0, 0.05);
            background-color: ${finalConfig.colors.light}; /* Fallback */
            color: #1a1a1a; /* Fallback */
        }

        .custom-noti-panel.dark {
            --panel-bg: ${finalConfig.colors.dark};
            --text-color: #ffffff;
            --border-color: rgba(255, 255, 255, 0.1);
            --hover-bg: rgba(255, 255, 255, 0.1);
            background-color: ${finalConfig.colors.dark}; /* Fallback */
            color: #ffffff; /* Fallback */
        }

        .custom-noti-panel.show {
            ${finalConfig.position}: 0;
        }

        /* Bootstrap compatibility classes */
        .custom-noti-panel .btn-close {
            ${bootstrapVersion >= 5 ? 'opacity: 0.75;' : ''}
        }

        .custom-noti-toggle {
            position: absolute;
            top: 20px;
            ${finalConfig.position === 'right' ? 'left: -56px' : 'right: -56px'};
            width: 56px;
            height: 56px;
            background: #1a1a1a;
            border: none;
            color: #ffffff;
            ${getCssPrefixes('border-radius', 
                finalConfig.position === 'right' ? 
                '12px 0 0 12px' : '0 12px 12px 0')}
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            ${getCssPrefixes('transition', 'all 0.3s ease')}
            ${getCssPrefixes('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.1)')}
            ${getCssPrefixes('transform', 'translateZ(0)')} /* Hardware acceleration */
        }

        .custom-noti-toggle svg {
            ${getCssPrefixes('transition', 'transform 0.3s ease')}
        }

        .custom-noti-toggle:hover svg {
            ${getCssPrefixes('transform', 'scale(1.2) rotate(15deg)')}
        }

        .custom-noti-panel.show .custom-noti-toggle {
            background: var(--panel-bg, ${finalConfig.colors.light});
            color: var(--text-color, #1a1a1a);
        }

        .custom-noti-header {
            position: sticky;
            top: 0;
            background: var(--panel-bg, ${finalConfig.colors.light});
            padding: ${bootstrapVersion >= 4 ? '1rem 1.5rem' : '15px 20px'};
            border-bottom: 1px solid var(--border-color, rgba(0, 0, 0, 0.1));
            z-index: ${baseZIndex + 1};
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-noti-header h5 {
            font-size: ${bootstrapVersion >= 4 ? '1.25rem' : '18px'};
            font-weight: 600;
            color: var(--text-color, #1a1a1a);
            margin: 0;
        }

                .custom-noti-header {
            position: sticky;
            top: 0;
            background: var(--panel-bg);
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            z-index: ${baseZIndex + 1};
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-noti-header h5 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .custom-noti-close {
            background: none;
            border: none;
            color: var(--text-color, #1a1a1a);
            cursor: pointer;
            padding: 8px;
            ${getCssPrefixes('transition', 'all 0.2s ease')}
            ${getCssPrefixes('border-radius', '8px')}
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-noti-close:hover {
            background-color: var(--hover-bg, rgba(0, 0, 0, 0.05));
        }

        .custom-noti-body {
            height: calc(100% - ${bootstrapVersion >= 4 ? '82px' : '72px'});
            overflow-y: auto;
            padding: ${bootstrapVersion >= 4 ? '1.25rem 1.5rem' : '15px 20px'};
            color: var(--text-color, #1a1a1a);
        }

        .notification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.3);
            opacity: 0;
            visibility: hidden;
            ${getCssPrefixes('transition', 'all 0.3s ease')}
            z-index: ${baseZIndex - 1};
        }

        .notification-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Bootstrap Modal backdrop compatibility */
        .modal-open .notification-overlay {
            margin-right: ${bootstrapVersion >= 4 ? '0' : '15px'};
        }

        @media (max-width: 576px) {
            .custom-noti-panel {
                width: 100% !important;
                height: 100% !important;
                top: 0 !important;
                ${getCssPrefixes('border-radius', '0')}
            }

            .custom-noti-toggle {
                top: 50%;
                ${getCssPrefixes('transform', 'translateY(-50%)')}
            }

            /* Bootstrap modal mobile compatibility */
            .modal-open .custom-noti-panel {
                padding-right: 0 !important;
            }
        }

        /* IE11 Support */
        @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
            .custom-noti-panel {
                background-color: ${finalConfig.theme === 'dark' ? finalConfig.colors.dark : finalConfig.colors.light};
            }
            .custom-noti-body {
                -ms-overflow-style: -ms-autohiding-scrollbar;
            }
        }
    `;
    document.head.appendChild(style);

    // Create panel structure using Bootstrap classes when appropriate
    const getCloseButton = () => {
        return `<button class="custom-noti-close" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" 
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>`;
    };

    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'notification-overlay';
    document.body.appendChild(overlay);

    // Create panel
    const panel = document.createElement('div');
    panel.id = 'customNotiPanel';
    panel.className = `custom-noti-panel ${getCurrentTheme()}`;
    
    panel.innerHTML = `
        <button class="custom-noti-toggle" id="notiToggleBtn" aria-label="Toggle notifications">
            ${getIconSvg()}
        </button>
        <div class="custom-noti-header">
            <h5>${finalConfig.title}</h5>
            ${getCloseButton()}
        </div>
        <div class="custom-noti-body" id="notiContent">
        </div>
    `;

    document.body.appendChild(panel);

    // Handle content
    const contentContainer = document.getElementById('notiContent');
    if (typeof dataDisplay === 'function') {
        contentContainer.innerHTML = dataDisplay() || '';
    } else {
        contentContainer.innerHTML = dataDisplay;
    }

    // Theme change listener
    if (finalConfig.theme === 'system') {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const themeChangeHandler = (e) => {
            panel.className = `custom-noti-panel ${e.matches ? 'dark' : 'light'}`;
        };
        
        // Handle both modern and legacy browsers
        if (mediaQuery.addEventListener) {
            mediaQuery.addEventListener('change', themeChangeHandler);
        } else if (mediaQuery.addListener) {
            mediaQuery.addListener(themeChangeHandler);
        }
    }

    const togglePanel = () => {
        panel.classList.toggle('show');
        overlay.classList.toggle('show');
        
        if (!panel.classList.contains('show')) {
            setTimeout(() => {
                if (!panel.classList.contains('show')) {
                    overlay.classList.remove('show');
                }
            }, 300);
        }

        // Handle Bootstrap modal compatibility
        const body = document.body;
        if (body.classList.contains('modal-open')) {
            body.style.paddingRight = panel.classList.contains('show') ? '0' : '';
        }
    };

    // Event listeners with IE11 compatibility
    const toggleBtn = document.getElementById('notiToggleBtn');
    const closeBtn = panel.querySelector('.custom-noti-close');
    
    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        togglePanel();
    });

    closeBtn.addEventListener('click', togglePanel);

    overlay.addEventListener('click', () => {
        if (panel.classList.contains('show')) {
            togglePanel();
        }
    });

    // Handle escape key
    document.addEventListener('keydown', (e) => {
        e = e || window.event; // IE11 compatibility
        if ((e.key === 'Escape' || e.key === 'Esc') && panel.classList.contains('show')) {
            togglePanel();
        }
    });

    // Return methods
    return {
        open: () => {
            if (!panel.classList.contains('show')) {
                togglePanel();
            }
        },
        close: () => {
            if (panel.classList.contains('show')) {
                togglePanel();
            }
        },
        toggle: togglePanel,
        isOpen: () => panel.classList.contains('show'),
        getBootstrapVersion: () => bootstrapVersion
    };
};