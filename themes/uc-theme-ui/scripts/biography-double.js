(function () {
	function sideCap(img) {
		var cs = window.getComputedStyle(img);
		return img.offsetHeight
			+ (parseFloat(cs.marginTop) || 0)
			+ (parseFloat(cs.marginBottom) || 0);
	}

	function reflow(root) {
		var img = root.querySelector('.side--left img.img');
		var col2p = root.querySelector('.col2 .inner p');
		if (!img || !col2p) {
			return;
		}

		root.querySelectorAll('.side .inner p, .col2 .inner p').forEach(function (p) {
			if (!p.dataset.original) {
				p.dataset.original = p.textContent.trim();
			}
			p.textContent = p.dataset.original;
		});

		var cap = sideCap(img);
		var overflow = [];

		root.querySelectorAll('.side').forEach(function (side) {
			var p = side.querySelector('.inner p');
			if (!p) {
				return;
			}
			var words = p.textContent.trim().split(/\s+/).filter(Boolean);
			while (words.length && side.offsetHeight > cap) {
				overflow.unshift(words.pop());
				p.textContent = words.join(' ');
			}
		});

		if (overflow.length) {
			col2p.textContent = overflow.join(' ') + ' ' + col2p.textContent;
		}
	}

	function run() {
		document.querySelectorAll('.uc-biography-double').forEach(reflow);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
	window.addEventListener('resize', run);
})();
