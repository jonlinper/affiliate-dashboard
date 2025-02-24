(function ($) {
    "use strict";
    $(function () {
        var body = $("body");
        var contentWrapper = $(".content-wrapper");
        var scroller = $(".container-scroller");
        var footer = $(".footer");
        var sidebar = $(".sidebar");

        //Close other submenu in sidebar on opening any
        sidebar.on("show.bs.collapse", ".collapse", function () {
            sidebar.find(".collapse.show").collapse("hide");
        });

        //Change sidebar and content-wrapper height
        applyStyles();

        function applyStyles() {
            //Applying perfect scrollbar
            if (!body.hasClass("rtl")) {
                if ($(".settings-panel .tab-content .tab-pane.scroll-wrapper").length) {
                    const settingsPanelScroll = new PerfectScrollbar(".settings-panel .tab-content .tab-pane.scroll-wrapper");
                }
                if ($(".chats").length) {
                    const chatsScroll = new PerfectScrollbar(".chats");
                }
                if (body.hasClass("sidebar-fixed")) {
                    if ($("#sidebar").length) {
                        var fixedSidebarScroll = new PerfectScrollbar("#sidebar .nav");
                    }
                }
            }
        }

        $('[data-bs-toggle="minimize"]').on("click", function () {
            if (body.hasClass("sidebar-toggle-display") || body.hasClass("sidebar-absolute")) {
                body.toggleClass("sidebar-hidden");
            } else {
                body.toggleClass("sidebar-icon-only");
            }
        });

        //checkbox and radios
        $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');

        //Horizontal menu in mobile
        $('[data-toggle="horizontal-menu-toggle"]').on("click", function () {
            $(".horizontal-menu .bottom-navbar").toggleClass("header-toggled");
        });
        // Horizontal menu navigation in mobile menu on click
        var navItemClicked = $(".horizontal-menu .page-navigation >.nav-item");
        navItemClicked.on("click", function (event) {
            if (window.matchMedia("(max-width: 991px)").matches) {
                if (!$(this).hasClass("show-submenu")) {
                    navItemClicked.removeClass("show-submenu");
                }
                $(this).toggleClass("show-submenu");
            }
        });

        $(window).scroll(function () {
            if (window.matchMedia("(min-width: 992px)").matches) {
                var header = $(".horizontal-menu");
                if ($(window).scrollTop() >= 70) {
                    $(header).addClass("fixed-on-scroll");
                } else {
                    $(header).removeClass("fixed-on-scroll");
                }
            }
        });
        if ($.cookie("staradmin2-free-banner") != "true") {
            document.querySelector("#proBanner").classList.add("d-flex");
            document.querySelector(".navbar").classList.remove("fixed-top");
        } else {
            document.querySelector("#proBanner").classList.add("d-none");
            document.querySelector(".navbar").classList.add("fixed-top");
        }

        if ($(".navbar").hasClass("fixed-top")) {
            document.querySelector(".page-body-wrapper").classList.remove("pt-0");
            document.querySelector(".navbar").classList.remove("pt-5");
        } else {
            document.querySelector(".page-body-wrapper").classList.add("pt-0");
            document.querySelector(".navbar").classList.add("pt-5");
            document.querySelector(".navbar").classList.add("mt-3");
        }
    });
})(jQuery);
