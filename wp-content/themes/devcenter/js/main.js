<<<<<<< HEAD
jQuery(document).ready(function($) {
	
	$('#tutorials-nav a').dotdotdot({
		ellipsis	: '... ',
		wrap		: 'word',
		fallbackToLetter: true,
		after		: null,
		watch		: false,
		height		: null,
		tolerance	: 0,
		callback	: function( isTruncated, orgContent ) {
			//...
		},
		lastCharacter	: {
			remove		: [ ' ', ',', ';', '.', '!', '?' ],
			noEllipsis	: []
		}
	});

	$('.horizontal-tab-nav a').click(function() {
		var dataId = $(this).attr('data-id');
		$('.horizontal-tab-nav a').removeClass('active');
		$(this).addClass('active');
		$('.horizontal-tab').hide();
		$('.horizontal-tab.tab-' + dataId).fadeIn();
	});

	$('.tabs-menu.tab-menu-inputs li').first().addClass('current');
	$('.tabs-menu.tab-menu-events li').first().addClass('current');

	$(".tabs-menu a").click(function(event) {
        event.preventDefault();
        $(this).parent().addClass("current");
        $(this).parent().siblings().removeClass("current");
        var tab = $(this).attr("href");
        $(".tab-content").not(tab).css("display", "none");
        $(tab).fadeIn();
    });

    $('.home-link').click(function() {
    	window.location = '/portal';
    });
=======
jQuery(document).ready(function($) {
	
	$('#tutorials-nav a').dotdotdot({
		ellipsis	: '... ',
		wrap		: 'word',
		fallbackToLetter: true,
		after		: null,
		watch		: false,
		height		: null,
		tolerance	: 0,
		callback	: function( isTruncated, orgContent ) {
			//...
		},
		lastCharacter	: {
			remove		: [ ' ', ',', ';', '.', '!', '?' ],
			noEllipsis	: []
		}
	});

	$('.horizontal-tab-nav a').click(function() {
		var dataId = $(this).attr('data-id');
		$('.horizontal-tab-nav a').removeClass('active');
		$(this).addClass('active');
		$('.horizontal-tab').hide();
		$('.horizontal-tab.tab-' + dataId).fadeIn();
	});

	$('.tabs-menu.tab-menu-inputs li').first().addClass('current');
	$('.tabs-menu.tab-menu-events li').first().addClass('current');

	$(".tabs-menu a").click(function(event) {
        event.preventDefault();
        $(this).parent().addClass("current");
        $(this).parent().siblings().removeClass("current");
        var tab = $(this).attr("href");
        $(".tab-content").not(tab).css("display", "none");
        $(tab).fadeIn();
    });

    $('.home-link').click(function() {
    	window.location = '/portal';
    });
>>>>>>> ccc08261a72e6f9daab5a1f686985eb43716cb98
}); // End document ready