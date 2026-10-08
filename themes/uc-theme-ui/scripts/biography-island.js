(function () {
	var NARROW = '(max-width: 600px), (orientation: portrait)';
	var MIN_SIDE = 8;

	function sourceText(p) {
		p.querySelectorAll('.bio-shim').forEach(function (shim) {
			shim.remove();
		});
		if (!p.dataset.bioSource) {
			p.dataset.bioSource = p.textContent.replace(/\s+/g, ' ').trim();
		}
		return p.dataset.bioSource;
	}

	function pxVar(row, name) {
		var probe = document.createElement('div');
		probe.style.cssText = 'position:absolute;visibility:hidden;height:var(' + name + ');width:0;';
		row.appendChild(probe);
		var px = probe.getBoundingClientRect().height;
		probe.remove();
		return px;
	}

	function pack(words, max, widthOf) {
		var line = [];
		var limit = Math.max(0, max - 2);
		while (words.length) {
			var trial = line.concat(words[0]).join(' ');
			if (line.length && widthOf(trial) > limit) {
				break;
			}
			line.push(words.shift());
			if (widthOf(line.join(' ')) > limit) {
				break;
			}
		}
		return line.join(' ');
	}

	function teardown(row) {
		row.classList.remove('is-island');
		var flow = row.querySelector('.bio-flow');
		if (flow) {
			flow.remove();
		}
		var figure = row.querySelector('figure.wrap-photos');
		if (figure) {
			figure.style.top = '';
			figure.style.width = '';
			figure.style.maxWidth = '';
		}
	}

	function layoutRow(row) {
		window.__bio = window.__bio || [];
		var p = row.querySelector('p');
		var figure = row.querySelector('figure.wrap-photos');
		if (!p || !figure) {
			window.__bio.push('missing');
			return;
		}

		var img = figure.querySelector('img');
		if (img && !img.complete) {
			window.__bio.push('wait-img');
			img.addEventListener('load', function () { layoutRow(row); }, { once: true });
			img.addEventListener('error', function () { layoutRow(row); }, { once: true });
			return;
		}

		teardown(row);

		if (window.matchMedia(NARROW).matches) {
			window.__bio.push('narrow');
			return;
		}

		var text = sourceText(p);
		var words = text.split(/\s+/).filter(Boolean);
		if (!words.length) {
			window.__bio.push('no-words');
			return;
		}

		var pcs = window.getComputedStyle(p);
		var flow = document.createElement('div');
		flow.className = 'bio-flow';
		flow.setAttribute('aria-hidden', 'true');
		flow.style.font = pcs.font;
		flow.style.letterSpacing = pcs.letterSpacing;
		flow.style.wordSpacing = pcs.wordSpacing;
		row.appendChild(flow);

		var measurer = document.createElement('span');
		measurer.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;left:0;top:0;';
		flow.appendChild(measurer);

		function widthOf(str) {
			measurer.textContent = str || '';
			return measurer.getBoundingClientRect().width;
		}

		var lineProbe = document.createElement('div');
		lineProbe.textContent = 'Mg';
		flow.appendChild(lineProbe);
		var lineHeight = lineProbe.getBoundingClientRect().height || parseFloat(pcs.fontSize) * 1.4;
		lineProbe.remove();

		var flowStyle = window.getComputedStyle(flow);
		var inner = flow.clientWidth - parseFloat(flowStyle.paddingLeft) - parseFloat(flowStyle.paddingRight);
		var gap = pxVar(row, '--bio-gap');
		if (!gap) {
			gap = 12;
		}
		var imgW = pxVar(row, '--bio-img-w');
		if (imgW) {
			figure.style.width = imgW + 'px';
			figure.style.maxWidth = '100%';
		}
		var holeW = figure.getBoundingClientRect().width + gap * 2;
		var sideW = (inner - holeW) / 2;
		var minSide = widthOf(new Array(MIN_SIDE + 1).join('n'));

		if (sideW < minSide) {
			window.__bio.push('side ' + Math.round(sideW) + '<' + Math.round(minSide) + ' inner ' + Math.round(inner) + ' hole ' + Math.round(holeW));
			flow.remove();
			return;
		}

		var abovePx = pxVar(row, '--bio-island-above');
		var linesAbove = Math.max(0, Math.floor(abovePx / lineHeight));
		var linesBeside = Math.max(1, Math.ceil(figure.getBoundingClientRect().height / lineHeight));
		var lines = [];

		var i;
		for (i = 0; i < linesAbove && words.length; i++) {
			lines.push({ type: 'full', text: pack(words, inner, widthOf) });
		}
		for (i = 0; i < linesBeside; i++) {
			lines.push({
				type: 'split',
				left: words.length ? pack(words, sideW, widthOf) : '',
				right: words.length ? pack(words, sideW, widthOf) : ''
			});
		}
		while (words.length) {
			lines.push({ type: 'full', text: pack(words, inner, widthOf) });
		}

		var last = -1;
		lines.forEach(function (line, index) {
			var has = line.type === 'full' ? line.text : (line.left || line.right);
			if (has) {
				last = index;
			}
		});

		measurer.remove();
		flow.style.setProperty('--bio-hole-w', holeW + 'px');
		flow.style.setProperty('--bio-line-h', lineHeight + 'px');

		lines.forEach(function (line, index) {
			var el = document.createElement('div');
			el.className = 'bio-line' + (line.type === 'split' ? ' bio-line--split' : '');
			if (index === last) {
				el.classList.add('is-last');
			}
			if (line.type === 'full') {
				el.textContent = line.text;
			} else {
				var left = document.createElement('span');
				left.className = 'bio-side bio-side--left';
				left.textContent = line.left;
				var hole = document.createElement('span');
				hole.className = 'bio-hole';
				hole.setAttribute('aria-hidden', 'true');
				var right = document.createElement('span');
				right.className = 'bio-side bio-side--right';
				right.textContent = line.right;
				if (index === last) {
					right.classList.add('is-last');
				}
				el.appendChild(left);
				el.appendChild(hole);
				el.appendChild(right);
			}
			flow.appendChild(el);
		});

		row.classList.add('is-island');
		window.__bio.push('ok ' + row.className);

		var firstSplit = flow.querySelector('.bio-line--split');
		if (firstSplit) {
			var rowRect = row.getBoundingClientRect();
			var lineRect = firstSplit.getBoundingClientRect();
			var borderTop = parseFloat(window.getComputedStyle(row).borderTopWidth) || 0;
			figure.style.top = (lineRect.top - rowRect.top - borderTop) + 'px';
		}
	}

	function run() {
		try {
			document.querySelectorAll('.biographies .bio-row').forEach(layoutRow);
		} catch (err) {
			window.__bio = window.__bio || [];
			window.__bio.push('throw ' + err.message);
		}
	}

	var queued = false;
	function schedule() {
		if (queued) {
			return;
		}
		queued = true;
		window.requestAnimationFrame(function () {
			queued = false;
			run();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', schedule);
	} else {
		schedule();
	}

	window.addEventListener('resize', schedule);
	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(schedule);
	}
	if (window.matchMedia) {
		window.matchMedia(NARROW).addEventListener('change', schedule);
	}

	if (window.MutationObserver) {
		var watch = new MutationObserver(function (mutations) {
			var relevant = mutations.some(function (mutation) {
				var target = mutation.target;
				if (!target || !target.tagName) {
					return false;
				}
				if (mutation.attributeName === 'class' && target.tagName === 'FIGURE') {
					return true;
				}
				return mutation.attributeName === 'style' && target.tagName === 'IMG';
			});
			if (relevant) {
				schedule();
			}
		});
		function observeRows() {
			document.querySelectorAll('.biographies .bio-row').forEach(function (row) {
				if (row.dataset.bioWatch) {
					return;
				}
				row.dataset.bioWatch = '1';
				watch.observe(row, {
					subtree: true,
					attributes: true,
					attributeFilter: ['class', 'style']
				});
			});
		}
		observeRows();
		document.addEventListener('DOMContentLoaded', observeRows);
	}
})();
