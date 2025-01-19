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
 * @param {number|null} [config.count=null] - Notification count
 * @param {string} [bootstrapVersion='5'] - Bootstrap version ('3', '4', or '5')
 * @param {Object} [config.icon] - Custom icon configuration
 * @param {string} [config.icon.name='bell'] - Icon name ('bell' or 'custom')
 * @param {string} [config.icon.svg] - Custom SVG string if icon.name is 'custom'
 * @param {Object} [config.colors] - Custom color configuration
 * @param {string} [config.colors.light] - Light theme background color
 * @param {string} [config.colors.dark] - Dark theme background color
 * @returns {Object} Control methods: {open, close, toggle, isOpen, updateCount}
 */
const showNotiPanel = (dataDisplay = null, config = {}) => {
    
	// If dataDisplay is null, don't create anything
	if (dataDisplay === null) return;

	// Default configuration
	const defaultConfig = {
		position: "right",
		top: "80",
		width: "400px",
		height: "50%",
		title: "Notifications",
		zIndex: 1000,
		theme: "light",
		count: null,
		bootstrapVersion: 5,
		icon: {
			name: "bell",
			svg: null,
		},
		colors: {
			light: "#ffffff",
			dark: "#1a1a1a",
		},
	};

	// Merge default config with provided config
	const finalConfig = { ...defaultConfig, ...config };
	const baseZIndex = finalConfig.zIndex;

    // Format count value
    const formatCount = (count) => {
        if (count === null) return null;
        if (!Number.isInteger(count) || count < 0) return null;
        return count > 99 ? '99+' : count.toString();
    };

    // Validate and format initial count
    finalConfig.count = formatCount(finalConfig.count);

	// Detect Bootstrap version from document if not specified
	const detectBootstrapVersion = () => {
		if (typeof bootstrap !== "undefined") {
			if (bootstrap.Tooltip.VERSION?.startsWith("5")) return 5;
			if (bootstrap.Tooltip.VERSION?.startsWith("4")) return 4;
		}
		if (typeof $.fn?.tooltip?.Constructor?.VERSION !== "undefined") {
			if ($.fn.tooltip.Constructor.VERSION?.startsWith("3")) return 3;
		}
		return finalConfig.bootstrapVersion;
	};

	const bootstrapVersion = detectBootstrapVersion();

	// Theme handling functions
	const getSystemTheme = () =>
		window.matchMedia("(prefers-color-scheme: dark)").matches
			? "dark"
			: "light";

	const getCurrentTheme = () =>
		finalConfig.theme === "system" ? getSystemTheme() : finalConfig.theme;

	// Get icon SVG
	const getIconSvg = () => {
		if (finalConfig.icon.name === "custom" && finalConfig.icon.svg) {
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
		const prefixes = ["-webkit-", "-moz-", "-ms-", "-o-", ""];
		return prefixes
			.map((prefix) => `${prefix}${property}: ${value};`)
			.join("\n");
	};

	// Create style element with cross-browser compatibility
	const style = document.createElement("style");
	style.textContent = `
        .custom-noti-panel {
            position: fixed;
            ${finalConfig.position}: -${finalConfig.width};
            top: ${finalConfig.top}px;
            width: ${finalConfig.width};
            height: ${finalConfig.height};
            ${getCssPrefixes("transition", "all 0.3s ease")}
            z-index: ${baseZIndex};
            background-color: var(--panel-bg);
            ${getCssPrefixes("box-shadow", "0 8px 24px rgba(0, 0, 0, 0.12)")}
            ${getCssPrefixes(
							"border-radius",
							finalConfig.position === "right"
								? "16px 0 0 16px"
								: "0 16px 16px 0"
						)}
        }

        .noti-count-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #ff4444;
            color: white;
            border-radius: 12px;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            ${getCssPrefixes("transform", "scale(1)")}
            ${getCssPrefixes("transition", "transform 0.2s ease")}
        }

        .custom-noti-toggle:hover .noti-count-badge {
            ${getCssPrefixes("transform", "scale(1.1)")}
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
            background-color: ${finalConfig.colors.light};
            color: #1a1a1a;
        }

        .custom-noti-panel.dark {
            --panel-bg: ${finalConfig.colors.dark};
            --text-color: #ffffff;
            --border-color: rgba(255, 255, 255, 0.1);
            --hover-bg: rgba(255, 255, 255, 0.1);
            background-color: ${finalConfig.colors.dark};
            color: #ffffff;
        }

        .custom-noti-panel.show {
            ${finalConfig.position}: 0;
        }

        /* Bootstrap compatibility classes */
        .custom-noti-panel .btn-close {
            ${bootstrapVersion >= 5 ? "opacity: 0.75;" : ""}
        }

        .custom-noti-toggle {
            position: absolute;
            top: 20px;
            ${
							finalConfig.position === "right" ? "left: -56px" : "right: -56px"
						};
            width: 56px;
            height: 56px;
            background: #1a1a1a;
            border: none;
            color: #ffffff;
            ${getCssPrefixes(
							"border-radius",
							finalConfig.position === "right"
								? "12px 0 0 12px"
								: "0 12px 12px 0"
						)}
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            ${getCssPrefixes("transition", "all 0.3s ease")}
            ${getCssPrefixes("box-shadow", "0 4px 12px rgba(0, 0, 0, 0.1)")}
            ${getCssPrefixes("transform", "translateZ(0)")}
        }

        .custom-noti-toggle svg {
            ${getCssPrefixes("transition", "transform 0.3s ease")}
        }

        .custom-noti-toggle:hover svg {
            ${getCssPrefixes("transform", "scale(1.2) rotate(15deg)")}
        }

        .custom-noti-panel.show .custom-noti-toggle {
            background: var(--panel-bg, ${finalConfig.colors.light});
            color: var(--text-color, #1a1a1a);
        }

        .custom-noti-header {
            position: sticky;
            top: 0;
            background: var(--panel-bg, ${finalConfig.colors.light});
            padding: ${bootstrapVersion >= 4 ? "1rem 1.5rem" : "15px 20px"};
            border-bottom: 1px solid var(--border-color, rgba(0, 0, 0, 0.1));
            z-index: ${baseZIndex + 1};
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-noti-header h5 {
            font-size: ${bootstrapVersion >= 4 ? "1.25rem" : "18px"};
            font-weight: 600;
            color: var(--text-color, #1a1a1a);
            margin: 0;
        }

        .custom-noti-close {
            background: none;
            border: none;
            color: var(--text-color, #1a1a1a);
            cursor: pointer;
            padding: 8px;
            ${getCssPrefixes("transition", "all 0.2s ease")}
            ${getCssPrefixes("border-radius", "8px")}
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-noti-close:hover {
            background-color: var(--hover-bg, rgba(0, 0, 0, 0.05));
        }

        .custom-noti-body {
            height: calc(100% - ${bootstrapVersion >= 4 ? "82px" : "72px"});
            overflow-y: auto;
            padding: ${bootstrapVersion >= 4 ? "1.25rem 1.5rem" : "15px 20px"};
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
            ${getCssPrefixes("transition", "all 0.3s ease")}
            z-index: ${baseZIndex - 1};
        }

        .notification-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 576px) {
            .custom-noti-panel {
                width: 100% !important;
                height: 100% !important;
                top: 0 !important;
                ${getCssPrefixes("border-radius", "0")}
            }

            .custom-noti-toggle {
                top: 50%;
                ${getCssPrefixes("transform", "translateY(-50%)")}
            }
        }
    `;
	document.head.appendChild(style);

	// Get close button HTML
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
	const overlay = document.createElement("div");
	overlay.className = "notification-overlay";
	document.body.appendChild(overlay);

    // Update the toggle button creation to include formatted count
    const getToggleButton = () => {
        const countBadge = finalConfig.count !== null ? 
            `<span class="noti-count-badge">${finalConfig.count}</span>` : '';
        
        return `
            <button class="custom-noti-toggle" id="notiToggleBtn" aria-label="Toggle notifications">
                ${getIconSvg()}
                ${countBadge}
            </button>
        `;
    };

	// Create panel
	const panel = document.createElement("div");
	panel.id = "customNotiPanel";
	panel.className = `custom-noti-panel ${getCurrentTheme()}`;

	panel.innerHTML = `
        ${getToggleButton()}
        <div class="custom-noti-header">
            <h5>${finalConfig.title}</h5>
            ${getCloseButton()}
        </div>
        <div class="custom-noti-body" id="notiContent">
        </div>
    `;

	document.body.appendChild(panel);

	// Handle content
	const contentContainer = document.getElementById("notiContent");
	if (typeof dataDisplay === "function") {
		contentContainer.innerHTML = dataDisplay() || "";
	} else {
		contentContainer.innerHTML = dataDisplay;
	}

	// Theme change listener
	if (finalConfig.theme === "system") {
		const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
		const themeChangeHandler = (e) => {
			panel.className = `custom-noti-panel ${e.matches ? "dark" : "light"}`;
		};

		if (mediaQuery.addEventListener) {
			mediaQuery.addEventListener("change", themeChangeHandler);
		} else if (mediaQuery.addListener) {
			mediaQuery.addListener(themeChangeHandler);
		}
	}

	const togglePanel = () => {
		panel.classList.toggle("show");
		overlay.classList.toggle("show");

		if (!panel.classList.contains("show")) {
			setTimeout(() => {
				if (!panel.classList.contains("show")) {
					overlay.classList.remove("show");
				}
			}, 300);
		}

		// Handle Bootstrap modal compatibility
		const body = document.body;
		if (body.classList.contains("modal-open")) {
			body.style.paddingRight = panel.classList.contains("show") ? "0" : "";
		}
	};

	// Event listeners with IE11 compatibility
	const toggleBtn = document.getElementById("notiToggleBtn");
	const closeBtn = panel.querySelector(".custom-noti-close");

	toggleBtn.addEventListener("click", (e) => {
		e.stopPropagation();
		togglePanel();
	});

	closeBtn.addEventListener("click", togglePanel);

	overlay.addEventListener("click", () => {
		if (panel.classList.contains("show")) {
			togglePanel();
		}
	});

	// Handle escape key
	document.addEventListener("keydown", (e) => {
		e = e || window.event; // IE11 compatibility
		if (
			(e.key === "Escape" || e.key === "Esc") &&
			panel.classList.contains("show")
		) {
			togglePanel();
		}
	});

    // Update count function with formatting
    const updateCount = (newCount) => {
        const formattedCount = formatCount(newCount);
        if (formattedCount === null && newCount !== null) {
            console.warn('Invalid count value. Count must be a non-negative integer.');
            return;
        }
        
        const badge = panel.querySelector('.noti-count-badge');
        if (formattedCount === null) {
            badge?.remove();
        } else {
            if (badge) {
                badge.textContent = formattedCount;
            } else {
                const toggle = panel.querySelector('.custom-noti-toggle');
                const newBadge = document.createElement('span');
                newBadge.className = 'noti-count-badge';
                newBadge.textContent = formattedCount;
                toggle.appendChild(newBadge);
            }
        }
    };

	// Clean up function
	const cleanup = () => {
		if (finalConfig.theme === "system") {
			const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
			if (mediaQuery.removeEventListener) {
				mediaQuery.removeEventListener("change", themeChangeHandler);
			} else if (mediaQuery.removeListener) {
				mediaQuery.removeListener(themeChangeHandler);
			}
		}
		document.removeEventListener("keydown", handleEscKey);
		panel.remove();
		overlay.remove();
		style.remove();
	};

	// Return public methods
	return {
		open: () => {
			if (!panel.classList.contains("show")) {
				togglePanel();
			}
		},
		close: () => {
			if (panel.classList.contains("show")) {
				togglePanel();
			}
		},
		toggle: togglePanel,
		isOpen: () => panel.classList.contains("show"),
		getBootstrapVersion: () => bootstrapVersion,
		updateCount,
		destroy: cleanup,
	};
};