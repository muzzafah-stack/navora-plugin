/**
 * Navora Public Navigation Scripts
 * Performance & Cache-First Architecture (Perfmatters, FlyingPress, Cloudflare, WP Rocket compatible).
 */
(function() {
	'use strict';

	// Helper to close drawer
	function closeDrawer() {
		document.body.classList.remove('navora-drawer-open');
		var burgers = document.querySelectorAll('.navora-burger-btn');
		for (var i = 0; i < burgers.length; i++) {
			burgers[i].setAttribute('aria-expanded', 'false');
		}
	}

	// Scroll handler for sticky header elevation styling
	function updateStickyState() {
		var headers = document.querySelectorAll('.navora-header-nav.navora-sticky');
		if (!headers.length) return;

		var isScrolled = (window.pageYOffset || document.documentElement.scrollTop) > 40;
		for (var i = 0; i < headers.length; i++) {
			if (isScrolled) {
				headers[i].classList.add('navora-scrolled');
			} else {
				headers[i].classList.remove('navora-scrolled');
			}
		}
	}

	// Global delegated click handler (Resilient across delay-js and cached HTML)
	document.addEventListener('click', function(e) {
		// 1. Burger Toggle
		var burger = e.target.closest('.navora-burger-btn');
		if (burger) {
			e.preventDefault();
			var isOpen = document.body.classList.toggle('navora-drawer-open');
			burger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			return;
		}

		// 2. Close Drawer Buttons & Overlay
		var close = e.target.closest('.navora-close-btn, .navora-drawer-overlay');
		if (close) {
			e.preventDefault();
			closeDrawer();
			return;
		}

		// 3. Mobile Submenu Toggle Arrow
		var toggleBtn = e.target.closest('.navora-submenu-toggle');
		if (toggleBtn) {
			e.preventDefault();
			e.stopPropagation();

			var parentItem = toggleBtn.closest('.menu-item');
			if (!parentItem) return;

			var subMenu = parentItem.querySelector('.sub-menu');
			var isToggled = toggleBtn.classList.toggle('toggled');
			parentItem.classList.toggle('is-open', isToggled);
			toggleBtn.setAttribute('aria-expanded', isToggled ? 'true' : 'false');

			if (subMenu) {
				subMenu.classList.toggle('is-open', isToggled);
				subMenu.style.display = isToggled ? 'block' : 'none';
			}
			return;
		}

		// 4. In-page anchor click inside drawer should close drawer
		var drawerLink = e.target.closest('.navora-mobile-drawer a[href^="#"]');
		if (drawerLink && drawerLink.getAttribute('href') !== '#') {
			closeDrawer();
		}
	});

	// A11y: Close on Escape key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && document.body.classList.contains('navora-drawer-open')) {
			closeDrawer();
		}
	});

	// Attach passive scroll listener
	window.addEventListener('scroll', updateStickyState, { passive: true });

	// Initialize immediately and on DOM load/ready
	function initNavora() {
		updateStickyState();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initNavora);
	} else {
		initNavora();
	}

	// Elementor frontend hook support (for editor live preview mode)
	function bindElementorFrontend() {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/global', initNavora);
			window.elementorFrontend.hooks.addAction('frontend/element_ready/navora_menu.default', initNavora);
		}
	}

	bindElementorFrontend();
	window.addEventListener('elementor/frontend/init', bindElementorFrontend);
	if (window.jQuery) {
		window.jQuery(window).on('elementor/frontend/init', bindElementorFrontend);
	}
})();
