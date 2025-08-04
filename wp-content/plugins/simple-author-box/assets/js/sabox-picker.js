/**
  * SAB
  * (c) WebFactory Ltd, 2016 - 2021
  */

var canClick = false;

function activateSABVisualPicker($) {
	jQuery.fn.extend({
		getPath: function () {
			var path,
				node = this,
				realNode,
				name,
				parent,
				index,
				sameTagSiblings,
				allSiblings,
				className,
				classSelector,
				nestingLevel = true;

			while (node.length && nestingLevel) {
				realNode = node[0];
				name = realNode.localName;

				if (!name) break;

				name = name.toLowerCase();
				parent = node.parent();
				sameTagSiblings = parent.children(name);

				if (realNode.id) {
					name += "#" + node[0].id;

					nestingLevel = false;

				} else if (realNode.className.length) {
                    className =  realNode.className.trim().split(' ');
					classSelector = '';

					className.forEach(function (item) {
						classSelector += '.' + item;
					});

					name += classSelector;

				} else if (sameTagSiblings.length > 1) {
					allSiblings = parent.children();
					index = allSiblings.index(realNode) + 1;

					if (index > 1) {
						name += ':nth-child(' + index + ')';
					}
				}

				path = name + (path ? '>' + path : '');
				node = parent;
			}

			return path;
		}
	});

	function handlerMouseEnter(ev) {
        var target = $(ev.target);

		var pathToSelect = target.getPath();

		$(pathToSelect).addClass("sabox-visual-picker-selected");
		$(pathToSelect).mouseout(handlerMouseLeave);
		$(pathToSelect).click(handlerClick);

		window.top.postMessage({
			messageType: 'sabox-iframe',
			pathToSelect: pathToSelect
		}, '*');
	}

	function handlerMouseLeave(ev) {
		$(ev.target).removeClass("sabox-visual-picker-selected");
	}

	function handlerClick(ev) {
		if (!canClick) {
			ev.preventDefault();

			window.top.postMessage({
				messageType: 'sabox-close-iframe'
			}, '*');
		}
	}

	$("body").mouseover(handlerMouseEnter);
}

(function($) {
	$(document).ready(function($) {
        if (sabox.visual_picker) {
            activateSABVisualPicker($);

            $('body').append('<style>.sabox-visual-picker-selected { outline: 3px dashed #007cba; outline-offset: -3px; } </style>');

            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('sabox-can-click')){
                canClick = urlParams.get('sabox-can-click');
            }

            $('a').on('click', function(){
                var href = new URL($(this).attr('href'));
                href.searchParams.set('sabox-disable-admin-bar', true);
                href.searchParams.set('sabox-can-click', canClick);
                $(this).attr('href', href.toString());
            });
			window.onmessage = function(e){
				if (e.data.messageType == 'sabox-tick') {
                    canClick = e.data.canClick;
				}
			};
		}
	});
}(jQuery));
