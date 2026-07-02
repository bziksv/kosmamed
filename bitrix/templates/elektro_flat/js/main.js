// $(document).on("mouseenter",".magic_slide_h",
//   function() {
//     $( this ).addClass( "hover" );
//   }, function() {
//     $( this ).removeClass( "hover" );
//   }
// );

function kmPrepareCatalogSlideImagesInSlick(slick) {
	if (!slick || !slick.$slides) {
		return;
	}
	$(slick.$slides).find('.magic_slide.item_img').each(function () {
		this.style.removeProperty('display');
		this.style.display = 'block';
		this.setAttribute('loading', 'eager');
	});
}

function kmSyncCatalogSlideIndicators($slider) {
	if (!$slider.length || !$slider.hasClass('slick-initialized')) {
		return;
	}
	var idx = $slider.slick('slickCurrentSlide');
	var $itemImage = $slider.closest('.item-image');
	$itemImage.find('.magic_slide_p div').removeClass('act');
	$itemImage.find('.magic_slide_p [data-sliderh="' + idx + '"]').addClass('act');
}

function kmBindCatalogSliderEvents($slider) {
	if ($slider.data('km-slick-bound')) {
		return;
	}
	$slider.on('init reInit afterChange', function (event, slick) {
		kmPrepareCatalogSlideImagesInSlick(slick);
	});
	$slider.on('afterChange reInit', function (event) {
		kmSyncCatalogSlideIndicators($(event.currentTarget));
	});
	$slider.data('km-slick-bound', true);
}

function kmGoToCatalogSlide($itemImage, index, dontAnimate) {
	var $slider = $itemImage.find('.magic_slide_ss.slick-initialized');
	if ($slider.length) {
		var slideIndex = parseInt(index, 10);
		if (isNaN(slideIndex)) {
			return false;
		}
		var current = $slider.slick('slickCurrentSlide');
		if (current === slideIndex) {
			return true;
		}
		$slider.slick('slickGoTo', slideIndex, dontAnimate === true);
		kmSyncCatalogSlideIndicators($slider);
		return true;
	}
	return false;
}

function kmCatalogSlideCount($itemImage) {
	var $slider = $itemImage.find('.magic_slide_ss.slick-initialized');
	if ($slider.length) {
		return $slider.find('.slick-slide:not(.slick-cloned)').length;
	}
	return $itemImage.find('.magic_slide_h').length;
}

function kmHoverIndexFromX($itemImage, clientX) {
	var el = $itemImage[0];
	if (!el) {
		return null;
	}
	var rect = el.getBoundingClientRect();
	if (rect.width <= 0) {
		return null;
	}
	var count = kmCatalogSlideCount($itemImage);
	if (count <= 1) {
		return null;
	}
	var ratio = Math.max(0, Math.min(0.999999, (clientX - rect.left) / rect.width));
	return Math.floor(ratio * count);
}

var kmCatalogHoverRaf = 0;
var kmCatalogHoverPending = null;

function kmFlushCatalogHoverSlide() {
	kmCatalogHoverRaf = 0;
	if (!kmCatalogHoverPending) {
		return;
	}
	var pending = kmCatalogHoverPending;
	kmCatalogHoverPending = null;
	var idx = kmHoverIndexFromX(pending.$itemImage, pending.clientX);
	if (idx !== null) {
		kmGoToCatalogSlide(pending.$itemImage, idx, true);
	}
}

$(document).on('mouseenter', '.catalog-item-card .item-image', function () {
	var card = this.closest('.catalog-item-card');
	kmRestoreDeferredImages(card || this);
});

$(document).on('mousemove', '.catalog-item-card .item-image', function (e) {
	if ($(e.target).closest('.slick-dots').length) {
		return;
	}
	kmCatalogHoverPending = {
		$itemImage: $(this),
		clientX: e.clientX
	};
	if (!kmCatalogHoverRaf) {
		kmCatalogHoverRaf = window.requestAnimationFrame(kmFlushCatalogHoverSlide);
	}
});

$(document).on('mouseleave', '.catalog-item-card .item-image', function () {
	kmCatalogHoverPending = null;
	if (kmCatalogSlideCount($(this)) > 1) {
		kmGoToCatalogSlide($(this), 0, true);
	}
});

