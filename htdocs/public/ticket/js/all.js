;(function ($) {
	'use strict';

	function animation() {
		if ($("[data-cs-st]:not(.animated)").length) {
			$("[data-cs-st]:not(.animated)").each(function () {
				const element   = $(this);

				element.waypoint(function () {
					const duration  = +element.data('cs-du') || 350,
						  delay     = +element.data('cs-de') || 0,
						  animation = element.data('cs-st');

					if (!isNaN(duration)) {
						element.css({
							'animation-duration': duration + 'ms',
							'transition-duration': duration + 'ms'
						})
					}

					element
						.addClass(animation);

					if (element.hasClass('swiper-slide') && element.hasClass('swiper-slide-visible') || !element.hasClass('swiper-slide' || !element.hasClass('js-portfolio-item'))) {
						if (!isNaN(delay)) {
							element.css({
								'animation-delay': delay + 'ms',
								'transition-delay': delay + 'ms'
							})
						}

						element
							.addClass("animated");
					}

				}, {
					offset: "85%"
				});
			});
		}
	}

	$(window).on('load', function () {
		animation();
	});
})(jQuery, window, document);
;;(function ($, window) {
	'use strict';

	const comingSoonElements = $('.js-coming-desc');

	const getTimeRemaining = (endtime) => {
		const t       = Date.parse(endtime) - Date.parse(new Date());
		const seconds = Math.floor((t / 1000) % 60);
		const minutes = Math.floor((t / 1000 / 60) % 60);
		const hours   = Math.floor((t / (1000 * 60 * 60)) % 24);
		const days    = Math.floor(t / (1000 * 60 * 60 * 24));

		return {
			'total': t,
			'days': days,
			'hours': hours,
			'minutes': minutes,
			'seconds': seconds
		};
	};

	const updateClock = ($clock, endTime, updateDays) => {
		const t = getTimeRemaining(endTime);

		if (updateDays) {
			$clock.find('.js-coming-soon__count--days').text(t.days);
		}

		if (updateDays || t.minutes === 59) {
			$clock.find('.js-coming-soon__count--hours').text(('0' + t.hours).slice(-2));
		}

		if (updateDays || t.seconds === 59) {
			$clock.find('.js-coming-soon__count--mins').text(('0' + t.minutes).slice(-2));
		}

		$clock.find('.js-coming-soon__count--secs').text(('0' + t.seconds).slice(-2));

		if (t.total <= 0) {
			clearInterval(timeinterval);
		}
	};

	const comingSoonValue = () => {
		if (comingSoonElements.length) {
			comingSoonElements.each(function () {
				const thisElement = $(this),
					text        = thisElement.data('desktop'),
					mobileText  = thisElement.data('mobile');

				if ($(window).width() < 768) {
					thisElement.text(mobileText);
				} else {
					thisElement.text(text);
				}
			})
		}
	};

	if ($('.js-coming-soon').length) {
		$('.js-coming-soon').each(function () {
			const self    = $(this),
				endTime = self.attr('data-end');

			updateClock(self, endTime, true);
			setInterval(function () {
				updateClock(self, endTime)
			}, 1000);
		});
	}

	$(window).on('load resize', () => {
		comingSoonValue();
	});

})(jQuery, window, document);
;;(function ($, window) {
	'use strict';

	const breakpoints = [1200, 991, 768];

	const setPosition = () => {
		$('.js-custom-media').each(function () {
			let position, max_width;

			if ( $(window).width() >= breakpoints[0] ) {
				position = $(this).data( 'xl-size' ) || null;
			} else if ( $(window).width() >= breakpoints[1] ) {
				position = $(this).data( 'lg-size' ) || null;
			} else if ( $(window).width() >= breakpoints[2] ) {
				position = $(this).data( 'md-size' ) || null;
			} else if ( $(window).width() < breakpoints[2] ) {
				position = $(this).data( 'sm-size' ) || null;
			}

			if( position !== null ) {
				position = position.split(' ');
			} else {
				position = [0, 0, 0, 0];
			}

			function getSpaceValue(positionOne, positionTwo) {
				var value = +position[positionOne] + +position[positionTwo],
					operation = isNaN( parseInt( value.toString().charAt(0) ) ) ? '+' : '-',
					space = (operation == '+') ? value.toString().slice(1) : value;

				return {
					oparation: operation,
					space: space
				};
			}

			$(this).css({
				'top' : position[0] + 'px',
				'right' : position[1] + 'px',
				'bottom' : position[2] + 'px',
				'left' : position[3] + 'px',
				'width' : 'calc(100% ' + getSpaceValue(1, 3).oparation + ' ' + getSpaceValue(1, 3).space + 'px)',
				'height' : 'calc(100% ' + getSpaceValue(0, 2).oparation + ' ' + getSpaceValue(0, 2).space + 'px)'
			});

			if ( $(window).width() < breakpoints[2] ) {
				max_width = $(this).data( 'max-width' ) || null;
				$(this).children().css('width', max_width + 'px');
			} else {
				$(this).children().css('width', '100%');
			}
		});
	};

	$(window).on('load resize', () => {
		setPosition();
	});

})(jQuery, window, document);;;(function () {
    'use strict';

    jQuery(function() {
        if( jQuery(".wpb-date").length ){
            jQuery(".wpb-date").datetimepicker({dateFormat: 'yy/mm/dd'});
        }
    });

})(jQuery, window, document);



