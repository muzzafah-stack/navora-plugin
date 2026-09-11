/**
 * Navora Public Scripts - Vanilla JS for maximum compatibility and lightweight footprint.
 */
document.addEventListener('DOMContentLoaded', function() {
	var body = document.body;
	var header = document.querySelector('.navora-header-nav');
	var burgerBtn = document.querySelector('.navora-burger-btn');
	var closeBtn = document.querySelector('.navora-close-btn');
	var overlay = document.querySelector('.navora-drawer-overlay');
	var mobileMenu = document.querySelector('.navora-mobile-menu');

	// Sticky Header dynamic class helper
	if (header && header.classList.contains('navora-sticky')) {
		var handleScroll = function() {
			if (window.scrollY > 80) {
				header.classList.add('navora-scrolled');
			} else {
				header.classList.remove('navora-scrolled');
			}
		};
		window.addEventListener('scroll', handleScroll);
		handleScroll(); // Run immediately on load.
	}

	// Mobile Drawer Triggers
	var closeDrawer = function() {
		body.classList.remove('navora-drawer-open');
		var openBurgers = document.querySelectorAll('.navora-burger-btn');
		openBurgers.forEach(function(btn) {
			btn.setAttribute('aria-expanded', 'false');
		});
	};

	document.addEventListener('click', function(e) {
		var burger = e.target.closest('.navora-burger-btn');
		if (burger) {
			e.preventDefault();
			body.classList.add('navora-drawer-open');
			burger.setAttribute('aria-expanded', 'true');
			return;
		}

		var close = e.target.closest('.navora-close-btn') || e.target.closest('.navora-drawer-overlay');
		if (close) {
			e.preventDefault();
			closeDrawer();
			return;
		}
	});

	// Close menu on pressing Escape key (A11y)
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && body.classList.contains('navora-drawer-open')) {
			closeDrawer();
		}
	});

	// Inject and handle mobile sub-menu dropdown toggle arrows
	var mobileMenus = document.querySelectorAll('.navora-mobile-menu');
	mobileMenus.forEach(function(menu) {
		var parentItems = menu.querySelectorAll('.menu-item-has-children');
		
		parentItems.forEach(function(item) {
			// Find primary link inside parent item
			var link = item.querySelector('a');
			if (!link || item.querySelector('.navora-submenu-toggle')) return;

			// Create toggle button
			var toggleBtn = document.createElement('button');
			toggleBtn.className = 'navora-submenu-toggle';
			toggleBtn.setAttribute('type', 'button');
			toggleBtn.setAttribute('aria-label', 'Toggle submenu');
			toggleBtn.setAttribute('aria-expanded', 'false');

			// Append toggle button inside list item
			item.insertBefore(toggleBtn, link.nextSibling);

			// Handle toggle click
			toggleBtn.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var subMenu = item.querySelector('.sub-menu');
				if (!subMenu) return;

				var isToggled = toggleBtn.classList.contains('toggled');

				if (isToggled) {
					// Hide submenu
					subMenu.style.display = 'none';
					toggleBtn.classList.remove('toggled');
					toggleBtn.setAttribute('aria-expanded', 'false');
				} else {
					// Show submenu
					subMenu.style.display = 'block';
					toggleBtn.classList.add('toggled');
					toggleBtn.setAttribute('aria-expanded', 'true');
				}
			});
		});
	});
});