$(document).on('click', '.catalog-item-card .magic_slide_h', function (e) {
	e.preventDefault();
	e.stopPropagation();
	kmGoToCatalogSlide($(this).parents('.item-image'), $(this).data('sliderh'), true);
});

$(document).on('click', '.catalog-item-card .magic_slide_p div', function (e) {
	e.preventDefault();
	e.stopPropagation();
	kmGoToCatalogSlide($(this).parents('.item-image'), $(this).data('sliderh'));
});

$(document).on('click', '.catalog-item-card .item-image .magic_slide_ss .slick-dots, .catalog-item-card .item-image .magic_slide_ss .slick-list', function (e) {
	e.stopPropagation();
});

// $( ".magic_slide_h" ).hover(
//   function() {
//     var id = $( this ).data('sliderh');
//     $( this ).parents('.item-image').find( ".magic_slide" ).hide();
//     $( this ).parents('.item-image').find('[data-slider='+id+']').show();
//   }, function() {
//   	// $( this ).parents('.item-image').find( ".magic_slide" ).hide();
//    //  $( this ).parents('.item-image').find('[data-slider=0]').show();
//   }
// );

// $(document).on({
//     mouseenter: function() {
//     var id = $( this ).data('sliderh');
//     $( this ).parents('.item-image').find( ".magic_slide" ).hide();
//     $( this ).parents('.item-image').find('[data-slider='+id+']').show();
//     },
//     mouseleave: function() {
//   	// $( this ).parents('.item-image').find( ".magic_slide" ).hide();
//    //  $( this ).parents('.item-image').find('[data-slider=0]').show();
//     }
// }, '.touch');

function fixCatalogDetailGallery() {
	if (!$('.catalog-detail-element').length) {
		return;
	}
	var $main = $('.detail_picture_pa');
	var $nav = $('.more_photo ul');
	if ($main.hasClass('slick-initialized')) {
		$main.slick('setPosition');
	}
	if ($nav.hasClass('slick-initialized')) {
		$nav.slick('setPosition');
	}
}

function kmRestoreDeferredImages(root) {
	var scope = root ? (root.jquery ? root[0] : root) : document;
	if (!scope || !scope.querySelectorAll) {
		return;
	}
	if (typeof window.kmLoadDeferredImages === 'function') {
		window.kmLoadDeferredImages(scope);
		return;
	}
	scope.querySelectorAll('source[data-km-srcset]').forEach(function (el) {
		var srcset = el.getAttribute('data-km-srcset');
		if (srcset) {
			el.setAttribute('srcset', srcset);
			el.removeAttribute('data-km-srcset');
		}
	});
	scope.querySelectorAll('img[data-km-src]').forEach(function (img) {
		var src = img.getAttribute('data-km-src');
		if (src && (!img.src || img.src.indexOf('data:image/gif') !== -1)) {
			img.src = src;
			img.removeAttribute('data-km-src');
		}
	});
}

function kmInitCatalogDeferredImages() {
	if (!('IntersectionObserver' in window)) {
		kmRestoreDeferredImages(document);
		return;
	}
	if (!window.__kmCatalogDeferredIo) {
		window.__kmCatalogDeferredIo = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					kmRestoreDeferredImages(entry.target);
					window.__kmCatalogDeferredIo.unobserve(entry.target);
				}
			});
		}, { rootMargin: '400px 0px' });
	}

	document.querySelectorAll('.catalog-item-card').forEach(function (card) {
		if (card.__kmDeferredObserved) {
			return;
		}
		if (card.querySelector('img[data-km-src], source[data-km-srcset]')) {
			window.__kmCatalogDeferredIo.observe(card);
			card.__kmDeferredObserved = true;
		}
	});
}