;;(function ($, window) {
	'use strict';

	/*=================================*/
	/* HEADER SCROLL */
	/*=================================*/
	const headerScrollHandler = () => {
		if ($('.cs-header-t').length) {
			if ($(window).scrollTop() >= 30) {
				$('.cs-header-t').addClass('cs-header-bg');
			} else {
				$('.cs-header-t').removeClass('cs-header-bg');
			}
		}
	};

	const fixedMobileMenu = () => {
		const adminBarHeight = $('#wpadminbar').outerHeight();
		$('.cs-header-t, .cs-header-f, .cs-header__inner').css('top', adminBarHeight);
	};

	const menuCloseHandler = () => {
		$('.js-header').removeClass('is-opened');
	};

	const menuOpenHandler = () => {
		$('.js-header').addClass('is-opened');
	};

	$('.js-menu-toggle').on('click', () => {
		if ($('.js-header').hasClass('is-opened')) {
			menuCloseHandler();
		} else {
			menuOpenHandler();
		}
	});

	function activeSection() {
		if ($('.vc_row[id], .vc_section[id]').length) {
			const winT = $(window).scrollTop();
			$('.vc_row[id], .vc_section[id]').each(function () {
				const $this     = $(this);
				const currentId = $this.attr('id');
				if (currentId.length > 2) {
					if (winT >= $(this).offset().top - $('#wpadminbar').outerHeight() - $('.cs-header-t, .cs-header-f').outerHeight()) {
						const reqLink = $('.menu > li > a[href="#' + currentId + '"]');
						reqLink.parent('li').addClass('is-active').siblings().removeClass('is-active');
					}
				}
			});
		}
	}

	$('a[href^="#"]').on('click', function (e) {
		e.preventDefault();
		const elem = $(this).attr('href');

		if ($(elem).length) {
			$('html,body').animate({
				scrollTop: $(elem).offset().top - $('.cs-header-t, .cs-header-f').outerHeight() - $('#wpadminbar').outerHeight()
			}, 'slow');
			menuCloseHandler();
		}
	});

	function menuArrows() {
		if (window.outerWidth <= 1024) {
			if (!$('.menu-item-has-children i').length) {
				$('header .menu-item-has-children').append('<i class="fa fa-angle-down js-btn-mobile"></i>');
			}

			$('.js-btn-mobile').on('click', function () {
				const animationDuration = 350;

				if ($(this).hasClass('animation')) {
					return;
				}

				$(this)
					.addClass('animation')
					.prev('.sub-menu').slideToggle(animationDuration)
					.parent().toggleClass('is-opened')
					.siblings().removeClass('is-opened')
					.find('.sub-menu').slideUp(animationDuration);

				setTimeout(() => {
					$('.js-btn-mobile').removeClass('animation');
				}, animationDuration);
			});
		} else {
			$('.js-btn-mobile').remove();
		}
	}

	function adminBarState() {
		if ($('#wpadminbar').length) {
			$('#wpadminbar').css('position', 'fixed');
		}
	}

	$(window).on('load', () => {
		adminBarState();
	});

	$(window).on('load resize', () => {
		fixedMobileMenu();
		menuArrows();
	});

	$(window).on('load scroll', () => {
		headerScrollHandler();
		activeSection();
	});

	window.addEventListener("orientationchange", function () {
		headerScrollHandler();
		fixedMobileMenu();
		menuArrows();
		activeSection();
	});
})(jQuery, window, document);
;;(function ($, window, document) {
	'use strict';

	/*=================================*/
	/* SHARE POPUP */
	/*=================================*/
	$('[data-share]').on('click', function (e) {
		e.preventDefault();

		const w           = window,
			  url         = this.getAttribute('data-share'),
			  title       = '',
			  w_pop       = 600,
			  h_pop       = 600,
			  screen_left = w.screenLeft ? w.screenLeft : screen.left,
			  screen_top  = w.screenTop ? w.screenTop : screen.top,
			  width       = w.innerWidth,
			  height      = w.innerHeight,
			  left        = ((width / 2) - (w_pop / 2)) + screen_left,
			  top         = ((height / 2) - (h_pop / 2)) + screen_top,
			  newWindow   = w.open(url, title, 'scrollbars=yes, width=' + w_pop + ', height=' + h_pop + ', top=' + top + ', left=' + left);

		if (w.focus) {
			newWindow.focus();
		}

		return false;
	});

	/*=================================*/
	/* FULL HEIGHT BANNER */
	/*=================================*/
	const topBannerHeight = () => {
		const headerHeight = $(".js-header").not('.cs-header-t, .cs-header-f, .cs-header-a').outerHeight() || 0;
		const offsetTop    = headerHeight + $('#wpadminbar').outerHeight();
		const windowH      = $(window).height();

		$('.js-full-height').css('min-height', (windowH - offsetTop) + 'px');
	};

	/*=================================*/
	/* COUNTER */
	/*=================================*/
	const counter = () => {
		if ($('.js-counter').length) {
			$('.js-counter').not('.is-complete').each(function () {
				if ($(window).scrollTop() >= $(this).offset().top - $(window).height() * 1) {
					$(this).countTo().addClass('is-complete');
				}
			});
		}
	};

	/*=================================*/
	/* SKILL */
	/*=================================*/
	const skill = () => {
		if ($('.js-skill').length) {
			$('.js-skill').not('.is-complete').each(function () {
				if ($(window).scrollTop() >= $(this).offset().top - $(window).height() * 1) {
					const level = +$(this).data('skill');

					if (!isNaN(level)) {
						$(this).css('width', `${level}%`).addClass('is-complete');
					}
				}
			});
		}
	};

	/*=================================*/
	/* VIDEO POPUP */

	/*=================================*/
	function videoPopup() {
		$('.js-video-play').not('.is-magnific').each(function () {
			$(this).addClass('is-magnific').magnificPopup({
				disableOn: 700,
				type: 'iframe',
				mainClass: 'mfp-fade',
				removalDelay: 160,
				preloader: false,
				fixedContentPos: true,
				fixedBgPos: true
			});
		});
	}

	/*=================================*/
	/* IS TOUCH DEVICE */
	/*=================================*/
	const isTouchDevice = () => 'ontouchstart' in document.documentElement;

	/*=================================*/
	/* SWIPER SLIDER */
	/*=================================*/
	const swipers       = [];
	let swiperIteration = 0;
	const initSwiper    = () => {
		$('.swiper-container:not(.initialized)').each(function () {
			const $t = $(this);

			const index = 'swiper-id-' + swiperIteration;
			$t.addClass(index + ' initialized').attr('id', index);
			$t.parent().parent().find('.swiper-pagination').addClass('swiper-pagination-' + index);
			$t.parent().parent().find('.swiper-button-next').addClass('swiper-button-next-' + index);
			$t.parent().parent().find('.swiper-button-prev').addClass('swiper-button-prev-' + index);

			if (isTouchDevice() && $t.data('mode') == 'vertical') {
				$t.data('noswiping', 1);
				$(this).find('.swiper-slide').addClass('swiper-no-swiping');
			}

			// string variables
			const paginationType = $t.data('pagination-type') || 'bullets';
			const direction      = $t.data('mode') || 'horizontal';
			const effect         = $t.data('effect') || 'slide';

			// bool variables
			const noSwiping           = $t.data('noswiping') ? !!$t.data('noswiping') : true;
			const responsive          = $t.data('responsive') ? !!$t.data('responsive') : false;
			const grabCursor          = $t.data('grab-cursor') ? !!$t.data('grab-cursor') : false;
			const loop                = $t.data('loop') ? !!$t.data('loop') : false;
			const mousewheel          = $t.data('mouse') ? !!$t.data('mouse') : false;
			const centeredSlides      = $t.data('center') ? !!$t.data('center') : false;
			const slideToClickedSlide = $t.data('slide-to-clicked') ? !!$t.data('slide-to-clicked') : false;
			const heightVar           = $t.data('height') ? !!$t.data('height') : false;
			let lazyVar               = $t.data('lazy') ? !!$t.data('lazy') : false;

			// number variables
			const autoPlayVar     = +$t.data('autoplay') || 0;
			const initialSlide    = +$t.data('init-slide') || 0;
			const spaceBetween    = +$t.data('space') || 0;
			const speed           = +$t.data('speed') || 1500;
			const slidesPerColumn = +$t.data('slidespercolumn') || 1;
			const lazyAmountVar   = +$t.data('lazy-amount') || 3;
			const slidesPerView   = +$t.data('slidesperview') || 'auto';

			// object variables
			let breakpoints = {};

			if (lazyVar) {
				lazyVar = {
					loadPrevNext: false,
					loadPrevNextAmount: lazyAmountVar,
				};
			}

			let autoPlayObject = false;
			if (autoPlayVar) {
				autoPlayObject = {
					delay: autoPlayVar,
					waitForTransition: false,
					disableOnInteraction: false
				};
			}

			if (responsive) {
				const lg = $t.attr('data-lg-slides') ? $t.attr('data-lg-slides') : slidesPerView;
				const md = $t.attr('data-md-slides') ? $t.attr('data-md-slides') : lg;
				const sm = $t.attr('data-sm-slides') ? $t.attr('data-sm-slides') : md;
				const xs = $t.attr('data-xs-slides') ? $t.attr('data-xs-slides') : sm;

				const lg_col = $t.attr('data-lg-column') ? $t.attr('data-lg-column') : slidesPerColumn;
				const md_col = $t.attr('data-md-column') ? $t.attr('data-md-column') : lg_col;
				const sm_col = $t.attr('data-sm-column') ? $t.attr('data-sm-column') : md_col;
				const xs_col = $t.attr('data-xs-column') ? $t.attr('data-xs-column') : sm_col;

				const spaceLg = $t.attr('data-lg-space') ? $t.attr('data-lg-space') : spaceBetween;
				const spaceMd = $t.attr('data-md-space') ? $t.attr('data-md-space') : spaceLg;
				const spaceSm = $t.attr('data-sm-space') ? $t.attr('data-sm-space') : spaceMd;
				const spaceXs = $t.attr('data-xs-space') ? $t.attr('data-xs-space') : spaceSm;

				breakpoints = {
					767: {
						slidesPerView: parseInt(xs, 10),
						slidesPerColumn: parseInt(xs_col, 10),
						spaceBetween: parseInt(spaceXs, 10)
					},
					991: {
						slidesPerView: parseInt(sm, 10),
						slidesPerColumn: parseInt(sm_col, 10),
						spaceBetween: parseInt(spaceSm, 10)
					},
					1200: {
						slidesPerView: parseInt(md, 10),
						slidesPerColumn: parseInt(md_col, 10),
						spaceBetween: parseInt(spaceMd, 10)
					},
					1440: {
						slidesPerView: parseInt(lg, 10),
						slidesPerColumn: parseInt(lg_col, 10),
						spaceBetween: parseInt(spaceLg, 10)
					}
				};
			}

			swipers[index] = new Swiper('.' + index, {
				effect,
				grabCursor,
				noSwiping,
				initialSlide,
				spaceBetween,
				loop,
				mousewheel,
				speed,
				direction,
				centeredSlides,
				breakpoints,
				slidesPerView,
				slidesPerColumn,
				slideToClickedSlide,
				// ...
				pagination: {
					el: '.swiper-pagination-' + index,
					clickable: true,
					type: paginationType,
				},
				clickable: true,
				navigation: {
					nextEl: '.swiper-button-next-' + index,
					prevEl: '.swiper-button-prev-' + index,
					disabledClass: 'swiper-button-disabled',
				},
				fadeEffect: {
					crossFade: true
				},
				noSwipingClass: 'swiper-no-swiping',
				watchSlidesVisibility: true,
				autoplay: autoPlayObject,
				iOSEdgeSwipeDetection: true,
				autoHeight: heightVar,
				preloadImages: false,
				lazy: lazyVar,
				//
				parallax: true,
				on: {
					init: function () {

					},
					slideChange: function () {
						paginationScroll($t);
					},
					transitionStart: function () {

					},
					transitionEnd: function () {

					},
				},
			});

			let control = $t.data('control') ? $t.data('control') : false;

			if (control) {
				control = $('.' + control).attr('id') ? $('.' + control).attr('id') : '';

				if (control) {
					swipers[control].controller.control = swipers[index];
					swipers[index].controller.control   = swipers[control];
				}
			}

			swiperIteration++;
		});
	};

	/*=================================*/

	const paginationScroll = parent => {
		if (parent.data('pagination-scroll')) {
			const pagination = parent.parent().find('.swiper-pagination'),
				  active     = $(pagination).find('.swiper-pagination-bullet-active')[0];
			if (active) {
				$(pagination).animate({
					scrollTop: $(active).offset().top - $(pagination).offset().top + $(pagination).scrollTop()
				}, 500)
			}
		}
	};

	/*=================================*/

	const swiperEqualHeight = () => {
		$('.swiper-container[data-equal-height]').each(function () {
			const $t        = $(this);
			const eq_height = parseInt($t.data('equal-height'), 10);
			const id        = $t.attr('id');
			let max_height  = 0;

			if (eq_height) {
				$t.find('.swiper-slide').each(function () {
					const swiperHeight = $(this).css('height', 'auto').outerHeight();
					max_height         = (swiperHeight > max_height) ? swiperHeight : max_height;
				});

				$t.css('height', max_height);
				swipers[id].update();
			}
		});
	};

	const animatedBOX = (wrap, progress) => {
		const box = wrap.find('.cs-banner__box, .yuyu-banner__box');

		const scaleData = +box.data('scale') || 1;
		const offset    = +box.data('offset') || 0;
		const translate = progress * offset;
		const scale     = 1 - (1 - scaleData) * progress;

		box.css({
			'transform': `translateY(${translate}vh) scale(${scale})`,
		});
	};

	const animatedFooterLogo = (wrap, svg, progress) => {
		const logoDark  = wrap.find('.cs-footer__logo--dark');
		const logoLight = wrap.find('.cs-footer__logo--light');
		const path      = document.getElementById("path1");
		const svgT      = svg.offset().top;
		const svgW      = svg.outerWidth();

		if (logoDark.length && logoLight.length && path != null) {
			const pathH      = (path.getBBox().height * (1 - progress)) / 1922 * svgW;
			const svgB       = svgT + pathH;
			const logoT      = logoDark.offset().top;
			const logoH      = logoDark.outerHeight();
			let progressLogo = (svgB - logoT) / logoH;

			progressLogo = (progressLogo > 0) ? (progressLogo < 1) ? progressLogo : 1 : 0;

			logoDark.css({
				'clip-path': `polygon(0% 0%, 100% 0, 100% ${progressLogo * 100}%, 0% ${progressLogo * 100}%)`
			});
		}
	};

	const animatedSVG = (wrap, progress) => {
		const svg = wrap.find('svg');

		if (svg.length) {
			const scale = progress * -1 + 1;

			svg.css({
				'transform': `scaleY(${scale})`,
			});

			animatedFooterLogo(wrap, svg, progress);
		}
	};

	const animatedPATH = (wrap, progress) => {
		const paths = wrap.find('.js-path-change');

		if (paths.length) {
			paths.each(function () {
				const path        = $(this);
				const move        = +path.data('move') || 0;
				const opacityFrom = +path.data('opacity-from') || 0;
				const opacityTo   = +path.data('opacity-to') || 0;

				path.css({
					'transform': `translateX(${move * progress}%)`,
					'opacity': (opacityTo - opacityFrom) * progress + opacityFrom
				});
			});
		}
	};

	const animatedBanner = () => {
		const banners = $('.js-banner-animation');

		if (banners.length) {
			const offset  = 50;
			const headerH = $('.cs-header-t').outerHeight() + offset;
			const winS    = $(window).scrollTop();

			banners.each(function () {
				const banner  = $(this);
				const bannerH = banner.outerHeight() - headerH;
				const bannerT = banner.offset().top;

				let progress = (winS - bannerT) / bannerH;

				progress = (progress > 0) ? (progress < 1) ? progress : 1 : 0;

				animatedSVG(banner, progress);
				animatedPATH(banner, progress);
				animatedBOX(banner, progress);
			});
		}
	};

	const footerAnimation = () => {
		const footer = $('.js-footer-animation');

		if (footer.length) {
			const offset = 50;
			const winS   = $(window).scrollTop() + $(window).outerHeight() - offset;

			const footerH = footer.outerHeight() - offset < $(window).outerHeight() ? footer.outerHeight() - offset : $(window).outerHeight();
			const footerT = footer.offset().top;

			let progress = (footerT - winS) / footerH + 1;

			progress = (progress > 0) ? (progress < 1) ? progress : 1 : 0;

			animatedSVG(footer, progress);
			animatedPATH(footer, progress);
			animatedBOX(footer, progress);
		}
	};

	/*=================================*/
	/* ADD IMAGE ON BACKGROUND */

	/*=================================*/
	function wpc_add_img_bg(img_sel, parent_sel) {
		if (!img_sel) {
			return false;
		}

		var $parent, $imgDataHidden, _this;
		$(img_sel).not('.is-complete').each(function () {
			_this          = $(this);
			$imgDataHidden = _this.data('s-hidden');
			$parent        = _this.closest(parent_sel);
			$parent        = $parent.length ? $parent : _this.parent();
			$parent.css('background-image', 'url(' + this.src + ')').addClass('s-back-switch');
			if ($imgDataHidden) {
				_this.css('visibility', 'hidden');
				_this.show();
			}
			else {
				_this.hide();
			}
			_this.addClass('is-complete');
		});
	}

	/*=================================*/
	/* BLOG ISOTOPE */
	/*=================================*/
	const initBlogIsotope = () => {
		const $blog_isotope = $('.js-blog-masonry');
		if ($blog_isotope.length) {
			$blog_isotope.isotope({
				itemSelector: '.col-12',
			});

			$blog_isotope.imagesLoaded().progress(function () {
				$blog_isotope.isotope('layout');
			});
		}
	};

	/*=================================*/
	/* BLOG LOAD MORE */

	/*=================================*/
	function f_load_more_posts() {
		if (window.load_more_posts) {
			let nextLink = load_more_posts.nextLink;
			let pageNum  = +load_more_posts.current_page + 1;

			$('.js-load-more').on('click', function (e) {
				e.preventDefault();

				const $button    = $(this),
					  buttonText = $button.html();

				const container  = $button.data('container');
				const $container = $(container);

				if (!$container.length) {
					return;
				}

				$.ajax({
					url: nextLink,
					type: "get",
					beforeSend() {
						$button.text('Loading...');
					},
					success(data) {
						if (data) {
							const newElements = $(data).find(container + ' .col-12');

							$container.append(newElements);

							$container.isotope('appended', newElements);
							$container.isotope('layout');

							setTimeout(initSwiper, 500);
							videoPopup();

							wpc_add_img_bg('.s-img-switch');
							$('img[data-lazy-src]').foxlazy('', function () {
								$container.isotope('layout');
							});

							$container.imagesLoaded().progress(function () {
								$container.isotope('layout');
							});

							pageNum++;
							nextLink = nextLink.replace(/\/page\/[0-9]?/, '/page/' + pageNum);

							if (pageNum > load_more_posts.max_page) {
								$button.parent().remove();
							} else {
								$button.text(buttonText);
							}
						}
					},
				});
			});
		}
	}


	const parallaxElements = () => {
		const elemenents = $('.js-parallax-el');

		if (elemenents.length) {
			elemenents.each(function () {
				const el      = $(this);
				const speed   = +$(this).data('speed');
				const parent  = el.closest('.vc_custom_parallax');
				const parentT = parent.offset().top;

				const scrollT = $(window).scrollTop();
				const scrollB = scrollT + $(window).outerHeight();

				let progress = (scrollB - parentT) / parentT;

				progress *= speed;

				el.css({
					'transform': `translateY(${progress * 100}%)`
				})
			});
		}
	};

	/*=================================*/
	/* ACCORDION */
	/*=================================*/

	$('.js-accordion').on('click', function (e) {
		e.preventDefault();
		$(this).parent().toggleClass('is-open').find('.cs-accordion__text').slideToggle(350);

		if (!$(this).closest('.js-accordion-parent').data('multiple')) {
			$(this).parent().siblings().removeClass('is-open').find('.cs-accordion__text').slideUp(350);
		}
	});

	/*=================================*/
	/* COPYRIGHT */
	/*=================================*/
	if ($('.js-copyright').length) {
		$(document).on('contextmenu', (event) => {
			event.preventDefault();

			$('.js-copyright').addClass('is-active');
		}).on('click', () => {
			$('.js-copyright').removeClass('is-active');
		});
	}

	$('.js-services-load').on('click', function (e) {
		e.preventDefault();
		$(this).text($(this).attr('data-loading'));

		setTimeout(() => {
			$(this).closest('.cs-services-list').find('.hidden').removeClass('hidden');
			$(this).parent().remove();
		}, 750);
	});

	$('.js-slider-filter').on('click', function (e) {
		e.preventDefault();

		const filter    = $(this).data('filter') !== '*' ? $(this).data('filter') : '';
		const slider_id = $(this).closest('.js-filter-slider').find('.swiper-container').addClass('loading').attr('id');

		if (filter) {
			$(this).closest('.js-filter-slider').find('.swiper-slide').css('display', 'none');
			$(this).closest('.js-filter-slider').find('.swiper-slide' + filter).css('display', 'block');
		} else {
			$(this).closest('.js-filter-slider').find('.swiper-slide').css('display', 'block');
		}

		$(this).addClass('active').parent().siblings().find('a').removeClass('active');

		swipers[slider_id].update();
		swipers[slider_id].slideTo(0, 700, true);
		swipers[slider_id].lazy.load();

		setTimeout(() => {
			$(this).closest('.js-filter-slider').find('.swiper-container').removeClass('loading');
		}, 350);
	});


	/*=================================*/

	$(window).on('load', function () {
		wpc_add_img_bg('.s-img-switch');
		topBannerHeight();
		initSwiper();
		swiperEqualHeight();
		f_load_more_posts();
		videoPopup();

		counter();
		skill();
		initBlogIsotope();
		animatedBanner();
		footerAnimation();
		parallaxElements();
		if ($('.js-preloader').length) {
			$('.js-preloader').fadeOut(400);
		}
	});

	/*=================================*/

	$(window).on('resize', function () {
		swiperEqualHeight();
		topBannerHeight();
	});

	/*=================================*/

	$(window).on('scroll', function () {
		counter();
		skill();
		animatedBanner();
		footerAnimation();
		parallaxElements();
	});

	/*=================================*/

	window.addEventListener("orientationchange", function () {
		topBannerHeight();
		counter();
		skill();
		swiperEqualHeight();
		animatedBanner();
		footerAnimation();
		parallaxElements();
	});

	/*=================================*/

	document.addEventListener("mousemove", function (event) {
	});

})(jQuery, window, document);
;;(function ($) {
	'use strict';

	$('.cs-services-list__item').hover(function () {
		const $this = $(this);
		const parent = $this.closest('.cs-services-list');

		if (parent.hasClass('style-1')) {
			$this.addClass('is-active').parent().siblings().find('.cs-services-list__item').removeClass('is-active');
		}
	});

})(jQuery, window, document);
;;(function ($) {
	'use strict';

	const showcases = $('.js-showcase-animation').not('.is-animated');

	const showcaseAnimation = () => {
		if (showcases.length) {
			const offset = 0.15;
			const windowH = $(window).outerHeight();
			const scrollB = $(window).scrollTop() + windowH;

			showcases.each(function () {
				const $this = $(this);
				const thisTop = $this.offset().top + windowH * offset;

				if (thisTop < scrollB) {
					$this.addClass('is-animated');
				}
			});
		}
	};

	$(window).on('scroll load', function () {
		showcaseAnimation();
	});

})(jQuery, window, document);
