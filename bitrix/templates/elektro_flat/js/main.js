// $(document).on("mouseenter",".magic_slide_h",
//   function() {
//     $( this ).addClass( "hover" );
//   }, function() {
//     $( this ).removeClass( "hover" );
//   }
// );

$(document).on({
    mouseenter: function () {
	    var id = $( this ).data('sliderh');
	    $( this ).parents('.item-image').find('.magic_slide_p div').removeClass('act');
	    $( this ).parents('.item-image').find('[data-sliderh='+id+']').addClass('act');
	    $( this ).parents('.item-image').find( ".magic_slide" ).hide();
	    $( this ).parents('.item-image').find('[data-slider='+id+']').show();
    },
    mouseleave: function () {
  	// $( this ).parents('.item-image').find( ".magic_slide" ).hide();
   //  $( this ).parents('.item-image').find('[data-slider=0]').show();
    }
}, '.magic_slide_h');

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



$(function() {

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


	    $('.item-image .magic_slide_ss').slick({
	        dots: true,
	        //dots: false,
	        arrows: false,
	        infinite: false,
	        slidesToShow: 1,
	        slidesToScroll: 1
	    });

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

	fixCatalogDetailGallery();
	$(window).on('load resize', fixCatalogDetailGallery);
	setTimeout(fixCatalogDetailGallery, 150);
	setTimeout(fixCatalogDetailGallery, 600);
	requestAnimationFrame(fixCatalogDetailGallery);

    $(document).ready(function() {

	if (window.innerWidth < 1253) {

		$(".tabs__tab").click(function(){
			setTimeout(function(){
				$('.item-image .magic_slide_ss').slick('setPosition');
 			},0);
		});
	}

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
	
	//TABS_MAIN//
	if($(".tabs__box.new .filtered-items").length < 1)
		$(".tabs__tab.new, .tabs__box.new").remove();
	if($(".tabs__box.hit .filtered-items").length < 1)
		$(".tabs__tab.hit, .tabs__box.hit").remove();
	if($(".tabs__box.discount .filtered-items").length < 1)
		$(".tabs__tab.discount, .tabs__box.discount").remove();
	
	$(".tabs-main .tabs__tab").first().addClass("current");	
	$(".tabs-main .tabs__box").first().css({"display":"block"});

	//ITEMS_HEIGHT//
	var kmItemHeightViewportHandler = null;

	function bindItemHeightResize(itemsTable) {
		if (!itemsTable || !itemsTable.length) {
			return;
		}
		var syncHeights = function() {
			adjustItemHeight(itemsTable);
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
		$(this).addClass("current").siblings().removeClass("current")
			.parent().siblings(".tabs__box").eq($(this).index()).fadeIn(150).siblings(".tabs__box").hide();
		
		bindItemHeightResize($(this).parent().siblings(".tabs__box").eq($(this).index()).find(".catalog-item-card"));
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