/** Слайдер фото в карточках каталога — на всех ширинах экрана. */
function initCatalogCardSliders() {
	function kmPrepareCatalogSlideImages($slider) {
		kmRestoreDeferredImages($slider.closest('.catalog-item-card')[0] || $slider[0]);
		$slider.find('.magic_slide.item_img').each(function () {
			this.style.removeProperty('display');
			this.style.display = 'block';
			this.setAttribute('loading', 'eager');
		});
	}

	$('.catalog-item-card .item-image .magic_slide_ss:not(.slick-slider)').each(function () {
		var $slider = $(this);
		kmPrepareCatalogSlideImages($slider);
		kmBindCatalogSliderEvents($slider);
		$slider.slick({
			dots: true,
			arrows: false,
			infinite: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			speed: 0,
			waitForAnimate: false,
			cssEase: 'linear'
		});
		$slider.slick('setPosition');
		kmSyncCatalogSlideIndicators($slider);
	});

	$('.catalog-item-card .item-image .magic_slide_ss.slick-slider').each(function () {
		var $slider = $(this);
		kmPrepareCatalogSlideImages($slider);
		kmBindCatalogSliderEvents($slider);
		$slider.slick('setPosition');
		kmSyncCatalogSlideIndicators($slider);
	});
}

$(function(){
	$nav = $('header');

	$window = $(window);
	$h = $nav.offset().top;

	if($h<50){
		var fix_b = 'fix_b';
		$("body").removeClass('fix_bb');
	}else{
		var fix_b = 'fix_bb';
		$("body").removeClass('fix_b');
	}


	$h = 80;


	$window.scroll(function(){
		if ($window.scrollTop() > $h){
			$nav.addClass('fixed');
			$nav.addClass('fixed_f');
			$("body").addClass('km-header-stuck').removeClass(fix_b);
		} else {
			//$nav.removeClass('fixed');
			$nav.removeClass('fixed_f');
			$("body").removeClass('km-header-stuck').addClass(fix_b);
		}
	});
});
$(document).on("click",".geolocation-delivery__title a span", function () {
	$( ".geolocation__value" ).trigger( "click" );
	return false;
});
$(document).on("click",".catalog_more_section", function () {
    $('.catalog-section').toggleClass('o_h_s');
            if ($(this).is(':hidden')) {
                $('.catalog_more_section .btn_buy').html('Показать ещё');
            } else {
                $('.catalog_more_section .btn_buy').html('Скрыть');
            }   
});

// $(document).on("click", ".more_photo li", function () {
//   $(".more_photo li").removeClass('active');
//   $(this).addClass("active").parents('.catalog-detail-pictures').find('.detail_picture').find('.catalog-detail-images').hide().eq($(this).index()).fadeIn(150);
//   return false;
// });


$(document).on("click", ".product-item-preview-full-more", function () {

    $('.catalog-item-card').removeClass("act");
    $(this).parents('.catalog-item-card').addClass("act");
});

$(document).on("click", ".product-item-preview-full-hide", function () {
    //$('.catalog-item-card').removeClass("act");



    $(".product-item-preview-full").animate({top: "255"}, "fast", function() {
        $('.catalog-item-card').removeClass("act");
        $(".product-item-preview-full").removeAttr("style");
    });

});


function getSliderSettings(){
  return {
            dots: false,
            arrows: false,
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1
  }
}



/** Вкладки «Рекомендуем / Новинки / …» на главной — вызывать до slick, иначе при ошибке слайдера блок остаётся display:none. */
function kmInitHomeTabs() {
	var $wrap = $('.tabs-wrap.tabs-main');
	if (!$wrap.length) {
		return;
	}
	if ($('.tabs__box.new .filtered-items').length < 1) {
		$('.tabs__tab.new, .tabs__box.new').remove();
	}
	if ($('.tabs__box.hit .filtered-items').length < 1) {
		$('.tabs__tab.hit, .tabs__box.hit').remove();
	}
	if ($('.tabs__box.discount .filtered-items').length < 1) {
		$('.tabs__tab.discount, .tabs__box.discount').remove();
	}
	var $tabs = $wrap.find('.tabs__tab');
	var $boxes = $wrap.children('.tabs__box');
	if (!$tabs.length || !$boxes.length) {
		return;
	}
	$tabs.removeClass('current').first().addClass('current');
	$boxes.hide().first().css('display', 'block');
}

