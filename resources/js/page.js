// https://stackoverflow.com/a/488073
function isWithinView(e, full) {
    var pageTop = $(window).scrollTop();
    var pageBottom = pageTop + $(window).height();
    var elementTop = e.offset().top;
    var elementBottom = elementTop + e.height();

    if (full === true) {
        return pageTop < elementTop && pageBottom > elementBottom;
    } else {
        return elementTop <= pageBottom && elementBottom >= pageTop;
    }
}

function getHiddenMeta(meta) {
    return $("span#hiddenpage" + meta).html();
}

function isElementHTMLEmpty(element) {
    return element.html().length === 0 || element.html().indexOf("<br") === 0;
}

var showingInfobox = true;

$(document).ready(function () {
    var infobox = $("table.infobox");
    $(window).scroll(function () {
        onWindowScroll();
        if (!isWithinView(infobox, false)) {
            if (showingInfobox) {
                // not visible, but still showing, so hide it
                infobox.hide();
                showingInfobox = false;
            } else {
                // not visible, not showing, all good
            }
        } else {
            if (!showingInfobox) {
                // visible, but not showing, so show it
                infobox.show();
                showingInfobox = true;
            } else {
                // visible, showing, all good
            }
        }
    });
});