$(function() {

	kmInitHomeTabs();

	$nav = $('header');

	$window = $(window);
	$h = $nav.offset().top;

	if($h<50){
		var fix_b = 'fix_b';
		$("body").removeClass('fix_bb');
	}else{
		var fix_b = 'fix_bb';
		$("body").removeClass('fix_b');
	}
	$("body").addClass(fix_b);

	BX.addCustomEvent('onAjaxSuccess', function() {
		$('input[autocomplete="tel"]').inputmask({"mask": "+7 (999) 999-9999"});
	});
	
	//SCROLL_UP//
	var top_show = 150,
		delay = 500;
	$("body").append($("<a />").addClass("scroll-up").attr({"href": "javascript:void(0)", "id": "scrollUp"}).append($("<i />").addClass("fa fa-angle-up")));
	$("#scrollUp").click(function(e) {
		e.preventDefault();
		$("body, html").animate({scrollTop: 0}, delay);
		return false;
    });	

    $("[data-hide-text]").each(function(i, el){
        let self = $(el);
        self.html(self.attr("data-hide-text"))
    });	

    $("[data-replace-content]").each(function(i, el){
        let self = $(el);

        self.replaceWith('<a>' + self.html() +'</a>')
    });
    $("[data-href-content]").each(function(i, el){
        let self = $(el);
        self.parents('a').attr("href", self.attr("data-href-content"))
    });	


// $( ".magic_slide_h" ).hover(
//   function() {
//     var id = $( this ).data('sliderh');
//     $( this ).parents('.item-image').find( ".magic_slide" ).hide();
//     $( this ).parents('.item-image').find('[data-slider='+id+']').show();
//   }, function() {
//   	// $( this ).parents('.item-image').find( ".magic_slide" ).hide();
//    //  $( this ).parents('.item-image').find('[data-slider=0]').show();
//   }
// );

    $('.tag-slider').slick({
        dots: false,
        arrows: true,
        infinite: true,
        autoplay: true,
        variableWidth: true,
        centerMode: true,
        slidesToShow: 2,
        responsive: [
            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

    if (window.innerWidth < 1253) {


	    $('.detail_picture_pa').slick({
		    slidesToShow: 1,
		    slidesToScroll: 1,
	        dots: false,
	        arrows: true,
		    fade: true,
		    asNavFor: '.more_photo ul'
	    });
	    $('.more_photo ul').slick({
	        slidesToShow: 4,
	        slidesToScroll: 1,
	        arrows: false,
	        asNavFor: '.detail_picture_pa',
	        //centerMode: true,
	        focusOnSelect: true
	    });	  

	    // $('.more_photo ul').slick({
	    //     dots: false,
	    //     arrows: false,
	    //     infinite: false,
	    //     slidesToShow: 4,
	    //     slidesToScroll: 4
	    // });


	}else{
	    $('.detail_picture_pa').slick({
		    slidesToShow: 1,
		    slidesToScroll: 1,
	        dots: false,
	        arrows: true,
		    fade: true,
		    asNavFor: '.more_photo ul'
	    });
	    var $detailThumbs = $('.more_photo ul');
	    var detailThumbCount = Math.max(1, Math.min($detailThumbs.children('li').length, 8));
	    $detailThumbs.slick({
	        slidesToShow: detailThumbCount,
	        slidesToScroll: 1,
	        arrows: false,
	        asNavFor: '.detail_picture_pa',
	        focusOnSelect: true
	    });	    

    // $('.detail_picture_pa').slick({
    // slidesToShow: 1,
    // slidesToScroll: 1,
    // arrows: true,
    // fade: true,
    // asNavFor: '.more_photo ul'
    // });
    // $('.more_photo ul').slick({
    // slidesToShow: 3,
    // slidesToScroll: 1,
    // asNavFor: '.detail_picture_pa',
    // dots: true,
    // centerMode: true,
    // focusOnSelect: true
    // });

	}

	initCatalogCardSliders();
	kmInitCatalogDeferredImages();
	fixCatalogDetailGallery();
	$(window).on('load resize', function () {
		initCatalogCardSliders();
		kmInitCatalogDeferredImages();
		fixCatalogDetailGallery();
	});
	setTimeout(fixCatalogDetailGallery, 150);
	setTimeout(fixCatalogDetailGallery, 600);
	requestAnimationFrame(fixCatalogDetailGallery);

    $(document).ready(function() {

	$(".tabs__tab").click(function(){
		setTimeout(function(){
			initCatalogCardSliders();
		}, 0);
	});

        $(".subcategories .open").click(function(){
            $(this).hide();
            $(".subcategories .close").show();
            $(".subcategories .sub-links-2").addClass("open");
            $('.tag-slider').slick('unslick');
        });
        $(".subcategories .close").click(function(){
            $(this).hide();
            $(".subcategories .open").show();
            $(".subcategories .sub-links-2").removeClass("open");
            $('.tag-slider').slick({
                dots: false,
                arrows: true,
                infinite: true,
                autoplay: true,
                variableWidth: true,
                centerMode: true,
                slidesToShow: 3,
            });
        });
    });





	
	$(window).scroll(function () {
		if($(this).scrollTop() > top_show) {
			$("#scrollUp").fadeIn();
		} else {
			$("#scrollUp").fadeOut();
		}
    });

	//DISABLE_FORM_SUBMIT_ENTER//
	$(".add2basket_form").on("keyup keypress", function(e) {
		var keyCode = e.keyCode || e.which;
		if(keyCode === 13) {
			e.preventDefault();
			return false;
		}
	});

	//CALLBACK//
	var callbackBtn = BX("callbackAnch");
	if(!!callbackBtn)
		BX.bind(callbackBtn, "click", BX.delegate(function(){openFormCallback();}, this));

	//BTN_ANIMATION
    setInterval( BX.delegate(function () {
        openbtn();
    }, this), 5000);

	//TOP_PANEL_CONTACTS//
	$(".showcontacts").click(function(e) {
		var href = $(this).attr("href") || "";
		if (href.indexOf("tel:") === 0) {
			return true;
		}
		var clickitem = $(this);
		if(clickitem.parent("li").hasClass("")) {
			clickitem.parent("li").addClass("active");
		} else {
			clickitem.parent("li").removeClass("active");
		}
		if($(".showsection").parent("li").hasClass("active")) {
			$(".showsection").parent("li").removeClass("active");
			$(".showsection").parent("li").find(".catalog-section-list").css({"display":"none"});
		}
		if($(".showsubmenu").parent("li").hasClass("active")) {
			$(".showsubmenu").parent("li").removeClass("active");
			$(".showsubmenu").parent("li").find("ul.submenu").css({"display":"none"});
		}
		if($(".showsearch").parent("li").hasClass("active")) {
			$(".showsearch").parent("li").removeClass("active");
			$(".header_2").css({"display":"none"});
			$(".title-search-result").css({"display":"none"});
		}
		$(".header_4").slideToggle();
	});
	
	//TOP_PANEL_SEARCH//
	$(".showsearch").click(function(e) {
		e.preventDefault();
		var clickitem = $(this);
		var $li = clickitem.parent("li");
		var $header2 = $("header .header_2");
		var opening = !$li.hasClass("active");

		if($(".showsection").parent("li").hasClass("active")) {
			$(".showsection").parent("li").removeClass("active");
			$(".showsection").parent("li").find(".catalog-section-list").css({"display":"none"});
		}
		if($(".showsubmenu").parent("li").hasClass("active")) {
			$(".showsubmenu").parent("li").removeClass("active");
			$(".showsubmenu").parent("li").find("ul.submenu").css({"display":"none"});
		}
		if($(".showcontacts").parent("li").hasClass("active")) {
			$(".showcontacts").parent("li").removeClass("active");
			$(".header_4").css({"display":"none"});
		}

		$li.toggleClass("active", opening);
		$("body").toggleClass("km-mobile-search-open", opening);
		if(opening) {
			$header2.stop(true, true).slideDown(200, function() {
				var $input = $("#altop_search input[type=\"text\"], #altop_search input[name=\"q\"]").first();
				if($input.length) {
					$input.trigger("focus");
				}
			});
		} else {
			$header2.stop(true, true).slideUp(200);
			$(".title-search-result").css({"display":"none"});
		}
	});
	
	//CHANGE_TAB//
	var kmItemHeightViewportHandler = null;

	function bindItemHeightResize(itemsTable) {
		if (!itemsTable || !itemsTable.length) {
			return;
		}
		var syncHeights = function() {
			if (typeof adjustItemHeight === 'function') {
				adjustItemHeight(itemsTable);
			}
		};
		$(window).off("resize.kmItemHeight orientationchange.kmItemHeight");
		$(window).on("resize.kmItemHeight orientationchange.kmItemHeight", syncHeights);
		if (window.visualViewport) {
			if (kmItemHeightViewportHandler) {
				window.visualViewport.removeEventListener("resize", kmItemHeightViewportHandler);
			}
			kmItemHeightViewportHandler = syncHeights;
			window.visualViewport.addEventListener("resize", kmItemHeightViewportHandler);
		}
		syncHeights();
	}

	var itemsTable = $(".filtered-items:visible .catalog-item-card");
	bindItemHeightResize(itemsTable);
	
	//CHANGE_TAB//
	$("body").on("click", ".tabs__tab:not(.current)", function() {
		var $box = $(this).parent().siblings(".tabs__box").eq($(this).index());
		$(this).addClass("current").siblings().removeClass("current")
			.parent().siblings(".tabs__box").eq($(this).index()).fadeIn(150).siblings(".tabs__box").hide();

		if (window.kmLoadDeferredImages && $box.length) {
			window.kmLoadDeferredImages($box[0]);
		}
		
		bindItemHeightResize($box.find(".catalog-item-card"));
	});
	
	//DELAY//
	var currPage = window.location.pathname;
	var delayIndex = window.location.search;
	if((currPage == "/personal/cart/") && (document.getElementById("id-shelve-list")) && (delayIndex == "?delay=Y")) {
		$("#id-shelve-list").show();
		$("#id-cart-list").hide();
	} else {
		$("#id-shelve-list").hide();
		$("#id-cart-list").show();
	}
	
	//CUSTOM_FORMS//
	$(".custom-forms").customForms({});


//CATALOG_MENU_HIDDEN//
    var flag=1;
    $("#catalog_wrap_btn").click(function() {
   
        $("#catalog_wrap").slideToggle("slow");
       if(flag==0){
	       flag=1;    
            $("#catalog_wrap_btn .showfilter .fa-angle-down").css({"display":"block"});
            $("#catalog_wrap_btn .showfilter .fa-angle-up").css({"display":"none"});
	   }
        else{
		 flag=0;
            $("#catalog_wrap_btn .showfilter .fa-angle-down").css({"display":"none"});
            $("#catalog_wrap_btn .showfilter .fa-angle-up").css({"display":"block"});     
     	}
    });	

	$(document).ready(function () {	

		let count_show_order = $('[data-order-show]').data('order-show');

		//console.log(count_show_order);

		let order_show = 0;
		$('[data-order-show]').each(function( index ) {
			if(count_show_order>order_show){
				$(this).removeClass('show_order');
				$(this).html('Под заказ');
			}
			order_show++;
		});

		let more_photo = 0;
		$('.more_photo ul li').each(function( index ) {
			more_photo++;
		});
		if(more_photo<5){
			$('.more_photo ul').addClass('fix_li');
		}

	});

	function refreshAnythingSliders() {
		$(".anythingBase").each(function() {
			var sliderData = $(this).data("AnythingSlider");
			if (!sliderData) {
				return;
			}
			sliderData.setDimensions();
			sliderData.$el.trigger("slideshow_resized", sliderData);
		});
	}

	var kmSliderResizeTimer = null;
	function scheduleAnythingSliderRefresh() {
		clearTimeout(kmSliderResizeTimer);
		kmSliderResizeTimer = setTimeout(refreshAnythingSliders, 120);
	}

	$(window).on("resize.kmSliders orientationchange.kmSliders", scheduleAnythingSliderRefresh);
	if (window.visualViewport) {
		window.visualViewport.addEventListener("resize", scheduleAnythingSliderRefresh);
	}

});
